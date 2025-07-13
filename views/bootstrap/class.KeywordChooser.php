<?php
/**
 * Implementation of KeywordChooser view
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
 * Class which outputs the html page for KeywordChooser view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_KeywordChooser extends SeedDMS_Theme_Style
{

	function js()
	{ /* {{{ */
		$form = $this->params['form'];
		header('Content-Type: application/javascript; charset=UTF-8');
		?>
		var targetObj = document.<?php echo $form ?>.keywords;
		var myTA;

		function insertKeywords(keywords) {

		if (navigator.appName == "Microsoft Internet Explorer") {
		myTA.value += " " + keywords;
		}
		//assuming Mozilla
		else {
		selStart = myTA.selectionStart;

		myTA.value = myTA.value.substring(0,myTA.selectionStart) + " "
		+ keywords
		+ myTA.value.substring(myTA.selectionStart,myTA.value.length);

		myTA.selectionStart = selStart + keywords.length+1;
		myTA.selectionEnd = selStart + keywords.length+1;
		}
		myTA.focus();
		}

		function cancel() {
		// window.close();
		return true;
		}

		function acceptKeywords() {
		targetObj.value = myTA.value;
		// window.close();
		return true;
		}

		obj = new Array();
		obj[0] = -1;
		obj[1] = -1;
		function showKeywords(which) {
		if (obj[which] != -1)
		obj[which].style.display = "none";

		list = document.getElementById("categories" + which);

		id = list.options[list.selectedIndex].value;
		if (id == -1)
		return;

		obj[which] = document.getElementById("keywords" + id);
		obj[which].style.display = "";
		}

		$('#categories0').change(function(ev) {
		showKeywords(0);
		});

		$('#categories1').change(function(ev) {
		showKeywords(1);
		});

		$('.insertkeyword').click(function(ev) {
		attr_keyword = $(ev.currentTarget).attr('keyword');
		insertKeywords(attr_keyword);
		});

		myTA = document.getElementById("keywordta");
		myTA.value = targetObj.value;
		myTA.focus();
		<?php
	} /* }}} */
	private function decrypt($encrypted_combined_base64, $key)
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
		$categories = $this->params['categories'];

		//		$this->htmlStartPage(getMLText("use_default_keywords"));
		?>

		<div>
			<?php
			$this->contentContainerStart();
			$this->formField(
				getMLText("keywords"),
				array(
					'element' => 'textarea',
					'id' => 'keywordta',
					'rows' => 4,
				)
			);
			$options = array();
			$options[] = array('-1', getMLText('choose_category'));
			foreach ($categories as $category) {
				$owner = $category->getOwner();
				if ($owner->isAdmin())
					$options[] = array('' . $category->getID(), htmlspecialchars($category->getName()));
			}
			$this->formField(
				getMLText("global_default_keywords"),
				array(
					'element' => 'select',
					'id' => 'categories0',
					'options' => $options,
				)
			);
			$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
			foreach ($categories as $category) {
				$owner = $category->getOwner();
				if ($owner->isAdmin()) {
					$lists = $category->getKeywordLists();
					if (count($lists)) {
						$kw = array();
						foreach ($lists as $list) {
							$decrypted = $this->decrypt($list["keywords"], $encryption_key);
							$list = ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]') ? $list["keywords"] : $decrypted;
							$kw[] = "<a class=\"insertkeyword\" keyword=\"" . htmlspecialchars($list) . "\"><span class=\"badge badge-secondary\">" . htmlspecialchars($list) . "</span></a>";
						}
						echo '<div id="keywords' . $category->getId() . '" style="display: none;">';
						$this->formField(
							getMLText("default_keywords"),
							array(
								'element' => 'plain',
								'name' => 'categories0',
								'value' => implode(' ', $kw),
							)
						);
						echo '</div>';
					}
				}
			}
			$options = array();
			$options[] = array('-1', getMLText('choose_category'));
			foreach ($categories as $category) {
				$owner = $category->getOwner();
				if (!$owner->isAdmin())
					$options[] = array('' . $category->getID(), htmlspecialchars($category->getName()));
			}
			$this->formField(
				getMLText("personal_default_keywords"),
				array(
					'element' => 'select',
					'id' => 'categories1',
					'options' => $options,
				)
			);
			foreach ($categories as $category) {
				$owner = $category->getOwner();
				if (!$owner->isAdmin()) {
					$lists = $category->getKeywordLists();
					if (count($lists)) {
						$kw = array();
						foreach ($lists as $list) {
							$kw[] = "<a class=\"insertkeyword\" keyword=\"" . htmlspecialchars($list["keywords"]) . "\"><span class=\"badge badge-secondary\">" . htmlspecialchars($list["keywords"]) . "</span></a>";
						}
						echo '<div id="keywords' . $category->getId() . '" style="display: none;">';
						$this->formField(
							getMLText("default_keywords"),
							array(
								'element' => 'plain',
								'name' => 'categories0',
								'value' => implode(', ', $kw),
							)
						);
						echo '</div>';
					}
				}
			}
			$this->contentContainerEnd();
			echo '<script src="../out/out.KeywordChooser.php?action=js&' . $_SERVER['QUERY_STRING'] . '"></script>' . "\n";
		//		$this->htmlEndPage();
//		echo "</body>\n</html>\n";
	} /* }}} */
}
?>