#define FFI_SCOPE "MD4C"
#define FFI_LIB "/Users/helge/code/markdown-fight/native/libmd4cshim.dylib"
char* md2html(const char* input, size_t input_len, size_t* out_len, unsigned int parser_flags, unsigned int renderer_flags);
void md2html_free(char* p);
unsigned int md2html_dialect_github(void);
char* md2html_batch(const char* packed, const size_t* in_offsets, size_t n, size_t* out_offsets, unsigned int parser_flags, unsigned int renderer_flags, int threads);
