# Phase 1.37m.1 - Editor.js upload permission hotfix

## Goal

Fix the browser image upload failure where the Editor.js Image Tool receives an HTML admin Dashboard response instead of the expected JSON upload payload.

## Diagnosis

The static verification suite was green, but browser upload returned HTML. The response content was the admin Dashboard page, which indicates the route did not deliver the JSON upload response to the Image Tool.

The likely permission mismatch is that page editors can see/use the editor but the upload route required only `media.manage`.

## Implemented

- Changed `POST /admin/media/editorjs/upload` permission from `media.manage` to `['media.manage', 'page.manage']`.
- Added a regression test proving the route allows both media managers and page managers.
- Documented the permission model.

## Security

The upload endpoint is still authenticated, CSRF protected and validates/stores files through the media services. It is not public.
