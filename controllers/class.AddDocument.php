<?php
/**
 * Implementation of AddDocument controller
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
class SeedDMS_Controller_AddDocument extends SeedDMS_Controller_Common
{

	public function run()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$fulltextservice = $this->params['fulltextservice'];
		$folder = $this->params['folder'];

		/* Call preAddDocument early, because it might need to modify some
		 * of the parameters.
		 */
		if (false === $this->callHook('preAddDocument')) {
			if (empty($this->errormsg))
				$this->errormsg = 'hook_preAddDocument_failed';
			return null;
		}

		$name = $this->getParam('name');
		$comment = $this->getParam('comment');
		$documentsource = $this->params['documentsource'];
		$expires = $this->getParam('expires');
		$keywords = $this->getParam('keywords');
		$cats = $this->getParam('categories');
		$owner = $this->getParam('owner');
		$userfiletmp = $this->getParam('userfiletmp');
		$userfilename = $this->getParam('userfilename');
		$filetype = $this->getParam('filetype');
		$userfiletype = $this->getParam('userfiletype');
		$sequence = $this->getParam('sequence');
		$reviewers = $this->getParam('reviewers');
		$approvers = $this->getParam('approvers');
		$recipients = $this->getParam('recipients');
		$reqversion = $this->getParam('reqversion');
		$version_comment = $this->getParam('versioncomment');
		$attributes = $this->getParam('attributes');
		foreach ($attributes as $attrdefid => &$attribute) {
			if ($attrdef = $dms->getAttributeDefinition($attrdefid)) {
				if (null === ($ret = $this->callHook('validateAttribute', $attrdef, $attribute))) {
					if ($attribute) {
						switch ($attrdef->getType()) {
							case SeedDMS_Core_AttributeDefinition::type_date:
								if (is_array($attribute))
									$attribute = array_map(fn($value): string => date('Y-m-d', makeTsFromDate($value)), $attribute);
								else
									$attribute = date('Y-m-d', makeTsFromDate($attribute));
								break;
							case SeedDMS_Core_AttributeDefinition::type_folder:
								if (is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getFolder((int) $value), $attribute);
								else
									$attribute = $dms->getFolder((int) $attribute);
								break;
							case SeedDMS_Core_AttributeDefinition::type_document:
								if (is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getDocument((int) $value), $attribute);
								else
									$attribute = $dms->getDocument((int) $attribute);
								break;
							case SeedDMS_Core_AttributeDefinition::type_user:
								if (is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getUser((int) $value), $attribute);
								else
									$attribute = $dms->getUser((int) $attribute);
								break;
							case SeedDMS_Core_AttributeDefinition::type_group:
								if (is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getGroup((int) $value), $attribute);
								else
									$attribute = $dms->getGroup((int) $attribute);
								break;
						}
						if (!$attrdef->validate($attribute, null, true)) {
							$this->errormsg = getAttributeValidationError($attrdef->getValidationError(), $attrdef->getName(), $attribute);
							return false;
						}
					} elseif ($attrdef->getMinValues() > 0) {
						$this->errormsg = array("attr_min_values", array("attrname" => $attrdef->getName()));
						return false;
					}
				} else {
					if ($ret === false)
						return false;
				}
			}
		}
		if ($attributes_version = $this->getParam('attributesversion')) {
			foreach ($attributes_version as $attrdefid => &$attribute) {
				if ($attrdef = $dms->getAttributeDefinition($attrdefid)) {
					if (null === ($ret = $this->callHook('validateAttribute', $attrdef, $attribute))) {
						if ($attribute) {
							switch ($attrdef->getType()) {
								case SeedDMS_Core_AttributeDefinition::type_date:
									if (is_array($attribute))
										$attribute = array_map(fn($value): string => date('Y-m-d', makeTsFromDate($value)), $attribute);
									else
										$attribute = date('Y-m-d', makeTsFromDate($attribute));
									break;
								case SeedDMS_Core_AttributeDefinition::type_folder:
									if (is_array($attribute))
										$attribute = array_map(fn($value): object => $dms->getFolder((int) $value), $attribute);
									else
										$attribute = $dms->getFolder((int) $attribute);
									break;
								case SeedDMS_Core_AttributeDefinition::type_document:
									if (is_array($attribute))
										$attribute = array_map(fn($value): object => $dms->getDocument((int) $value), $attribute);
									else
										$attribute = $dms->getDocument((int) $attribute);
									break;
								case SeedDMS_Core_AttributeDefinition::type_user:
									if (is_array($attribute))
										$attribute = array_map(fn($value): object => $dms->getUser((int) $value), $attribute);
									else
										$attribute = $dms->getUser((int) $attribute);
									break;
								case SeedDMS_Core_AttributeDefinition::type_group:
									if (is_array($attribute))
										$attribute = array_map(fn($value): object => $dms->getGroup((int) $value), $attribute);
									else
										$attribute = $dms->getGroup((int) $attribute);
									break;
							}
							if (!$attrdef->validate($attribute, null, true)) {
								$this->errormsg = getAttributeValidationError($attrdef->getValidationError(), $attrdef->getName(), $attribute);
								return false;
							}
						} elseif ($attrdef->getMinValues() > 0) {
							$this->errormsg = array("attr_min_values", array("attrname" => $attrdef->getName()));
							return false;
						}
					} else {
						if ($ret === false)
							return false;
					}
				}
			}
		}
		$workflow = $this->getParam('workflow');
		$notificationgroups = $this->getParam('notificationgroups');
		$notificationusers = $this->getParam('notificationusers');
		$initialdocumentstatus = $this->getParam('initialdocumentstatus');
		$maxsizeforfulltext = $this->getParam('maxsizeforfulltext');
		$defaultaccessdocs = $this->getParam('defaultaccessdocs');
		/*
																																												
																																												 */
		// Define encryption parameters
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

		// Encrypt the keywords
		$encryption_iv_keywords = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
		$encrypted_keywords = openssl_encrypt($keywords, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_keywords);
		$combined_keywords = $encryption_iv_keywords . $encrypted_keywords;
		$iv_base64_keywords = base64_encode($combined_keywords);

		// Encrypt the userfilename
		$encryption_iv_userfilename = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
		$encrypted_userfilename = openssl_encrypt($userfilename, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_userfilename);
		$combined_userfilename = $encryption_iv_userfilename . $encrypted_userfilename;
		$iv_base64_userfilename = base64_encode($combined_userfilename);

		// Now use the encrypted values in the database operation
		$document = $this->callHook('addDocument');
		if ($document === null) {
			$filesize = SeedDMS_Core_File::fileSize($userfiletmp);

			$res = $folder->addDocument(
				$iv_base64_name,  // Encrypted name
				$iv_base64_comment,  // Encrypted comment
				$expires,
				$owner,
				$iv_base64_keywords,  // Encrypted keywords
				$cats,
				$userfiletmp,
				$iv_base64_userfilename,  // Encrypted userfilename
				$filetype,
				$userfiletype,
				$sequence,
				$reviewers,
				$approvers,
				$reqversion,
				$version_comment,
				$attributes,
				$attributes_version,
				$workflow,
				$initialdocumentstatus
			);

			if (is_bool($res) && !$res) {
				$this->errormsg = "error_occured";
				return false;
			}

			$document = $res[0];

			// --- Audit log: log document addition ---
			try {
				$db = $dms->getDB();
				$documentId = $document->getId();
				$username = $user->getLogin();
				$now = date('Y-m-d H:i:s');
				$action = 'Document Added';
				$details = 'User added a new document.';
				$username_esc = method_exists($db, 'qstr') ? $db->qstr($username) : "'" . addslashes($username) . "'";
				$action_esc = method_exists($db, 'qstr') ? $db->qstr($action) : "'" . addslashes($action) . "'";
				$details_esc = method_exists($db, 'qstr') ? $db->qstr($details) : "'" . addslashes($details) . "'";
				$now_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";
				$query = "INSERT INTO audit_logs (document_id, created_at, user, action, details) VALUES (" . intval($documentId) . ", $now_esc, $username_esc, $action_esc, $details_esc)";
				$result = $db->getResult($query);
				if (!$result) {
					error_log('Audit log insert failed (add document): ' . $db->getErrorMsg());
				}
			} catch (Exception $e) {
				error_log('Audit log exception (add document): ' . $e->getMessage());
			}

			/* Set access as specified in settings. */
			if ($defaultaccessdocs) {
				if ($defaultaccessdocs > 0 && $defaultaccessdocs < 4) {
					$document->setInheritAccess(0, true);
					$document->setDefaultAccess($defaultaccessdocs, true);
				}
			}

			$lc = $document->getLatestContent();
			if ($recipients) {
				if ($recipients['i']) {
					foreach ($recipients['i'] as $uid) {
						if ($u = $dms->getUser($uid)) {
							$res = $lc->addIndRecipient($u, $user);
						}
					}
				}
				if ($recipients['g']) {
					foreach ($recipients['g'] as $gid) {
						if ($g = $dms->getGroup($gid)) {
							$res = $lc->addGrpRecipient($g, $user);
						}
					}
				}
			}

			/* Add a default notification for the owner of the document */
			if ($settings->_enableOwnerNotification) {
				$res = $document->addNotify($owner->getID(), true);
			}
			/* Check if additional notification shall be added */
			foreach ($notificationusers as $notuser) {
				if ($document->getAccessMode($notuser) >= M_READ)
					$res = $document->addNotify($notuser->getID(), true);
			}
			foreach ($notificationgroups as $notgroup) {
				if ($document->getGroupAccessMode($notgroup) >= M_READ)
					$res = $document->addNotify($notgroup->getID(), false);
			}
		} elseif ($document === false) {
			if (empty($this->errormsg))
				$this->errormsg = 'hook_addDocument_failed';
			return false;
		}

		if ($fulltextservice && ($index = $fulltextservice->Indexer()) && $document) {
			$idoc = $fulltextservice->IndexedDocument($document);
			if (false !== $this->callHook('preIndexDocument', $document, $idoc)) {
				$index->addDocument($idoc);
				$index->commit();
			}
		}

		if (false === $this->callHook('postAddDocument', $document)) {
			if (empty($this->errormsg))
				$this->errormsg = 'hook_postAddDocument_failed';
			return false;
		}

		return $document;
	} /* }}} */
	function decrypt_name($combined_encrypted, $key)
	{
		$data = base64_decode($combined_encrypted);
		$iv = substr($data, 0, 16); // First 16 bytes = IV
		$ciphertext = substr($data, 16);

		return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
	}

}

