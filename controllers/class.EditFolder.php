<?php
/**
 * Implementation of EditFolder controller
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
 * Class which does the busines logic for editing a folder
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Controller_EditFolder extends SeedDMS_Controller_Common {

	public function run() {
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$fulltextservice = $this->params['fulltextservice'];
		$folder = $this->params['folder'];

		if(false === $this->callHook('preEditFolder')) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_preEditFolder_failed';
			return null;
		}

		$result = $this->callHook('editFolder', $folder);
		if($result === null) {
			$name = $this->params['name'];

			$encryption_method = 'AES-256-CBC';
			$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
			// Encrypt the name
			$encryption_iv_name = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_name = openssl_encrypt($name, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_name);
			$combined_name = $encryption_iv_name . $encrypted_name;
			$iv_base64_name = base64_encode($combined_name);

			if(($oldname = $folder->getName()) != $iv_base64_name)
				if(!$folder->setName($iv_base64_name))
					return false;

			$comment = $this->params['comment'];
			// Encrypt the comment
			$encryption_iv_comment = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
			$encrypted_comment = openssl_encrypt($comment, $encryption_method, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv_comment);
			$combined_comment = $encryption_iv_comment . $encrypted_comment;
			$iv_base64_comment = base64_encode($combined_comment);

			if(($oldcomment = $folder->getComment()) != $iv_base64_comment)
				if(!$folder->setComment($iv_base64_comment))
					return false;

			$attributes = $this->params['attributes'];
			$oldattributes = $folder->getAttributes();
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
							if(!$attrdef->validate($attribute, $folder, false)) {
								$this->errormsg	= getAttributeValidationText($attrdef->getValidationError(), $attrdef->getName(), $attribute);
								return false;
							}

							if(!isset($oldattributes[$attrdefid]) || $attribute != $oldattributes[$attrdefid]->getValue()) {
								if(!$folder->setAttributeValue($dms->getAttributeDefinition($attrdefid), $attribute))
									return false;
							}
						} elseif($attrdef->getMinValues() > 0) {
							$this->errormsg = getMLText("attr_min_values", array("attrname"=>$attrdef->getName()));
								return false;
						} elseif(isset($oldattributes[$attrdefid])) {
							if(!$folder->removeAttribute($dms->getAttributeDefinition($attrdefid)))
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
					if(!$folder->removeAttribute($dms->getAttributeDefinition($attrdefid)))
						return false;
				}
			}

			$sequence = $this->params['sequence'];
			if(strcasecmp($sequence, "keep")) {
				if($folder->setSequence($sequence)) {
				} else {
					return false;
				}
			}

			/* There are various hooks in inc/inc.FulltextInit.php which will take
			 * care of reindexing it. They just delete the indexing date which is
			 * faster then indexing the folder completely
			 *
			if($fulltextservice && ($index = $fulltextservice->Indexer()) && $folder) {
				$idoc = $fulltextservice->IndexedDocument($folder);
				if(false !== $this->callHook('preIndexFolder', $folder, $idoc)) {
					$lucenesearch = $fulltextservice->Search();
					if($hit = $lucenesearch->getFolder((int) $folder->getId())) {
						$index->delete($hit->id);
					}
					$index->addDocument($idoc);
					$index->commit();
				}
			}
			 */
 
		} elseif($result === false) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_editFolder_failed';
			return false;
		}

		if(false === $this->callHook('postEditFolder')) {
		}

		return true;
	}
}
