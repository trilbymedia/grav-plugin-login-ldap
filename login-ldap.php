<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\User\User;
use Grav\Common\Utils;
use Grav\Plugin\Login\Events\UserLoginEvent;
use Grav\Plugin\Login\Login;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoginLDAPPlugin
 * @package Grav\Plugin
 */
class LoginLDAPPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => [
                ['autoload', 100000],
                ['onPluginsInitialized', 0]
            ],
            'onUserLoginAuthenticate'   => ['userLoginAuthenticate', 1000],
            'onUserLoginFailure'        => ['userLoginFailure', 0],
            'onUserLogin'               => ['userLogin', 0],
            'onUserLogout'              => ['userLogout', 0],
        ];
    }

    /**
     * [onPluginsInitialized:100000] Composer autoload.
     *
     * @return ClassLoader
     */
    public function autoload()
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Check for PHP LDAP
        if (!function_exists('ldap_connect')) {
            throw new \RuntimeException('The PHP LDAP module needs to be installed and enabled');
        }

        // Check to ensure login plugin is enabled.
        if (!$this->grav['config']->get('plugins.login.enabled')) {
            throw new \RuntimeException('The Login plugin needs to be installed and enabled');
        }
    }

    public function userLoginAuthenticate(UserLoginEvent $event)
    {
        $credentials = $event->getCredentials();
        
        // empty username -> ignore
        if($credentials['username'] == ''){
            $event->setStatus($event::AUTHENTICATION_FAILURE);
            return;
        }

        // Get Proper username
        $user_dn            = $this->config->get('plugins.login-ldap.user_dn');
        $search_dn          = $this->config->get('plugins.login-ldap.search_dn');
        $group_dn           = $this->config->get('plugins.login-ldap.group_dn');
        $group_query        = $this->config->get('plugins.login-ldap.group_query');
        $group_indentifier  = $this->config->get('plugins.login-ldap.group_indentifier');

        $username   = str_replace('[username]', $credentials['username'], $user_dn);

        // Get Host info
        $host               = $this->config->get('plugins.login-ldap.host');
        $port               = $this->config->get('plugins.login-ldap.port');
        $version            = $this->config->get('plugins.login-ldap.version');
        $ssl                = $this->config->get('plugins.login-ldap.ssl');
        $start_tls          = $this->config->get('plugins.login-ldap.start_tls');
        $opt_referrals      = $this->config->get('plugins.login-ldap.opt_referrals');
        $blacklist          = $this->config->get('plugins.login-ldap.blacklist_ldap_fields', []);

        if (is_null($host)) {
            throw new ConnectionException('FATAL: LDAP host entry missing in plugin configuration...');
        }

        // Set Encryption
        if ((bool) $ssl) {
            $encryption = 'ssl';
        } elseif ((bool) $start_tls) {
            $encryption = 'tls';
        } else {
            $encryption = 'none';
        }

        try {
            /** @var Ldap $ldap */
            $ldap = Ldap::create('ext_ldap', array(
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
                'options' => array(
                    'protocol_version' => $version,
                    'referrals' => (bool) $opt_referrals,
                ),
            ));

            // Map Info
            $map_username = $this->config->get('plugins.login-ldap.map_username');
            $map_fullname = $this->config->get('plugins.login-ldap.map_fullname');
            $map_email    = $this->config->get('plugins.login-ldap.map_email');
            $map_dn    = $this->config->get('plugins.login-ldap.map_dn');

            // Try to login via LDAP
            $ldap->bind($username, $credentials['password']);

            // Create Grav User
            $grav_user = User::load(strtolower($username));

            // Set defaults with only thing we know... username provided
            $grav_user['login'] = $credentials['username'];
            $grav_user['fullname'] = $credentials['username'];
            $user_groups = [];

            // If search_dn is set we can try to get information from LDAP
            if ($search_dn) {
                $query_string = $map_username .'='. $credentials['username'];
                $query = $ldap->query($search_dn, $query_string);
                $results = $query->execute()->toArray();

                // Get LDAP Data
                if (empty($results)) {
                    $this->grav['log']->error('plugin.login-ldap: [401] user search for "' . $query_string . '" returned no user data');
                    $ldap_data =[];
                } else {
                    $ldap_data = array_shift($results)->getAttributes();
                }

                $userdata = [];

                $userdata['login'] = $this->getLDAPMappedItem($map_username, $ldap_data);
                $userdata['fullname'] = $this->getLDAPMappedItem($map_fullname, $ldap_data);
                $userdata['email'] = $this->getLDAPMappedItem($map_email, $ldap_data);
                $userdata['dn'] = $this->getLDAPMappedItem($map_dn, $ldap_data);
                $userdata['provider'] = 'ldap';

                // Get LDAP Data if required
                if ($this->config->get('plugins.login-ldap.store_ldap_data', false)) {
                    foreach($ldap_data as $key => $data) {
                        $userdata['ldap'][$key] = array_shift($data);
                    }
                    unset($userdata['ldap']['userPassword']);
                }

                // Remove blacklisted fields
                foreach ($blacklist as $fieldName) {
                    if (isset($userdata['ldap'][$fieldName])) {
                        unset($userdata['ldap'][$fieldName]);
                    }
                }

                // Get Groups if group_dn if set
                if ($group_dn) {
                    // retrieves all extra groups for user
                    $group_query = str_replace('[username]', $credentials['username'], $group_query);
                    $group_query = str_replace('[dn]', $ldap->escape($userdata['dn'], '', LdapInterface::ESCAPE_FILTER), $group_query);
                    $query = $ldap->query($group_dn, $group_query);
                    $groups = $query->execute()->toArray();

                    // retrieve current primary group for user
                    $query = $ldap->query($group_dn, 'gidnumber=' . $this->getLDAPMappedItem('gidNumber', $ldap_data));
                    $groups = array_merge($groups, $query->execute()->toArray());

                    foreach ($groups as $group) {
                        $attributes = $group->getAttributes();
                        
                        // make sure we have an array to read
                        if ( !empty($attributes) && !empty($attributes[$group_indentifier]) && is_array($attributes[$group_indentifier]) )
                        {
                            $user_group = array_shift($attributes[$group_indentifier]);
                            $user_groups[] = $user_group;

                            if ($this->config->get('plugins.login-ldap.store_ldap_data', false)) {
                                $userdata['ldap']['groups'][] = $user_group;
                            }
                        }
                    }
                }

                // Merge the LDAP user data with Grav user
                $grav_user->merge($userdata);
            }

            // Set Groups
            $current_groups = $grav_user->get('groups');
            if (!$current_groups) {
                $groups = $this->config->get('plugins.login-ldap.default_access_levels.groups', []);
                if (count($groups) > 0) {
                    $data['groups'] = $groups;
                    $grav_user->merge($data);
                }
            }

            // Set Access
            $current_access = $grav_user->get('access');
            $access = $this->config->get('plugins.login-ldap.default_access_levels.access.site');

            if (!$current_access && $access) {
                if (count($access) > 0) {
                    $data['access']['site'] = $access;
                    $grav_user->merge($data);
                }
            }

            // Give Admin Access
            $admin_access = $this->config->get('plugins.login-ldap.default_access_levels.access.groups');
            if ($admin_access && count($user_groups) && strlen($admin_access) > 0) {
                $groups_access = Yaml::parse($admin_access);
                foreach ($groups_access as $key => $group_access) {
                    if (in_array($key, $user_groups)) {
                        $access_levels = Utils::arrayMergeRecursiveUnique($grav_user->access, $group_access);
                        $grav_user->merge(['access' => $access_levels]);
                    }
                }
            }

            // Optional save
            if ($this->config->get('plugins.login-ldap.save_grav_user', false)) {
                $grav_user->save();
            }

            $event->setUser($grav_user);

            $event->setStatus($event::AUTHENTICATION_SUCCESS);
            $event->stopPropagation();

            return;

        } catch (ConnectionException $e) {
            $message = $e->getMessage();

            $this->grav['log']->error('plugin.login-ldap: ['. $e->getCode() . '] ' . $username . ' - ' . $message);

            // Just return so other authenticators can take a shot...
            if ($message = "Invalid credentials") {
                return;
            }

            $event->setStatus($event::AUTHENTICATION_FAILURE);
            $event->stopPropagation();

            return;
        }

    }

    public function userLoginFailure(UserLoginEvent $event)
    {
        // This gets fired if user fails to log in.
    }

    public function userLogin(UserLoginEvent $event)
    {
        // This gets fired if user successfully logs in.
    }

    public function userLogout(UserLoginEvent $event)
    {
        // This gets fired on user logout.
    }

    protected function getLDAPMappedItem($map, $ldap_data)
    {
        $item_bits = [];
        $map_bits = explode(' ', $map);
        foreach($map_bits as $bit) {
            if(isset($ldap_data[$bit])) {
            $item_bits[] = array_shift($ldap_data[$bit]);
            }
        }
        $item = implode(' ', $item_bits);
        return $item;
    }
}
