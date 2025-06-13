<?php
/**
 * Implementation of EditDocumentFile controller
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class which does the busines logic for editing a document
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Controller_EditDocumentFile extends SeedDMS_Controller_Common {

	public function run() {
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$document = $this->params['document'];
		$file = $this->params['file'];

		if(false === $this->callHook('preEditDocumentFile')) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_preEditDocumentFile_failed';
			return null;
		}

		$result = $this->callHook('editDocumentFile', $document);
		if($result === null) {
			$name = $this->params['name'];
			$oldname = $file->getName();
			if($oldname != $name)
				if(!$file->setName($name))
					return false;

			$comment = $this->params['comment'];
			if(($oldcomment = $file->getComment()) != $comment)
				if(!$file->setComment($comment))
					return false;

			$version = $this->params["version"];
			$oldversion = $file->getVersion();
			if ($oldversion != $version)
				if(!$file->setVersion($version))
					return false;

			$public = $this->params["public"];
			$file->setPublic($public == 'true' ? 1 : 0);

			if(!$this->callHook('postEditDocumentFile')) {
			}

		} else
			return $result;

		// --- Audit log: log document file edit ---
        try {
            $db = $dms->getDB();
            $documentId = $document->getId();
            $username = $user->getLogin();
            $now = date('Y-m-d H:i:s');
            $action = 'Document File Edited';
            $details = 'User edited the document file.';
            $username_esc = method_exists($db, 'qstr') ? $db->qstr($username) : "'" . addslashes($username) . "'";
            $action_esc = method_exists($db, 'qstr') ? $db->qstr($action) : "'" . addslashes($action) . "'";
            $details_esc = method_exists($db, 'qstr') ? $db->qstr($details) : "'" . addslashes($details) . "'";
            $now_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";
            $query = "INSERT INTO audit_logs (document_id, created_at, user, action, details) VALUES (" . intval($documentId) . ", $now_esc, $username_esc, $action_esc, $details_esc)";
            $result = $db->getResult($query);
            if (!$result) {
                error_log('Audit log insert failed (edit document file): ' . $db->getErrorMsg());
            }
        } catch (Exception $e) {
            error_log('Audit log exception (edit document file): ' . $e->getMessage());
        }

		return true;
	}
}
