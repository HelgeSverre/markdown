/*
 * Minimal config.h for the vendored libyaml 0.2.5 source.
 *
 * The upstream autotools/cmake build generates this file; we hand-write the
 * only macros the sources actually require (the version, used by api.c's
 * yaml_get_version*()). Compile the libyaml translation units with
 * -DHAVE_CONFIG_H so yaml_private.h includes this. Nothing else in libyaml
 * 0.2.5 hard-depends on a generated config macro.
 */
#ifndef MARKDOWN_FIGHT_LIBYAML_CONFIG_H
#define MARKDOWN_FIGHT_LIBYAML_CONFIG_H

#define YAML_VERSION_MAJOR 0
#define YAML_VERSION_MINOR 2
#define YAML_VERSION_PATCH 5
#define YAML_VERSION_STRING "0.2.5"

#endif /* MARKDOWN_FIGHT_LIBYAML_CONFIG_H */
