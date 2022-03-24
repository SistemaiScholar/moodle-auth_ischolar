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
 * Language file for auth_ischolar, en-US
 *
 * File         auth_ischolar.php
 * Encoding     UTF-8
 *
 * @package     auth_ischolar
 *
 * @copyright   2021 - iScholar
 * @author      iScholar Gestão Escolar
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Autenticação iScholar <> Moodle';
$string['privacy:metadata'] = 'O plugin '.$string['pluginname'].' não armazena nenhum dado pessoal.';

$string['ischolarsettings'] = 'Autenticação iScholar <> Moodle';
$string['settings:enabled'] = 'Ativado:';
$string['settings:enabledinfo'] = 'Ativa / desativa este plugin.';
$string['settings:tokenischolar'] = 'Token do iScholar:';
$string['settings:tokenischolarinfo'] = 'Token de acesso de seu sistema iScholar. Clique <a href="https://ajuda.ischolar.com.br/pt-BR/articles/5491781-autenticacao-ischolar-moodle" target="_blank">aqui</a> para saber mais.';
$string['settings:healthcheck'] = 'Status de configuração:';
$string['settings:initialsetupinfo'] = '';
$string['settings:userlastname'] = 'Integrações';
$string['settings:userdescription'] = 'NÃO ALTERE E NÃO REMOVA ESTE USUÁRIO! A alteração ou remoção deste usuário acarretará no mal funcionamento em plugins do iScholar.';
$string['settings:schoolcode']  = 'Código da escola:';
$string['settings:schoolcodeinfo'] = 'Código da escola no iScholar.';

$string['config:pluginenabled'] = 'Ativação do plugin.';
$string['config:webservice'] = 'Permissão do Moodle para conexão com sistemas externos.';
$string['config:webserviceprotocols'] = 'Protocolo de comunicação.';
$string['config:createuser'] = 'Usuário de integração.';
$string['config:usercapability'] = 'Permissões do usuário de integração.';
$string['config:selectservice'] = 'Serviço iScholar.';
$string['config:servicefunctions'] = 'Funções do serviço iScholar.';
$string['config:serviceuser'] = 'Usuário do serviço.';
$string['config:createtoken'] = 'Token de acesso do Moodle.';
$string['config:webservicedocs'] = 'Configuração de documentações no Moodle.';
$string['config:servicetest'] = 'Configuração no sistema iScholar.';
$string['config:servicetestfail'] = 'Falha na comunicação com o iScholar.';
$string['config:manageauth'] = 'Ativação do método de autenticação.';
$string['config:plugindisabled'] = 'Plugin desativado.';

$string['configerror:general'] = 'Falha na comunicação com o iScholar:';
$string['configerror:communication'] = 'O iScholar não conseguiu estabelecer comunicação com o Moodle.';
$string['configerror:tokeninvalido'] = 'O Token do iScholar fornecido é inválido para esta aplicação.';
$string['configerror:tokenexpirado'] = 'O Token do iScholar fornecido expirou. Acesse seu iScholar para gerar um novo token.';
$string['configerror:escoladesconhecida'] = 'A escola associada ao Token do iScholar é desconhecida.';
$string['configerror:escolainvalida'] = 'A escola associada ao Token do iScholar é diferente do Código da escola fornecido.';
$string['configerror:integracaodesconhecida'] = 'A integração associada ao Token do iScholar fornecido é desconhecida.';
$string['configerror:tokennaoencontrado'] = 'Token do iScholar não encontrado.';
$string['configerror:fixbutton'] = 'Corrigir configurações';
$string['configerror:passgenerror'] = 'Houve uma falha ao tentar criar uma senha para o usuário de integração.';
