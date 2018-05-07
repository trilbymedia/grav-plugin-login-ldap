<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\User\User;
use Grav\Plugin\Login\Events\UserLoginEvent;
use Grav\Plugin\Login\Login;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;

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
        $username = $event->getUser();
        $credentials = $event->getCredentials();

        // This gets fired for user authentication.
        $username="cn=amiller,ou=users,dc=trilbymedia,dc=com";
        $credentials="gom8Jabar";

        try {

            $ldap = Ldap::create('ext_ldap', array(
                'host' => 'ldap.trilbymedia.com',
            ));


            $ldap->bind($username, $credentials);



            $query = $ldap->query('dc=trilbymedia,dc=com', 'uid=amiller');
            $results = $query->execute();

            $userdata = ['ldap' => $results[0]->getAttributes()];
            unset($userdata['ldap']['userPassword']);

            // Create Grav User
            $grav_user = User::load($username);

            $current_access = $grav_user->get('access');
            if (!$current_access) {
                $access = $this->config->get('plugins.login.user_registration.access.site', []);
                if (count($access) > 0) {
                    $data['access']['site'] = $access;
                    $grav_user->merge($data);
                }
            }

            $grav_user->merge($userdata);
            $grav_user->save();

            $event->setUser($grav_user);

            $event->setStatus($event::AUTHENTICATION_SUCCESS);
            $event->stopPropagation();

            return;

        } catch (ConnectionException $e) {
            print $e->getMessage();

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

}
