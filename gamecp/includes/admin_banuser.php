<?php
/**
 * Game Control Panel v2
 * Copyright (c) www.intrepid-web.net
 *
 * The use of this product is subject to a license agreement
 * which can be found at http://www.intrepid-web.net/rf-game-cp-v2/license-agreement/
 */

if (!defined('COMMON_INITIATED')) {
    die("Hacking attempt! Logged");
}

if (!empty($setmodules)) {
    $file = basename(__FILE__);
    $module[_l('Support')][_l('Manage Bans')] = $file;
    return;
}

$lefttitle = _l('Support Desk - Admin User Ban Management');
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        # Main variables
        $max_pages = 10;
        $top_limit = 60;
        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $search_fun = (isset($_POST['search_fun'])) ? $_POST['search_fun'] : "";
        $enable_exit = false;
        $enable_account = false;
        $query_p2 = '';
        $search_query = '';

        # Display 'global' layout
        $out .= '<table class="tborder" cellpadding="3" cellspacing="1" border="0" width="50%" align="center">' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt1" style="text-align: center;"><a href="./' . $script_name . '?do=' . $_GET['do'] . '">' . _l('View Ban List') . '</a></td>' . "\n";
        $out .= '		<td class="alt1" style="text-align: center;"><a href="./' . $script_name . '?do=' . $_GET['do'] . '&page=addedit">Ban User</a></td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '</table>' . "\n";

        if (empty($page)) {

            connectuserdb();

            # Search box
            $out .= '<form method="post">' . "\n";
            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="thead" colspan="2">' . _l('Look up a banned user') . '</td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt2" width="1%" nowrap>' . _l('Account Name') . ': </td>' . "\n";
            $out .= '		<td class="alt1"><input type="text" class="form-control" name="account_name"/></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt2" width="1%" nowrap>' . _l('Account Serial') . ': </td>' . "\n";
            $out .= '		<td class="alt1"><input type="text" class="form-control" name="account_serial"/></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt2" colspan="2" nowrap><input type="submit"  class="btn btn-default" name="search_fun" value="' . _l('Search') . '"/></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '</table>' . "\n";
            $out .= '</form>' . "\n";

            # You searched, sir?#
            if ($search_fun != '') {
                # Get our variables from...post
                $account_serial = (isset($_POST['account_serial']) && is_int((int)$_POST['account_serial'])) ? antiject((int)$_POST['account_serial']) : 0;
                $account_name = (isset($_POST['account_name'])) ? antiject($_POST['account_name']) : '';
                $chat_ban = (isset($_POST['chat_ban'])) ? antiject($_POST['chat_ban']) : '';

                if ($account_serial == 0 && $account_name == "") {
                    $enable_exit = true;
                    $out .= '<p style="text-align: center; font-weight: bold;">' . _l('You must enter a Account Name or serial to do a search') . '</p>';
                }

                if ($enable_exit === false) {
                    $search_query = ' WHERE ';

                    // '._l('Account Name').'
                    if ($account_name != "" && $account_serial == 0) {
                        $search_query .= " U.id = convert(binary,'$account_name')";
                    } else {
                        $search_query .= " U.Serial = '$account_serial'";
                    }

                    $query_p2 .= ' AND ';
                } else {
                    $query_p2 = ' WHERE ';
                }
            } else {
                $query_p2 = ' WHERE ';
            }

            # Lets get our data, shall we?
            $query_p1 = 'SELECT
					CONVERT(varchar, U.id) AS username, B.nAccountSerial, B.dtStartDate, B.nPeriod, B.nKind, B.szReason, B.GMWriter, U.Serial
					FROM
					tbl_UserBan AS B
					INNER JOIN
					tbl_UserAccount AS U
					ON U.serial = B.nAccountSerial
					' . $search_query;
            $query_p2 .= 'U.Serial NOT IN
			( SELECT TOP [OFFSET] U.Serial
			FROM
			tbl_UserBan AS B
			INNER JOIN
			tbl_UserAccount AS U
			ON U.serial = B.nAccountSerial
			' . $search_query . '
			ORDER BY B.dtStartDate DESC) ORDER BY B.dtStartDate DESC';

            // Pageination
            include('./includes/pagination/ps_pagination.php');

            //Create a PS_Pagination object
            $pager = new PS_Pagination($user_dbconnect, $query_p1, $query_p2, $top_limit, $max_pages, '' . $script_name . '?do=' . $_GET['do']);

            //The paginate() function returns a mysql
            //result set for the current page
            $rs = $pager->paginate();

            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="thead" style="text-align: center;" nowrap>' . _l('Serial') . '</td>' . "\n";
            $out .= '		<td class="thead" nowrap>' . _l('Start Date') . '</td>' . "\n";
            $out .= '		<td class="thead" nowrap>' . _l('Account Name') . '</td>' . "\n";
            $out .= '		<td class="thead" nowrap>' . _l('Period') . '</td>' . "\n";
            $out .= '		<td class="thead" style="text-align: center;" nowrap>' . _l('Chat Ban') . '</td>' . "\n";
            $out .= '		<td class="thead" nowrap>' . _l('Reason') . '</td>' . "\n";
            $out .= '		<td class="thead" style="text-align: center;" nowrap>' . _l('Game Master') . '</td>' . "\n";
            $out .= '		<td class="thead" style="text-align: center;" colspan="2" nowrap>' . _l('Options') . '</td>' . "\n";
            $out .= '	</tr>' . "\n";
            while ($row = mssql_fetch_array($rs)) {

                $gmwriter = antiject($row['GMWriter']);
                if ($gmwriter == 'WS0') {
                    $gmwriter = '-';
                    $row['szReason'] = _l('Auto-banned by FireGuard');
                }

                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" style="text-align: center;" nowrap>' . $row['Serial'] . '</td>' . "\n";
                $out .= '		<td class="alt1" nowrap>' . $row['dtStartDate'] . '</td>' . "\n";
                $out .= '		<td class="alt1" nowrap>' . antiject($row['username']) . '</td>' . "\n";
                $out .= '		<td class="alt1" nowrap>' . $row['nPeriod'] . '</td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" nowrap>' . (($row['nKind'] == 1) ? 'Yes ' : 'No') . '</td>' . "\n";
                $out .= '		<td class="alt1" nowrap>' . antiject($row['szReason']) . '</td>' . "\n";
                $out .= '		<td class="alt1" nowrap>' . antiject($gmwriter) . '</td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" nowrap><a href="./' . $script_name . '?do=' . $_GET['do'] . '&page=addedit&edit_ban_serial=' . $row['Serial'] . '" style="text-decoration: none;">Edit</a></td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" nowrap><a href="./' . $script_name . '?do=' . $_GET['do'] . '&page=delete&ban_serial=' . $row['Serial'] . '" style="text-decoration: none;">Delete</a></td>' . "\n";
                $out .= '	</tr>' . "\n";
            }

            if (mssql_num_rows($rs) <= 0) {
                $out .= '		<tr>' . "\n";
                $out .= '			<td class="alt1" colspan="9" style="text-align: center; font-weight: bold;">' . _l('No banned accounts found') . '</td>' . "\n";
                $out .= '		</tr>' . "\n";
            } else {
                $out .= '		<tr>' . "\n";
                $out .= '			<td class="alt2" colspan="9" style="text-align: center; font-weight: bold;">' . $pager->renderFullNav() . '</td>' . "\n";
                $out .= '		</tr>' . "\n";
            }

            // Free Result
            @mssql_free_result($rs);

            $out .= '</table>' . "\n";

        } elseif ($page == 'addedit') {

            connectuserdb();

            # Variables
            $display_form = true;
            $do_process = 0;
            $exit_process = false;
            $exit_text = '';
            $add_submit = (isset($_POST['add_submit'])) ? 1 : 0;
            $edit_submit = (isset($_POST['edit_submit'])) ? 1 : 0;
            if (isset($_POST['edit_ban_serial']) || isset($_GET['edit_ban_serial'])) {
                $edit_ban_serial = (isset($_POST['edit_ban_serial'])) ? intval($_POST['edit_ban_serial']) : intval($_GET['edit_ban_serial']);
                if (!is_numeric($edit_ban_serial)) {
                    $edit_ban_serial = '';
                }
            } else {
                $edit_ban_serial = '';
            }
            $ban_serial = (isset($_POST['ban_serial']) && is_numeric($_POST['ban_serial'])) ? antiject($_POST['ban_serial']) : 0;
            $ban_period = (isset($_POST['ban_period']) && is_numeric($_POST['ban_period'])) ? antiject($_POST['ban_period']) : 119988;
            $ban_period = ($ban_period > 119988) ? '119988' : $ban_period;
            $ban_reason = (isset($_POST['ban_reason'])) ? antiject($_POST['ban_reason']) : '';
            $ban_chat = (isset($_POST['ban_chat'])) ? antiject($_POST['ban_chat']) : 0;

            # Do we have to process data?
            if ($add_submit == 1 or $edit_submit == 1) {
                $do_process = 1;
            }

            # Editing are we? ... sir?
            if ($edit_ban_serial != '') {
                $page_mode = 'edit_submit';
                $submit_name = 'Update Account';
                $this_mode_title = 'Edit Banned Account';
                $disable = ' disabled';

                if ($do_process == 0) {
                    $select_sql = "SELECT nPeriod, szReason, nKind FROM tbl_UserBan WHERE nAccountSerial = '$edit_ban_serial'";
                    if (!($select_result = mssql_query($select_sql))) {
                        $display_form = false;
                        $out .= '<p style="text-align: center; font-weight: bold;">' . _l('SQL Error occurred while trying to obtain account data') . '</p>';
                    } else {
                        if (mssql_num_rows($select_result) > 0) {
                            $info = mssql_fetch_array($select_result);

                            $ban_period = $info['nPeriod'];
                            $ban_reason = $info['szReason'];
                            $ban_chat = $info['nKind'];
                        } else {
                            $display_form = false;
                            $out .= '<p style="text-align: center; font-weight: bold;">' . _l('No such user found') . '</p>';
                        }
                    }
                    // Free Result
                    @mssql_free_result($select_result);
                }

                $ban_serial = $edit_ban_serial;
            } else {
                $page_mode = 'add_submit';
                $submit_name = _l('Ban Account');
                $this_mode_title = _l('New Account Ban');
                $disable = '';
            }

            # Process --- i---i-eraera--ing
            if ($do_process == 1) {
                # Error checking
                if ($ban_serial == 0) {
                    $exit_process = true;
                    $exit_text .= '&raquo; ' . _l('You have not provided a user serial') . '<br/>';
                }

                if ($ban_period == 0) {
                    $exit_process = true;
                    $exit_text .= '&raquo; ' . _l('You must provide a ban period (i.e. 119988)') . '<br/>';
                }

                if ($ban_reason == '') {
                    $exit_process = true;
                    $exit_text .= '&raquo; ' . _l('You must provide a reason for this ban') . '<br/>';
                }

                if ($exit_process != true) {
                    $users_select = "SELECT Serial FROM tbl_UserAccount WHERE Serial = '$ban_serial'";
                    if (!($users_result = mssql_query($users_select))) {
                        $exit_process = true;
                        $exit_text .= '&raquo; ' . _l('SQL Error while trying to obtain account info');
                    } else {
                        if (mssql_num_rows($users_result) <= 0) {
                            $exit_process = true;
                            $exit_text .= '&raquo; ' . _l('No such account serial found in the database');
                        }
                    }
                }
            }

            # So, if we got errors, display, else, continue
            if ($exit_process != 1) {
                if ($add_submit == 1) {
                    $insert_sql = "INSERT INTO tbl_UserBan (nAccountSerial, nPeriod, nKind, szReason, GMWriter) VALUES ('$ban_serial', '$ban_period', '$ban_chat', '$ban_reason','" . $userdata['username'] . "')";
                    if (!($insert_result = @mssql_query($insert_sql))) {
                        $exit_process = true;
                        $exit_text .= '&raquo; .' . _l('This account has already been banned');
                        #$out .= '<p style="text-align: center; font-weight: bold;">This account has already been banned</p>';
                    } else {
                        $exit_process = true;
                        $exit_text .= '<b>&raquo; ' . _l('Successfully banned the account') . ': $ban_serial</b>';
                        #$out .= '<p style="text-align: center; font-weight: bold;">Successfully banned the account: '.$ban_serial.'</p>';
                        // Writing an admin log :D
                        gamecp_log(4, $userdata['username'], "ADMIN - MANAGE BANS - ADDED - Account Serial: $ban_serial", 1);
                        #$display_form = false;
                    }
                } elseif ($edit_submit == 1) {
                    $update_sql = "UPDATE tbl_UserBan SET nPeriod = '$ban_period', szReason = '$ban_reason', nKind = '$ban_chat' WHERE nAccountSerial = '$edit_ban_serial'";
                    if (!($update_result = mssql_query($update_sql, $user_dbconnect))) {
                        $out .= '<p style="text-align: center; font-weight: bold;">SQL Error while trying to update user ban</p>';
                    } else {
                        if (mssql_num_rows($update_result) > 0) {
                            #$out .= '<p style="text-align: center; font-weight: bold;">Successfully upddated the banned account: '.$edit_ban_serial.'</p>';
                            #$display_form = false;
                            $exit_process = true;
                            $exit_text .= '&raquo; Successfully updated the banned account: ' . $edit_ban_serial;
                            // Writing an admin log :D
                            gamecp_log(4, $userdata['username'], "ADMIN - MANAGE BANS - UPDATED - Account Serial: $edit_ban_serial", 1);
                        } else {
                            $out .= '<p style="text-align: center; font-weight: bold;">' . _l('No such banned account found') . '</p>';
                        }
                    }
                }

            }

            if ($exit_process == 1) {
                $out .= '<table class="table table-bordered">' . "\n";
                $out .= '		<tr>' . "\n";
                $out .= '			<td>' . "\n";
                $out .= '				' . $exit_text . "\n";
                $out .= '			</td>' . "\n";
                $out .= '		</tr>' . "\n";
                $out .= '</table>' . "\n";
            }

            # Main form
            if ($display_form == true) {
                $out .= '<form method="post">' . "\n";
                $out .= '<table class="table table-bordered">' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="thead" colspan="2">' . $this_mode_title . '</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" width="1" nowrap>' . _l('Account Serial') . ':</td>' . "\n";
                $out .= '		<td class="alt1"><input type="text" class="form-control" name="ban_serial" value="' . (($ban_serial != 0) ? $ban_serial : '') . '"' . $disable . ' /></td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" width="1" nowrap>' . _l('Chat Ban') . ':</td>' . "\n";
                $checked_yes = ($ban_chat == 1) ? ' checked' : '';
                $checked_no = ($ban_chat == 0) ? ' checked' : '';
                $out .= '		<td class="alt1">Yes <input type="radio" name="ban_chat" value="1" ' . $checked_yes . '/> No <input type="radio" name="ban_chat" value="0" ' . $checked_no . '/></td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" width="1" nowrap>Period:</td>' . "\n";
                $out .= '		<td class="alt1">';
                $out .= '			<select class="form-control"name="ban_period">';
                foreach ($banTimes as $time) {
                    $out .= '				<option value="' . $time['hours'] . '"' . (($ban_period == $time['hours']) ? ' selected="selected"' : '') . '>' . $time['title'] . '</option>';
                }
                $out .= '			</select>';
                $out .= '		</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" width="1" nowrap>Reason:</td>' . "\n";
                $out .= '		<td class="alt1">' . "\n";
                $out .= '			<select class="form-control"name="ban_reason">' . "\n";
                for ($i = 0; $i < $reasons_count; $i++) {
                    if ($ban_reasons[$i] == $ban_reason) {
                        $selected = ' selected="selected"';
                    } else {
                        $selected = '';
                    }
                    $out .= '				<option' . $selected . '>' . $ban_reasons[$i] . '</option>' . "\n";
                }
                $out .= '			</select>' . "\n";
                $out .= '		</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" colspan="2" nowrap>' . "\n";
                $out .= '			<input name="page" type="hidden" value="addedit"/>' . "\n";
                $out .= '			<input name="' . $page_mode . '" type="submit" value="' . $submit_name . '"/></td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '</table>' . "\n";
                $out .= '</form>' . "\n";
            }

        } elseif ($page == 'delete') {

            $ban_serial = (isset($_GET['ban_serial']) && is_int((int)$_GET['ban_serial'])) ? antiject((int)$_GET['ban_serial']) : '';

            if ($ban_serial == '') {
                $out .= '<p style="text-align: center; font-weight: bold;">' . _l('No such banned account found') . '</p>';
            } else {
                connectuserdb();
                $user_query = mssql_query("SELECT convert(varchar,id) as Account FROM tbl_UserBan AS B INNER JOIN tbl_UserAccount AS U ON B.nAccountSerial = U.Serial WHERE B.nAccountSerial = '$ban_serial'");
                $user_info = mssql_fetch_array($user_query);

                if (mssql_num_rows($user_query) <= 0) {
                    $out .= '<p style="text-align: center; font-weight: bold;">' . _l('No such banned account found') . '</p>';
                } else {
                    $out .= '<form method="post">' . "\n";
                    $out .= '<p style="text-align: center; font-weight: bold;">Are you sure you want to UNBAN the Account: <u>' . antiject($user_info['Account']) . '</u> (Serial: ' . $ban_serial . ')?</p>' . "\n";
                    $out .= '<p style="text-align: center;"><input type="hidden" name="ban_serial" value="' . $ban_serial . '"/><input type="hidden" name="page" value="delete_user"/><input type="submit"  class="btn btn-default" name="yes" value="Yes"/> <input type="submit"  class="btn btn-default" name="no" value="No"/></p>';
                    $out .= '</form>';
                }

                // Free Result
                @mssql_free_result($user_query);
            }

        } elseif ($page == 'delete_user') {

            $yes = (isset($_POST['yes'])) ? '1' : '0';
            $no = (isset($_POST['no'])) ? '1' : '0';
            if (isset($_POST['ban_serial']) && is_int((int)$_POST['ban_serial'])) {
                $ban_serial = antiject((int)$_POST['ban_serial']);
            } else {
                $ban_serial = '';
            }

            if ($no != 1 && $ban_serial != '') {
                connectuserdb();
                $user_query = mssql_query("SELECT convert(varchar,id) as Account, U.Serial FROM tbl_UserBan AS B INNER JOIN tbl_UserAccount AS U ON B.nAccountSerial = U.Serial WHERE B.nAccountSerial = '$ban_serial'");
                $user = mssql_fetch_array($user_query);

                if (mssql_num_rows($user_query) <= 0) {
                    $out .= '<p style="text-align: center; font-weight: bold;">' . _l('No such banned account found') . '</p>';
                } else {
                    $cquery = mssql_query("DELETE FROM tbl_UserBan WHERE nAccountSerial = " . $ban_serial);

                    $out .= '<p style="text-align: center; font-weight: bold;">' . _l('Successfully unbanned the account: %s (Serial: %d)', antiject($user['Account']), $user['Serial']) . '</p>';
                    gamecp_log(5, $userdata['username'], "ADMIN - MANAGE BANS - UNBAN - Account Name:  " . antiject($user['Account']) . " | Serial: " . $user['Serial'], 1);
                }
                // Free Result
                @mssql_free_result($user_query);
            } else {
                header("Location: $script_name?do=" . $_GET['do']);
            }

        } else {

            $out .= _l('invalid_page_id');

        }

    } else {

        $out .= _l('no_permission');

    }

} else {
    $out .= _l('invalid_page_load');
}
?>