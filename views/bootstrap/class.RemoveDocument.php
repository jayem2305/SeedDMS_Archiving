<?php
/**
 * Implementation of RemoveDocument view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
//require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for RemoveDocument view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_RemoveDocument extends SeedDMS_Theme_Style
{
	private function decryptName($encrypted_combined_base64, $key)
	{
		$data = base64_decode($encrypted_combined_base64);
		if ($data === false || strlen($data) < 16) {
			return '[INVALID NAME]';
		}
		$iv = substr($data, 0, 16);
		$ciphertext = substr($data, 16);
		$decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
		return $decrypted === false ? '[DECRYPTION FAILED]' : $decrypted;
	}


	function show()
	{ /* {{{ */
		parent::show();
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$document = $this->params['document'];
		$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
		$decrypted_name = htmlspecialchars($this->decryptName($document->getName(), $encryption_key));
		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("rm_document"));
		if ($document->isCheckedOut()) {
			$msg = getMLText('document_is_checked_out_remove');
			$this->warningMsg($msg);
		}

		$this->warningMsg(getMLText("confirm_rm_document", array("documentname" => htmlspecialchars($decrypted_name))));
		?>
		<form action="../op/op.RemoveDocument.php" name="form1" method="post">
			<input type="Hidden" name="documentid" value="<?php print $document->getID(); ?>">
			<?php echo createHiddenFieldWithKey('removedocument'); ?>
			<p><?php $this->formSubmit('<i class="fa fa-remove"></i> ' . getMLText('rm_document'), '', '', 'danger'); ?></p>
		</form>
		<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>