<?php
/**
 * Implementation of UserList view
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
 * Class which outputs the html page for UserList view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_UserList extends SeedDMS_Theme_Style
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
	private function decryptName($encrypted_combined_base64, $key)
	{
		// Base64 decode with strict mode
		$data = base64_decode($encrypted_combined_base64, true);
		if ($data === false) {
			return '[INVALID NAME: BASE64 DECODE FAILED]';
		}

		// Check minimum length for IV + ciphertext
		if (strlen($data) < 17) {
			return '[INVALID NAME: DATA TOO SHORT]';
		}

		// Extract IV and ciphertext
		$iv = substr($data, 0, 16);
		$ciphertext = substr($data, 16);

		// Try decrypting
		$decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

		if ($decrypted === false) {
			return '[DECRYPTION FAILED: OPENSSL ERROR]';
		}

		return $decrypted;
	}

	function js()
	{ /* {{{ */
		header('Content-Type: application/javascript; charset=UTF-8');
		?>
		$(document).ready(function(){
		$("#myInput").on("keyup", function() {
		var value = $(this).val().toLowerCase();
		$("#myTable tbody tr").filter(function() {
		$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		});
		});
		});
		<?php
	} /* }}} */

	function show()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$allUsers = $this->params['allusers'];
		$httproot = $this->params['httproot'];
		$quota = $this->params['quota'];
		$pwdexpiration = $this->params['pwdexpiration'];
		$accessobject = $this->params['accessobject'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation("", "admin_tools");
		$this->contentHeading(getMLText("user_list"));

		$sessionmgr = new SeedDMS_SessionMgr($dms->getDB());
		?>

		<input type="text" id="myInput" class="form-control" placeholder="<?= getMLText('type_to_filter'); ?>">
		<table id="myTable" class="table table-condensed table-sm">
			<thead>
				<tr>
					<th></th>
					<th><?php printMLText('name'); ?></th>
					<th><?php printMLText('groups'); ?></th>
					<th><?php printMLText('role'); ?></th>
					<th><?php printMLText('discspace'); ?></th>
					<th><?php printMLText('authentication'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($allUsers as $currUser) {
					echo "<tr" . ($currUser->isDisabled() ? " class=\"table-danger error\"" : ($currUser->isHidden() ? " class=\"table-warning warning\"" : "")) . ">";
					echo "<td>";
					if ($currUser->hasImage())
						print "<img width=\"100\" src=\"" . $this->html_url('UserImage', array('userid' => $currUser->getId())) . "\" >";
					echo "</td>";
					echo "<td>";
					$fullName = $currUser->getFullName() ?? '';
					$login = $currUser->getLogin() ?? '';
					$email = $currUser->getEmail() ?? '';
					$comment = $currUser->getComment() ?? '';
					$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';

					$decrypted = $this->decrypt($currUser->getFullName(), $encryption_key);
					$fullname_v1 = ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]') ? $currUser->getFullName() : $decrypted;
					$decryptedlogin = $this->decrypt($currUser->getLogin(), $encryption_key);
					$login = ($decryptedlogin === '[DECRYPTION FAILED]' || $decryptedlogin === '[INVALID NAME]') ? $currUser->getLogin() : $decryptedlogin;
					$decryptedemail = $this->decrypt($currUser->getEmail(), $encryption_key);
					$email = ($decryptedemail === '[DECRYPTION FAILED]' || $decryptedemail === '[INVALID NAME]') ? $currUser->getEmail() : $decryptedemail;
					$decryptedcomment = $this->decrypt($currUser->getComment(), $encryption_key);
					$comment_v1 = ($decryptedemail === '[DECRYPTION FAILED]' || $decryptedemail === '[INVALID NAME]') ? $currUser->getEmail() : $decryptedemail;

					echo htmlspecialchars($fullname_v1) . " (" . htmlspecialchars($login) . ")<br />";
					echo "<a href=\"mailto:" . htmlspecialchars($email) . "\">" . htmlspecialchars($email) . "</a><br />";
					echo "<small>" . htmlspecialchars($comment_v1) . "</small>";
					echo "</td>";
					echo "<td>";
					$groups = $currUser->getGroups();

					if (count($groups) != 0) {
						for ($j = 0; $j < count($groups); $j++) {

							$encrypted_name = $groups[$j]->getName();
							$encrypted_name = $groups[$j]->getName();
							$maybe_decrypted = $this->decrypt($encrypted_name, $encryption_key);

							// If decryption failed or returned an invalid response, assume it was already decrypted
							$decrypted_name = ($maybe_decrypted === '[DECRYPTION FAILED]' || $maybe_decrypted === '[INVALID NAME]')
								? $encrypted_name
								: $maybe_decrypted;
							// If decryption failed, fallback to something readable
							if ($decrypted_name == '[DECRYPTION FAILED]' || $decrypted_name === '[INVALID NAME]') {
								$decrypted_name = '(Unreadable)';
							}

							print htmlspecialchars($decrypted_name, ENT_QUOTES, 'UTF-8');

							if ($j + 1 < count($groups)) {
								print ", ";
							}
						}
					}
					echo "</td>";
					echo "<td>";
					echo htmlspecialchars($currUser->getRole()->getName());
					echo "</td>";
					echo "<td>";
					echo SeedDMS_Core_File::format_filesize($currUser->getUsedDiskSpace());
					if ($quota) {
						echo " / ";
						$qt = $currUser->getQuota() ? $currUser->getQuota() : $quota;
						echo SeedDMS_Core_File::format_filesize($qt) . "<br />";
						echo $this->getProgressBar($currUser->getUsedDiskSpace(), $qt);
					}
					echo "</td>";
					echo "<td>";
					if ($pwdexpiration) {
						$now = new DateTime();
						$expdate = new DateTime($currUser->getPwdExpiration());
						$diff = $now->diff($expdate);
						if ($expdate > $now) {
							printf(getMLText('password_expires_in_days'), $diff->format('%a'));
							echo " (" . $expdate->format('Y-m-d H:i:sP') . ")";
						} else {
							printMLText("password_expired");
						}
					}
					$sessions = $sessionmgr->getUserSessions($currUser, 10);
					if ($sessions) {
						foreach ($sessions as $session) {
							echo "<br />" . getMLText('lastaccess') . ": " . getLongReadableDate($session->getLastAccess());
						}
					}
					echo "</td>";
					echo "<td>";
					if ($accessobject->check_view_access(array('UsrMgr', 'RemoveUser'))) {
						echo "<div class=\"list-action\">";
						echo $this->html_link('UsrMgr', array('userid' => $currUser->getID()), array(), '<i class="fa fa-edit"></i>', false);
						echo $this->html_link('RemoveUser', array('userid' => $currUser->getID()), array(), '<i class="fa fa-remove"></i>', false);
						echo "</div>";
					}
					echo "</td>";
					echo "</tr>";
				}
				echo "</tbody></table>";

				echo '<a class="btn btn-primary" href="../op/op.UserListCsv.php">' . getMLText('export_user_list_csv') . '</a>';
				$this->contentEnd();
				$this->htmlEndPage();
	} /* }}} */
}
?>