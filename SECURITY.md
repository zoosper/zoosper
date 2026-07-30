# Security Policy

## Supported Versions

Zoosper CMS is currently under active, pre-release development
(`dev`/`*@dev` package constraints; no tagged stable releases have shipped
yet). Security fixes are applied to the `dev` branch only. Once tagged
releases begin, this table will be updated to reflect which release lines
receive security patches.

| Version | Supported          |
| ------- | ------------------ |
| `dev`   | :white_check_mark: |

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**
Publicly disclosing a vulnerability before a fix is available puts every
Zoosper installation at risk.

If you discover a security vulnerability in Zoosper CMS, please report it
privately by emailing:

**security@zoosper.com**

Please include as much of the following as you can:

- A description of the vulnerability and its potential impact.
- Step-by-step instructions to reproduce the issue.
- The affected file(s), module(s), or component(s), if known.
- Any proof-of-concept code, request/response samples, or screenshots that
  help demonstrate the issue.
- Whether you are aware of the vulnerability being exploited in the wild.

**Please do not include any real secrets, credentials, session tokens, TOTP
codes, recovery codes, or payment data in your report** — describe the class
of data that could be exposed, not actual live values.

## What to Expect

- **Acknowledgement**: we aim to acknowledge receipt of your report within
  **5 business days**.
- **Assessment**: we will investigate and aim to provide an initial
  assessment (severity, affected versions, expected timeline) within
  **10 business days** of acknowledgement.
- **Resolution**: timelines for a fix depend on severity and complexity.
  Critical vulnerabilities (e.g. authentication bypass, privilege
  escalation, remote code execution, SQL injection) are prioritised above
  all other work.
- **Disclosure**: we will coordinate with you on public disclosure timing.
  Our default preference is to disclose once a fix is available and
  reasonably deployed, giving credit to the reporter unless you prefer to
  remain anonymous.

## Scope

This policy covers the Zoosper CMS core codebase and all first-party
modules maintained within this repository (`app/zoosper-*`,
`packages/zoosper-*`). Vulnerabilities in third-party dependencies (e.g.
`ezyang/htmlpurifier`, `latte/latte`, `marko/framework`) should be reported
to their respective maintainers, though we would still appreciate being
notified if a dependency vulnerability materially affects Zoosper.

## Our Commitment

We take security seriously and will not pursue legal action against
researchers who:

- Make a good-faith effort to avoid privacy violations, data destruction,
  and service disruption during their research.
- Only interact with accounts/data they own or have explicit permission to
  test.
- Report vulnerabilities promptly and do not publicly disclose them before
  a coordinated disclosure timeline has been agreed.

---
