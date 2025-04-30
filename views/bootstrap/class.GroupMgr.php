<?php
/**
 * Implementation of GroupMgr view
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
 * Class which outputs the html page for GroupMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_GroupMgr extends SeedDMS_Theme_Style
{

	function js()
	{ /* {{{ */
		$selgroup = $this->params['selgroup'];
		$strictformcheck = $this->params['strictformcheck'];

		header('Content-Type: application/javascript; charset=UTF-8');
		parent::jsTranslations(array('js_form_error', 'js_form_errors'));
		?>
		function runValidation() {
		$("#form_1").validate({
		rules: {
		name: {
		required: true
		},
		<?php
		if ($strictformcheck) {
			?>
			comment: {
			required: true
			},
			<?php
		}
		?>
		},
		messages: {
		name: "<?php printMLText("js_no_name"); ?>",
		<?php
		if ($strictformcheck) {
			?>
			comment: "<?php printMLText("js_no_comment"); ?>",
			<?php
		}
		?>
		}
		});
		$("#form_2").validate({
		rules: {
		userid: {
		required: true
		},
		},
		messages: {
		userid: "<?php printMLText("js_select_user"); ?>",
		}
		});
		}

		$(document).ready( function() {
		$( "#selector" ).change(function() {
		$('div.ajax').trigger('update', {groupid: $(this).val()});
		window.history.pushState({"html":"","pageTitle":""},"", '../out/out.GroupMgr.php?groupid=' + $(this).val());
		});
		});
		<?php
	} /* }}} */

	function info()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$selgroup = $this->params['selgroup'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$workflowmode = $this->params['workflowmode'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		if ($selgroup) {
			$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
			$this->contentHeading(getMLText("group_info"));
			echo "<table class=\"table table-condensed table-sm\">\n";
			if ($workflowmode == "traditional") {
				$reviewstatus = $selgroup->getReviewStatus();
				$i = 0;
				foreach ($reviewstatus as $rv) {
					if ($rv['status'] == 0) {
						$i++;
					}
				}
				echo "<tr><td>" . getMLText('pending_reviews') . "</td><td>" . $i . "</td></tr>";
			}
			if ($workflowmode == "traditional" || $workflowmode == 'traditional_only_approval') {
				$approvalstatus = $selgroup->getApprovalStatus();
				$i = 0;
				foreach ($approvalstatus as $rv) {
					if ($rv['status'] == 0) {
						$i++;
					}
				}
				echo "<tr><td>" . getMLText('pending_approvals') . "</td><td>" . $i . "</td></tr>";
			}
			if ($workflowmode == 'advanced') {
				$workflowStatus = $selgroup->getWorkflowStatus();
				if ($workflowStatus)
					echo "<tr><td>" . getMLText('pending_workflows') . "</td><td>" . count($workflowStatus) . "</td></tr>\n";
			}
			echo "</table>";
		}
	} /* }}} */

	function actionmenu()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$selgroup = $this->params['selgroup'];

		if ($selgroup) {
			$button = array(
				'label' => getMLText('action'),
				'menuitems' => array(
				)
			);
			$button['menuitems'][] = array('label' => '<i class="fa fa-remove"></i> ' . getMLText("rm_group"), 'link' => '../out/out.RemoveGroup.php?groupid=' . $selgroup->getID());
			if ($selgroup->getUsers())
				$button['menuitems'][] = array('label' => '<i class="fa fa-download"></i> ' . getMLText("export_user_list_csv"), 'link' => '../op/op.UserListCsv.php?groupid=' . $selgroup->getID());
			self::showButtonwithMenu($button);
		}
	} /* }}} */
	private function decryptName($encrypted_combined_base64, $key)
	{
		// Decode the base64 encoded string
		$data = base64_decode($encrypted_combined_base64);

		// If the data can't be decoded properly or is too short, return an error
		if ($data === false || strlen($data) < 16) {
			error_log("Base64 decode failed or data too short for: " . $encrypted_combined_base64);
			return '[INVALID NAME]';
		}

		// Extract the IV (first 16 bytes) and ciphertext (remaining data)
		$iv = substr($data, 0, 16);
		$ciphertext = substr($data, 16);

		// Log the IV and ciphertext for debugging (as hex to see byte values)
		error_log("IV (Hex): " . bin2hex($iv));
		error_log("Ciphertext (Hex): " . bin2hex($ciphertext));

		// Attempt decryption using AES-256-CBC
		$decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

		// If decryption fails, log the error and return a failure message
		if ($decrypted === false) {
			error_log("Decryption failed for data: " . $encrypted_combined_base64);
			return '[DECRYPTION FAILED]';
		}

		return $decrypted;
	}

	private function isProbablyEncrypted($text)
	{
		$decoded = base64_decode($text, true);
		return $decoded !== false && strlen($decoded) > 16;
	}


	function showGroupForm($group)
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$allUsers = $this->params['allusers'];
		$groups = $this->params['allgroups'];
		$sortusersinlist = $this->params['sortusersinlist'];
		?>
		<form class="form-horizontal" action="../op/op.GroupMgr.php" name="form_1" id="form_1" method="post">
			<?php
			$namedecrypt = '';
			$commentdecrypt = '';
			$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';

			if ($group) {
				echo createHiddenFieldWithKey('editgroup');
				$namedecrypt = $this->decryptName($group->getName(), $encryption_key);
				$commentdecrypt = $this->decryptName($group->getComment(), $encryption_key);
				?>
				<input type="hidden" name="groupid" value="<?php print $group->getID(); ?>">
				<input type="hidden" name="action" value="editgroup">
				<?php
			} else {
				echo createHiddenFieldWithKey('addgroup');
				// Leave name and comment blank for new group
				$namedecrypt = '';
				$commentdecrypt = '';
				?>
				<input type="hidden" name="action" value="addgroup">
				<?php
			}

			$this->contentContainerStart();

			$this->formField(
				getMLText("name"),
				array(
					'element' => 'input',
					'type' => 'text',
					'id' => 'name',
					'name' => 'name',
					'value' => htmlspecialchars($namedecrypt)
				)
			);

			$this->formField(
				getMLText("comment"),
				array(
					'element' => 'textarea',
					'id' => 'comment',
					'name' => 'comment',
					'rows' => 4,
					'value' => htmlspecialchars($commentdecrypt)
				)
			);

			$this->contentContainerEnd();
			$this->formSubmit("<i class=\"fa fa-save\"></i> " . getMLText('save'));
			?>
		</form>

		<?php
		if ($group) {
			$this->contentHeading(getMLText("group_members"));
			?>
			<table class="table table-condensed table-sm">
				<?php
				$members = $group->getUsers();
				if (count($members) == 0)
					print "<tr><td>" . getMLText("no_group_members") . "</td></tr>";
				else {

					foreach ($members as $member) {

						print "<tr>";
						print "<td><i class=\"fa fa-user\"></i></td>";
						print "<td>" . htmlspecialchars($member->getFullName() . " (" . $member->getLogin() . ")") . "<br>" . htmlspecialchars($member->getEmail()) . "</td>";
						print "<td>" . ($group->isMember($member, true) ? getMLText("manager") : "&nbsp;") . "</td>";
						print "<td>";
						print "<form action=\"../op/op.GroupMgr.php\" method=\"post\" class=\"form-inline\" style=\"display: inline-block; margin-bottom: 0px;\"><input type=\"hidden\" name=\"action\" value=\"rmmember\" /><input type=\"hidden\" name=\"groupid\" value=\"" . $group->getID() . "\" /><input type=\"hidden\" name=\"userid\" value=\"" . $member->getID() . "\" />" . createHiddenFieldWithKey('rmmember') . "<button type=\"submit\" class=\"btn btn-danger btn-mini btn-sm\"><i class=\"fa fa-remove\"></i><span class=\"d-none d-lg-block\"> " . getMLText("delete") . "</span></button></form>";
						print "&nbsp;";
						print "<form action=\"../op/op.GroupMgr.php\" method=\"post\" class=\"form-inline\" style=\"display: inline-block; margin-bottom: 0px;\"><input type=\"hidden\" name=\"groupid\" value=\"" . $group->getID() . "\" /><input type=\"hidden\" name=\"action\" value=\"tmanager\" /><input type=\"hidden\" name=\"userid\" value=\"" . $member->getID() . "\" />" . createHiddenFieldWithKey('tmanager') . "<button type=\"submit\" class=\"btn btn-secondary btn-mini btn-sm\"><i class=\"fa fa-random\"></i><span class=\"d-none d-lg-block\"> " . getMLText("toggle_manager") . "</span></button></form>";
						print "</td>";
						echo "<td>";
						if ($group->isMember($member, true) && $member->isAdmin() && $settings->_addManagerAsReviewer && !$settings->_enableAdminRevApp) {
							$this->warningMsg(getMLText("settings_manager_reviewer_is_admin", ['login' => $member->getLogin()]));
						}
						if ($group->isMember($member, true) && $member->isAdmin() && $settings->_addManagerAsApprover && !$settings->_enableAdminRevApp) {
							$this->warningMsg(getMLText("settings_manager_approver_is_admin", ['login' => $member->getLogin()]));
						}
						echo "</td>";
						print "</tr>";
					}
				}
				?>
			</table>

			<?php
			$this->contentHeading(getMLText("add_member"));
			?>

			<form class="form-horizontal" action="../op/op.GroupMgr.php" method="POST" name="form_2" id="form_2">
				<?php echo createHiddenFieldWithKey('addmember'); ?>
				<input type="Hidden" name="action" value="addmember">
				<input type="Hidden" name="groupid" value="<?php print $group->getID(); ?>">
				<?php
				$this->contentContainerStart();
				$options = array();
				$allUsers = $dms->getAllUsers($sortusersinlist);
				foreach ($allUsers as $currUser) {
					if (!$group->isMember($currUser))
						$options[] = array($currUser->getID(), htmlspecialchars($currUser->getLogin() . ' - ' . $currUser->getFullName()), ($currUser->getID() == $user->getID()), array(array('data-subtitle', htmlspecialchars($currUser->getEmail()))));
				}
				$this->formField(
					getMLText("user"),
					array(
						'element' => 'select',
						'id' => 'userid',
						'name' => 'userid',
						'class' => 'chzn-select',
						'options' => $options
					)
				);
				$this->formField(
					getMLText("manager"),
					array(
						'element' => 'input',
						'type' => 'checkbox',
						'name' => 'manager',
						'value' => 1
					)
				);
				$this->contentContainerEnd();
				$this->formSubmit("<i class=\"fa fa-save\"></i> " . getMLText('add'));
				?>
			</form>
			<?php
		}
	} /* }}} */

	function form()
	{ /* {{{ */
		$selgroup = $this->params['selgroup'];

		$this->showGroupForm($selgroup);
	} /* }}} */

	function show()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$accessop = $this->params['accessobject'];
		$selgroup = $this->params['selgroup'];
		$allUsers = $this->params['allusers'];
		$allGroups = $this->params['allgroups'];
		$strictformcheck = $this->params['strictformcheck'];

		$this->htmlAddHeader('<script type="text/javascript" src="../views/' . $this->theme . '/vendors/jquery-validation/jquery.validate.js"></script>' . "\n", 'js');
		$this->htmlAddHeader('<script type="text/javascript" src="../views/' . $this->theme . '/styles/validation-default.js"></script>' . "\n", 'js');

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");

		$this->contentHeading(getMLText("group_management"));
		$this->rowStart();
		$this->columnStart(4);
		?>
		<form class="form-horizontal">
			<?php
			$options = array();
			$options[] = array("-1", getMLText("choose_group"));
			$options[] = array("0", getMLText("add_group"));
			$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';

			foreach ($allGroups as $group) {
				try {
					$decrypted = $this->decryptName($group->getName(), $encryption_key);

					// Check if decryption failed and log the error
					if ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]') {
						// Log the error to the JavaScript console
						echo '<script>';
						echo 'console.log(' . json_encode("Decryption failed for group ID: " . $group->getID() . " with name: " . $group->getName()) . ');';
						echo '</script>';

						// Fallback to '(Unreadable)'
						$decrypted = '(Unreadable)';
					}

					// Sanitize the output for safe HTML rendering
					$decrypted_names = htmlspecialchars($decrypted, ENT_QUOTES, 'UTF-8');

				} catch (Exception $e) {
					// Log the error message if an exception occurs during decryption
					error_log("Exception during decryption for group ID: " . $group->getID() . ". Error: " . $e->getMessage());
					$decrypted_names = '(Unreadable)';
				}

				// Add the decrypted group name to options
				$options[] = array(
					$group->getID(),
					$decrypted_names,
					$selgroup && $group->getID() == $selgroup->getID()
				);
			}

			// Create the select dropdown field
			$this->formField(
				null, //getMLText("selection"),
				array(
					'element' => 'select',
					'id' => 'selector',
					'class' => 'chzn-select',
					'options' => $options,
					'placeholder' => getMLText('select_groups'),
				)
			);
			?>
		</form>

		<div class="ajax" style="margin-bottom: 15px;" data-view="GroupMgr" data-action="actionmenu" <?php echo ($selgroup ? "data-query=\"groupid=" . $selgroup->getID() . "\"" : "") ?>></div>
		<?php if ($accessop->check_view_access($this, array('action' => 'info'))) { ?>
			<div class="ajax" data-view="GroupMgr" data-action="info" <?php echo ($selgroup ? "data-query=\"groupid=" . $selgroup->getID() . "\"" : "") ?>></div>
			<?php
		}
		$this->columnEnd();
		$this->columnStart(8);
		if ($accessop->check_view_access($this, array('action' => 'form'))) {
			?>
			<div class="ajax" data-view="GroupMgr" data-action="form" data-afterload="()=>{runValidation();}" <?php echo ($selgroup ? "data-query=\"groupid=" . $selgroup->getID() . "\"" : "") ?>></div>
			<?php
		}
		$this->columnEnd();
		$this->rowEnd();
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>