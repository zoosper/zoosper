# Admin translator injected runtime verifier hotfix docs seed

Future documentation should describe the admin translator priority order:

```text
AdminContextTranslatorResolver first
Injected TranslatorInterface second
IdentityTranslator fallback last
```
