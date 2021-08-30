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
 * Settings page
 *
 * @package   auth_ischolar
 * @copyright 2021, iScholar - Gestão Escolar
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_ischolar\ischolar;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $OUTPUT, $CFG;

    // Página de configurações.
    $settings = new admin_settingpage(
        ischolar::SETTINGS_PAGE,
        new lang_string('ischolarsettings', ischolar::PLUGIN_ID)
    );

    // Cabeçalho (topo).
    if ($ADMIN->fulltree && isset($_GET['section']) && $_GET['section'] == ischolar::SETTINGS_PAGE) {
        $settings->add(
            new admin_setting_heading(
                ischolar::PLUGIN_ID.'/header',
                '',
                '<div style="margin:10px 0px 30px 0px; text-align:center;
                        display:flex; flex-direction:row; justify-content:space-around; align-items:center;">
                    <a href="https://ischolar.com.br" target="_blank">
                        <img width="250" src="'.$OUTPUT->image_url('logo1', ischolar::PLUGIN_ID).'" />
                    </a>
                    <h2 style="margin:0px 0px 0px 10px; display:inline-block !important; font-size:140%;">'
                        .new lang_string('ischolarsettings', ischolar::PLUGIN_ID).'</h2>
                </div>'
            )
        );

        // Ativa / desativa.
        $settings->add(
            new admin_setting_configcheckbox(
                ischolar::PLUGIN_ID.'/enabled',
                get_string('settings:enabled', ischolar::PLUGIN_ID),
                get_string('settings:enabledinfo', ischolar::PLUGIN_ID),
                '1', '1', '0'
            )
        );

        // Token ischolar.
        $settings->add(
            new admin_setting_configtextarea(
                ischolar::PLUGIN_ID.'/tokenischolar',
                get_string('settings:tokenischolar', ischolar::PLUGIN_ID),
                get_string('settings:tokenischolarinfo', ischolar::PLUGIN_ID),
                '',
                PARAM_RAW, '80', '8'
            )
        );

        // Status de configuração.
        $checkup = ischolar::healthcheck();
        if ($checkup != '') {
            $settings->add(
                new admin_setting_description(
                    ischolar::PLUGIN_ID.'/healthcheck',
                    get_string('settings:healthcheck', ischolar::PLUGIN_ID),
                    $checkup
                )
            );
        }

        // Se o usuário clicou no botão para resetar as configurações.
        if (isset($_GET['fix']) && $_GET['fix'] == 1) {
            ischolar::setintegration();
            redirect($_SERVER['SCRIPT_NAME'].'?section='.ischolar::SETTINGS_PAGE);
        }

        // Se o usuário clicou no botão de salvar configurações.
        if ($data = data_submitted() and confirm_sesskey() and
                isset($data->action) and $data->action == 'save-settings') {
            if ($data->s_auth_ischolar_enabled == '1') {
                ischolar::setintegration();
            } else {
                ischolar::unsetintegration();
            }
        }

    } // Fim de if admin fulltree.
}
