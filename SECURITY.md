## Security Policy

### Supported Versions

Zoosper CMS is under active pre-release development. The latest pre-release is `v0.3.0-alpha.5`; the supported development branch is `dev` on the `0.3.1-alpha.1-dev` development identity. No stable release has shipped. Security fixes are developed on `dev` and should be verified against the latest pre-release and current `dev` branch as applicable. Once tagged stable releases begin, this table will be updated to reflect which release lines receive security patches.

<table>
<tr>
<th>
Version
</th>
<th>
Supported
</th>
</tr>
<tr>
<td>
dev
</td>
<td>
:white_check_mark:
</td>
</tr>
</table>

### Reporting a Vulnerability

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

### What to Expect

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

### Scope

This policy covers the Zoosper CMS core codebase and all first-party modules maintained within this repository (`app/zoosper-*`, `packages/zoosper-*`). Security review covers the root Composer dependency graph and all first-party packages discovered from `app/*/composer.json` and `packages/*/composer.json`. `composer.json` and `composer.lock` are the source of truth for the current dependency set.

The CI workflow runs Composer validation and `composer audit`. Vulnerabilities in third-party dependencies — including Marko framework packages (`marko/core`, `marko/errors`, `marko/errors-simple`, `marko/cache`, `marko/cache-file`, `marko/cache-redis`, `marko/config`, `marko/encryption`; see ROADMAP.md §14 for the current verified adoption list), plus `ezyang/htmlpurifier`, `latte/latte`, and `predis/predis` — should be reported to their respective maintainers, though reports affecting Zoosper integration are welcome.

### Our Commitment

We take security seriously and will not pursue legal action against researchers who:
- Make a good-faith effort to avoid privacy violations, data destruction, and service disruption during their research.
- Only interact with accounts/data they own or have explicit permission to test.
- Report vulnerabilities promptly and do not publicly disclose them before a coordinated disclosure timeline has been agreed.
