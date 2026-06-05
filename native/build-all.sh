#!/usr/bin/env bash
#
# Cross-compile the md4c FFI shim for EVERY supported platform and drop the
# results into lib/<platform>/ so they can be committed and SHIPPED with the
# package — users then need no C compiler at all.
#
# Produces:
#   lib/darwin/libmd4cshim.dylib          (universal: arm64 + x86_64)
#   lib/linux-x86_64/libmd4cshim.so       (glibc)
#   lib/linux-aarch64/libmd4cshim.so      (glibc)
#   lib/windows-x86_64/md4cshim.dll
#
# macOS slices are built with the native clang (universal binary). The Linux
# and Windows targets are cross-compiled with `zig cc`, which bundles the
# toolchains — so this whole script runs from a single macOS or Linux host.
#
# Requirements: clang (for the macOS universal slice) and/or zig (for the rest).
set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$DIR/.." && pwd)"
cd "$DIR"

SOURCES=(shim.c md4c/md4c.c md4c/md4c-html.c md4c/entity.c)
LIBDIR="$ROOT/lib"

symbols() {
  # Portable "did the 4 entry points make it in?" check.
  local f="$1"
  if command -v nm >/dev/null 2>&1; then
    local n
    n=$(nm "$f" 2>/dev/null | grep -c -E 'md2html(_free|_dialect_github|_batch)?$' || true)
    echo "      $n md2html* symbols"
  fi
}

build_macos_universal() {
  local out="$LIBDIR/darwin/libmd4cshim.dylib"
  mkdir -p "$(dirname "$out")"
  echo "==> macOS universal (arm64+x86_64) via clang"
  clang -O3 -fPIC -shared -DNDEBUG -pthread \
    -arch arm64 -arch x86_64 \
    -o "$out" "${SOURCES[@]}"
  command -v lipo >/dev/null 2>&1 && echo "      arches: $(lipo -archs "$out")"
  symbols "$out"
}

# zig cc cross-build. $1 = zig target triple, $2 = output path, $3.. = extra flags
# `-Wl,-s` strips the symbol/debug tables at link time; the dynamic-symbol and
# PE export tables (what FFI actually needs) survive, so the artifact stays small.
build_zig() {
  local target="$1" out="$2"; shift 2
  mkdir -p "$(dirname "$out")"
  echo "==> $target via zig cc -> ${out#"$ROOT"/}"
  zig cc -target "$target" -O3 -fPIC -shared -DNDEBUG -Wl,-s "$@" \
    -o "$out" "${SOURCES[@]}"
  symbols "$out"
}

echo "Cross-compiling into $LIBDIR"
echo

if command -v clang >/dev/null 2>&1 && [ "$(uname -s)" = "Darwin" ]; then
  build_macos_universal
else
  echo "==> skipping macOS universal slice (not on macOS / no clang)"
fi

if command -v zig >/dev/null 2>&1; then
  # -pthread on the POSIX targets enables the md2html_batch thread pool.
  build_zig x86_64-linux-gnu   "$LIBDIR/linux-x86_64/libmd4cshim.so"   -pthread
  build_zig aarch64-linux-gnu  "$LIBDIR/linux-aarch64/libmd4cshim.so"  -pthread
  # Windows: the shim compiles the batch path sequentially (no pthreads).
  build_zig x86_64-windows-gnu "$LIBDIR/windows-x86_64/md4cshim.dll"
else
  echo "!! zig not found — Linux/Windows libs were NOT built."
  echo "   Install it (brew install zig / https://ziglang.org) and re-run."
fi

# Drop build by-products we don't ship (debug db + import lib from the Win build).
find "$LIBDIR" -type f \( -name '*.pdb' -o -name '*.lib' -o -name '*.exp' \) -delete 2>/dev/null || true

echo
echo "==> Done. Shipped libraries:"
find "$LIBDIR" -type f \( -name '*.so' -o -name '*.dylib' -o -name '*.dll' \) | sort | sed "s#^$ROOT/#      #"
