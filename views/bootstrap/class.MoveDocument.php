<?php
/**
 * Implementation of MoveDocument view
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
 * Class which outputs the html page for MoveDocument view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_MoveDocument extends SeedDMS_Theme_Style
{

	function js()
	{ /* {{{ */
		header('Content-Type: application/javascript; charset=UTF-8');

		?>
		$(document).ready( function() {
		$('input[id^=choosefoldersearch]').focus();
		});
		<?php

		//		$this->printFolderChooserJs("form1");
	} /* }}} */
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
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$document = $this->params['document'];
		$target = $this->params['target'];
		$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
		$decrypted = $this->decryptName($document->getName(), $encryption_key);
		$optionname = ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]') ? $document->getName() : $decrypted;

		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($optionname))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("move_document"));
		?>
		<form class="form-horizontal" action="../op/op.MoveDocument.php" name="form1">
			<?php echo createHiddenFieldWithKey('movedocument'); ?>
			<input type="hidden" name="documentid" value="<?php print $document->getID(); ?>">
			<?php

			$this->contentContainerStart('warning');
			$this->formField(getMLText("choose_target_folder"), $this->getFolderChooserHtml("form1", M_READ, -1, $target));
			$this->contentContainerEnd();
			$this->formSubmit(getMLText('move'));
			?>
		</form>
		<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>