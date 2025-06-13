<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005 Markus Westphal
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

if(!isset($settings))
	require_once("../inc/inc.Settings.php");
require_once("inc/inc.Utils.php");
require_once("inc/inc.LogInit.php");
require_once("inc/inc.Language.php");
require_once("inc/inc.Init.php");
require_once("inc/inc.Extension.php");
require_once("inc/inc.DBInit.php");
require_once("inc/inc.ClassUI.php");
require_once("inc/inc.ClassAccessOperation.php");
require_once("inc/inc.Authentication.php");

$tmp = explode('.', basename($_SERVER['SCRIPT_FILENAME']));
$view = UI::factory($theme, $tmp[1], array('dms'=>$dms, 'user'=>$user));
$accessop = new SeedDMS_AccessOperation($dms, $user, $settings);
if (!$accessop->check_view_access($view, $_GET)) {
	UI::exitError(getMLText("document_title"), getMLText("access_denied"));
}

if (!isset($_GET["documentid"]) || !is_numeric($_GET["documentid"]) || intval($_GET["documentid"]<1)) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}

$document = $dms->getDocument($_GET["documentid"]);

if (!is_object($document)) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}

$folder = $document->getFolder();

if ($document->getAccessMode($user) < M_READWRITE) {
	UI::exitError(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))),getMLText("access_denied"));
}

if($view) {
	$view->setParam('folder', $folder);
	$view->setParam('document', $document);
	$view->setParam('strictformcheck', $settings->_strictFormCheck);
	$view->setParam('enablelargefileupload', $settings->_enableLargeFileUpload);
	$view->setParam('uploadedattachmentispublic', $settings->_uploadedAttachmentIsPublic);
	$view->setParam('accessobject', $accessop);
	$view($_GET);
	exit;
}

// --- Audit log: log attachment upload ---
// This code only runs when the Add Attachment form is opened, not when a file is actually uploaded.
// The real upload audit log should be in op/op.AddFile.php or op/op.AddFile2.php.
// You may want to remove this block from here if you only want to log actual uploads.
            error_log('DEBUG: Entering audit log block for drag-and-drop upload');
            try {
                $db = $dms->getDB();
                if (!$db) {
                    error_log('DEBUG: $db is null or invalid in drag-and-drop audit log');
                } else {
                    error_log('DEBUG: $db object obtained, class: ' . get_class($db));
                }
                $documentId = $document->getId();
                $username = $user->getLogin();
                $now = date('Y-m-d H:i:s');
                $action = 'Attachment Uploaded';
                $details = 'User uploaded an attachment.';
                $username_esc = method_exists($db, 'qstr') ? $db->qstr($username) : "'" . addslashes($username) . "'";
                $action_esc = method_exists($db, 'qstr') ? $db->qstr($action) : "'" . addslashes($action) . "'";
                $details_esc = method_exists($db, 'qstr') ? $db->qstr($details) : "'" . addslashes($details) . "'";
                $now_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";
                $query = "INSERT INTO audit_logs (document_id, created_at, user, action, details) VALUES (" . intval($documentId) . ", $now_esc, $username_esc, $action_esc, $details_esc)";
                error_log('DEBUG: Audit log SQL query: ' . $query);
                $result = $db->getResult($query);
                if ($result) {
                    error_log('DEBUG: Audit log insert succeeded for drag-and-drop upload');
                } else {
                    error_log('Audit log insert failed (attachment upload): ' . $db->getErrorMsg());
                }
            } catch (Exception $e) {
                error_log('Audit log exception (attachment upload): ' . $e->getMessage());
            }
