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
 * Version file
 *
 * @package   auth_ischolar
 * @copyright 2021, iScholar - Gestão Escolar
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component    = "auth_ischolar";     // Tipo_nome.
$plugin->release      = "1.0.0";             // Versão do plugin em formato legível.
$plugin->version      = 2021102500;          // Versão do plugin em formato data+contador.
$plugin->maturity     = MATURITY_STABLE;     // MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC or MATURITY_STABLE.
$plugin->dependencies = [];                  // Dependências.
$plugin->requires     = 2013051400;          // Specifies the minimum version number of Moodle core that this plugin requires.
