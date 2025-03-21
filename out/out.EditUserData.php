<?php

/**
 * MyDMS. Document Management System
 * Copyright (C) 2002-2005 Markus Westphal
 * Copyright (C) 2006-2008 Malcolm Cowe
 * Copyright (C) 2010 Matteo Lucarelli
 * Copyright (C) 2010-2024 Uwe Steinmann
 *
 * PHP version 8
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * @category SeedDMS
 * @package  SeedDMS
 * @author   Uwe Steinmann <info@seeddms.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://www.seeddms.org Main Site
 */

if (!isset($settings)) {
	require_once "../inc/inc.Settings.php";
}
require_once "inc/inc.Utils.php";
require_once "inc/inc.LogInit.php";
require_once "inc/inc.Language.php";
require_once "inc/inc.Init.php";
require_once "inc/inc.Extension.php";
require_once "inc/inc.DBInit.php";
require_once "inc/inc.ClassUI.php";
require_once "inc/inc.Authentication.php";

$tmp = explode('.', basename($_SERVER['SCRIPT_FILENAME']));
$view = UI::factory($theme, $tmp[1], array('dms' => $dms, 'user' => $user));
$accessop = new SeedDMS_AccessOperation($dms, $user, $settings);

if ($user->isGuest()) {
	UI::exitError(getMLText("edit_user_details"), getMLText("access_denied"));
}

if (!$user->isAdmin() && ($settings->_disableSelfEdit)) {
	UI::exitError(getMLText("edit_user_details"), getMLText("access_denied"));
}


if ($view) {
	$view->setParam('enableuserimage', $settings->_enableUserImage);
	$view->setParam('enablelanguageselector', $settings->_enableLanguageSelector);
	$view->setParam('enablethemeselector', $settings->_enableThemeSelector);
	$view->setParam('passwordstrength', $settings->_passwordStrength);
	$view->setParam('disablechangepassword', $settings->_disableChangePassword);
	$view->setParam('httproot', $settings->_httpRoot);
	$view->setParam('accessobject', $accessop);
	$view($_GET);
	exit;
}
