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
user_dn: 'uid=[username],dc=company,dc=com'
search_dn:
group_dn:
group_query: '(&(cn=*)(memberUid=[username]))'
group_indentifier: cn
map_username: uid
map_fullname: givenName lastName
map_email: mail
save_grav_user: false
store_ldap_data: false
default_access_levels:
  groups: 
   - ldap_users
  access:
    site:
      login: true
    groups:
      admin:    
        admin:
          login: true
          super: true 
      user:
        site:
          login: true  
```

### Server Settings

|Key                   |Description                 | Values |
|:---------------------|:---------------------------|:-------|
|enabled|Enables the plugin | [default: `true`] \| `false`|
|host|The DNS name or IP address of your LDAP server | e.g. `ldap.yourcompany.com` |
|port|The TCP port of the host that the LDAP server runs under |  [default: `389`]|
|version|LDAP Version 3 is most popular (only change this if you know what you are doing) | [default: `3`]  |
|ssl|Enable SSL for the connection (typically for port 636 or 3269) | `true` \| [default: `false`] |
|start_tls|Negotiate TLS encryption with the LDAP server (requires all traffic to be encrypted) | `true` \| [default: `false`] |
|opt_referrals|Sets the value of LDAP_OPT_REFERRALS (Set to "off" for Windows 2003 servers) | `true` \| [default: `false`] |

### LDAP Configuration

|Key                   |Description                 | Values |
|:---------------------|:---------------------------|:-------|
|user_dn|DN String used to authenticate a user, where `[username]` is replaced by username value entered via login | [default: `uid=[username],dc=company,dc=com`] |
|search_dn|DN String used to retrieve user data. If not provided, extra LDAP user data will not be stored in Grav user account file [OPTIONAL]| e.g. `ou=users,dc=company,dc=com` |
|group_dn|DN String used to retrieve user group data. If not provided, extra LDAP group data will not be stored in Grav user account file [OPTIONAL] | e.g. `ou=groups,dc=company,dc=com` |
|group_query|The query used to search Groups. Only change this if you know what you are doing| [default: `(&(cn=*)(memberUid=[username]))`] |
|group_indentifier|The Group identifier that will come back in the response, this is directly related to group query.| [default: `cn`] |
|map_username|LDAP Attribute(s) that contains the user's username | [default: `uid`] |
|map_fullname|LDAP Attribute(s) that contains the user's full name | [default: `givenName lastName`] |
|map_email|LDAP Attribute(s) that contains the user's email address | [default: `mail`] |

### Advanced Configuration

|Key                   |Description                 | Values |
|:---------------------|:---------------------------|:-------|
|save_grav_user|Store the grav user account as a local YAML account | true \| [default: `false`] |
|store_ldap_data|If storing a local Grav user, you can also store LDAP data so its available in Grav| true \| [default: `false`] |
|default_access_levels.groups|Set a default group for all users logging in via LDAP [OPTIONAL] | e.g. `ldap_users` |
|default_access_levels.access.site|Set the default **site access** for all users logging in via LDAP (used if no `access.groups` mapping applies) | e.g. `[login: 'true']` |
|default_access_levels.access.groups|The default **access to assign** to users logging in based on **LDAP group membership**| e.g. `user: [site: [login: 'true']]` |


> Note that if you use the admin plugin, a file with your configuration will be saved in the `user/config/plugins/login-ldap.yaml`.

### Usage

Once properly configured, the functionality of the LDAP plugin is transparent to the user.  A user will be able to login via the normal login process and have access based on their account setup. 

For the most basic of authentication, only the `user_dn` is required.  This uses LDAP **bind** to simply map a full user **DN** to an entry in the LDAP directory with an associated password.  If no `search_dn` is provided, once authenticated, the only information available about the user is the `username` provided during login.

#### LDAP User Data

In order to obtain data about the user a valid `search_dn` is required.  This will search the LDAP directory at the level indicated in the DN and search for a userid with the `username` provided.  the `map_username` field is used in this LDAP search query, so it's important that the `map_username` field is one that properly maps the `username` provided during login to the LDAP user entry. 

#### LDAP Group Data

To be able to know the groups a user is associated with, a valid `group_dn` and `group_query` is required. Any invalid information will throw an exception stating that the search could not complete.  


### LDAP Group to Grav Access Mapping

The LDAP plugin provides a flexible way to map your LDAP users into Grav.  

> For Groups and Access mapping to work properly a valid `search_dn`, `query_dn` and `group_query` is required.

The default configuration for `default_access_levels.access.groups` looks like:

```yaml
admin:
    admin:
        login: true
      super: true
    site:
      login: true
user:
  site:
    login: true
```

How this works is the two top-level YAML keys (`admin` and `user`) are LDAP groups that if a user is a member of, will assign the subsequent values as the Grav user access.

In order for a front-end user to be able to log into a Grav site the minimum of `site: [login: true]` is required.  So as long as the user is a member of the LDAP group `user`, they will be assigned the necessary access to log in to the front of the site, and nothing more.  You can of course configure this with any access settings you wish to provide. 

For a user to have access to the admin, they must have at least `admin: [login: true]` access, and to be a full administrator with access to everything, they also need to have `admin: [super: true]` access.  In the default configuration, users with LDAP group `admin` will be assigned this access. This will probably need to be modified for your directory configuration.

> NOTE: See the [Groups and Permissions Documentation](https://learn.getgrav.org/advanced/groups-and-permissions?target=_blank) for more information about how Grav permissions work in conjunction with access levels and groups.


### Storing Grav User

By default the LDAP plugin does not store any local user information.  Upon successfully authenticating against the LDAP user, a user is created and is available during the session.  However, upon returning, the user must re-authenticate and the LDAP data is retrieved again.

If you want to be able to set user data (extra fields, or specific user access) for a particular user, you can enable the `save_grav_user` option, and this will create a local Grav user in the `accounts/` folder.  This is a local record of the user and attributes can be set here.  

> NOTE: Any attribute stored under the `ldap:` key in the user account file will be overwritten by the plugin during the next login.  This information is always in sync with latest data in the LDAP server.  The same rule goes for the **mapped** fields.  So updating `email` in your LDAP directory will ensure the entry in the local Grav user is updated on next login.
>  
> Also note that the password will never be stored in the Grav user under `accounts/`.

### Blacklist LDAP Fields

With the Blacklist Fields you have the option of ignoring fields. This is useful for skipping users sensitive data or fields that are stored as media. For example phone numbers, home addresses or images, videos, etc.

### Troubleshooting

If a user is simply unable to authenticate against the LDAP server, an entry will be logged into the Grav log (`logs/grav.log`) file with the attempted `dn`. This can be used to ensure the `user_dn` entry is correct and can be tested against any other LDAP login system.

If either the `user_dn`, `search_dn`, `group_dn` or `group_query` are incorrect an error will be thrown during login, and a message with the error stored in the `logs/grav.log` file.

If you expect `fullname`, or `email` to be stored in the Grav user object, but they are not appearing, it's probably a problem with your field mappings.  Double check with your LDAP administrator that these are the correct mappings.

To get a quick state of your LDAP configuration, you can simply dump out the Grav user on a temporary _secure_ page:

```markdown
---
title: LDAP Test
cache_enabled: false
process:
    twig: true
access:
    site.login: true
---

# Grav User

{{ vardump(grav.user) }}
```

For a more detailed example, you can look in the `example.` folder of this plugin, where you can find a `default.md` page that you can use to see the data collected during LDAP authentication. It's an useful way for configuring the plugin as well as tweaking the Blacklist.

