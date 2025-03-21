<?php
declare(strict_types=1);

/**
 * Implementation of the attribute object in the document management system
 *
 * @category   DMS
 * @package    SeedDMS_Core
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2012-2024 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class to represent an attribute in the document management system
 *
 * Attributes are key/value pairs which can be attachted to documents,
 * folders and document content. The number of attributes is unlimited.
 * Each attribute has a value and is related to an attribute definition,
 * which holds the name and other information about the attribute.
 *
 * @see SeedDMS_Core_AttributeDefinition
 *
 * @category   DMS
 * @package    SeedDMS_Core
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2012-2024 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Core_Attribute { /* {{{ */
	/**
	 * @var integer id of attribute
	 *
	 * @access protected
	 */
	protected $_id;

	/**
	 * @var SeedDMS_Core_Folder|SeedDMS_Core_Document|SeedDMS_Core_DocumentContent SeedDMS_Core_Object folder, document or document content
	 * this attribute belongs to
	 *
	 * @access protected
	 */
	protected $_obj;

	/**
	 * @var SeedDMS_Core_AttributeDefinition definition of this attribute
	 *
	 * @access protected
	 */
	protected $_attrdef;

	/**
	 * @var mixed value of this attribute
	 *
	 * @access protected
	 */
	protected $_value;

	/**
	 * @var integer validation error
	 *
	 * @access protected
	 */
	protected $_validation_error;

	/**
	 * @var SeedDMS_Core_DMS reference to the dms instance this attribute belongs to
	 *
	 * @access protected
	 */
	protected $_dms;

	/**
	 * SeedDMS_Core_Attribute constructor.
	 * @param $id
	 * @param $obj
	 * @param $attrdef
	 * @param $value
	 */
	public function __construct($id, $obj, $attrdef, $value) { /* {{{ */
		$this->_id = $id;
		$this->_obj = $obj;
		$this->_attrdef = $attrdef;
		$this->_value = $value;
		$this->_validation_error = 0;
		$this->_dms = null;
	} /* }}} */

	/**
	 * Set reference to dms
	 *
	 * @param SeedDMS_Core_DMS $dms
	 */
	public function setDMS($dms) { /* {{{ */
		$this->_dms = $dms;
	} /* }}} */

	/**
	 * Get dms of attribute
	 *
	 * @return object $dms
	 */
	public function getDMS() { return $this->_dms; }

	/**
	 * Get internal id of attribute
	 *
	 * @return integer id
	 */
	public function getID() { return $this->_id; }

	/**
	 * Return attribute value as stored in database
	 *
	 * This function will return the value of multi value attributes
	 * including the separator char.
	 *
	 * @return string the attribute value as it is stored in the database.
	 */
	public function getValue() { return $this->_value; }

	/**
	 * Return attribute value parsed into a php type or object
	 *
	 * This function will return the value of multi value attributes
	 * including the separator char.
	 *
	 * DEPRECATED
	 *
	 * @return string the attribute value as it is stored in the database.
	 */
	public function __getParsedValue() { /* {{{ */
		switch ($this->_attrdef->getType()) {
			case SeedDMS_Core_AttributeDefinition::type_float:
				return (float) $this->_value;
				break;
			case SeedDMS_Core_AttributeDefinition::type_boolean:
				return (bool) $this->_value;
				break;
			case SeedDMS_Core_AttributeDefinition::type_int:
				return (int) $this->_value;
				break;
			default:
				return $this->_value;
		}
	} /* }}} */

	/**
	 * Return attribute values as an array
	 *
	 * This function returns the attribute value as an array. The array
	 * has one element for non multi value attributes and n elements for
	 * multi value attributes.
	 *
	 * @return array the attribute values
	 */
	public function getValueAsArray() { /* {{{ */
		if (is_array($this->_value))
			return $this->_value;
		else
			return [$this->_value];
	} /* }}} */

	public function getValueAsString() { /* {{{ */
		if (is_array($this->_value))
			return implode(', ', $this->_value);
		else
			return (string) $this->_value;
	} /* }}} */

	/**
	 * Set a value of an attribute
	 *
	 * The attribute is completely deleted if the value is an empty string
	 * or empty array. An array of values is only allowed if the attribute may
	 * have multiple values. If an array is passed and the attribute may
	 * have only a single value, then the first element of the array will
	 * be taken.
	 *
	 * @param string $values value as string or array to be set
	 * @return boolean true if operation was successfull, otherwise false
	 */
	public function setValue($values) { /* {{{*/
		$db = $this->_dms->getDB();

		/* if $values is an array but the attribute definition does not allow
		 * multi values, then the first element of the array is taken.
		 */
		if ($values && is_array($values) && !$this->_attrdef->getMultipleValues())
			$values = $values[0];

		/* Create a value to be stored in the database */
		$value = $this->_attrdef->createValue($values);

		switch (get_class($this->_obj)) {
			case $this->_dms->getClassname('document'):
				if (trim($value) === '')
					$queryStr = "DELETE FROM `tblDocumentAttributes` WHERE `document` = " . $this->_obj->getID() . " AND `attrdef` = " . $this->_attrdef->getId();
				else
					$queryStr = "UPDATE `tblDocumentAttributes` SET `value` = ".$db->qstr($value)." WHERE `document` = " . $this->_obj->getID() .	" AND `attrdef` = " . $this->_attrdef->getId();
				break;
			case $this->_dms->getClassname('documentcontent'):
				if (trim($value) === '')
					$queryStr = "DELETE FROM `tblDocumentContentAttributes` WHERE `content` = " . $this->_obj->getID() . " AND `attrdef` = " . $this->_attrdef->getId();
				else
					$queryStr = "UPDATE `tblDocumentContentAttributes` SET `value` = ".$db->qstr($value)." WHERE `content` = " . $this->_obj->getID() .	" AND `attrdef` = " . $this->_attrdef->getId();
				break;
			case $this->_dms->getClassname('folder'):
				if (trim($value) === '')
					$queryStr = "DELETE FROM `tblFolderAttributes` WHERE `folder` = " . $this->_obj->getID() .	" AND `attrdef` = " . $this->_attrdef->getId();
				else
					$queryStr = "UPDATE `tblFolderAttributes` SET `value` = ".$db->qstr($value)." WHERE `folder` = " . $this->_obj->getID() .	" AND `attrdef` = " . $this->_attrdef->getId();
				break;
			default:
				return false;
		}
		if (!$db->getResult($queryStr))
			return false;

		$oldvalue = $this->_value;
		$this->_value = $values;

		/* Check if 'onPostUpdateAttribute' callback is set */
		$kk = (trim($value) === '') ? 'Remove' : 'Update';
		if (isset($this->_dms->callbacks['onPost'.$kk.'Attribute'])) {
			foreach ($this->_dms->callbacks['onPost'.$kk.'Attribute'] as $callback) {
				if (!call_user_func($callback[0], $callback[1], $this->_obj, $this->_attrdef, $value, $oldvalue)) {
				}
			}
		}

		return true;
	} /* }}} */

	/**
	 * Validate attribute value
	 *
	 * This function checks if the attribute values fits the attribute
	 * definition.
	 * If the validation fails the validation error will be set which
	 * can be requested by SeedDMS_Core_Attribute::getValidationError()
	 *
	 * @return boolean true if validation succeeds, otherwise false
	 */
	public function validate() { /* {{{ */
		/** @var SeedDMS_Core_AttributeDefinition $attrdef */
		$attrdef = $this->_attrdef;
		$result = $attrdef->validate($this->_value);
		$this->_validation_error = $attrdef->getValidationError();
		return $result;
	} /* }}} */

	/**
	 * Get validation error from last validation
	 *
	 * @return integer error code
	 */
	public function getValidationError() { return $this->_validation_error; }

	/**
	 * Set validation error
	 *
	 * @param integer error code
	 */
	public function setValidationError($error) { $this->_validation_error = $error; }

	/**
	 * Get definition of attribute
	 *
	 * @return object attribute definition
	 */
	public function getAttributeDefinition() { return $this->_attrdef; }

} /* }}} */

/**
 * Class to represent an attribute definition in the document management system
 *
 * Attribute definitions specify the name, type, object type, minimum and
 * maximum values and a value set. The object type determines the object
 * an attribute may be attached to. If the object type is set to object_all
 * the attribute can be used for documents, document content and folders.
 *
 * The type of an attribute specifies the skalar data type.
 *
 * Attributes for which multiple values are allowed must have the
 * multiple flag set to true and specify a value set. A value set
 * is a string consisting of n separated values. The separator is the
 * first char of the value set. A possible value could be '|REV-A|REV-B'
 * If multiple values are allowed, then minvalues and maxvalues may
 * restrict the allowed number of values.
 *
 * @see SeedDMS_Core_Attribute
 *
 * @category   DMS
 * @package    SeedDMS_Core
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2012-2024 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Core_AttributeDefinition { /* {{{ */
	/**
	 * @var integer id of attribute definition
	 *
	 * @access protected
	 */
	protected $_id;

	/**
	 * @var string name of attribute definition
	 *
	 * @access protected
	 */
	protected $_name;

	/**
	 * @var string object type of attribute definition. This can be one of
	 * type_int, type_float, type_string, type_boolean, type_url, or type_email.
	 *
	 * @access protected
	 */
	protected $_type;

	/**
	 * @var string type of attribute definition. This can be one of objtype_all,
	 * objtype_folder, objtype_document, or objtype_documentcontent.
	 *
	 * @access protected
	 */
	protected $_objtype;

	/**
	 * @var boolean whether an attribute can have multiple values
	 *
	 * @access protected
	 */
	protected $_multiple;

	/**
	 * @var integer minimum values of an attribute
	 *
	 * @access protected
	 */
	protected $_minvalues;

	/**
	 * @var integer maximum values of an attribute
	 *
	 * @access protected
	 */
	protected $_maxvalues;

	/**
	 * @var string list of possible values of an attribute
	 *
	 * @access protected
	 */
	protected $_valueset;

	/**
	 * @var string regular expression the value must match
	 *
	 * @access protected
	 */
	protected $_regex;

	/**
	 * @var integer validation error
	 *
	 * @access protected
	 */
	protected $_validation_error;

	/**
	 * @var SeedDMS_Core_DMS reference to the dms instance this attribute definition belongs to
	 *
	 * @access protected
	 */
	protected $_dms;

	/**
	 * @var string just the separator of a value set (not used)
	 *
	 * @access protected
	 */
	protected $_separator;

	/*
	 * Possible skalar data types of an attribute
	 */
	const type_int = 1;
	const type_float = 2;
	const type_string = 3;
	const type_boolean = 4;
	const type_url = 5;
	const type_email = 6;
	const type_date = 7;

	/*
	 * Addtional data types of an attribute representing objects in seeddms
	 */
	const type_folder = 101;
	const type_document = 102;
	//const type_documentcontent = 103;
	const type_user = 104;
	const type_group = 105;

	/*
	 * The object type for which a attribute may be used
	 */
	const objtype_all = 0;
	const objtype_folder = 1;
	const objtype_document = 2;
	const objtype_documentcontent = 3;

	/*
	 * The validation error codes
	 */
	const val_error_none = 0;
	const val_error_min_values = 1;
	const val_error_max_values = 2;
	const val_error_boolean = 8;
	const val_error_int = 6;
	const val_error_date = 9;
	const val_error_float = 7;
	const val_error_regex = 3;
	const val_error_email = 5;
	const val_error_url = 4;
	const val_error_document = 10;
	const val_error_folder = 11;
	const val_error_user = 12;
	const val_error_group = 13;
	const val_error_valueset = 14;

	/**
	 * Constructor
	 *
	 * @param integer $id internal id of attribute definition
	 * @param string $name name of attribute
	 * @param integer $objtype type of object for which this attribute definition
	 *        may be used.
	 * @param integer $type skalar type of attribute
	 * @param boolean $multiple set to true if multiple values are allowed
	 * @param integer $minvalues minimum number of values
	 * @param integer $maxvalues maximum number of values
	 * @param string $valueset separated list of allowed values, the first char
	 *        is taken as the separator
	 * @param $regex
	 */
	public function __construct($id, $name, int $objtype, int $type, $multiple, $minvalues, $maxvalues, $valueset, $regex) { /* {{{ */
		$this->_id = $id;
		$this->_name = $name;
		$this->_type = $type;
		$this->_objtype = $objtype;
		$this->_multiple = $multiple;
		$this->_minvalues = $minvalues;
		$this->_maxvalues = $maxvalues;
		$this->_valueset = $valueset;
		$this->_separator = substr($valueset, 0, 1);
		$this->_regex = $regex;
		$this->_dms = null;
		$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_none;
	} /* }}} */

	/**
	 * Set reference to dms
	 *
	 * @param SeedDMS_Core_DMS $dms
	 */
	public function setDMS($dms) { /* {{{ */
		$this->_dms = $dms;
	} /* }}} */

	/**
	 * Get dms of attribute definition
	 *
	 * @return object $dms
	 */
	public function getDMS() { return $this->_dms; }

	/**
	 * Get internal id of attribute definition
	 *
	 * @return integer id
	 */
	public function getID() { return $this->_id; }

	/**
	 * Get name of attribute definition
	 *
	 * @return string name
	 */
	public function getName() { return $this->_name; }

	/**
	 * Set name of attribute definition
	 *
	 * @param string $name name of attribute definition
	 * @return boolean true on success, otherwise false
	 */
	public function setName($name) { /* {{{ */
		$db = $this->_dms->getDB();

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `name` =".$db->qstr($name)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_name = $name;
		return true;
	} /* }}} */

	/**
	 * Get object type of attribute definition
	 *
	 * This can be one of objtype_all,
	 * objtype_folder, objtype_document, or objtype_documentcontent.
	 *
	 * @return integer type
	 */
	public function getObjType() { return $this->_objtype; }

	/**
	 * Set object type of attribute definition
	 *
	 * This can be one of objtype_all,
	 * objtype_folder, objtype_document, or objtype_documentcontent.
	 *
	 * @param integer $objtype type
	 * @return bool
	 */
	public function setObjType($objtype) { /* {{{ */
		$db = $this->_dms->getDB();

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `objtype` =".intval($objtype)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_objtype = $objtype;
		return true;
	} /* }}} */

	/**
	 * Get type of attribute definition
	 *
	 * This can be one of type_int, type_float, type_string, type_boolean,
	 * type_url, type_email.
	 *
	 * @return integer type
	 */
	public function getType() { return $this->_type; }

	/**
	 * Set type of attribute definition
	 *
	 * This can be one of type_int, type_float, type_string, type_boolean,
	 * type_url, type_email.
	 *
	 * @param integer $type type
	 * @return bool
	 */
	public function setType($type) { /* {{{ */
		$db = $this->_dms->getDB();

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `type` =".intval($type)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_type = $type;
		return true;
	} /* }}} */

	/**
	 * Check if attribute definition allows multi values for attribute
	 *
	 * @return boolean true if attribute may have multiple values
	 */
	public function getMultipleValues() { return $this->_multiple; }

	/**
	 * Set if attribute definition allows multi values for attribute
	 *
	 * @param boolean $mv true if attribute may have multiple values, otherwise
	 * false
	 * @return bool
	 */
	public function setMultipleValues($mv) { /* {{{ */
		$db = $this->_dms->getDB();

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `multiple` =".intval($mv)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_multiple = $mv;
		return true;
	} /* }}} */

	/**
	 * Return minimum number of values for attributes
	 *
	 * Attributes with multiple values may be limited to a range
	 * of values. This functions returns the minimum number of values.
	 *
	 * @return integer minimum number of values
	 */
	public function getMinValues() { return $this->_minvalues; }

	public function setMinValues($minvalues) { /* {{{ */
		$db = $this->_dms->getDB();

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `minvalues` =".intval($minvalues)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_minvalues = $minvalues;
		return true;
	} /* }}} */

	/**
	 * Return maximum number of values for attributes
	 *
	 * Attributes with multiple values may be limited to a range
	 * of values. This functions returns the maximum number of values.
	 *
	 * @return integer maximum number of values
	 */
	public function getMaxValues() { return $this->_maxvalues; }

	public function setMaxValues($maxvalues) { /* {{{ */
		$db = $this->_dms->getDB();

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `maxvalues` =".intval($maxvalues)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_maxvalues = $maxvalues;
		return true;
	} /* }}} */

	/**
	 * Get the value set as saved in the database
	 *
	 * This is a string containing the list of valueѕ separated by a
	 * delimiter which also precedes the whole string, e.g. '|Yes|No'
	 *
	 * Use {@link SeedDMS_Core_AttributeDefinition::getValueSetAsArray()}
	 * for a list of values returned as an array.
	 *
	 * @return string value set
	 */
	public function getValueSet() { /* {{{ */
		return $this->_valueset;
	} /* }}} */

	/**
	 * Get the separator used for the value set
	 *
	 * This is the first char of the value set string.
	 *
	 * @return string separator or an empty string if a value set is not set
	 */
	public function getValueSetSeparator() { /* {{{ */
		if (strlen($this->_valueset) > 1) {
			return $this->_valueset[0];
		} elseif ($this->_multiple) {
			if ($this->_type == SeedDMS_Core_AttributeDefinition::type_boolean)
				return '';
			else
				return ',';
		} else {
			return '';
		}
	} /* }}} */

	/**
	 * Get the whole value set as an array
	 *
	 * Each element is trimmed.
	 *
	 * @return array values of value set or false if the value set has
	 *         less than 2 chars
	 */
	public function getValueSetAsArray() { /* {{{ */
		if (strlen($this->_valueset) > 1)
			return array_map('trim', explode($this->_valueset[0], substr($this->_valueset, 1)));
		else
			return array();
	} /* }}} */

	/**
	 * Get the n'th trimmed value of a value set
	 *
	 * @param $ind starting from 0 for the first element in the value set
	 * @return string n'th value of value set or false if the index is
	 *         out of range or the value set has less than 2 chars
	 * @internal param int $index
	 */
	public function getValueSetValue($ind) { /* {{{ */
		if (strlen($this->_valueset) > 1) {
			$tmp = explode($this->_valueset[0], substr($this->_valueset, 1));
			if (isset($tmp[$ind]))
				return trim($tmp[$ind]);
			else
				return false;
		} else
			return false;
	} /* }}} */

	/**
	 * Set the value set
	 *
	 * A value set is a list of values allowed for an attribute. The values
	 * are separated by a char which must also be the first char of the
	 * value set string. The method decomposes the value set, removes all
	 * leading and trailing white space from the elements and recombines them
	 * into a string.
	 *
	 * @param string $valueset
	 * @return boolean true if value set could be set, otherwise false
	 */
	public function setValueSet($valueset) { /* {{{ */
	/*
		$tmp = array();
		foreach ($valueset as $value) {
			$tmp[] = str_replace('"', '""', $value);
		}
		$valuesetstr = implode(",", $tmp);
	 */
		$valueset = trim($valueset);
		if ($valueset) {
			$valuesetarr = array_map('trim', explode($valueset[0], substr($valueset, 1)));
			$valuesetstr = $valueset[0].implode($valueset[0], $valuesetarr);
		} else {
			$valuesetstr = '';
		}

		$db = $this->_dms->getDB();

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `valueset` =".$db->qstr($valuesetstr)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_valueset = $valuesetstr;
		$this->_separator = substr($valuesetstr, 0, 1);
		return true;
	} /* }}} */

	/**
	 * Get the regular expression as saved in the database
	 *
	 * @return string regular expression
	 */
	public function getRegex() { /* {{{ */
		return $this->_regex;
	} /* }}} */

	/**
	 * Set the regular expression
	 *
	 * A value of the attribute must match this regular expression.
	 *
	 * The methods checks if the regular expression is valid by running
	 * preg_match() on an empty string and see if it fails. Trying to set
	 * an invalid regular expression will not overwrite the current
	 * regular expression.
	 *
	 * All leading and trailing spaces of $regex will be removed.
	 *
	 * @param string $regex
	 * @return boolean true if regex could be set or is invalid, otherwise false
	 */
	public function setRegex($regex) { /* {{{ */
		$db = $this->_dms->getDB();

		$regex = trim($regex);
		if ($regex && @preg_match($regex, '') === false)
			return false;

		$queryStr = "UPDATE `tblAttributeDefinitions` SET `regex` =".$db->qstr($regex)." WHERE `id` = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;

		$this->_regex = $regex;
		return true;
	} /* }}} */

	/**
	 * Check if the attribute definition is used
	 *
	 * Checks all documents, folders and document content whether at least
	 * one of them referenceѕ this attribute definition
	 *
	 * @return boolean true if attribute definition is used, otherwise false
	 */
	public function isUsed() { /* {{{ */
		$db = $this->_dms->getDB();

		$queryStr = "SELECT * FROM `tblDocumentAttributes` WHERE `attrdef`=".$this->_id;
		$resArr = $db->getResultArray($queryStr);
		if (is_array($resArr) && count($resArr) == 0) {
			$queryStr = "SELECT * FROM `tblFolderAttributes` WHERE `attrdef`=".$this->_id;
			$resArr = $db->getResultArray($queryStr);
			if (is_array($resArr) && count($resArr) == 0) {
				$queryStr = "SELECT * FROM `tblDocumentContentAttributes` WHERE `attrdef`=".$this->_id;
				$resArr = $db->getResultArray($queryStr);
				if (is_array($resArr) && count($resArr) == 0) {
					return false;
				}
			}
		}
		return true;
	} /* }}} */

	/**
	 * Parse a given value stored in the database according to attribute definition
	 *
	 * The return value is an array, if the attribute allows multiple values.
	 * Otherwise it is a single value.
	 * If the type of attribute is any of document, folder, user,
	 * or group then this method will fetch each object from the database and
	 * return an array of SeedDMS_Core_Document, SeedDMS_Core_Folder, etc.
	 *
	 * @param $value string
	 * @return array|bool
	 */
	public function parseValue(string $value) { /* {{{ */
		if ($this->getMultipleValues()) {
			/* If the value doesn't start with the separator used in the value set,
			 * then assume that the value was not saved with a leading separator.
			 * This can happen, if the value was previously a single value from
			 * the value set and later turned into a multi value attribute.
			 */
			$sep = substr($value, 0, 1);
			$vsep = $this->getValueSetSeparator();
			if ($sep == $vsep)
				$values = explode($sep, substr($value, 1));
			else
				$values = array($value);
		} else {
			$values = array($value);
		}

		$tmp = [];
		switch ((string) $this->getType()) {
		case self::type_document:
			foreach ($values as $value) {
				if ($u = $this->_dms->getDocument((int) $value))
					$tmp[] = $u;
			}
			$values = $tmp;
			break;
		case self::type_folder:
			foreach ($values as $value) {
				if ($u = $this->_dms->getFolder((int) $value))
					$tmp[] = $u;
			}
			$values = $tmp;
			break;
		case self::type_user:
			foreach ($values as $value) {
				if ($u = $this->_dms->getUser((int) $value))
					$tmp[] = $u;
			}
			$values = $tmp;
			break;
		case self::type_group:
			foreach ($values as $value) {
				if ($u = $this->_dms->getGroup((int) $value))
					$tmp[] = $u;
			}
			$values = $tmp;
			break;
		case self::type_boolean:
			foreach ($values as $value) {
				$tmp[] = (bool) $value;
			}
			$values = $tmp;
			break;
		case self::type_int:
			foreach ($values as $value) {
				$tmp[] = (int) $value;
			}
			$values = $tmp;
			break;
		case self::type_float:
			foreach ($values as $value) {
				$tmp[] = (float) $value;
			}
			$values = $tmp;
			break;
		}

		if ($this->getMultipleValues())
			return $values;
		else
			return $values[0];
	} /* }}} */

	/**
	 * Create the value stored in the database
	 */
	public function createValue($values) { /* {{{ */
		if (is_array($values)) {
			switch ($this->getType()) {
			case SeedDMS_Core_AttributeDefinition::type_document:
			case SeedDMS_Core_AttributeDefinition::type_folder:
			case SeedDMS_Core_AttributeDefinition::type_user:
			case SeedDMS_Core_AttributeDefinition::type_group:
				$tmp = array_map(fn($value): int => is_object($value) ? (int) $value->getId() : (int) $value, $values);
				break;
			case SeedDMS_Core_AttributeDefinition::type_boolean:
				$tmp = array_map(fn($value): int => $value ? '1' : '0', $values);
				break;
			default:
				$tmp = $values;
			}
		} else {
			switch ($this->getType()) {
			case SeedDMS_Core_AttributeDefinition::type_document:
			case SeedDMS_Core_AttributeDefinition::type_folder:
			case SeedDMS_Core_AttributeDefinition::type_user:
			case SeedDMS_Core_AttributeDefinition::type_group:
				$tmp = is_object($values) ? [$values->getId()] : (is_numeric($values) ? [$values] : []);
				break;
			case SeedDMS_Core_AttributeDefinition::type_boolean:
				$tmp = [$values ? 1 : 0];
				break;
			default:
				$tmp = [$values];
			}
		}

		if ($this->getMultipleValues()) {
			$vsep = $this->getValueSetSeparator();
		} else {
			$vsep = '';
		}
		return $vsep.implode($vsep, $tmp);
	} /* }}} */

	/**
	 * Return a list of documents, folders, document contents where this
	 * attribute definition is used
	 *
	 * @param integer $limit return not more the n objects of each type
	 * @return array|bool
	 */
	public function getStatistics($limit = 0) { /* {{{ */
		$db = $this->_dms->getDB();

		$result = array('docs'=>array(), 'folders'=>array(), 'contents'=>array());
		if ($this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_all ||
		   $this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_document) {
			$queryStr = "SELECT * FROM `tblDocumentAttributes` WHERE `attrdef`=".$this->_id;
			if ($limit)
				$queryStr .= " limit ".(int) $limit;
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $rec) {
					if ($doc = $this->_dms->getDocument($rec['document'])) {
						$result['docs'][] = $doc;
					}
				}
			}
			$valueset = $this->getValueSetAsArray();
			$possiblevalues = array();
			foreach ($valueset as $value) {
				$possiblevalues[md5($value)] = array('value'=>$value, 'c'=>0);
			}
			$queryStr = "SELECT count(*) c, `value` FROM `tblDocumentAttributes` WHERE `attrdef`=".$this->_id." GROUP BY `value` ORDER BY c DESC";
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $row) {
					$value = $this->parseValue($row['value']);
					$tmpattr = new SeedDMS_Core_Attribute(0, null, $this, $value);
					foreach ($tmpattr->getValueAsArray() as $value) {
						if (is_object($value))
							$key = md5((string) $value->getId());
						else
							$key = md5((string) $value);
						if (isset($possiblevalues[$key])) {
							$possiblevalues[$key]['c'] += $row['c'];
						} else {
							$possiblevalues[$key] = array('value'=>$value, 'c'=>$row['c']);
						}
					}
				}
				$result['frequencies']['document'] = $possiblevalues;
			}
		}

		if ($this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_all ||
		   $this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_folder) {
			$queryStr = "SELECT * FROM `tblFolderAttributes` WHERE `attrdef`=".$this->_id;
			if ($limit)
				$queryStr .= " limit ".(int) $limit;
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $rec) {
					if ($folder = $this->_dms->getFolder($rec['folder'])) {
						$result['folders'][] = $folder;
					}
				}
			}
			$valueset = $this->getValueSetAsArray();
			$possiblevalues = array();
			foreach ($valueset as $value) {
				$possiblevalues[md5($value)] = array('value'=>$value, 'c'=>0);
			}
			$queryStr = "SELECT count(*) c, `value` FROM `tblFolderAttributes` WHERE `attrdef`=".$this->_id." GROUP BY `value` ORDER BY c DESC";
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $row) {
					$value = $this->parseValue($row['value']);
					$tmpattr = new SeedDMS_Core_Attribute(0, null, $this, $value);
					foreach ($tmpattr->getValueAsArray() as $value) {
						if (is_object($value))
							$key = md5((string) $value->getId());
						else
							$key = md5((string) $value);
						if (isset($possiblevalues[$key])) {
							$possiblevalues[$key]['c'] += $row['c'];
						} else {
							$possiblevalues[$key] = array('value'=>$value, 'c'=>$row['c']);
						}
					}
				}
				$result['frequencies']['folder'] = $possiblevalues;
			}
		}

		if ($this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_all ||
		   $this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_documentcontent) {
			$queryStr = "SELECT * FROM `tblDocumentContentAttributes` WHERE `attrdef`=".$this->_id;
			if ($limit)
				$queryStr .= " limit ".(int) $limit;
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $rec) {
					if ($content = $this->_dms->getDocumentContent($rec['content'])) {
						$result['contents'][] = $content;
					}
				}
			}
			$valueset = $this->getValueSetAsArray();
			$possiblevalues = array();
			foreach ($valueset as $value) {
				$possiblevalues[md5($value)] = array('value'=>$value, 'c'=>0);
			}
			$queryStr = "SELECT count(*) c, `value` FROM `tblDocumentContentAttributes` WHERE `attrdef`=".$this->_id." GROUP BY `value` ORDER BY c DESC";
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $row) {
					$value = $this->parseValue($row['value']);
					$tmpattr = new SeedDMS_Core_Attribute(0, null, $this, $value);
					foreach ($tmpattr->getValueAsArray() as $value) {
						if (is_object($value))
							$key = md5((string) $value->getId());
						else
							$key = md5((string) $value);
						if (isset($possiblevalues[$key])) {
							$possiblevalues[$key]['c'] += $row['c'];
						} else {
							$possiblevalues[$key] = array('value'=>$value, 'c'=>$row['c']);
						}
					}
				}
				$result['frequencies']['content'] = $possiblevalues;
			}
		}

		return $result;
	} /* }}} */

	/**
	 * Remove the attribute definition
	 * Removal is only executed when the definition is not used anymore.
	 *
	 * @return boolean true on success or false in case of an error
	 */
	public function remove() { /* {{{ */
		$db = $this->_dms->getDB();

		if ($this->isUsed())
			return false;

		// Delete user itself
		$queryStr = "DELETE FROM `tblAttributeDefinitions` WHERE `id` = " . $this->_id;
		if (!$db->getResult($queryStr)) return false;

		return true;
	} /* }}} */

	/**
	 * Get all documents and folders by a given attribute value
	 *
	 * @param string $attrvalue value of attribute
	 * @param integer $limit limit number of documents/folders
	 * @return array array containing list of documents and folders
	 */
	public function getObjects($attrvalue, $limit = 0, $op = O_EQ) { /* {{{ */
		$db = $this->_dms->getDB();

		$result = array('docs'=>array(), 'folders'=>array(), 'contents'=>array());
		if ($this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_all ||
		  $this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_document) {
			$queryStr = "SELECT * FROM `tblDocumentAttributes` WHERE `attrdef`=".$this->_id;
			if ($attrvalue != null) {
				$queryStr .= " AND ";
				if ($this->getMultipleValues()) {
					$sep = $this->getValueSetSeparator();
					$queryStr .= "(`value` like ".$db->qstr($sep.$attrvalue.'%')." OR `value` like ".$db->qstr('%'.$sep.$attrvalue.$sep.'%')." OR `value` like ".$db->qstr('%'.$sep.$attrvalue).")";
				} else {
					$queryStr .= "`value`".$op.$db->qstr((string) $attrvalue);
				}
			}
			if ($limit)
				$queryStr .= " limit ".(int) $limit;
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $rec) {
					if ($doc = $this->_dms->getDocument($rec['document'])) {
						$result['docs'][] = $doc;
					}
				}
			}
		}

		if ($this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_all ||
		   $this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_folder) {
			$queryStr = "SELECT * FROM `tblFolderAttributes` WHERE `attrdef`=".$this->_id." AND ";
			if ($this->getMultipleValues()) {
				$sep = $this->getValueSetSeparator();
				$queryStr .= "(`value` like ".$db->qstr($sep.$attrvalue.'%')." OR `value` like ".$db->qstr('%'.$sep.$attrvalue.$sep.'%')." OR `value` like ".$db->qstr('%'.$sep.$attrvalue).")";
			} else {
				$queryStr .= "`value`=".$db->qstr($attrvalue);
			}
			if ($limit)
				$queryStr .= " limit ".(int) $limit;
			$resArr = $db->getResultArray($queryStr);
			if ($resArr) {
				foreach ($resArr as $rec) {
					if ($folder = $this->_dms->getFolder($rec['folder'])) {
						$result['folders'][] = $folder;
					}
				}
			}
		}

		return $result;
	} /* }}} */

	/**
	 * Remove a given attribute value from all documents, versions and folders
	 *
	 * @param string $attrvalue value of attribute
	 * @return array array containing list of documents and folders
	 */
	public function removeValue($attrvalue) { /* {{{ */
		$db = $this->_dms->getDB();

		foreach (array('document', 'documentcontent', 'folder') as $type) {
			if ($type == 'document') {
				$tablename = "tblDocumentAttributes";
				$objtype = SeedDMS_Core_AttributeDefinition::objtype_document;
			} elseif ($type == 'documentcontent') {
				$tablename = "tblDocumentContentAttributes";
				$objtype = SeedDMS_Core_AttributeDefinition::objtype_documentcontent;
			} elseif ($type == 'folder') {
				$tablename = "tblFolderAttributes";
				$objtype = SeedDMS_Core_AttributeDefinition::objtype_folder;
			}
			if ($this->_objtype == SeedDMS_Core_AttributeDefinition::objtype_all || $objtype) {
				$queryStr = "SELECT * FROM `".$tablename."` WHERE `attrdef`=".$this->_id." AND ";
				if ($this->getMultipleValues()) {
					$sep = $this->getValueSetSeparator();
					$queryStr .= "(`value` like ".$db->qstr($sep.$attrvalue.'%')." OR `value` like ".$db->qstr('%'.$sep.$attrvalue.$sep.'%')." OR `value` like ".$db->qstr('%'.$sep.$attrvalue).")";
				} else {
					$queryStr .= "`value`=".$db->qstr($attrvalue);
				}

				$resArr = $db->getResultArray($queryStr);
				if ($resArr) {
					$db->startTransaction();
					foreach ($resArr as $rec) {
						if ($rec['value'] == $attrvalue) {
							$queryStr = "DELETE FROM `".$tablename."` WHERE `id`=".$rec['id'];
						} else {
							if ($this->getMultipleValues()) {
								$sep = substr($rec['value'], 0, 1);
								$vsep = $this->getValueSetSeparator();
								if ($sep == $vsep)
									$values = explode($sep, substr($rec['value'], 1));
								else
									$values = array($rec['value']);
								if (($key = array_search($attrvalue, $values)) !== false) {
									unset($values[$key]);
								}
								if ($values) {
									$queryStr = "UPDATE `".$tablename."` SET `value`=".$db->qstr($sep.implode($sep, $values))." WHERE `id`=".$rec['id'];
								} else {
									$queryStr = "DELETE FROM `".$tablename."` WHERE `id`=".$rec['id'];
								}
							} else {
							}
						}
						if (!$db->getResult($queryStr)) {
							$db->rollbackTransaction();
							return false;
						}
					}
					$db->commitTransaction();
				}
			}
		}
		return true;
	} /* }}} */

	/**
	 * Validate value against attribute definition
	 *
	 * This function checks if the given value fits the attribute
	 * definition.
	 * If the validation fails the validation error will be set which
	 * can be requested by SeedDMS_Core_Attribute::getValidationError()
	 * Set $new to true if the value to be checked isn't saved to the database
	 * already. It will just be passed to the callback onAttributeValidate where
	 * it could be used to, e.g. check if a value is unique once it is saved to
	 * the database. $object is set to a folder, document or documentcontent
	 * if the attribute belongs to such an object. This will be null, if a
	 * new object is created.
	 *
	 * @param string|array $attrvalue attribute value
	 * @param object $object set if the current attribute is saved for this object
	 *   (this will only be passed to the onAttributeValidate callback)
	 * @param boolean $new set to true if the value is new value and not taken from
	 *   an existing attribute
	 *   (this will only be passed to the onAttributeValidate callback)
	 * @return boolean true if validation succeeds, otherwise false
	 */
	public function validate($attrvalue, $object = null, $new = false) { /* {{{ */
		/* Check if 'onAttributeValidate' callback is set */
		if (isset($this->_dms->callbacks['onAttributeValidate'])) {
			foreach ($this->_dms->callbacks['onAttributeValidate'] as $callback) {
				$ret = call_user_func($callback[0], $callback[1], $this, $attrvalue, $object, $new);
				if (is_bool($ret))
					return $ret;
			}
		}

		/* Turn $attrvalue into an array of values. Checks if $attrvalue starts
		 * with a separator char as set in the value set and use it to explode
		 * the $attrvalue. If the separator doesn't match or this attribute
		 * definition doesn't have a value set, then just create a one element
		 * array. if $attrvalue is empty, then create an empty array.
		 */
		if ($this->getMultipleValues()) {
			if (is_string($attrvalue) && $attrvalue) {
				$sep = $attrvalue[0];
				$vsep = $this->getValueSetSeparator();
				if ($sep == $vsep)
					$values = explode($attrvalue[0], substr($attrvalue, 1));
				else
					$values = array($attrvalue);
			} elseif (is_array($attrvalue)) {
				$values = $attrvalue;
			} elseif (is_string($attrvalue) && !$attrvalue) {
				$values = array();
			} else
				$values = array($attrvalue);
		} elseif ($attrvalue !== null) {
			$values = array($attrvalue);
		} else {
			$values = array();
		}

		/* Check if attribute value has at least the minimum number of values */
		$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_none;
		if ($this->getMinValues() > count($values)) {
			$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_min_values;
			return false;
		}
		/* Check if attribute value has not more than maximum number of values */
		if ($this->getMaxValues() && $this->getMaxValues() < count($values)) {
			$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_max_values;
			return false;
		}

		$success = true;
		switch ((string) $this->getType()) {
		case self::type_boolean:
			foreach ($values as $value) {
				$success = $success && (preg_match('/^[01]$/', (string) $value) || $value === true || $value === false);
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_boolean;
			break;
		case self::type_int:
			foreach ($values as $value) {
				$success = $success && (preg_match('/^[0-9]*$/', (string) $value) ? true : false);
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_int;
			break;
		case self::type_date:
			foreach ($values as $value) {
				$d = explode('-', $value, 3);
				$success = $success && (count($d) == 3) && checkdate((int) $d[1], (int) $d[2], (int) $d[0]);
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_date;
			break;
		case self::type_float:
			foreach ($values as $value) {
				$success = $success && is_numeric($value);
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_float;
			break;
		case self::type_string:
			if (trim($this->getRegex()) != '') {
				foreach ($values as $value) {
					$success = $success && (preg_match($this->getRegex(), $value) ? true : false);
				}
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_regex;
			break;
		case self::type_email:
			foreach ($values as $value) {
				//$success &= filter_var($value, FILTER_VALIDATE_EMAIL) ? true : false;
				$success = $success && (preg_match('/^[a-z0-9._-]+@[a-z0-9-]{2,63}(\.[a-z0-9-]{2,63})*\.[a-z]{2,63}$/i', $value) ? true : false);
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_email;
			break;
		case self::type_url:
			foreach ($values as $value) {
				$success = $success && (preg_match('/^http(s)?:\/\/[a-z0-9_-]+(\.[a-z0-9-]{2,63})*(:[0-9]+)?(\/.*)?$/i', $value) ? true : false);
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_url;
			break;
		case self::type_document:
			$success = true;
			foreach ($values as $value) {
				if (!$value->isType('document'))
					$success = $success && false;
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_document;
			break;
		case self::type_folder:
			$success = true;
			foreach ($values as $value) {
				if (!$value->isType('folder'))
					$success = $success && false;
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_folder;
			break;
		case self::type_user:
			$success = true;
			foreach ($values as $value) {
				if (!$value->isType('user'))
					$success = $success && false;
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_user;
			break;
		case self::type_group:
			$success = true;
			foreach ($values as $value) {
				if (!$value->isType('group'))
					$success = $success && false;
			}
			if (!$success)
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_group;
			break;
		}

		if (!$success)
			return $success;

		/* Check if value is in value set */
		if ($valueset = $this->getValueSetAsArray()) {
			/* An empty value cannot be the value set */
			if (!$values) {
				$success = false;
				$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_valueset;
			} else {
				foreach ($values as $value) {
					if (!in_array($value, $valueset)) {
						$success = false;
						$this->_validation_error = SeedDMS_Core_AttributeDefinition::val_error_valueset;
					}
				}
			}
		}

		return $success;

	} /* }}} */

	/**
	 * Get validation error from last validation
	 *
	 * @return integer error code
	 */
	public function getValidationError() { return $this->_validation_error; }

} /* }}} */
