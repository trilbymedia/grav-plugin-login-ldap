# Login LDAP Plugin

The **Login LDAP** Plugin for [Grav CMS](http://github.com/getgrav/grav) allows user authentication against an LDAP Server. 

### Installation

Installing the Login LDAP plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install login-ldap

This will install the Login LDAP plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/login-ldap`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `login-ldap`. You can find these files on [GitHub](https://github.com/trilbymedia/grav-plugin-login-ldap) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/login-ldap
    
Before configuring this plugin, you should copy the `user/plugins/login-ldap/login-ldap.yaml` to `user/config/plugins/login-ldap.yaml` and only edit that copy.    

### Admin Installation

If you use the admin plugin, you can install directly through the admin plugin by browsing the to `Plugins` in the sidebar menu and clicking on the `Add` button.

Configuring the Login LDAP plugin is as easy as navigating to the `Plugins` manager, and editing the configuration options.

## Configuration Options

The default configuration and an explanation of available options:

```yaml
enabled: true
host:
port: 389
version: 3
ssl: false
start_tls: false
opt_referrals: false
user_dn: uid=[username],dc=company,dc=com
search_dn: dc=company,dc=com
map_username: uid
map_fullname: givenName lastName
map_email: mail

save_grav_user: false
store_ldap_data: false
default_access_levels:
  groups: ldap_users
  access:
    site:
      login: 'true'
```

### Server Settings

|Key                   |Description                 | Values |
|:---------------------|:---------------------------|:-------|
|enabled|Enables the plugin | [default: **true**] \| false|
|host|The DNS name or IP address of your LDAP server | e.g. ldap.yourcompany.com |
|port|The TCP port of the host that the LDAP server runs under |  [default: **389**]|
|version|LDAP Version 3 is most popular (only change this if you know what you are doing) | [default: **3**]  |
|ssl|Enable SSL for the connection (typically for port 636or 3269) | true \| [default: **false**] |
|start_tls|Negotiate TLS encryption with the LDAP server (requires all traffic to be encrypted) | true \| [default: **false**] |
|opt_referrals|Sets the value of LDAP_OPT_REFERRALS (Set to "off" for Windows 2003 servers) | true \| [default: **false**] |

### LDAP Configuration

|Key                   |Description                 | Values |
|:---------------------|:---------------------------|:-------|
|user_dn|DN String used to authenticate a user, where `[username]` is replaced by username value entered via login | e.g. `uid=[username],dc=company,dc=com` |
|search_dn|DN String used to retrieve user data | e.g. `ou=users,dc=company,dc=com` |
|group_dn|DN String used to retrieve user group data [OPTIONAL] | e.g. `ou=groups,dc=company,dc=com` |
|map_username|LDAP Attribute(s) that contains the user's username | [default: **uid**] |
|map_fullname|LDAP Attribute(s) that contains the user's full name | [default: **givenName lastName**] |
|map_email|LDAP Attribute(s) that contains the user's email address | [default: **mail**] |

### Advanced Configuration

|Key                   |Description                 | Values |
|:---------------------|:---------------------------|:-------|
|save_grav_user|Store the grav user account as a local YAML account | true \| [default: **false**] |
|store_ldap_data|If storing a local Grav user, you can also store LDAP data so its available in Grav| true \| [default: **false**] |
|default_access_levels.groups|Set a default group for all users logging in via LDAP [OPTIONAL] | e.g. `ldap_users` |
|default_access_levels.access.site|The default access to assign to users logging in via LDAP | e.g. `site: [login: 'true']` |

> Note that if you use the admin plugin, a file with your configuration will be saved in the `user/config/plugins/login-ldap.yaml`.

### Usage

Once properly configured, the functionality of the LDAP plugin is transparent to the user.  A user will be able to login via the normal login process and have access based on their account setup. 


