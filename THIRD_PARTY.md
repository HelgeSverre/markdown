# Third-party software

This project bundles and redistributes third-party code — in source form under
`native/md4c/` and as compiled shared libraries under `lib/`.

## md4c

- **Author:** Martin Mitáš
- **License:** MIT — see [`native/md4c/LICENSE.md`](native/md4c/LICENSE.md)
- **Upstream:** <https://github.com/mity/md4c> (release-0.5.2)
- **Role:** does the actual CommonMark/GFM parsing. [`native/shim.c`](native/shim.c)
  is a thin FFI-friendly wrapper around it, and every shipped `lib/**` binary is
  md4c compiled together with that shim.

The MIT license requires its copyright notice travel with redistributions, which
is why md4c's source and `LICENSE.md` ship in this repository.
