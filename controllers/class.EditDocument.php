<?php
/**
 * Implementation of EditDocument controller
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
class SeedDMS_Controller_EditDocument extends SeedDMS_Controller_Common {

	public function run() {
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$fulltextservice = $this->params['fulltextservice'];
		$document = $this->params['document'];
		$name = $this->params['name'];

		if(false === $this->callHook('preEditDocument')) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_preEditDocument_failed';
			return null;
		}

		$result = $this->callHook('editDocument', $document);
		if($result === null) {
			$name = $this->params['name'];
			// Encrypt the name
			$encryption_method = 'AES-256-CBC';
			$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
			$encryption_iv_name = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_name = openssl_encrypt($name, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_name);
			$combined_name = $encryption_iv_name . $encrypted_name;
			$iv_base64_name = base64_encode($combined_name);

			$oldname = $document->getName();
			if($oldname != $iv_base64_name)
				if(!$document->setName($iv_base64_name))
					return false;

			$comment = $this->params['comment'];
			// Encrypt the comment
			$encryption_iv_comment = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_comment = openssl_encrypt($comment, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_comment);
			$combined_comment = $encryption_iv_comment . $encrypted_comment;
			$iv_base64_comment = base64_encode($combined_comment);

			if(($oldcomment = $document->getComment()) != $iv_base64_comment)
				if(!$document->setComment($iv_base64_comment))
					return false;

			$expires = $this->params['expires'];
			$oldexpires = $document->getExpires();
			if ($expires != $oldexpires) {
				if(false === $this->callHook('preSetExpires', $document, $expires)) {
				}

				if(!$document->setExpires($expires)) {
					return false;
				}

				$document->verifyLastestContentExpriry();

				if(false === $this->callHook('postSetExpires', $document, $expires)) {
				}
			}

			$keywords = $this->params['keywords'];
			// Encrypt the keywords
			$encryption_iv_keywords = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_keywords = openssl_encrypt($keywords, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_keywords);
			$combined_keywords = $encryption_iv_keywords . $encrypted_keywords;
			$iv_base64_keywords = base64_encode($combined_keywords);

			$oldkeywords = $document->getKeywords();
			if ($oldkeywords != $iv_base64_keywords) {
				if(false === $this->callHook('preSetKeywords', $document, $iv_base64_keywords, $oldkeywords)) {
				}

				if(!$document->setKeywords($iv_base64_keywords)) {
					return false;
				}

				if(false === $this->callHook('postSetKeywords', $document, $iv_base64_keywords, $oldkeywords)) {
				}
			}

			$categories = $this->params['categories'];
			$oldcategories = $document->getCategories();
			if($categories) {
				$categoriesarr = array();
				foreach($categories as $catid) {
					if($cat = $dms->getDocumentCategory($catid)) {
						$categoriesarr[] = $cat;
					}
					
				}
				$oldcatsids = array();
				foreach($oldcategories as $oldcategory)
					$oldcatsids[] = $oldcategory->getID();

				if (count($categoriesarr) != count($oldcategories) ||
						array_diff($categories, $oldcatsids)) {
					if(false === $this->callHook('preSetCategories', $document, $categoriesarr, $oldcategories)) {
					}
					if(!$document->setCategories($categoriesarr)) {
						return false;
					}
					if(false === $this->callHook('postSetCategories', $document, $categoriesarr, $oldcategories)) {
					}
				}
			} elseif($oldcategories) {
				if(false === $this->callHook('preSetCategories', $document, array(), $oldcategories)) {
				}
				if(!$document->setCategories(array())) {
					return false;
				}
				if(false === $this->callHook('postSetCategories', $document, array(), $oldcategories)) {
				}
			}

			$attributes = $this->params['attributes'];
			$oldattributes = $document->getAttributes();
			if($attributes) {
				foreach($attributes as $attrdefid=>$attribute) {
					if($attrdef = $dms->getAttributeDefinition($attrdefid)) {
						if(null === ($ret = $this->callHook('validateAttribute', $attrdef, $attribute))) {
						if($attribute) {
							switch($attrdef->getType()) {
							case SeedDMS_Core_AttributeDefinition::type_date:
								if(is_array($attribute))
									$attribute = array_map(fn($value): string => date('Y-m-d', makeTsFromDate($value)), $attribute);
								else
									$attribute = date('Y-m-d', makeTsFromDate($attribute));
								break;
							case SeedDMS_Core_AttributeDefinition::type_folder:
								if(is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getFolder((int) $value), $attribute);
								else
									$attribute = $dms->getFolder((int) $attribute);
								break;
							case SeedDMS_Core_AttributeDefinition::type_document:
								if(is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getDocument((int) $value), $attribute);
								else
									$attribute = $dms->getDocument((int) $attribute);
								break;
							case SeedDMS_Core_AttributeDefinition::type_user:
								if(is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getUser((int) $value), $attribute);
								else
									$attribute = $dms->getUser((int) $attribute);
								break;
							case SeedDMS_Core_AttributeDefinition::type_group:
								if(is_array($attribute))
									$attribute = array_map(fn($value): object => $dms->getGroup((int) $value), $attribute);
								else
									$attribute = $dms->getGroup((int) $attribute);
								break;
							}
							if(!$attrdef->validate($attribute, $document, false)) {
								$this->errormsg	= getAttributeValidationError($attrdef->getValidationError(), $attrdef->getName(), $attribute);
								return false;
							}

							if(!isset($oldattributes[$attrdefid]) || $attribute != $oldattributes[$attrdefid]->getValue()) {
								if(!$document->setAttributeValue($dms->getAttributeDefinition($attrdefid), $attribute))
									return false;
							}
						} elseif($attrdef->getMinValues() > 0) {
							$this->errormsg = array("attr_min_values", array("attrname"=>$attrdef->getName()));
						} elseif(isset($oldattributes[$attrdefid])) {
							if(!$document->removeAttribute($dms->getAttributeDefinition($attrdefid)))
								return false;
						}
						} else {
							if($ret === false)
								return false;
						}
					}
				}
			}
			foreach($oldattributes as $attrdefid=>$oldattribute) {
				if(!isset($attributes[$attrdefid])) {
					if(!$document->removeAttribute($dms->getAttributeDefinition($attrdefid)))
						return false;
				}
			}

			$sequence = $this->params['sequence'];
			if(strcasecmp($sequence, "keep")) {
				if($document->setSequence($sequence)) {
				} else {
					return false;
				}
			}

			/* There are various hooks in inc/inc.FulltextInit.php which will take
			 * care of reindexing it. They just delete the indexing date which is
			 * faster then indexing the folder completely
			 *
			if($fulltextservice && ($index = $fulltextservice->Indexer()) && $document) {
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
			 */
 
		} elseif($result === false) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_editDocument_failed';
			return false;
		}

		if(false === $this->callHook('postEditDocument')) {
		}

		// --- Audit log: log document edit ---
        try {
            $db = $dms->getDB();
            $documentId = $document->getId();
            $username = $user->getLogin();
            $now = date('Y-m-d H:i:s');
            $action = 'Document Edited';
            $details = 'User edited the document.';
            $username_esc = method_exists($db, 'qstr') ? $db->qstr($username) : "'" . addslashes($username) . "'";
            $action_esc = method_exists($db, 'qstr') ? $db->qstr($action) : "'" . addslashes($action) . "'";
            $details_esc = method_exists($db, 'qstr') ? $db->qstr($details) : "'" . addslashes($details) . "'";
            $now_esc = method_exists($db, 'qstr') ? $db->qstr($now) : "'" . addslashes($now) . "'";
            $query = "INSERT INTO audit_logs (document_id, created_at, user, action, details) VALUES (" . intval($documentId) . ", $now_esc, $username_esc, $action_esc, $details_esc)";
            $result = $db->getResult($query);
            if (!$result) {
                error_log('Audit log insert failed (edit document): ' . $db->getErrorMsg());
            }
        } catch (Exception $e) {
            error_log('Audit log exception (edit document): ' . $e->getMessage());
        }

		return true;
	}
}
