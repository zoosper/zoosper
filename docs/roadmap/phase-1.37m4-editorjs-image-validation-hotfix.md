# Phase 1.37m.4 - Editor.js image validation hotfix

## Goal

Allow uploaded Editor.js image blocks to pass server-side page save validation.

## Diagnosis

Upload succeeded, but page save failed because `BlockJsonValidator` did not yet list `image` as a supported block type.

## Implemented

- Added image block validation to `BlockJsonValidator`.
- Required image URLs to use managed `/media/` storage.
- Rejected remote image URLs.
- Added regression tests for accepted managed images, rejected remote images and older config compatibility.

## Security

The save pipeline still rejects remote image URLs and malformed flags. Image blocks must be backed by managed media storage.
