<?php
/**
 * Implementation of UpdateDocument controller
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
class SeedDMS_Controller_UpdateDocument extends SeedDMS_Controller_Common {

	public function run() { /* {{{ */
		$name = $this->getParam('name');
		$comment = $this->getParam('comment');

		/* Call preUpdateDocument early, because it might need to modify some
		 * of the parameters.
		 */
		if(false === $this->callHook('preUpdateDocument', $this->params['document'])) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_preUpdateDocument_failed';
			return null;
		}

		$comment = $this->getParam('comment');
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$document = $this->params['document'];
		$settings = $this->params['settings'];
		$fulltextservice = $this->params['fulltextservice'];
		$folder = $this->params['folder'];
		$userfiletmp = $this->getParam('userfiletmp');
		$userfilename = $this->getParam('userfilename');
		$filetype = $this->getParam('filetype');
		$userfiletype = $this->getParam('userfiletype');
		$reviewers = $this->getParam('reviewers');
		$approvers = $this->getParam('approvers');
		$recipients = $this->getParam('recipients');
		$reqversion = $this->getParam('reqversion');
		$comment = $this->getParam('comment');
		$attributes = $this->getParam('attributes');
		$workflow = $this->getParam('workflow');
		$maxsizeforfulltext = $this->getParam('maxsizeforfulltext');
		$initialdocumentstatus = $this->getParam('initialdocumentstatus');

		$result = null; // Initialize $result with a default value
		$content = $this->callHook('updateDocument');
		if($content === null) {
			$filesize = SeedDMS_Core_File::fileSize($userfiletmp);
			if($contentResult=$document->addContent($comment, $user, $userfiletmp, utf8_basename($userfilename), $filetype, $userfiletype, $reviewers, $approvers, 0, $attributes, $workflow, $initialdocumentstatus)) {

				if ($this->hasParam('expires')) {
					if($document->setExpires($this->getParam('expires'))) {
					} else {
					}
				}

				if(!empty($recipients['i'])) {
					foreach($recipients['i'] as $uid) {
						if($u = $dms->getUser($uid)) {
							$res = $contentResult->getContent()->addIndRecipient($u, $user);
						}
					}
				}
				if(!empty($recipients['g'])) {
					foreach($recipients['g'] as $gid) {
						if($g = $dms->getGroup($gid)) {
							$res = $contentResult->getContent()->addGrpRecipient($g, $user);
						}
					}
				}

				$content = $contentResult->getContent();
			} else {
				$this->errormsg = 'error_update_document';
				$result = false;
			}
		} elseif($result === false) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_updateDocument_failed';
			return false;
		}

		if($fulltextservice && ($index = $fulltextservice->Indexer()) && $content) {
			$idoc = $fulltextservice->IndexedDocument($document);
			if(false !== $this->callHook('preIndexDocument', $document, $idoc)) {
				$lucenesearch = $fulltextservice->Search();
				if($hit = $lucenesearch->getDocument((int) $document->getId())) {
					$index->delete($hit->id);
				}
				$index->addDocument($idoc);
				$index->commit();
			}
		}

		if(false === $this->callHook('postUpdateDocument', $document, $content)) {
		}

		// --- Audit log: log document update ---
		try {
			$db = $dms->getDB();
			$documentId = $document->getId();
			$username = $user->getLogin();
			$now = date('Y-m-d H:i:s');
			
			// Define encryption parameters
			$encryption_method = 'AES-256-CBC';
			$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
			
			// Build detailed change log
			$changes = array();
			$oldValues = array();
			$newValues = array();
			
			// Log file changes
			if ($userfilename) {
			$changes[] = "File updated";
			$oldValues[] = "File:\n" . $document->getLatestContent()->getOriginalFileName() . "\n";
			$newValues[] = "File:\n" . $userfilename . "\n";
			}
			
			// Log comment changes
			if ($comment) {
			$changes[] = "Comment added";
			$oldValues[] = "Comment:\nNone\n";
			$newValues[] = "Comment:\n" . $comment . "\n";
			}

			// Log expiration changes
			if ($this->hasParam('expires')) {
			$new_exp = $this->getParam('expires') ? date('Y-m-d', $this->getParam('expires')) : 'Does not expire';
			$changes[] = "Expiration changed";
			$oldValues[] = "Expiration:\n" . ($document->getExpires() ? date('Y-m-d', $document->getExpires()) : 'Does not expire') . "\n";
			$newValues[] = "Expiration:\n" . $new_exp . "\n";
			}

			// Log reviewer changes
			if ($reviewers) {
			$rev_ind = array();
			$rev_grp = array();
			if(!empty($reviewers['i'])) {
				foreach($reviewers['i'] as $uid) {
				if($u = $dms->getUser($uid)) {
					$rev_ind[] = $u->getFullName();
				}
				}
			}
			if(!empty($reviewers['g'])) {
				foreach($reviewers['g'] as $gid) {
				if($g = $dms->getGroup($gid)) {
					$rev_grp[] = $g->getName();
				}
				}
			}
			$changes[] = "Reviewers assigned";
			$oldValues[] = "Reviewers:\nNone\n";
			$newValues[] = "Reviewers:\nIndividuals: " . implode(", ", $rev_ind) . "\nGroups: " . implode(", ", $rev_grp) . "\n";
			}

			// Log approver changes
			if ($approvers) {
			$app_ind = array();
			$app_grp = array();
			if(!empty($approvers['i'])) {
				foreach($approvers['i'] as $uid) {
				if($u = $dms->getUser($uid)) {
					$app_ind[] = $u->getFullName();
				}
				}
			}
			if(!empty($approvers['g'])) {
				foreach($approvers['g'] as $gid) {
				if($g = $dms->getGroup($gid)) {
					$app_grp[] = $g->getName();
				}
				}
			}
			$changes[] = "Approvers assigned";
			$oldValues[] = "Approvers:\nNone\n";
			$newValues[] = "Approvers:\nIndividuals: " . implode(", ", $app_ind) . "\nGroups: " . implode(", ", $app_grp) . "\n";
			}

			$oldValuesStr = implode("\n", $oldValues);
			$newValuesStr = implode("\n", $newValues);
			
			// Encrypt username
			$encryption_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_username = openssl_encrypt($username, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv);
			$combined_username = $encryption_iv . $encrypted_username;
			$encrypted_username_base64 = base64_encode($combined_username);

			// Encrypt old values
			$encryption_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_old = openssl_encrypt($oldValuesStr, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv);
			$combined_old = $encryption_iv . $encrypted_old;
			$encrypted_old_base64 = base64_encode($combined_old);

			// Encrypt new values
			$encryption_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_new = openssl_encrypt($newValuesStr, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv);
			$combined_new = $encryption_iv . $encrypted_new;
			$encrypted_new_base64 = base64_encode($combined_new);

			$username_esc = method_exists($db, 'qstr') ? $db->qstr($encrypted_username_base64) : "'" . addslashes($encrypted_username_base64) . "'";
			$oldValues_esc = method_exists($db, 'qstr') ? $db->qstr($encrypted_old_base64) : "'" . addslashes($encrypted_old_base64) . "'";
			$newValues_esc = method_exists($db, 'qstr') ? $db->qstr($encrypted_new_base64) : "'" . addslashes($encrypted_new_base64) . "'";
			$time_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";
			
			$query = "INSERT INTO audit_logs (document_id, created_at, user, old_value, new_value) VALUES (" . 
				 intval($documentId) . ", " . $time_esc . ", " . $username_esc . ", " . $oldValues_esc . ", " . $newValues_esc . ")";
			
			$result = $db->getResult($query);
			if (!$result) {
			error_log('Audit log insert failed (update document): ' . $db->getErrorMsg());
			}
		} catch (Exception $e) {
			error_log('Audit log exception (update document): ' . $e->getMessage());
		}
        return true;
    } /* }}} */
}

