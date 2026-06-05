<?php
$lib = dirname(__DIR__) . '/native/libmd4cshim.dylib';
$ffi = FFI::cdef(<<<C
char* md2html(const char* input, size_t input_len, size_t* out_len, unsigned int parser_flags, unsigned int renderer_flags);
void md2html_free(char* p);
unsigned int md2html_dialect_github(void);
C, $lib);

$md = "# Hello *world*\n\nA list:\n- one\n- two\n\n| a | b |\n|---|---|\n| 1 | 2 |\n\n~~struck~~ and `code`.\n";
$gh = $ffi->md2html_dialect_github();
$len = FFI::new('size_t');
$ptr = $ffi->md2html($md, strlen($md), FFI::addr($len), $gh, 0);
if ($ptr === null) { fwrite(STDERR, "PARSE FAILED\n"); exit(1); }
$html = FFI::string($ptr, $len->cdata);
$ffi->md2html_free($ptr);
echo "=== md4c via FFI (dialect=0x".dechex($gh).") ===\n$html\n=== {$len->cdata} bytes ===\n";

require dirname(__DIR__).'/vendor/autoload.php';
echo "\n=== tempest/markdown ===\n";
echo (new \Tempest\Markdown\Markdown())->parse($md)->html . "\n";
echo "\n=== league GFM ===\n";
echo (new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($md)->getContent() . "\n";
