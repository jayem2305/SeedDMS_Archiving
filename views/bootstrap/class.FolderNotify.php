<?php
/**
 * Implementation of FolderNotify view
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
 * Class which outputs the html page for FolderNotify view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_FolderNotify extends SeedDMS_Theme_Style
{
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
	function js()
	{ /* {{{ */
		header('Content-Type: application/javascript; charset=UTF-8');
		?>
		function checkForm()
		{
		msg = new Array();
		if ((document.form1.userid.options[document.form1.userid.selectedIndex].value == -1) &&
		(document.form1.groupid.options[document.form1.groupid.selectedIndex].value == -1))
		msg.push("<?php printMLText("js_select_user_or_group"); ?>");
		if (msg != "") {
		noty({
		text: msg.join('<br />'),
		type: 'error',
		dismissQueue: true,
		layout: 'topRight',
		theme: 'defaultTheme',
		_timeout: 1500,
		});
		return false;
		}
		else
		return true;
		}
		$(document).ready(function() {
		$('body').on('submit', '#form1', function(ev){
		if(checkForm()) return;
		ev.preventDefault();
		});
		});
		<?php
	} /* }}} */

	function show()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$sortusersinlist = $this->params['sortusersinlist'];
		$enableusersview = $this->params['enableusersview'];

		$notifyList = $folder->getNotifyList(0, true);

		$this->htmlStartPage(getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageSidebar();
		?>
		<div class="dashboard-main-content-wrapper" style="min-height: 80vh;">
			<div class="dashboard-card-container" style="width: 100%; margin: 0;">
				<div class="card" style="width: 100%;">
					<div class="card-body">
						<?php $this->pageNavigation($this->getFolderPathHTML($folder, true), "view_folder", $folder); ?>
						<?php $this->contentHeading(getMLText("edit_existing_notify")); ?>
						<?php
						$userNotifyIDs = array();
						foreach ($notifyList["users"] as $userNotify) {
							$userNotifyIDs[] = $userNotify->getID();
						}
						$groupNotifyIDs = array();
						foreach ($notifyList["groups"] as $groupNotify) {
							$groupNotifyIDs[] = $groupNotify->getID();
						}

						$this->rowStart();
						$this->columnStart(6);

						?>

						<form class="form-horizontal" action="../op/op.FolderNotify.php" method="post" id="form1" name="form1">
							<?php echo createHiddenFieldWithKey('foldernotify'); ?>
							<input type="hidden" name="folderid" value="<?php print $folder->getID() ?>">
							<input type="hidden" name="action" value="addnotify">
							<?php
							$options = array();
							$options[] = array('-1', getMLText("select_one"));
							$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
							if ($user->isAdmin()) {
								$allUsers = $dms->getAllUsers($sortusersinlist);
								foreach ($allUsers as $userObj) {
									$decrypted = $this->decrypt($userObj->getFullName(), $encryption_key);
									$fullname = ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]') ? $userObj->getFullName() : $decrypted;
									$decrypted_login = $this->decrypt($userObj->getLogin(), $encryption_key);
									$login = ($decrypted_login === '[DECRYPTION FAILED]' || $decrypted_login === '[INVALID NAME]') ? $userObj->getLogin() : $decrypted_login;
									$decrypted_email = $this->decrypt($userObj->getLogin(), $encryption_key);
									$email = ($decrypted_email === '[DECRYPTION FAILED]' || $decrypted_email === '[INVALID NAME]') ? $userObj->getEmail() : $decrypted_email;

									if (!$userObj->isGuest() && !$userObj->isDisabled() && ($folder->getAccessMode($userObj) >= M_READ) && !in_array($userObj->getID(), $userNotifyIDs))
										$options[] = array($userObj->getID(), htmlspecialchars($login . ' - ' . $fullname), false, array(array('data-subtitle', htmlspecialchars($email))));
								}
							} elseif (!$user->isGuest() && !in_array($user->getID(), $userNotifyIDs)) {
								$options[] = array($user->getID(), htmlspecialchars($user->getLogin() . " - " . $user->getFullName()));
							}
							$this->contentContainerStart();
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
							$options = array();
							$options[] = array('-1', getMLText("select_one"));
							$allGroups = $dms->getAllGroups();
							foreach ($allGroups as $groupObj) {
								if (($user->isAdmin() || $groupObj->isMember($user, true)) && $folder->getGroupAccessMode($groupObj) >= M_READ && !in_array($groupObj->getID(), $groupNotifyIDs)) {
									$options[] = array($groupObj->getID(), htmlspecialchars($groupObj->getName()));
								}
							}
							$this->formField(
								getMLText("group"),
								array(
									'element' => 'select',
									'id' => 'groupid',
									'name' => 'groupid',
									'class' => 'chzn-select',
									'options' => $options
								)
							);
							$this->contentContainerEnd();
							$this->formSubmit(getMLText('add'));
							?>
						</form>
						<?php
						$this->columnEnd();
						$this->columnStart(6);
						$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
						if (empty($notifyList["users"]) && empty($notifyList["groups"])) {
							$this->infoMsg(getMLText("empty_notify_list"));
						} else {
							print "<table class=\"table table-condensed table-sm\">\n";

							foreach ($notifyList["users"] as $userNotify) {
								$decrypted = $this->decrypt($userNotify->getFullName(), $encryption_key);
								$fullname = ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]') ? $userNotify->getFullName() : $decrypted;

								$decrypted_login = $this->decrypt($userNotify->getLogin(), $encryption_key);
								$login = ($decrypted_login === '[DECRYPTION FAILED]' || $decrypted_login === '[INVALID NAME]') ? $userNotify->getLogin() : $decrypted_login;

								$decrypted_email = $this->decrypt($userNotify->getLogin(), $encryption_key); // Assuming this is intentional
								$email = ($decrypted_email === '[DECRYPTION FAILED]' || $decrypted_email === '[INVALID NAME]') ? $userNotify->getEmail() : $decrypted_email;

								if ($user->isAdmin() || $user->getID() == $userNotify->getID()) {
									print "<tr>";
									print "<td><i class=\"fa fa-user\"></i></td>";
									print "<td>" . htmlspecialchars($login . " - " . $fullname) . "</td>";
									print "<form action=\"../op/op.FolderNotify.php\" method=\"post\">\n";
									echo createHiddenFieldWithKey('foldernotify') . "\n";
									print "<input type=\"Hidden\" name=\"folderid\" value=\"" . $folder->getID() . "\">\n";
									print "<input type=\"Hidden\" name=\"action\" value=\"delnotify\">\n";
									print "<input type=\"Hidden\" name=\"userid\" value=\"" . $userNotify->getID() . "\">\n";
									print "<td>";
									print "<button type=\"submit\" class=\"btn btn-danger btn-mini btn-sm\"><i class=\"fa fa-remove\"></i> " . getMLText("delete") . "</button>";
									print "</td>";
									print "</form>\n";
									print "</tr>";
								}
							}

							foreach ($notifyList["groups"] as $groupNotify) {
								$decrypted = $this->decrypt($groupNotify->getName(), $encryption_key);
								$fullname = ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]') ? $groupNotify->getName() : $decrypted;

								/* admins and members of a group may see exiting notifications */
								if ($user->isAdmin() || /*$enableusersview || */ $groupNotify->isMember($user, false)) {
									print "<tr>";
									print "<td><i class=\"fa fa-group\"></i></td>";
									print "<td>" . htmlspecialchars($fullname) . "</td>";
									print "<form action=\"../op/op.FolderNotify.php\" method=\"post\">\n";
									echo createHiddenFieldWithKey('foldernotify') . "\n";
									print "<input type=\"Hidden\" name=\"folderid\" value=\"" . $folder->getID() . "\">\n";
									print "<input type=\"Hidden\" name=\"action\" value=\"delnotify\">\n";
									print "<input type=\"Hidden\" name=\"groupid\" value=\"" . $groupNotify->getID() . "\">\n";
									print "<td>";
									print "<button type=\"submit\" class=\"btn btn-danger btn-mini btn-sm\"><i class=\"fa fa-remove\"></i> " . getMLText("delete") . "</button>";
									print "</td>";
									print "</form>\n";
									print "</tr>";
								}
							}
							print "</table>\n";
						}

						$this->columnEnd();
						$this->rowEnd();
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>