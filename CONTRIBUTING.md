# Contributing to the Elastic Email Mailer for Mautic

Thanks for taking the time to contribute! This document explains how to report problems, suggest changes and submit pull requests.

## How the plugin is built

This repository is a Mautic plugin bundle (`MauticPlugin\ElasticEmailMailerBundle`). It registers a Symfony Mailer transport factory and two transports:

- `Mailer/Factory/ElasticEmailTransportFactory.php` maps the `elasticemail`, `elasticemail+api` and `elasticemail+smtp` DSN schemes to a transport.
- `Mailer/Transport/ElasticEmailApiTransport.php` builds requests with the [Elastic Email PHP SDK](https://github.com/ElasticEmail/elasticemail-php) and sends them through the REST API v4.
- `Mailer/Transport/ElasticEmailSmtpTransport.php` sends through `smtp.elasticemail.com`.

If the problem is in how API requests or models are generated (for example a missing field on `EmailContent`), it belongs in the [PHP SDK](https://github.com/ElasticEmail/elasticemail-php/issues) rather than here.

If you're not sure where something belongs, open an issue first and ask.

## Reporting bugs

Search [existing issues](https://github.com/ElasticEmail/elasticemail-mautic-mailer/issues) first. If nothing matches, open a new issue using the **Bug report** template and include:

- Plugin version (from the release you installed, e.g. `1.0.2`)
- Mautic version and PHP version
- `elasticemail/elasticemail-php` version (`composer show elasticemail/elasticemail-php`)
- The DSN scheme you use (`elasticemail+api` or `elasticemail+smtp`), **without** your key
- The error shown in Mautic and the relevant lines from `var/logs/`

**Never paste your API key or SMTP password** into an issue, log or screenshot.

Questions about your Elastic Email account, sending limits, deliverability or billing aren't handled in this repository. The preferred way to get help is the **chat widget on [elasticemail.com](https://elasticemail.com)**.

Looking for sample code? See the [Elastic Email examples repository](https://github.com/ElasticEmail/elasticemail-examples), including the [PHP and Symfony examples](https://github.com/ElasticEmail/elasticemail-examples/tree/main/php-elasticemail-examples).

## Suggesting features

Open an issue using the **Feature request** template. Describe the use case first and the solution second. It helps us decide whether the change belongs in the plugin, the PHP SDK or the API.

## Security issues

Please do **not** report security vulnerabilities in public issues. See [SECURITY.md](SECURITY.md).

## Development setup

Requirements: a local Mautic 5.1+ installation, PHP 8.1 or later and [Composer](https://getcomposer.org).

```bash
cd /path/to/mautic/plugins
git clone https://github.com/ElasticEmail/elasticemail-mautic-mailer.git ElasticEmailMailerBundle
cd ..
composer require elasticemail/elasticemail-php
php bin/console cache:clear
php bin/console mautic:plugins:reload
```

The clone must live in `plugins/ElasticEmailMailerBundle`. Test your change by sending a test email from **Settings → Configuration → Email Settings** with both the API and SMTP schemes where relevant.

Keep the plugin compatible with the PHP and Symfony versions in [`composer.json`](composer.json), and don't add new runtime dependencies without discussing it in an issue first.

## Pull requests

1. Fork the repository and create a branch from `main` (`git checkout -b fix/short-description`).
2. Keep each change focused. One logical change per pull request.
3. Make sure `composer validate` passes and the plugin loads after `cache:clear` and `mautic:plugins:reload`.
4. Send a test email through the transport you changed.
5. Update the README if your change affects installation or configuration.
6. Open a pull request against `main` and fill in the template.

A maintainer will review your pull request and may ask for changes.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold it.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
