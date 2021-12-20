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

    // Settings page.
    $settings = new admin_settingpage(
        ischolar::SETTINGS_PAGE,
        new lang_string('ischolarsettings', ischolar::PLUGIN_ID)
    );

    // Header (top).
    if ($ADMIN->fulltree && isset($_GET['section']) && $_GET['section'] == ischolar::SETTINGS_PAGE) {
        if ($CFG->version < 2017051500) {  // If the version of moodle is older than 3.3.
            $settings->add(
                new admin_setting_heading(
                    ischolar::PLUGIN_ID.'/header',
                    '',
                    '<div style="margin:10px 0px 30px 0px; text-align:center;
                            display:flex; flex-direction:row; justify-content:space-around; align-items:center;">
                        <a href="https://ischolar.com.br" target="_blank">
                            <img width="250" src="'.$CFG->wwwroot.'/auth/ischolar/pix/logo1.png" />
                        </a>
                        <h2 style="margin:0px 0px 0px 10px; display:inline-block !important; font-size:140%;">'
                            .new lang_string('ischolarsettings', ischolar::PLUGIN_ID).'</h2>
                    </div>'
                )
            );
        } else {
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
        }

        // Enable / Disable.
        $settings->add(
            new admin_setting_configcheckbox(
                ischolar::PLUGIN_ID.'/enabled',
                get_string('settings:enabled', ischolar::PLUGIN_ID),
                get_string('settings:enabledinfo', ischolar::PLUGIN_ID),
                '1', '1', '0'
            )
        );

        // School code.
        $settings->add(
            new admin_setting_configtext(
                ischolar::PLUGIN_ID.'/schoolcode',
                new lang_string('settings:schoolcode', ischolar::PLUGIN_ID),
                new lang_string('settings:schoolcodeinfo', ischolar::PLUGIN_ID),
                '', PARAM_RAW, 59
            )
        );

        // Ischolar token.
        $settings->add(
            new admin_setting_configtextarea(
                ischolar::PLUGIN_ID.'/tokenischolar',
                get_string('settings:tokenischolar', ischolar::PLUGIN_ID),
                get_string('settings:tokenischolarinfo', ischolar::PLUGIN_ID),
                '',
                PARAM_RAW, '80', '8'
            )
        );

        // Configuration status.
        $checkup = ischolar::healthcheck();
        if ($checkup != '') {
            if ((int) $CFG->version < 2018120300) {     // If Moodle version is under 3.6.
                $settings->add(
                    new admin_setting_configempty (
                        ischolar::PLUGIN_ID.'/healthcheck',
                        get_string('settings:healthcheck', ischolar::PLUGIN_ID),
                        $checkup
                    )
                );
            } else {
                $settings->add(
                    new admin_setting_description(
                        ischolar::PLUGIN_ID.'/healthcheck',
                        get_string('settings:healthcheck', ischolar::PLUGIN_ID),
                        $checkup
                    )
                );
            }
        }

        // If the user clicked the button to reset the settings.
        if (isset($_GET['fix']) && $_GET['fix'] == 1) {
            ischolar::setintegration();
            redirect($_SERVER['SCRIPT_NAME'].'?section='.ischolar::SETTINGS_PAGE);
        }

        // If the user clicked the save settings button.
        if ($data = data_submitted() and confirm_sesskey() and
                isset($data->action) and $data->action == 'save-settings') {
            if ($data->s_auth_ischolar_enabled == '1') {
                ischolar::setintegration();
            } else {
                ischolar::unsetintegration();
            }
        }

        // End of if admin fulltree.
    } else if ($CFG->version < 2016052300 && $ADMIN->fulltree) {      // For version 3.0 or earlier.
        if ($data = data_submitted() and confirm_sesskey() and
                isset($data->section) and $data->section == ischolar::SETTINGS_PAGE) {
            set_config('enabled', $data->s_auth_ischolar_enabled, ischolar::PLUGIN_ID);
            set_config('schoolcode', $data->s_auth_ischolar_schoolcode, ischolar::PLUGIN_ID);
            set_config('tokenischolar', $data->s_auth_ischolar_tokenischolar, ischolar::PLUGIN_ID);

            if ($data->s_auth_ischolar_enabled == '1') {
                ischolar::setintegration();
                header("Location: settings.php?section=".ischolar::SETTINGS_PAGE);
            } else {
                ischolar::unsetintegration();
                header("Location: settings.php?section=".ischolar::SETTINGS_PAGE);
            }
        }
    }
}
