<?php
/**
 * Implementation of ViewOnline controller
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
class SeedDMS_Controller_ViewOnline extends SeedDMS_Controller_Common {

	public function run() {
		$dms = $this->params['dms'];
		$settings = $this->params['settings'];
		$type = $this->params['type'];

		switch($type) {
			case "version":
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
						header("Content-Type: " . $content->getMimeType());
						$efilename = rawurlencode($content->getOriginalFileName());
						if (!isset($settings->_viewOnlineFileTypes) || !is_array($settings->_viewOnlineFileTypes) || !in_array(strtolower($content->getFileType()), $settings->_viewOnlineFileTypes)) {
							header("Content-Disposition: attachment; filename=\"" . $efilename . "\"; filename*=UTF-8''".$efilename);
						} else {
							header("Content-Disposition: filename=\"" . $efilename . "\"; filename*=UTF-8''".$efilename);
						}
						header("Cache-Control: must-revalidate");
						header("ETag: ".$content->getChecksum());

                        // --- Audit log: log file viewed online ---
                        try {
                            $db = $dms->getDB();
                            $username = isset($this->params['user']) ? $this->params['user']->getLogin() : 'unknown';
                            $documentId = $document ? $document->getId() : 0;
                            $now = date('Y-m-d H:i:s');
                            $action = 'File Viewed Online';
                            $details = 'User viewed a file online.';
                            $username_esc = method_exists($db, 'qstr') ? $db->qstr($username) : "'" . addslashes($username) . "'";
                            $action_esc = method_exists($db, 'qstr') ? $db->qstr($action) : "'" . addslashes($action) . "'";
                            $details_esc = method_exists($db, 'qstr') ? $db->qstr($details) : "'" . addslashes($details) . "'";
                            $now_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";
                            $query = "INSERT INTO audit_logs (document_id, created_at, user, action, details) VALUES (" . intval($documentId) . ", $now_esc, $username_esc, $action_esc, $details_esc)";
                            $result = $db->getResult($query);
                            if (!$result) {
                                error_log('Audit log insert failed (file viewed online): ' . $db->getErrorMsg());
                            }
                        } catch (Exception $e) {
                            error_log('Audit log exception (file viewed online): ' . $e->getMessage());
                        }

						sendFile($dms->contentDir.$content->getPath());
					}
				}
				break;
		}
		return true;
	}
}

