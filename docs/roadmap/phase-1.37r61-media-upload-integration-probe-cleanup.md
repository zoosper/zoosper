# Phase 1.37r.6.1 - Media upload integration probe cleanup

## Goal

Clean the readiness probe warnings and lock the concrete-fixture strategy for the next behavioural upload failure-path test.

## Diagnosis

The Phase 1.37r.6 probe passed, but printed PHP warnings because it imported global classes with non-compound `use` statements:

```php
use ReflectionClass;
use ReflectionException;
```

Those imports are unnecessary in the global namespace and should be removed before we build the next real integration-style test.

## Implemented

- Removed non-compound Reflection imports from the probe.
- Updated the probe to print a recommended next test strategy.
- Added regression coverage ensuring the warning-prone imports stay removed.
- Expanded package-owned docs with the concrete fixture strategy.

## Expected result

```text
php8.5 packages/zoosper-media/tools/probe-media-upload-integration-readiness.php
```

should return `Result: OK` without PHP warnings.

## Next phase

Phase 1.37r.7 should implement the actual storage-succeeds / repository-fails behavioural test using concrete fixtures if final classes prevent clean substitution.
