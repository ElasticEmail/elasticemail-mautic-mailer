## Summary

<!-- What does this PR change, and why? Link related issues: Fixes #123 -->

## Type of change

- [ ] Bug fix
- [ ] New feature
- [ ] Documentation
- [ ] Build / CI / tooling
- [ ] Other:

## Checklist

- [ ] `composer validate` passes
- [ ] The plugin loads after `php bin/console cache:clear` and `php bin/console mautic:plugins:reload`
- [ ] I sent a test email with the affected transport (`elasticemail+api` / `elasticemail+smtp`)
- [ ] Tested on Mautic version: 
- [ ] Documentation is updated where needed
- [ ] No API keys, SMTP passwords or other secrets are included
