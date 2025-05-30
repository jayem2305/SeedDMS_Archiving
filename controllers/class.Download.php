<?php
/**
 * Implementation of Download controller
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
 * Class which does the busines logic for downloading a document
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Controller_Download extends SeedDMS_Controller_Common {

	public function version() { /* {{{ */
		$dms = $this->params['dms'];
		$version = $this->params['version'];
		$document = $this->params['document'];
		if($version < 1) {
			$content = $this->callHook('documentLatestContent', $document);
			if($content === null)
				$content = $document->getLatestContent();
		} else {
			$content = $this->callHook('documentContent', $document, $version);
			if($content === null)
				$content = $document->getContentByVersion($version);
		}
		if (!is_object($content)) {
			$this->errormsg = 'invalid_version';
			return false;
		}
		/* set params['content'] for compatiblity with older extensions which
		 * expect the content in the controller
		 */
		$this->params['content'] = $content;
		if(null === $this->callHook('version')) {
			if(file_exists($dms->contentDir . $content->getPath())) {
				header("Content-Transfer-Encoding: binary");
				$efilename = rawurlencode($content->getOriginalFileName());
				header("Content-Disposition: attachment; filename=\"" . $efilename . "\"; filename*=UTF-8''".$efilename);
				header("Content-Type: " . $content->getMimeType());
				header("Cache-Control: must-revalidate");
				header("ETag: ".$content->getChecksum());

				sendFile($dms->contentDir . $content->getPath());
			}
		}

		// --- Audit log: log document download ---
        try {
            $db = $dms->getDB();
            $username = isset($this->params['user']) ? $this->params['user']->getLogin() : 'unknown';
            $documentId = $document ? $document->getId() : 0;
            $now = date('Y-m-d H:i:s');
            $action = 'Document Downloaded';
            $details = $username . ' downloaded the document.';
            $username_esc = method_exists($db, 'qstr') ? $db->qstr($username) : "'" . addslashes($username) . "'";
            $action_esc = method_exists($db, 'qstr') ? $db->qstr($action) : "'" . addslashes($action) . "'";
            $details_esc = method_exists($db, 'qstr') ? $db->qstr($details) : "'" . addslashes($details) . "'";
            $now_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";
            $query = "INSERT INTO audit_logs (document_id, created_at, user, action, details) VALUES (" . intval($documentId) . ", $now_esc, $username_esc, $action_esc, $details_esc)";
            $result = $db->getResult($query);
            if (!$result) {
                error_log('Audit log insert failed (download document): ' . $db->getErrorMsg());
            }
        } catch (Exception $e) {
            error_log('Audit log exception (download document): ' . $e->getMessage());
        }
		return true;
	} /* }}} */

	public function file() { /* {{{ */
		$dms = $this->params['dms'];
		$file = $this->params['file'];

		if(null === $this->callHook('file')) {
			if(file_exists($dms->contentDir . $file->getPath())) {
				header("Content-Transfer-Encoding: binary");
				header("Content-Disposition: attachment; filename=\"" . $file->getOriginalFileName() . "\"");
				header("Content-Type: " . $file->getMimeType());
				header("Cache-Control: must-revalidate");

				sendFile($dms->contentDir . $file->getPath());
			}
		}
		return true;
	} /* }}} */

	public function archive() { /* {{{ */
		$dms = $this->params['dms'];
		$filename = $this->params['file'];
		$basedir = $this->params['basedir'];

		if(null === $this->callHook('archive')) {
			if(file_exists($basedir . $filename)) {
				header('Content-Description: File Transfer');
				header("Content-Type: application/zip");
				header("Content-Transfer-Encoding: binary");
				$efilename = rawurlencode($filename);
				header("Content-Disposition: attachment; filename=\"" .$efilename . "\"; filename*=UTF-8''".$efilename);
				header("Cache-Control: public");
				
				sendFile($basedir .$filename );
			}
		}
		return true;
	} /* }}} */

	public function log() { /* {{{ */
		$dms = $this->params['dms'];
		$filename = $this->params['file'];
		$basedir = $this->params['basedir'];

		if(null === $this->callHook('log')) {
			if(file_exists($basedir . $filename)) {
				header("Content-Type: text/plain; name=\"" . $filename . "\"");
				header("Content-Transfer-Encoding: binary");
				$efilename = rawurlencode($filename);
				header("Content-Disposition: attachment; filename=\"" .$efilename . "\"; filename*=UTF-8''".$efilename);
				header("Cache-Control: must-revalidate");

				sendFile($basedir.$filename);
			}
		}
		return true;
	} /* }}} */

	public function sqldump() { /* {{{ */
		$dms = $this->params['dms'];
		$filename = $this->params['file'];
		$basedir = $this->params['basedir'];

		if(null === $this->callHook('sqldump')) {
			if(file_exists($basedir . $filename)) {
				header("Content-Type: application/zip");
				header("Content-Transfer-Encoding: binary");
				$efilename = rawurlencode($filename);
				header("Content-Disposition: attachment; filename=\"" .$efilename . "\"; filename*=UTF-8''".$efilename);
				header("Cache-Control: must-revalidate");
				
				sendFile($basedir.$filename);
			}
		}
		return true;
	} /* }}} */

	public function approval() { /* {{{ */
		$dms = $this->params['dms'];
		$document = $this->params['document'];
		$logid = $this->params['approvelogid'];

		$filename = $dms->contentDir . $document->getDir().'a'.$logid;
		if (!file_exists($filename) ) {
			$this->error = 1;
			return false;
		}

		if(null === $this->callHook('approval')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $filename);

			header("Content-Type: ".$mimetype);
			header("Content-Transfer-Encoding: binary");
<<<<<<< HEAD
			header("Content-Disposition: attachment; filename=\"approval-" . $document->getID(). "-".(int) $_GET['approvelogid'] . get_extension($mimetype) . "\"");
=======
			header("Content-Disposition: attachment; filename=\"approval-" . $document->getID()."-".(int) $_GET['approvelogid'] . get_extension($mimetype) . "\"");
>>>>>>> 1f309085a20da01af576102fb7b70e417ed5d6b7
			header("Cache-Control: must-revalidate");
			sendFile($filename);
		}
		return true;
	} /* }}} */

	public function review() { /* {{{ */
		$dms = $this->params['dms'];
		$document = $this->params['document'];
		$logid = $this->params['reviewlogid'];

		$filename = $dms->contentDir . $document->getDir().'r'.$logid;
		if (!file_exists($filename) ) {
			$this->error = 1;
			return false;
		}

		if(null === $this->callHook('review')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $filename);

			header("Content-Type: ".$mimetype);
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: " . filesize($filename ));
<<<<<<< HEAD
			header("Content-Disposition: attachment; filename=\"review-" . $document->getID(). "-".(int) $_GET['reviewlogid'] . get_extension($mimetype) . "\"");
=======
			header("Content-Disposition: attachment; filename=\"review-" . $document->getID()."-".(int) $_GET['reviewlogid'] . get_extension($mimetype) . "\"");
>>>>>>> 1f309085a20da01af576102fb7b70e417ed5d6b7
			header("Cache-Control: must-revalidate");
			sendFile($filename);
		}
		return true;
	} /* }}} */

	public function run() { /* {{{ */
		$dms = $this->params['dms'];
		$type = $this->params['type'];

		switch($type) {
			case "version":
				return $this->version();
				break;
			case "file":
				return $this->file();
				break;
			case "archive":
				return $this->archive();
				break;
			case "log":
				return $this->log();
				break;
			case "sqldump":
				return $this->sqldump();
				break;
			case "approval":
				return $this->approval();
				break;
			case "review":
				return $this->review();
				break;
		}
	} /* }}} */
}
