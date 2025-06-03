<?php
/**
 * Implementation of RemoveFolder view
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
 * Class which outputs the html page for RemoveFolder view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_RemoveFolder extends SeedDMS_Theme_Style
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
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
		$decrypted_name = htmlspecialchars($this->decryptName($folder->getName(), $encryption_key));

		$this->htmlStartPage(getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true), "view_folder", $folder);
		$this->contentHeading(getMLText("rm_folder"));
		$this->warningMsg(getMLText("confirm_rm_folder", array("foldername" => htmlspecialchars($decrypted_name))));
		?>
		<form action="../op/op.RemoveFolder.php" method="post" name="form1">
			<input type="Hidden" name="folderid" value="<?php print $folder->getID(); ?>">
			<input type="Hidden" name="showtree" value="<?php echo showtree(); ?>">
			<?php echo createHiddenFieldWithKey('removefolder'); ?>
			<p><?php $this->formSubmit("<i class=\"fa fa-remove\"></i> " . getMLText('rm_folder'), '', '', 'danger'); ?></p>
		</form>
		<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>