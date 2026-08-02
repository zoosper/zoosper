# Phase 7B source exporter completeness hotfix

The first source export stopped immediately after the `GridDataSourceCapabilities.php` file header and contained only 61 lines. It was therefore not sufficient for a source-specific reliability implementation.

The corrected exporter builds a null-delimited file manifest first, exports every discovered source file without a pipeline subshell, compares discovered and exported counts, requires all four package roots to occur in the output, and rejects implausibly small exports.
