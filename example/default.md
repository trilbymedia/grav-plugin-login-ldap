---
title: LDAP Data
cache_enabled: false
process:
    twig: true
access:
    site.login: true
---

# This is a secure page...
## Welcome {{ grav.user.fullname }}

User `{{ grav.user.username }}` {{ grav.user.exists ? '**has** a' : 'does **not** have' }} local account...

* username: `{{ grav.user.username }}`
* email: `{{ grav.user.email }}`
* login: `{{ grav.user.login }}`
* provider: `{{ grav.user.provider }}`
* groups: {{ vardump(grav.user.groups) }}
* access: {{ vardump(grav.user.access) }}
* ldap: {{ vardump(grav.user.ldap) }}
