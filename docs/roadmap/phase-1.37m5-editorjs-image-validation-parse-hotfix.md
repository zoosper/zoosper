# Phase 1.37m.5 - Editor.js image validation parse hotfix

## Goal

Fix the parse error in `BlockJsonValidator` introduced during the image block validation hotfix.

## Diagnosis

The string `Editor's block JSON must contain a blocks array.` was placed inside a single-quoted PHP string and was not escaped, causing:

```text
ParseError: syntax error, unexpected identifier "s", expecting "]"
```

## Implemented

- Replaced the problematic single-quoted string with a double-quoted string.
- Kept the Phase 1.37m.4 image validation behaviour unchanged.

## Follow-up

Run the image validator tests, full verification, then retry saving the page with an uploaded image block.
