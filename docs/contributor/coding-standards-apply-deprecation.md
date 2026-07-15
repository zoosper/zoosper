# Deprecation Notice: `tools/apply-*.php` one-shot code-mod scripts

**Effective:** Phase 1.21.
**Status:** Deprecated - do not add new ones; stop using existing ones for source edits.

---

## What is deprecated

The `tools/apply-*.php` pattern - throwaway, regex-based, one-shot scripts that
automatically rewrote production source files - is deprecated and must not be used
for editing source going forward.

The companion pattern of writing a bespoke `tools/verify-*.php` script scoped to a
single just-introduced bug is likewise replaced by real Pest regression tests.

---

## Why

The phase/progress history showed a repeating, expensive cycle:

1. A feature ships.
2. It causes a regression (parse error, misplaced HTML in a heredoc, PDO
   placeholder/parameter mismatch, or the **wrong controller edited**).
3. A narrowly-scoped "hotfix" phase ships, often with a new verifier script written
   only to catch the exact bug just introduced.
4. This repeats on the **same** feature - the admin-user locale field alone went
   through roughly **twenty** numbered sub-phases (several titled "hotfix") before
   landing correctly.

**Diagnosis:** this is the signature of iterative, LLM-assisted patching without a
persistent regression suite or a careful direct-edit discipline. The `apply-*.php`
scripts demonstrably caused more bugs than they prevented (parenthesis/regex
mismatches, raw PHP tags injected into heredoc strings, edits applied to the wrong
file).

---

## The new rules (apply to every change)

1. **Edit real source files directly and completely.** No one-shot regex-based
   "apply" scripts against production code.
2. **Read the full file before editing it** - especially shared bootstrap files
   (`ApplicationFactory`, `Migrator`, `Router`). Several past hotfixes existed only
   because an earlier patch edited the wrong file.
3. **Every behavioural change ships with a Pest regression test**, not a bespoke
   CLI verifier script.
4. **Prefer additive, reversible changes** (feature flags, new methods) over
   rewriting working code paths in place.
5. New core-entity fields go through `FieldDefinition` / `FieldDefinitionRegistry`;
   new admin form sections go through `AdminFormSectionProviderInterface`; new tables
   go through the single unified schema engine (post Phase 1.23). Never inline
   `$_POST` handling or hand-built heredoc HTML in a controller.

---

## Migration of existing scripts

- Existing `tools/apply-*.php` scripts: stop using them; remove as their functions
  are absorbed into direct edits.
- Existing `tools/verify-*.php` scripts: keep temporarily as a safety net, and
  convert the highest-value ones into Pest tests. Remove each only once its coverage
  exists in the Pest suite.
