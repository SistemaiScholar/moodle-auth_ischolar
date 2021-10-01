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
 * Integrate with ischolar systems.
 *
 * @package    auth_ischolar
 * @copyright  2021 iScholar
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_ischolar;

defined('MOODLE_INTERNAL') || die();

/**
 * Integrate with ischolar systems.
 */
class ischolar {
    /**
     * @var string PLUGIN_ID Plugin id.
     */
    const PLUGIN_ID         = 'auth_ischolar';
    /**
     * @var string SERVICE_NAME Service name.
     */
    const SERVICE_NAME      = 'iScholar Authentication';
    /**
     * @var string SERVICE_ID Service id.
     */
    const SERVICE_ID        = 'ischolar_auth';
    /**
     * @var string SETTINGS_PAGE Settings page.
     */
    const SETTINGS_PAGE     = 'authsettingischolar';
    /**
     *  @var string SERVICE_FUNCTIONS Functions executed by the service.
     */
    const SERVICE_FUNCTIONS = [
        'core_course_get_categories',           // Return category details.
        'core_user_get_users_by_field',         // Retrieve users' information for a specified unique field.
    ];


    /**
     * Get the plugin configuration parameters.
     *
     * @return object (a collection of settings parameters/values).
     */
    public static function getsettings(): object {
        $config = get_config(self::PLUGIN_ID);

        return $config;
    }


    /**
     * Performs the configuration in the plugin and in the iScholar system.
     *
     * @return array An array containing the status of configuration.
     */
    public static function setintegration(): bool {
        global $CFG;
        require_once($CFG->dirroot . '/user/externallib.php');

        // Seguindo os passos descritos em 'Dashboard / Site administration / Server / Web services / Overview'.
        try {
            //
            // 1. Ativando webservice
            //
            set_config('enablewebservices', 1);

            //
            // 2. Ativando protocolo REST
            //
            if (!isset($CFG->webserviceprotocols) || $CFG->webserviceprotocols == '') {
                set_config('webserviceprotocols', 'rest');
            } else {
                $services = explode(',', $CFG->webserviceprotocols);
                if (array_search('rest', $services) === false) {
                    $services[] = 'rest';
                    set_config('webserviceprotocols', implode(',', $services));
                }
            }

            //
            // 3. Criando uusário específico (ischolar)
            //

            // Busca usuário ischolar.
            $user = \core_user_external::get_users_by_field('username', ['ischolar']);
            $user = \external_api::clean_returnvalue(\core_user_external::get_users_by_field_returns(), $user);

            // Se usuário ischolar não existe, será criado.
            if (count($user) == 0) {
                $user1 = array(
                    'username'    => 'ischolar',
                    'password'    => '1Sch0lar@2021',
                    'idnumber'    => 'ischolar',
                    'firstname'   => 'iScholar',
                    'lastname'    => get_string('settings:userlastname', self::PLUGIN_ID),
                    'email'       => 'walter@ischolar.com.br',
                    'maildisplay' => 0,
                    'description' => get_string('settings:userdescription', self::PLUGIN_ID),
                );
                $user = \core_user_external::create_users([$user1]);
                $user = \external_api::clean_returnvalue(\core_user_external::create_users_returns(), $user);

                // Altera usuário (moodle não permite criar usuários de webservice, mas permite alterar o usuário para webservice).
                $user1['id']   = $user[0]['id'];
                $user1['auth'] = 'webservice';
                $user          = \core_user_external::update_users([$user1]);
            } else {    // Se usuário já existe, é resetado.
                $ischolaruser = \core_user_external::get_users_by_field('username', ['ischolar']);
                $user1 = array(
                    'id'          => $ischolaruser[0]['id'],
                    'auth'        => 'webservice',
                    'username'    => 'ischolar',
                    'password'    => '1Sch0lar@2021',
                    'idnumber'    => 'ischolar',
                    'firstname'   => 'iScholar',
                    'lastname'    => 'Integrações',
                    'email'       => 'walter@ischolar.com.br',
                    'maildisplay' => 0,
                    'description' => 'NÃO ALTERE E NÃO REMOVA ESTE USUÁRIO. A alteração ou remoção '.
                                     'deste usuário acarretará no mal funcionamento da integração iScholar.',
                );
                \core_user_external::update_users([$user1]);
            }

            //
            // 4. Verificando capacidades do usuário
            // Coloca o usuário ischolar no grupo de administradores.
            //
            $potentialadmisselector = new \core_role_admins_potential_selector();
            $ischolar               = $potentialadmisselector->find_users('iScholar');
            $ischolar               = current($ischolar);
            if ($ischolar != false) {
                $ischolar   = current($ischolar);
                $idischolar = $ischolar->id;

                $admins = array();
                foreach (explode(',', $CFG->siteadmins) as $admin) {
                    $admin = (int)$admin;
                    if ($admin) {
                        $admins[$admin] = $admin;
                    }
                }
                $logstringold        = implode(', ', $admins);      // Log antes.
                $admins[$idischolar] = $idischolar;                 // Alteração.
                $logstringnew        = implode(', ', $admins);      // Log depois.

                set_config('siteadmins', implode(',', $admins));
                add_to_config_log('siteadmins', $logstringold, $logstringnew, 'core');
            }

            //
            // 5. Selecionando um serviço
            //
            require_once($CFG->dirroot . '/webservice/lib.php');
            $wsman      = new \webservice;
            $service    = $wsman->get_external_service_by_shortname(self::SERVICE_ID);
            if ($service == false) {                                        // Cria serviço caso não exista.
                $serviceid  = $wsman->add_external_service((object)[
                    'name'               => self::SERVICE_NAME,
                    'shortname'          => self::SERVICE_ID,
                    'enabled'            => 1,
                    'requiredcapability' => '',
                    'restrictedusers'    => true,
                    'component'          => null,
                    'downloadfiles'      => true,
                    'uploadfiles'        => true,
                ]);
            } else {                                                       // Se serviço já existe, reseta os parâmetros.
                $serviceid = $service->id;
                $wsman->update_external_service((object)[
                    'id'                 => $serviceid,
                    'name'               => self::SERVICE_NAME,
                    'shortname'          => self::SERVICE_ID,
                    'enabled'            => 1,
                    'requiredcapability' => '',
                    'restrictedusers'    => true,
                    'component'          => null,
                    'downloadfiles'      => true,
                    'uploadfiles'        => true,
                ]);
            }

            //
            // 6. Adiciona funções que o usuário poderá executar.
            //
            foreach (self::SERVICE_FUNCTIONS as $function) {
                $wsman->add_external_function_to_service($function, $serviceid);
            }

            //
            // 7. Adiciona usuário ischolar como usuário autorizado.
            //

            // Verificando se usuário já está autorizado.
            $authusers  = $wsman->get_ws_authorised_users($serviceid);
            $found      = false;
            foreach ($authusers as $user) {
                if ($user->firstname == 'iScholar') {
                    $found = true;
                    break;
                }
            }
            // Se não está, autoriza.
            if ($found == false) {
                $ischolaruser = \core_user_external::get_users_by_field('username', ['ischolar']);
                $serviceuser = new \stdClass();
                $serviceuser->externalserviceid = $serviceid;
                $serviceuser->userid = $ischolaruser[0]['id'];
                $wsman->add_ws_authorised_user($serviceuser);
            }

            //
            // 8. Cria um token para o usuário
            //
            $ischolaruser = \core_user_external::get_users_by_field('username', ['ischolar']);
            $tokens       = $wsman->get_user_ws_tokens($ischolaruser[0]['id']);
            $found        = false;

            foreach ($tokens as $token) {           // Procurando token.
                if ($token->name == 'iScholar Authentication') {
                    if ($token->enabled != '1') {   // Token inválida é removida.
                        delete_user_ws_token($token->id);
                    } else {
                        $found       = true;
                        $tokenmoodle = $token->token;
                    }
                }
            }

            if ($found == false) {                  // Se token não existe, será criado.
                $tokenmoodle = external_generate_token(
                    EXTERNAL_TOKEN_PERMANENT,
                    $serviceid,
                    $ischolaruser[0]['id'],
                    \context_system::instance()
                );
            }

            //
            // 9. Ativando Web services documentation (documentação de desenvolvedor)
            //
            set_config('enablewsdocumentation', 1);

            //
            // 10. Testa o serviço
            //
            $payload = [
                'token_moodle' => $tokenmoodle,
                'url_moodle'   => $CFG->wwwroot
            ];
            $response = self::callischolar("configura_moodle_auth", $payload);

            if (isset($response['status']) && $response['status'] == 'sucesso') {
                set_config('schoolcode', $response['dados']['escola'], self::PLUGIN_ID);
            }

            //
            // 11. Ativando o serviço como tipo de autenticação
            //
            get_enabled_auth_plugins(true);                         // Fix the list of enabled auths.
            if (empty($CFG->auth)) {
                $authsenabled = array();
            } else {
                $authsenabled = explode(',', $CFG->auth);
            }

            $auth = str_replace('auth_', '', self::PLUGIN_ID);
            if (!in_array($auth, $authsenabled)) {
                array_unshift($authsenabled, $auth);
                $authsenabled = array_unique($authsenabled);
                $value        = implode(',', $authsenabled);
                add_to_config_log('auth', $CFG->auth, $value, 'core');
                set_config('auth', $value);
            }

            \core\session\manager::gc();                            // Remove stale sessions.
            \core_plugin_manager::reset_caches();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * Disable plugin in Moodle and integration into iScholar system.
     *
     * @return array A array indicating the status e error message if any.
     */
    public static function unsetintegration(): array {
        global $CFG;

        try {
            //
            // Desativando integração no iScholar.
            //

            $response = self::callischolar("desativa_moodle_auth");

            if (isset($result['status']) && $result['status'] == 'sucesso') {
                $result['status'] = true;
            } else {
                $result['status'] = false;
            }

            //
            // Desativando serviço de autenticação no moodle.
            //
            get_enabled_auth_plugins(true);                         // Fix the list of enabled auths.
            if (empty($CFG->auth)) {
                $authsenabled = array();
            } else {
                $authsenabled = explode(',', $CFG->auth);
            }

            $auth = str_replace('auth_', '', self::PLUGIN_ID);
            $key  = array_search($auth, $authsenabled);

            if ($key !== false) {
                unset($authsenabled[$key]);
                $value = implode(',', $authsenabled);

                add_to_config_log('auth', $CFG->auth, $value, 'core');
                set_config('auth', $value);
            }

            if ($auth == $CFG->registerauth) {
                set_config('registerauth', '');
            }

            \core\session\manager::gc(); // Remove stale sessions.
            \core_plugin_manager::reset_caches();
        } catch (\Exception $e) {
            $result = [
                'status' => false,
                'msg'    => $e->getMessage()
            ];
        }

        return $result;
    }


    /**
     * Check plugin configuration status.
     *
     * @return array An array containing the status of each verified configuration.
     */
    public static function healthcheck(): string {
        global $CFG, $OUTPUT;
        require_once($CFG->dirroot . '/user/externallib.php');
        require_once($CFG->dirroot . '/webservice/lib.php');

        $config         = self::getsettings();
        $ischolaruserid = null;
        $serviceid      = null;
        $tokenmoodle    = null;

        try {
            //
            // 0. Ativação do plugin
            //
            $results[0]['desc'] = 'pluginenabled';
            if ($config->enabled == '1') {
                $results[0]['status'] = true;
            } else {
                $results[0]['status'] = false;
            }

            //
            // 1. Ativação do webservice
            //
            $results[1]['desc'] = 'webservice';
            if ($CFG->enablewebservices == 1) {
                $results[1]['status'] = true;
            } else {
                $results[1]['status'] = false;
            }

            //
            // 2. Ativação do protocolo REST
            //
            $results[2]['desc'] = 'webserviceprotocols';
            $protocols = (isset($CFG->webserviceprotocols)) ? explode(',', $CFG->webserviceprotocols) : [];
            if (array_search('rest', $protocols) !== false) {
                $results[2]['status'] = true;
            } else {
                $results[2]['status'] = false;
            }

            //
            // 3. Usuário específico do plugin (ischolar)
            //
            $results[3]['desc'] = 'createuser';
            $user = \core_user_external::get_users_by_field('username', ['ischolar']);
            $user = \external_api::clean_returnvalue(\core_user_external::get_users_by_field_returns(), $user);
            if (count($user) > 0) {
                $results[3]['status'] = true;
                $ischolaruserid       = $user[0]['id'];
            } else {
                $results[3]['status'] = false;
            }

            //
            // 4. Capacidades do usuário (verifica se usuário é administrador)
            //
            $results[4]['desc'] = 'usercapability';
            $admins = explode(',', $CFG->siteadmins);
            if ($ischolaruserid !== null && array_search($ischolaruserid, $admins) !== false) {
                $results[4]['status'] = true;
            } else {
                $results[4]['status'] = false;
            }

            //
            // 5. Serviço
            //
            $results[5]['desc'] = 'selectservice';
            $wsman = new \webservice;
            $service = $wsman->get_external_service_by_shortname(self::SERVICE_ID);
            if ($service !== false) {
                $results[5]['status'] = true;
                $serviceid = $service->id;
            } else {
                $results[5]['status'] = false;
            }

            //
            // 6. Funções que o usuário pode executar
            //
            $results[6]['desc'] = 'servicefunctions';
            if ($serviceid !== null) {
                $results[6]['status'] = true;

                $externalfunctions     = $wsman->get_external_functions([$serviceid]);
                $externalfunctionnames = [];
                foreach ($externalfunctions as $function) {
                    $externalfunctionnames[] = $function->name;
                }

                $results[6]['status'] = true;
                foreach (self::SERVICE_FUNCTIONS as $function) {
                    if (in_array($function, $externalfunctionnames) == false) {
                        $results[6]['status'] = false;
                        break;
                    }
                }
            } else {
                $results[6]['status'] = false;
            }

            //
            // 7. Autorização do usuário iScholar
            //
            $results[7]['desc'] = 'serviceuser';
            $authusers = $wsman->get_ws_authorised_users($serviceid);
            $results[7]['status'] = false;
            foreach ($authusers as $user) {
                if ($user->firstname == 'iScholar') {
                    $results[7]['status'] = true;
                    break;
                }
            }

            //
            // 8. Token para o usuário iScholar
            //
            $results[8]['desc']     = 'createtoken';
            $tokens                 = $wsman->get_user_ws_tokens($ischolaruserid);
            $results[8]['status']   = false;
            $tokenmoodle            = '';
            foreach ($tokens as $token) {
                if ($token->name == 'iScholar Authentication' && $token->enabled == '1') {
                    $results[8]['status']   = true;
                    $tokenmoodle            = $token->token;
                    break;
                }
            }

            //
            // 9. Ativando Web services documentation (documentação de desenvolvedor)
            //
            $results[9]['desc'] = 'webservicedocs';
            if ($CFG->enablewsdocumentation == 1) {
                $results[9]['status'] = true;
            } else {
                $results[9]['status'] = false;
            }

            //
            // 10. Testa o serviço
            //
            $payload = [
                'token_moodle' => $tokenmoodle,
                'url_moodle'   => $CFG->wwwroot
            ];
            $response = self::callischolar("configura_moodle_auth", $payload);

            $results[10]['desc'] = 'servicetest';
            if (isset($response['status']) && $response['status'] == 'sucesso') {
                $results[10]['status'] = true;
                set_config('schoolcode', $response['dados']['escola'], self::PLUGIN_ID);
            } else {
                $results[10]['status'] = false;
                $results[10]['msg'] = (isset($response['msg'])) ? $response['msg'] :
                                        get_string('config:servicetestfail', self::PLUGIN_ID);
            }

            //
            // 11. Ativando o serviço como tipo de autenticação
            //
            $results[11]['desc'] = 'manageauth';
            $results[11]['status'] = false;

            if (empty($CFG->auth)) {
                $authsenabled = array();
            } else {
                $authsenabled = explode(',', $CFG->auth);
            }

            $auth = str_replace('auth_', '', self::PLUGIN_ID);
            if (in_array($auth, $authsenabled)) {
                $results[11]['status'] = true;
            }
        } catch (\Exception $e) {
            $result[] = [
                'desc'   => 'exception',
                'status' => $e->getMessage()
            ];
        }

        //
        // Exibindo resultado em html.
        //
        $config = self::getsettings();
        if (isset($config->enabled)) {
            $healthyplugin  = 1;

            if ($config->enabled == '1') {
                $html  = '<div style="background-color:#eeeeee; border:solid 1px #8f959e; padding:8px;">';
                foreach ($results as $i => $result) {
                    $html .= '<p style="display:flex; flex-direction:row; justify-content:space-between; '.
                                'align-items:center; color:#333333;">';
                    $html .= '<span>'.get_string('config:'.$result['desc'], self::PLUGIN_ID).'</span>';
                    $html .= ($result['status']) ?
                        '<img style="width:20px; height:20px; margin:0px 10px;" src="'.
                            $OUTPUT->image_url('yes', self::PLUGIN_ID).'" />' :
                        '<img style="width:22px; height:22px; margin:0px 10px;" src="'.
                            $OUTPUT->image_url('no', self::PLUGIN_ID).'" />';
                    $html .= '</p>';

                    if (isset($result['msg'])) {
                        $errordesc    = (get_string_manager()->string_exists('configerror:'.$result['msg'], self::PLUGIN_ID)) ?
                                        get_string('configerror:'.$result['msg'], self::PLUGIN_ID) :
                                        get_string('configerror:general', self::PLUGIN_ID).' '.$result['msg'];
                        $html .= '<p style="color:#882020; margin-left:35px; margin-top:-16px;">';
                        $html .= '<span>'.$errordesc.'<span>';
                        $html .= '</p>';
                    }

                    if ($result['status'] == false && $i != 10) {   // Ignora checks que botão de corrigir não resolve.
                        $healthyplugin = 0;
                    }
                }

                if ($healthyplugin == 0) {
                    $html .= '<p style="text-align:right; display:block; margin:30px 0px 0px 0px;">
                                <a href="'.$_SERVER['SCRIPT_NAME'].'?section='.self::SETTINGS_PAGE.'&fix=1"
                                    class="btn btn-secondary" type="button">'.
                                    get_string('configerror:fixbutton', self::PLUGIN_ID).
                                    '</a></p>';
                }

                $html .= '</div>';
            } else {
                $healthyplugin = 0;
                $html          = '<div><p style="color:#882020;">'.
                                 get_string('config:plugindisabled', self::PLUGIN_ID).
                                 '</p></div>';
                $result        = self::unsetintegration();
            }

            set_config('healthyplugin', $healthyplugin, self::PLUGIN_ID);

            return $html;
        }

        return '';
    }


    /**
     * Make a call to a iScholar system.
     * @param string $endpoint Api endpoint to call.
     * @param string $payload Data to be sent.
     * @return array A array containing the status and error messages if any.
     */
    public static function callischolar($endpoint='', $payload='') {
        try {
            $settings = self::getsettings();

            $headers = ["Content-Type: application/json"];
            if (isset($settings->tokenischolar)) {
                $headers[] = "X-Autorizacao: " . $settings->tokenischolar;
            }
            if (isset($settings->schoolcode)) {
                $headers[] = "X-Codigo-Escola: ". $settings->schoolcode;
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL            => "https://api.ischolar.app/integracoes/". $endpoint,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => '',
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => json_encode($payload),
            ));

            $response = json_decode(curl_exec($curl), true);

            curl_close($curl);
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }

        return $response;
    }


    /**
     * Change the user logged on.
     * @param int $user User id.
     * @return object A user object.
     */
    public static function setuser($user = null): object {
        global $CFG, $DB;

        if (is_object($user)) {
            $user = clone($user);
        } else if (!$user) {
            $user               = new \stdClass();
            $user->id           = 0;
            $user->mnethostid   = $CFG->mnet_localhost_id;
        } else {
            $user = $DB->get_record('user', array('id' => $user));
        }
        unset($user->description);
        unset($user->access);
        unset($user->preference);

        // Enusre session is empty, as it may contain caches and user specific info.
        \core\session\manager::init_empty_session();

        \core\session\manager::set_user($user);

        return $user;
    }


    /**
     * A small tool for debug.
     *
     * @param mixed $debug Some vabiable or content.
     * @param mixed $title Title of the debug box.
     */
    public static function debugbox($debug, $title=null): void {
        $debug = var_export($debug, true);
        $title = ($title !== null) ?
            "<p style='color:white; background:#333333; margin:0px; padding:5px;'><strong>{$title}</strong></p>" :
            '';
        echo "<div id='debugbox' style='width:100%; margin-top:60px; background:lightgray; border:solid 1px black;'>
            {$title}
            <pre style='margin:7px;'>{$debug}</pre>
        </div>";
    }


    /* *
     *
     *
     * @param int $areaid
     * @param string $contenthash
     * @return bool
     * @throws \dml_exception
     */
}
