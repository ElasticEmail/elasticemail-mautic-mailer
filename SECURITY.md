# Security Policy

## Supported versions

Security fixes are released for the latest version of the plugin on [GitHub Releases](https://github.com/ElasticEmail/elasticemail-mautic-mailer/releases). Please upgrade to the latest version before reporting an issue.

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

Report them privately by one of these methods:

- [GitHub private vulnerability reporting](https://github.com/ElasticEmail/elasticemail-mautic-mailer/security/advisories/new)
- Email **integrations@elasticemail.com** with the subject `Security: elasticemail-mautic-mailer`

Please include:

- A description of the issue and its impact
- Steps to reproduce, or a proof of concept
- The affected plugin version(s) and your Mautic version

We will acknowledge your report, investigate, and keep you updated on the fix. Please give us reasonable time to release a fix before disclosing the issue publicly.

## API key safety

Mautic stores the Email DSN, including your API key or SMTP password, in its configuration. If you think a key has been exposed (in a commit, log, issue or screenshot), revoke it right away in your [Elastic Email API settings](https://app.elasticemail.com/marketing/settings/new/manage-api), create a new one and update the DSN in Mautic.
