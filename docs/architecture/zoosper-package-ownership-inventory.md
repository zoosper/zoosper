# Zoosper Package Ownership Inventory

## Purpose

This inventory classifies first-party packages by product responsibility. It is
a planning aid for reducing framework behaviour in `zoosper-core` without
moving CMS concerns into Marko.

## Package classification

| Package | Primary classification | Zoosper-owned responsibility | Framework overlap to remove or adapt |
| --- | --- | --- | --- |
| `zoosper/core` | Transitional product kernel | CMS composition and shared CMS contracts only | Bootstrap, config, container, cache, module discovery, events, plugins, routing and console require Marko comparison |
| `zoosper/errors` | Infrastructure adapter | CMS exception context, remediation links and redaction | Generic reporting and formatting should remain Marko-owned |
| `zoosper/admin` | CMS application module | Admin shell, navigation and reusable CMS administration patterns | Generic view, routing and authorisation mechanics may move to Marko contracts |
| `zoosper/auth` | CMS security domain | AdminUser, login workflow, role assignment integration and audit semantics | Generic guard, hashing, session and policy mechanics require later evaluation |
| `zoosper/two-factor` | CMS security domain | Enrolment, verification, recovery, reset and key-rotation workflow | Generic encryption contract may be adapted after security review |
| `zoosper/site` | CMS domain | Sites, domains, locale and request site context | Generic routing and configuration mechanics should not live here |
| `zoosper/page` | CMS domain | Pages, revisions, publication, Editor.js content and rendering policy | Generic form, view, event and cache mechanics should use framework contracts |
| `zoosper/theme` | CMS domain | Theme discovery, site theme selection and CMS template policy | Generic view and layout mechanics require later Marko evaluation |
| `zoosper/media` | CMS domain plus driver integration | Media library, metadata, public paths and upload policy | Generic filesystem and image transforms should use framework contracts where suitable |
| `zoosper/url-rewrite` | CMS domain | CMS URL rewrite records and resolution policy | Generic router remains framework-owned |
| `zoosper/mail` | CMS integration | CMS mail diagnostics, templates and operational policy | Generic mail contracts and transports require Marko evaluation |
| `zoosper/api` | CMS delivery module | CMS resources, payload policy and API authentication integration | Generic HTTP and API infrastructure should use framework contracts |
| `zoosper/install` | CMS operations | CMS installation, schema setup and initial product state | Generic CLI and environment validation should use framework mechanisms |

## Target for `zoosper/core`

`zoosper/core` should eventually contain only responsibilities that are both:

1. shared across multiple Zoosper CMS modules; and
2. specific to composing or expressing a CMS product.

A class is not a core candidate merely because many modules use it. Generic
reuse belongs in Marko. New core classes require an explicit ownership note in
code review.

## Immediate extraction rule

Do not select another physical `zoosper-core` extraction until configuration,
CLI and runtime module ownership are settled. First remove duplicate framework
responsibilities, then reassess the smaller remaining package boundaries.
