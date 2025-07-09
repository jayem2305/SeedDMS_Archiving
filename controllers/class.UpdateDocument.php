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

        // Helper to decrypt safely
        function decryptIfEncrypted($base64, $key, $method) {
            if (!$base64 || !base64_decode($base64, true)) return '';
            $combined = base64_decode($base64);
            $iv_length = openssl_cipher_iv_length($method);
            if (strlen($combined) < $iv_length) return '';
            $iv = substr($combined, 0, $iv_length);
            $encrypted = substr($combined, $iv_length);
            return trim(openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv));
        }

        // Fetch the true old file name BEFORE any update
        $encryption_method = 'AES-256-CBC';
        $encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
        $oldContent = $document->getLatestContent();
        $oldFileNameEncrypted = $oldContent ? $oldContent->getOriginalFileName() : '';
        $oldFileNameDecrypted = decryptIfEncrypted($oldFileNameEncrypted, $encryption_key, $encryption_method);
        // If decryption fails but the original is not empty, use the original (plain text)
        $oldFileNameForAudit = $oldFileNameDecrypted !== '' ? $oldFileNameDecrypted : $oldFileNameEncrypted;
        $oldexpires = $document->getExpires();

		// --- Document update logic ---
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

            // Use the old file name fetched BEFORE the update
            // Fetch the new file name AFTER the update
            $newContent = $document->getLatestContent();
            $newFileNameEncrypted = $newContent ? $newContent->getOriginalFileName() : '';
            $newFileNameDecrypted = decryptIfEncrypted($newFileNameEncrypted, $encryption_key, $encryption_method);
            $newexpires = $document->getExpires();

            $oldValues = [];
            $newValues = [];

            // 1. File name change
            if ($userfilename) {
                $newFileName = trim((string)$userfilename);
                if ($oldFileNameForAudit !== $newFileName) {
                    $oldValues[] = "File:\n" . $oldFileNameForAudit;
                    $newValues[] = "File:\n" . $newFileName;
                }
            }

            // 2. Expiration change
            if ($this->hasParam('expires')) {
                $newExp = $this->getParam('expires');
                if ($oldexpires != $newExp) {
                    $oldValues[] = "Expiration:\n" . ($oldexpires ? date('Y-m-d', $oldexpires) : 'Does not expire');
                    $newValues[] = "Expiration:\n" . ($newExp ? date('Y-m-d', $newExp) : 'Does not expire');
                }
            }

            // 3. Reviewer change
            if ($reviewers) {
                $rev_ind = [];
                $rev_grp = [];
                if (!empty($reviewers['i'])) {
                    foreach ($reviewers['i'] as $uid) {
                        if ($u = $dms->getUser($uid)) {
                            $rev_ind[] = $u->getFullName();
                        }
                    }
                }
                if (!empty($reviewers['g'])) {
                    foreach ($reviewers['g'] as $gid) {
                        if ($g = $dms->getGroup($gid)) {
                            $rev_grp[] = $g->getName();
                        }
                    }
                }
                if (!empty($rev_ind) || !empty($rev_grp)) {
                    $oldValues[] = "Reviewers:\nNone";
                    $newValues[] = "Reviewers:\nIndividuals: " . implode(', ', $rev_ind) . "\nGroups: " . implode(', ', $rev_grp);
                }
            }

            // 4. Approver change
            if ($approvers) {
                $app_ind = [];
                $app_grp = [];
                if (!empty($approvers['i'])) {
                    foreach ($approvers['i'] as $uid) {
                        if ($u = $dms->getUser($uid)) {
                            $app_ind[] = $u->getFullName();
                        }
                    }
                }
                if (!empty($approvers['g'])) {
                    foreach ($approvers['g'] as $gid) {
                        if ($g = $dms->getGroup($gid)) {
                            $app_grp[] = $g->getName();
                        }
                    }
                }
                if (!empty($app_ind) || !empty($app_grp)) {
                    $oldValues[] = "Approvers:\nNone";
                    $newValues[] = "Approvers:\nIndividuals: " . implode(', ', $app_ind) . "\nGroups: " . implode(', ', $app_grp);
                }
            }

            // Only log if something changed
            if (!empty($oldValues) || !empty($newValues)) {
                $oldValuesStr = implode("\n\n", $oldValues);
                $newValuesStr = implode("\n\n", $newValues);

                // Encrypt with IV (one IV per field)
                $encryption_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
                $encrypted_username = base64_encode($encryption_iv . openssl_encrypt($username, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv));

                $encryption_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
                $encrypted_old = base64_encode($encryption_iv . openssl_encrypt($oldValuesStr, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv));

                $encryption_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
                $encrypted_new = base64_encode($encryption_iv . openssl_encrypt($newValuesStr, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv));

                // Escape SQL inputs
                $username_esc = method_exists($db, 'qstr') ? $db->qstr($encrypted_username) : "'" . addslashes($encrypted_username) . "'";
                $oldValues_esc = method_exists($db, 'qstr') ? $db->qstr($encrypted_old) : "'" . addslashes($encrypted_old) . "'";
                $newValues_esc = method_exists($db, 'qstr') ? $db->qstr($encrypted_new) : "'" . addslashes($encrypted_new) . "'";
                $time_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";

                // Insert into audit_logs
                $query = "INSERT INTO audit_logs (document_id, created_at, user, old_value, new_value) VALUES (" .
                    intval($documentId) . ", $time_esc, $username_esc, $oldValues_esc, $newValues_esc)";
                $result = $db->getResult($query);
                if (!$result) {
                    error_log('Audit log insert failed (update document): ' . $db->getErrorMsg());
                }
            }
        } catch (Exception $e) {
            error_log('Audit log exception (update document): ' . $e->getMessage());
        }
        // --- End of Audit log ---


        return true;
    } /* }}} */
}

