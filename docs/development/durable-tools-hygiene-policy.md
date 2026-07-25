# Durable Tools Hygiene Policy

## Purpose

The tools hygiene gate exists to keep Zoosper lean. However, not every script with
an `apply-*` or `cleanup-*` name is temporary. Some tools are deliberately durable
because tests, audits, rollback flows, or closeout procedures still require them.

## Rule

A tool should be exempt from hygiene warnings when it is one of the following:

- covered by a Pest test that asserts the file exists;
- registered in a durable tool registry;
- needed as a documented dry-run-first operational command;
- needed for rollback or closeout verification of a recent architecture phase.

## Current implementation

`tools/gate.php` contains a `durableToolAllowlist` with an explicit reason for
each exempt tool. This keeps the rule visible and prevents accidental deletion.

## Future cleanup

When a durable tool is no longer needed, remove or update the corresponding tests
and registry entries first. Only then should the tool be retired.
