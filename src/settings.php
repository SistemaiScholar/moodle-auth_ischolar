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
 * @category  authentication
 * @copyright 2021, iScholar - Gestão Escolar
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_ischolar\config;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $OUTPUT, $CFG;

    // Página de configurações
    $settings = new admin_settingpage(
        config::SETTINGS_PAGE, 
        new lang_string('ischolarsettings', config::PLUGIN_ID)
    );
    
    // Cabeçalho (topo)
    if ($ADMIN->fulltree && $_SERVER['REQUEST_URI'] == '/moodle/admin/settings.php?section='.config::SETTINGS_PAGE) {
        $settings->add(
            new admin_setting_heading(
                config::PLUGIN_ID.'/header', 
                '',
                '<div style="margin:10px 0px 30px 0px; text-align:center; 
                        display:flex; flex-direction:row; justify-content:space-around; align-items:center;">
                    <a href="https://ischolar.com.br" target="_blank">
                        <img width="300" src="'.$OUTPUT->image_url('logo1', config::PLUGIN_ID).'" />
                    </a>
                    <h2 style="margin:0px 0px 0px 10px; display:inline-block !important; font-size:150%;">'
                        .new lang_string('ischolarsettings', config::PLUGIN_ID).'</h2>
                </div>' 
            )
        );
        
        // Ativa / desativa
        $settings->add(
            new admin_setting_configcheckbox(
                config::PLUGIN_ID.'/enabled', 
                get_string('settings:enabled', config::PLUGIN_ID), 
                get_string('settings:enabledinfo', config::PLUGIN_ID),
                '1', '1', '0'
            )
        );
        
        // Token ischolar
        $settings->add(
            new admin_setting_configtextarea(
                config::PLUGIN_ID.'/tokenischolar',
                get_string('settings:tokenischolar', config::PLUGIN_ID),
                get_string('settings:tokenischolarinfo', config::PLUGIN_ID),
                '',
                PARAM_RAW,'80','8'
            )
        );
    
        // Status de configuração
        $config = config::getsettings();
        if (isset($config->enabled)) {
            $healthyplugin  = '1';
            
            if ($config->enabled == '1') {
                $results = config::setintegration();
                
                $healthcheck  = '<div>';
                foreach ($results as $i=>$result){
                    $healthcheck .= '<p style="display:flex; flex-direction:row; justify-content:space-between; align-items:center; color:#333333;">';
                    $healthcheck .= '<span>'.get_string('config:'.$result['desc'], config::PLUGIN_ID).'</span>';
                    $healthcheck .=  ($result['status']) ? 
                        '<img style="width:20px; height:20px; margin:0px 10px;" src="'.$OUTPUT->image_url('yes', config::PLUGIN_ID).'" />' :
                        '<img style="width:22px; height:22px; margin:0px 10px;" src="'.$OUTPUT->image_url('no', config::PLUGIN_ID).'" />';
                    $healthcheck .= '</p>';
                    
                    if (isset($result['msg'])) {
                        $healthcheck .= '<p style="color:#882020; margin-left:35px; margin-top:-16px;">';
                        $healthcheck .= $result['msg'];
                        $healthcheck .= '</p>';
                    }
                    
                    if ($result['status'] == false) {
                        $healthyplugin = '0';
                    }
                }
                
                $healthcheck .= '</div><p>&nbsp;</p>';
            }
            else {
                $healthyplugin = '0';
                $healthcheck   = '<div><p style="color:#882020;">Plugin desativado</p></div>';
                $result        = config::unsetintegration();
            }
            
            $settings->add(
                new admin_setting_description(
                    config::PLUGIN_ID.'/healthcheck',
                    get_string('settings:healthcheck', config::PLUGIN_ID),
                    $healthcheck
                )
            );
            
            set_config('healthyplugin', $healthyplugin, config::PLUGIN_ID);
        }
    } // Fim de if admin fulltree
    //$ADMIN->add('auth', $settings);
}