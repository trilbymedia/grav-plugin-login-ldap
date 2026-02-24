# v1.2.0
## 02/24/2026

1. [](#new)
    * Added search bind support for LDAP environments with restrictive ACLs [#36](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/36)
1. [](#bugfix)
    * Fixed `User::load()` using full DN instead of username [#35](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/35)
    * Fixed assignment operator bug in error handling for invalid credentials [#35](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/35)
    * Fixed `group_identifier` typo - now accepts both spellings with backwards compatibility [#36](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/36)
    * Fixed empty `gidNumber` producing broken LDAP filter on non-posixAccount setups [#36](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/36)

# v1.1.0
## 04/16/2024

1. [](#improved)
     * Stop event propagation on empty username [#27](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/27)
     * Add LDAP configuration example for Active Directory [#19](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/19)
     * Fix plugin installation under PHP 8.1 [#31](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/31)

# v1.0.2
## 11/16/2020

1. [](#improved)
    * Allow to login if LDAP user's DN contains double quotes [#18](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/18)
    * Ignore authentication requests with empty username [#14](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/14)
    * Better handling a null condition with `array_shift` [#8](https://github.com/trilbymedia/grav-plugin-login-ldap/pulls/8)

# v1.0.1
## 06/11/2018

1. [](#improved)
    * Added ability to search for groups with customizable `distinguishedName` setting of the bound user (useful for ActiveDirectory domains) [#1](https://github.com/trilbymedia/grav-plugin-login-ldap/issues/1)
 
# v1.0.0
## 05/18/2018

1. [](#new)
    * Plugin released...
