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
 * @copyright 2021, iScholar - Gestão Escolar
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot . '/user/externallib.php');

use auth_ischolar\ischolar;

/**
 * Class used to authenticate on moodle.
 */
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
     * Returns true if the username and password work or don't exist and false if the user
     * exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    public function user_login ($username, $password):bool {
        global $DB;

        $user = $DB->get_record('user', array('username' => $username), '*', IGNORE_MISSING);

        if ($user) {
            return validate_internal_user_password($user, $password);
        }

        return false;
    }


    /**
     * Returns true if this authentication plugin can change users' password.
     *
     * @return bool
     */
    public function can_change_password():bool {
        return false;
    }


    /**
     * Returns true if this authentication plugin can edit the users' profile.
     *
     * @return bool
     */
    public function can_edit_profile():bool {
        return false;
    }


    /**
     * Returns true if this authentication plugin is "internal". Internal plugins use password
     * hashes from Moodle user table for authentication.
     *
     * @return bool
     */
    public function is_internal():bool {
        return false;
    }


    /**
     * Returns false if this plugin is enabled but not configured.
     *
     * @return bool
     */
    public function is_configured():bool {
        return isset($this->config->enabled) &&
            $this->config->enabled == '1' &&
            $this->config->healthyplugin == '1';
    }


    /**
     * Indicates if password hashes should be stored in local moodle database.
     * This function automatically returns the opposite boolean of what is_internal() returns.
     * Returning true means MD5 password hashes will be stored in the user table. Returning
     * false means flag 'not_cached' will be stored there instead.
     *
     * @return bool
     */
    public function prevent_local_passwords():bool {
        return false;
    }


    /**
     * Indicates if moodle should automatically update internal user records with data from
     * external sources using the information from get_userinfo() method.
     * This function automatically returns the opposite boolean of what is_internal() returns.
     *
     * @return bool
     */
    public function is_synchronised_with_external():bool {
        return false;
    }


    /**
     * Hook for overriding behaviour of login page.
     */
    public function loginpage_hook() {
        global $DB, $CFG;

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
                
                switch ($_POST['origem']) {
                    case 'painel':
                        redirect('https://'.$config->schoolcode.'.paineldoaluno.com.br/integracao/moodle?error');
                        break;
                        
                    case 'app':
                        redirect('https://'.$config->schoolcode.'.paineldoaluno.com.br/erro/?e=eyJvcmlnZW0iOiJtb29kbGVfYXV0aCJ9');
                        break;
                        
                    default:
                        redirect('https://'.$config->schoolcode.'.paineldoaluno.com.br/erro/?e=eyJvcmlnZW0iOiJtb29kbGVfYXV0aCJ9');
                        break;
                }
                
            }
        }

        if ($user !== false) {
            complete_user_login($user);
            redirect($CFG->wwwroot.'/my');
        }
    }
}
