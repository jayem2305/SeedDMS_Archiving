<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010 Matteo Lucarelli
//    Copyright (C) 2010-2016 Uwe Steinmann
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.LogInit.php");
include("../inc/inc.Language.php");
include("../inc/inc.Init.php");
include("../inc/inc.Extension.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");

if (!$user->isAdmin()) {
	UI::exitError(getMLText("admin_tools"),getMLText("access_denied"));
}

if (isset($_POST["action"])) $action = $_POST["action"];
else $action = null;

// Create new group --------------------------------------------------------
if ($action == "addgroup") {

	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('addgroup')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	$name = $_POST["name"];
	if(!$name) {
		UI::exitError(getMLText("admin_tools"),getMLText("group_name_missing"));
	}
	$comment = $_POST["comment"];
	if ($settings->_strictFormCheck && !$comment) {
		UI::exitError(getMLText("admin_tools"),getMLText("group_comment_missing"));
	}

	if (is_object($dms->getGroupByName($name))) {
		UI::exitError(getMLText("admin_tools"),getMLText("group_exists"));
	}

	// Define the encryption method and key
	$encryption_method = 'AES-256-CBC';
	$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';

	// Encrypt the name
	$encryption_iv_name = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
	$encrypted_name = openssl_encrypt($name, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_name);
	$combined_name = $encryption_iv_name . $encrypted_name;
	$iv_base64_name = base64_encode($combined_name);

	// Encrypt the comment
	$encryption_iv_comment = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
	$encrypted_comment = openssl_encrypt($comment, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_comment);
	$combined_comment = $encryption_iv_comment . $encrypted_comment;
	$iv_base64_comment = base64_encode($combined_comment);

	// Use the encrypted name and comment
	$name = $iv_base64_name;
	$comment = $iv_base64_comment;

	$newGroup = $dms->addGroup($name, $comment);
	if (!$newGroup) {
		UI::exitError(getMLText("admin_tools"),getMLText("error_occured"));
	}
	
	// Encrypt the newGroup ID
	$groupid = $newGroup->getID();
	$encryption_iv_groupid = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
	$encrypted_groupid = openssl_encrypt($groupid, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_groupid);
	$combined_groupid = $encryption_iv_groupid . $encrypted_groupid;
	$iv_base64_groupid = base64_encode($combined_groupid);

	$groupid = $iv_base64_groupid;
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_add_group')));

	add_log_line("&action=addgroup&name=".$name);
}

// Delete group -------------------------------------------------------------
else if ($action == "removegroup") {
	
	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('removegroup')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["groupid"]) || !is_numeric($_POST["groupid"]) || intval($_POST["groupid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}
	
	$group = $dms->getGroup($_POST["groupid"]);
	if (!is_object($group)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}

	if (!$group->remove($user)) {
		UI::exitError(getMLText("admin_tools"),getMLText("error_occured"));
	}
	
	$groupid = '';

	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_rm_group')));

	add_log_line("?groupid=".$_POST["groupid"]."&action=removegroup");
}

// Modifiy group ------------------------------------------------------------
else if ($action == "editgroup") {

	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('editgroup')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["groupid"]) || !is_numeric($_POST["groupid"]) || intval($_POST["groupid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}
	
	$groupid=$_POST["groupid"];
	$group = $dms->getGroup($groupid);
	
	if (!is_object($group)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}
	
	$name = $_POST["name"];
	$comment = $_POST["comment"];

	if ($group->getName() != $name)
		$group->setName($name);
	if ($group->getComment() != $comment)
		$group->setComment($comment);
		
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_edit_group')));

	add_log_line("?groupid=".$_POST["groupid"]."&action=editgroup");
}

// Add user to group --------------------------------------------------------
else if ($action == "addmember") {

	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('addmember')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["groupid"]) || !is_numeric($_POST["groupid"]) || intval($_POST["groupid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}
	
	$groupid=$_POST["groupid"];
	$group = $dms->getGroup($groupid);
	
	if (!is_object($group)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}

	if (!isset($_POST["userid"]) || !is_numeric($_POST["userid"]) || intval($_POST["userid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_user_id"));
	}
	
	$newMember = $dms->getUser($_POST["userid"]);
	if (!is_object($newMember)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_user_id"));
	}

	if (!$group->isMember($newMember)){
		$group->addUser($newMember);
		if (isset($_POST["manager"])) $group->toggleManager($newMember);
	}
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_add_group_member')));

	add_log_line("?groupid=".$groupid."&userid=".$_POST["userid"]."&action=addmember");
}

// Remove user from group --------------------------------------------------
else if ($action == "rmmember") {

	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('rmmember')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["groupid"]) || !is_numeric($_POST["groupid"]) || intval($_POST["groupid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}
	
	$groupid=$_POST["groupid"];
	$group = $dms->getGroup($groupid);
	
	if (!is_object($group)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}

	if (!isset($_POST["userid"]) || !is_numeric($_POST["userid"]) || intval($_POST["userid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_user_id"));
	}
	
	$oldMember = $dms->getUser($_POST["userid"]);
	if (!is_object($oldMember)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_user_id"));
	}

	$group->removeUser($oldMember);
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_rm_group_member')));

	add_log_line("?groupid=".$groupid."&userid=".$_POST["userid"]."&action=rmmember");
}

// toggle manager flag
else if ($action == "tmanager") {

	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('tmanager')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["groupid"]) || !is_numeric($_POST["groupid"]) || intval($_POST["groupid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}
	
	$groupid=$_POST["groupid"];
	$group = $dms->getGroup($groupid);
	
	if (!is_object($group)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_group_id"));
	}

	if (!isset($_POST["userid"]) || !is_numeric($_POST["userid"]) || intval($_POST["userid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_user_id"));
	}
	
	$usertoedit = $dms->getUser($_POST["userid"]);
	if (!is_object($usertoedit)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_user_id"));
	}
	
	$group->toggleManager($usertoedit);
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_toogle_group_manager')));

	add_log_line("?groupid=".$groupid."&userid=".$_POST["userid"]."&action=tmanager");
}

header("Location:../out/out.GroupMgr.php?groupid=".$groupid);

?>
