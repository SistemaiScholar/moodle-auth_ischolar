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
 * @package   auth_ischolar
 * @copyright 2021, Walter Alexandre <walter@ischolar.com.br>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
/**
 * Integração do sistemas iScholar.
 *
 * Realiza as configurações iniciais para a integração entre sistemas iScholar e Moodle.
 *
 * @package    auth_ischolar
 * @copyright  2021 iScholar
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_ischolar;

defined('MOODLE_INTERNAL') || die();

class config {
    /**
     * plugin's internal name
     */
    const PLUGIN_ID      = 'auth_ischolar';
    const SETTINGS_PAGE  = 'authsettingischolar';
    const PLUGIN_VERSION = '1.0.0';
    
     /**
     * Get settings for iScholar
     *
     * @return object (a collection of settings parameters/values)
     */
    public static function getsettings(): object {
        $config = get_config(self::PLUGIN_ID);
        
        return $config;
    }
    
    /**
     * Initial setup (10 steps to setup a webservice)
     *
     * @return bool true if success, false if failure
     */
    public static function setintegration(): array {
        global $CFG;
        require_once($CFG->dirroot . '/user/externallib.php');
        
        // Seguindo os passos descritos em 'Dashboard / Site administration / Server / Web services / Overview'
        try {
            $result[0]['desc']   = 'pluginenabled';
            $result[0]['status'] = true;
            
            //
            // 1. Ativando webservice
            //
            $CFG->enablewebservices = 1;
            
            $result[1]['desc']   = 'webservice';
            $result[1]['status'] = true;

            
            //
            // 2. Ativando protocolo REST
            //
            if (!isset($CFG->webserviceprotocols) || $CFG->webserviceprotocols == '') {
                set_config('webserviceprotocols', 'rest');
            }
            else {
                $services = explode(',', $CFG->webserviceprotocols);
                if (array_search('rest', $services) === false) {
                    $services[] = 'rest';
                    set_config('webserviceprotocols', implode(',', $services));
                }
            }
            
            $result[2]['desc']   = 'webserviceprotocols';
            $result[2]['status'] = true;

            
            //
            // 3. Criando uusário específico (ischolar)
            //
            
            // Busca usuário ischolar
            $user = \core_user_external::get_users_by_field('username', ['ischolar']);
            $user = \external_api::clean_returnvalue(\core_user_external::get_users_by_field_returns(), $user);
            
            // Se usuário ischolar não existe, será criado
            if (count($user) == 0) {
                $user1 = array(
                    'username'    => 'ischolar',
                    'password'    => '1Sch0lar@2021',
                    'idnumber'    => 'ischolar',
                    'firstname'   => 'iScholar',
                    'lastname'    => 'Integrações',
                    'email'       => 'walter@ischolar.com.br',
                    'maildisplay' => 0,
                    'description' => 'NÃO ALTERE E NÃO REMOVA ESTE USUÁRIO. A alteração ou remoção deste usuário acarretará no mal funcionamento da integração iScholar.',
                );
                $user = \core_user_external::create_users([$user1]);
                $user = \external_api::clean_returnvalue(\core_user_external::create_users_returns(), $user);
                
                // Altera usuário (O moodle não permite criar usuários de webservice, mas permite alterar o usuário para webservice)
                $user1['id']   = $user[0]['id'];
                $user1['auth'] = 'webservice';
                $user          = \core_user_external::update_users([$user1]);
            } 
            // Se usuário já existe, é resetado
            else {
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
                    'description' => 'NÃO ALTERE E NÃO REMOVA ESTE USUÁRIO. A alteração ou remoção deste usuário acarretará no mal funcionamento da integração iScholar.',
                );
                \core_user_external::update_users([$user1]);
            }
            
            $result[3]['desc']   = 'createuser';
            $result[3]['status'] = true;
            
            
            //
            // 4. Verificando capacidades do usuário
            //      Coloca o usuário ischolar no grupo de administradores
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
                $logstringold        = implode(', ', $admins);      // log antes
                $admins[$idischolar] = $idischolar;                 // alteração
                $logstringnew        = implode(', ', $admins);      // log depois
                
                set_config('siteadmins', implode(',', $admins));
                add_to_config_log('siteadmins', $logstringold, $logstringnew, 'core');
            }
            
            $result[4]['desc']   = 'usercapability';
            $result[4]['status'] = true;
            
            //
            // 5. Selecionando um serviço
            //
            require_once($CFG->dirroot . '/webservice/lib.php');
            $wsman      = new \webservice;
            $service    = $wsman->get_external_service_by_shortname('ischolar_access');
            if ($service == false) {                                        // Cria serviço caso não exista
                $serviceid  = $wsman->add_external_service((object)[
                    'name'               => 'iScholar Access',
                    'shortname'          => 'ischolar_access',
                    'enabled'            => 1,
                    'requiredcapability' => '',
                    'restrictedusers'    => true,
                    'component'          => NULL,
                    'downloadfiles'      => true,
                    'uploadfiles'        => true,
                ]);
            }   
            else {                                                          // Se serviço já existe, reseta os parâmetros
                $serviceid = $service->id;
                $wsman->update_external_service((object)[
                    'id'                 => $serviceid,
                    'name'               => 'iScholar Access',
                    'shortname'          => 'ischolar_access',
                    'enabled'            => 1,
                    'requiredcapability' => '',
                    'restrictedusers'    => true,
                    'component'          => NULL, //self::PLUGIN_ID,
                    'downloadfiles'      => true,
                    'uploadfiles'        => true,
                ]);
            }
            
            $result[5]['desc']   = 'selectservice';
            $result[5]['status'] = true;


            //
            // 6. Adiciona funções que o usuário poderá executar
            //
            
            // Create course categories
            $wsman->add_external_function_to_service('core_course_create_categories', $serviceid);
            // Create new courses
            $wsman->add_external_function_to_service('core_course_create_courses', $serviceid);
            // Return category details
            $wsman->add_external_function_to_service('core_course_get_categories', $serviceid);
            // Return course details
            $wsman->add_external_function_to_service('core_course_get_courses', $serviceid);
            // Get courses matching a specific field (id/s, shortname, idnumber, category)
            $wsman->add_external_function_to_service('core_course_get_courses_by_field', $serviceid);
            // Search courses by (name, module, block, tag)
            $wsman->add_external_function_to_service('core_course_search_courses', $serviceid);
            
            // Retrieve users' information for a specified unique field 
            $wsman->add_external_function_to_service('core_user_get_users_by_field', $serviceid);
            
            $result[6]['desc']   = 'servicefunctions';
            $result[6]['status'] = true;
            
            
            //
            // 7. Adiciona usuário ischolar como usuário autorizado
            //
            
            // Verificando se usuário já está autorizado
            $authusers  = $wsman->get_ws_authorised_users($serviceid);
            $found      = false;
            foreach ($authusers as $user) {
                if ($user->firstname == 'iScholar') {
                    $found = true;
                    break;
                }
            }
            // Se não está, autoriza
            if ($found == false) {
                $ischolaruser = \core_user_external::get_users_by_field('username', ['ischolar']);
                $serviceuser = new \stdClass();
                $serviceuser->externalserviceid = $serviceid;
                $serviceuser->userid = $ischolaruser[0]['id'];
                $wsman->add_ws_authorised_user($serviceuser);
            }
            
            $result[7]['desc']   = 'serviceuser';
            $result[7]['status'] = true;
            
            
            //
            // 8. Cria um token para o usuário
            //
            $ischolaruser = \core_user_external::get_users_by_field('username', ['ischolar']);
            $tokens       = $wsman->get_user_ws_tokens($ischolaruser[0]['id']);
            // Se token não existe, será criado
            if (count($tokens) == 0) {      
                $tokenmoodle = external_generate_token(
                    EXTERNAL_TOKEN_PERMANENT, 
                    $serviceid, 
                    $ischolaruser[0]['id'], 
                    \context_system::instance()
                );
            }
            // Se token existe, apenas busca o token
            else {                          
                $tokenmoodle = end($tokens)->token;
            }
            
            $result[8]['desc']   = 'createtoken';
            $result[8]['status'] = true;
            
            
            //
            // 9. Ativando Web services documentation (documentação de desenvolvedor)
            //
            $CFG->enablewsdocumentation = 1;
            
            $result[9]['desc']   = 'webservicedocs';
            $result[9]['status'] = true;
            
            
            //
            // 10. Testa o serviço
            //
            $settings    = self::getsettings();
            
            // HTTP_REFERER e SCRIPT_URI não funcionam no moodle.
            // HTTP_HOST não informa se o protocolo é http ou https, então a API no ischolar terá que tentar ambos.
            $url_moodle  = $_SERVER['HTTP_HOST']."/moodle/webservice/rest/server.php";

            $payload = [
                'token_moodle' => $tokenmoodle,
                'url_moodle'   => $url_moodle,     // "vitorhugov.sg-host.com/moodle/webservice/rest/server.php" //
            ];
            $response = self::callischolar("configura_ischolar", $payload);

            $result[10]['desc'] = 'servicetest';
            if (isset($response['status']) && $response['status'] == 'sucesso') {
                $result[10]['status'] = true;
                set_config('schoolcode', $response['dados']['escola'], self::PLUGIN_ID);
            }
            else {
                $result[10]['status']= false;
                $result[10]['msg'] = (isset($response['msg'])) ? $response['msg'] : get_string('config:servicetestfail', config::PLUGIN_ID);
            }
            
            
            //
            // 11. Ativando o serviço como tipo de autenticação
            //
            get_enabled_auth_plugins(true);                         // fix the list of enabled auths
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
            
            $result[11]['desc']   = 'manageauth';
            $result[11]['status'] = true;
            
            
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }
        
        return $result;
    }
    
    public static function unsetintegration(): array {
        global $CFG;
        
        try {
            //
            // Desativando integração no iScholar
            //
            
            $response = self::callischolar("desativa_integracao");

            if (isset($result['status']) && $result['status'] == 'sucesso')
                $result['status'] = true;
            else 
                $result['status'] = false;
                
            //
            // Desativando serviço de autenticação no moodle
            //
            get_enabled_auth_plugins(true);                         // fix the list of enabled auths
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
        } 
        catch (\Exception $e) {
            $result = [
                'status' => false,
                'msg'    => $e->getMessage()
            ];
        }
        
        return $result;
    }
    
    public static function callischolar($endpoint='', $payload=''): array {
        try {
            $settings = self::getsettings();
            
            $headers = ["Content-Type: application/json"];
            if (isset($settings->tokenischolar))
                $headers[] = "X-Autorizacao: " . $settings->tokenischolar;
            if (isset($settings->schoolcode))
                $headers[] = "X-Codigo-Escola: ". $settings->schoolcode;
            
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
                //CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POSTFIELDS     => json_encode($payload),
            ));
            
            $response = json_decode(curl_exec($curl), true);
            
            curl_close($curl);
        }
        catch (\Exception $e) {
            $response = $e->getMessage();
        }
        
        return $response;
    }
    
    public static function setuser($user = null) {
        global $CFG, $DB;

        if (is_object($user)) {
            $user = clone($user);
        } else if (!$user) {
            $user               = new \stdClass();
            $user->id           = 0;
            $user->mnethostid   = $CFG->mnet_localhost_id;
        } else {
            $user = $DB->get_record('user', array('id'=>$user));
        }
        unset($user->description);
        unset($user->access);
        unset($user->preference);

        // Enusre session is empty, as it may contain caches and user specific info.
        \core\session\manager::init_empty_session();

        \core\session\manager::set_user($user);
        
        return $user;
    }
    
    
    public static function debugbox($debug): void {
        $debug = var_export($debug, true);
        echo "<div id='debugbox' style='position:fixed; top:10px; left:10px; width:500px; background:lightgray; opacity:0.9; z-index:1000000; padding:5px; border:solid 1px black;'><pre>$debug</pre></div>";
    }
    
    /**
     * 
     *
     * @param int $areaid
     * @param string $contenthash
     * @return bool
     * @throws \dml_exception
     */
}