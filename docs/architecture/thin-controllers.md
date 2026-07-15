# Thin Controllers & the View Layer

## The principle (in plain English)

Think of a restaurant:

- The **controller** is the **waiter** - it takes your order, passes it to the
  kitchen, and brings back the result.
- The **template (view)** is the **interior decorator** - it decides how things
  *look* (all the HTML).
- The **service / domain** code is the **chef** - it does the actual cooking
  (business rules and logic).

A good waiter does **not** also cook the food and repaint the walls. When one
file tries to do all three jobs, it becomes huge, fragile, and scary to change -
which is exactly the churn we have been eliminating.

So a controller should **only**:

1. Check permission (is this user allowed?).
2. Validate the CSRF token (is this a genuine form submit?).
3. Read the request (form fields, query params).
4. Call a service / repository to do the work.
5. Return a `Response` - usually by **rendering a template**.

## The three layers

| Layer | Belongs here | Does NOT belong here |
|---|---|---|
| **Controller** | Permission + CSRF checks, reading the request, calling a service, returning a Response | HTML, SQL, validation rules, string/data massaging |
| **View / Template** (`.latte`) | All HTML, escaping, presentation loops | Business rules, DB access, permission logic |
| **Service / Domain** | Validation, persistence, business rules, normalisation | HTML, HTTP request/response details |

## What is wrong today

`PageAdminController` and `UserAdminController` embed large `<<<HTML ... HTML`
heredoc blocks - list tables, create/edit forms, status buttons, notices. That is
the **decorator's** job living inside the **waiter**. A few normalisation and
validation bits also live in the controller. This is the "oversized controller,
multiple responsibilities" smell.

## Target state

- **All HTML moves into Latte templates** (`.latte`).
- Controllers call `$this->views->render('zoosper-page::admin/pages/index', ...)`.
- Reusable HTML fragments (status button, role checkboxes, locale field, notices)
  become **template partials/components**.
- Validation moves into **lifecycle listeners** - we already started this with
  `PageSaveValidationListener`.

The good news: Zoosper **already has** the right tools - `AdminViewRenderer` plus
the Latte template system. In `PageAdminController::index()` the clean path is
already there (`$this->views->render(...)`); the giant heredoc is only the
*fallback*. This phase makes the clean path the **only** path.

## The "view model" idea (simple version)

A **view model** is just a small array (or object) of values the template needs -
already prepared and escaped. The controller builds it; the template only prints
it. This keeps templates "dumb" (no logic) and safe (no accidental raw output).

```php
// Controller builds a simple, ready-to-print bag of values:
$viewModel = [
    'pages'   => $pages,
    'sites'   => $sites,
    'createUrl' => $this->adminUrl('/pages/create'),
];
return Response::html($this->views->render('zoosper-page::admin/pages/index', $viewModel));
```

## Migration approach (safe, not scary)

- **Incremental and test-guarded** - one screen at a time. No big-bang rewrite.
- **Behaviour stays identical** - same routes, same status codes (419/422/404/200),
  same messages.
- **Delete a heredoc only once** its template renders the same output.

## Escaping & security

Latte **auto-escapes** output by default, which prevents most XSS. The one place
we deliberately output raw HTML is **already-sanitised CMS body content**, which
uses the `SanitizedHtml` value object in an explicit `|noescape` slot. PCI rule
still applies: never put secrets, tokens, or payment data into templates.

## Why this helps "true modular CMS"

Zoosper already has a **theme/template override system**. Once HTML lives in
templates, a module or theme can **override how a screen looks without editing the
controller** - the same "extend without touching core" principle that guides the
whole project.
