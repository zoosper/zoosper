# Site Lookup Service Binding v3 Hotfix

## Issue

The previous service-binding patcher either failed syntax validation or inserted a binding that was not visible to the audit/readiness tools.

## Fix

The v3 patcher writes the Site lookup service binding with fully-qualified class names directly in `app/zoosper-site/config/services.php`.

This avoids fragile import insertion and keeps core runtime source decoupled.
