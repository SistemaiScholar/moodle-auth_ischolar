<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class used to authenticate on moodle.
 *
 * @package   auth_ischolar
 * @category  authentication
 * @copyright 2021, iScholar - Gestão Escolar
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot . '/user/externallib.php');

use auth_ischolar\ischolar;


class auth_plugin_ischolar extends auth_plugin_base {

    /**
     * Class constructor.
     *
     */
    public function __construct() {
        $this->authtype = 'ischolar';
        $this->config   = ischolar::getsettings();
    }
    

    /**
     * Old syntax of class constructor.
     * 
     * @deprecated since Moodle 3.1 and PHP7.
     */
    public function auth_plugin_ischolar() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }


    /**
     *  Returns true if the username and password work or don't exist and false if the user
     *  exists and the password is wrong.
     *
     *  @param string $username The username
     *  @param string $password The password
     *  @return bool Authentication success or failure.
     */
    function user_login ($username, $password):bool {
        global $DB;

        $user = $DB->get_record('user', array('username' => $username), '*', IGNORE_MISSING);
        
        if ($user)
            return validate_internal_user_password($user, $password);
            
        return false;
    }
    

    /**
     *  Returns true if this authentication plugin can change users' password.
     *  
     *  @return bool
     */
    function can_change_password():bool {
        return false;
    }


    /**
     *  Returns the URL for changing the users' passwords, or empty if the default URL can be used. 
     *  
     *  @return string
     */
    //function change_password_url():string {
    //    return 'https://app.ischolar.com/integracoes/altera_senha';
    //}
    
    
    /** 
     *  Returns true if this authentication plugin can edit the users' profile.
     *  
     *  @return bool
     */
    function can_edit_profile():bool {
        return false;
    }
    
    
    /* *
     *  Returns the URL for editing users' profile, or empty if the defaults URL can be used. 
     *  
     *  @return string
     */
    //function edit_profile_url():string {
    //    return 'https://app.ischolar.com/integracoes/???';
    //}
    
    
    /**
     *  Returns true if this authentication plugin is "internal". Internal plugins use password 
     *  hashes from Moodle user table for authentication. 
     *  
     *  @return bool
     */
    function is_internal():bool {
        return false;
    }
    
    
    /**
     *   Returns false if this plugin is enabled but not configured. 
     *   
     *   @return bool
     */
    function is_configured():bool {
        return isset($this->config->enabled) && 
            $this->config->enabled == '1' &&
            $this->config->healthyplugin == '1';
    }
    
    
    /**
     *  Indicates if password hashes should be stored in local moodle database. 
     *  This function automatically returns the opposite boolean of what is_internal() returns. 
     *  Returning true means MD5 password hashes will be stored in the user table. Returning 
     *  false means flag 'not_cached' will be stored there instead. 
     *  
     *  @return bool
     */
    function prevent_local_passwords():bool {
        return false;
    }
    
    
    /**
     *  Indicates if moodle should automatically update internal user records with data from 
     *  external sources using the information from get_userinfo() method. 
     *  This function automatically returns the opposite boolean of what is_internal() returns. 
     *  
     *  @return bool
     */
    function is_synchronised_with_external():bool {
        return false;
    }
    
    
    /* *
     * Update the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     *
     * @return bool
     */
    //function user_update_password($user, $newpassword):bool {
    //    $user = get_complete_user_data('id', $user->id);
    //    // This will also update the stored hash to the latest algorithm
    //    // if the existing hash is using an out-of-date algorithm (or the
    //    // legacy md5 algorithm).
    //    return update_internal_user_password($user, $newpassword);
    //}
    
    
    /* *
     *  Called when the user record is updated. 
     *  It will modify the user information in external database. 
     */
    //function user_update($olduser, $newuser) {
    //    return ;
    //}
    
    
    /* *
     *  User delete requested. 
     *  Internal user record had been deleted. 
     */
    //function user_delete($olduser) {
    //    return ;
    //}

    
    /* *
     *  Returns true if plugin allows resetting of internal password.
     *  
     *  @return bool
     */
    //function can_reset_password() {
    //    return true;
    //}
    
    
    /* *
     *  Returns true if plugin allows resetting of internal password. 
     *  
     *  @return bool
     */
    //function can_signup() {
    //    return true;
    //}
    
    
    /* *
     *  Sign up a new user ready for confirmation, password is passed in plaintext. 
     *  
     *  @return 
     */
    //function user_signup($user, $notify=true) {
    //    return;
    //}
    
    
    /* *
     *  Returns true if plugin allows confirming of new users. 
     *  
     *  @return bool
     */
    //function can_confirm() {
    //    return false;
    //}
    
    
    /* *
     *   Confirm the new user as registered. 
     *   
     *   @return ???
     */
    //function user_confirm($username, $confirmsecret) {
    //    return ;
    //}
    
    
    /* *
     *  Checks if user exists in external db. 
     *  
     *  @return ???
     */
    //function user_exists($username) {
    //    return ;
    //}
    
    
    /* *
     *  Returns number of days to user password expires.
     *  
     *  @return int
     */
    //function password_expire($username) {
    //    
    //}
    
    /* *
     *  Sync roles for this user - usually creator
     */
    //function sync_roles() {
    //    
    //}
    
    
    /* *
     *  Read user information from external database and returns it as array. 
     */
    //function get_userinfo($username) {
    //    
    //}
    
        
    /**
     *  Hook for overriding behaviour of login page. 
     */
    function loginpage_hook() {
        global $DB;
        
        $user = false;
        
        if (isset($_POST['token'])) {
            $payload = [
                'token' => $_POST['token']
            ];
            
            $response = ischolar::callischolar('valida_token', $payload);
            
            if (@$response['status'] == 'sucesso') {
                $user = $DB->get_record('user', array('id' => $response['data']['id_moodle']), '*', IGNORE_MISSING);
            }
            
            if (!$user) {
                $config = ischolar::getsettings();
                redirect('https://'.$config->schoolcode.'.paineldoaluno.com.br/integracao/moodle?error');
            }
        }

        if ($user !== false) {
            complete_user_login($user);
            redirect('/moodle/my');
        }
    }
    
    /* *
     *  Hook for overriding behaviour of prior to redirecting to the login page, eg redirecting 
     *  to an external login url for SAML or OpenID authentication. 
     *  If you implement this you should also implement loginpage_hook as the user may go 
     *  directly to the login page. 
     */
    //function pre_loginpage_hook() {
    //}
    
    
    /* *
       Post authentication hook. This method is called from authenticate_user_login() for all 
       enabled auth plugins. 
     */
    //function user_authenticated_hook($user, $username, $password) {
    //    
    //}
    
    
    /* *
     *  Pre logout hook. 
     */
    //function prelogout_hook() {
    //    
    //}
    
    
    /* *
     *  This method replace the prelogout_hook method to avoid authentication plugins redirects 
     *  before the user logout event being triggered. At the moment the only authentication 
     *  plugin using this method is CAS (SSO). 
     */
    //function postlogout_hook() {
    //    
    //}
    
    
    /* *
     *  Hook for overriding behaviour of logout page. 
     */
    //function logoutpage_hook() {
    //    
    //}
    
    
    /* *
     *   This function was introduced in the base class and returns false by default. 
     *   If overriden by an authentication plugin to return true, the authentication plugin 
     *   will be able to be manually set for users. For example, when bulk uploading users you 
     *   will be able to select it as the authentication method they use. 
     */
    //function can_be_manually_set() {
    //    return true;
    //}
    
    
    /* *
     *   Override this method and return a list of Identification Providers (IDPs) that your 
     *   authentication plugin supports. An array of associative arrays containing url, icon and 
     *   name for the IDP. These will be displayed on the login page and in the login block. 
     */
    //function  loginpage_idp_list()  {
    //    
    //}
    
    
    /* *
     *  This method is called from authenticate_user_login() right after the user object is 
     *  generated. 
     *  This gives the auth plugin an option to make modification to the user object before the 
     *  verification process starts. 
     */
    //function pre_user_login_hook(&$user) {
    //    
    //}
    
    
    /* *
     *  (From 3.3 onwards) If this method exists, the "manage authentication plugins" page will 
     *  show a "test settings" link. 
     *  The method should output notifications to let the user know whether the settings are 
     *  correct, and what to do to rectify them if not. 
     */
    //function test_settings() {
    //    
    //}
}