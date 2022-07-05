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
    $module[_l('Support')][_l('Guild Search')] = $file;
    return;
}

$lefttitle = _l('Guild Search');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        $page = (isset($_GET['page']) || isset($_POST['page'])) ? ((isset($_GET['page'])) ? $_GET['page'] : $_POST['page']) : "";
        $search_fun = (isset($_GET['search_fun'])) ? $_GET['search_fun'] : "";
        $enable_exit = false;

        if (empty($page)) {

            $out .= '<form method="get" action="' . $script_name . '?do=support_desk">' . "\n";
            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="thead" colspan="2" style="padding: 4px;"><b>Look up a guild</b></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt1">Guild Serial:</td>' . "\n";
            $out .= '		<td class="alt2"><input type="text" class="form-control" name="guild_serial" /></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt1">Guild Name:</td>' . "\n";
            $out .= '		<td class="alt2"><input type="text" class="form-control" name="guild_name" /></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt1">Guild Delete Name:</td>' . "\n";
            $out .= '		<td class="alt2"><input type="text" class="form-control" name="guild_delete_name" /></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= '	<tr>';
            $out .= '		<td colspan="2"><input type="hidden" value="' . $_GET['do'] . '" name="do" /><input type="submit"  class="btn btn-default" value="Search" name="search_fun" /></td>';
            $out .= '	</tr>';
            $out .= '</table>' . "\n";
            $out .= '</form>' . "\n";

            if ($search_fun != "") {

                $guild_serial = (isset($_GET['guild_serial']) && is_int((int)$_GET['guild_serial'])) ? antiject((int)$_GET['guild_serial']) : 0;
                $guild_name = (isset($_GET['guild_name'])) ? antiject($_GET['guild_name']) : '';
                $guild_delete_name = (isset($_GET['guild_delete_name'])) ? antiject($_GET['guild_delete_name']) : '';

                if ($guild_serial == 0 && $guild_name == "" && $guild_delete_name == "") {
                    $enable_exit = true;
                    $out .= "<p align='center'><b>Guild name or serial is required</b></p>";
                }

                $search = '';
                if ($enable_exit != true) {

                    if ($guild_serial != 0) {
                        $search .= " serial = '$guild_serial' ";
                    }

                    if ($guild_name != '') {
                        $search .= ($search != '') ? ' AND ' : '';
                        if (!preg_match("/%/", $guild_name)) {
                            $search .= " id = '$guild_name' ";
                        } else {
                            $search .= " id LIKE '$guild_name' ";
                        }
                    }

                    if ($guild_delete_name != '') {
                        $search .= ($search != '') ? ' AND ' : '';
                        if (!preg_match("/%/", $guild_delete_name)) {
                            $search .= " deleteid = '$guild_delete_name' ";
                        } else {
                            $search .= " deleteid LIKE '$guild_delete_name' ";
                        }
                    }

                    connectdatadb();
                    if (!($result = mssql_query("SELECT id, serial, MemberCount, MasterSerial, Dalant, Gold FROM tbl_Guild WHERE $search"))) {
                        $out .= '<p style="text-align: center; font-weight: bold;">SQL Error! Failed in getting the guild info</p>';
                    }
                    $sql_rows = @mssql_num_rows($result);

                    $out .= '<br/>';
                    $out .= '<script type="text/javascript" src="checkall.js"></script>' . "\n";
                    while ($row = mssql_fetch_array($result)) {
                        $out .= '	<table class="table table-bordered">' . "\n";
                        $out .= '	<tr>' . "\n";
                        $out .= '		<td class="thead" style="padding: 4px" nowrap><b>Guild Serial </b></td>' . "\n";
                        $out .= '		<td class="thead" style="padding: 4px" nowrap><b>Guild Name</b></td>' . "\n";
                        $out .= '		<td class="thead" style="padding: 4px" nowrap><b>Member Count</b></td>' . "\n";
                        $out .= '		<td class="thead" style="padding: 4px" nowrap><b>Gold</b></td>' . "\n";
                        $out .= '		<td class="thead" style="padding: 4px" nowrap><b>Money</b></td>' . "\n";
                        $out .= '	</tr>' . "\n";
                        $out .= '	<tr>' . "\n";
                        $out .= '		<td style="font-weight: bold;" class="alt1" nowrap><a href="index.php?guild_serial=' . $row['serial'] . '&do=support_guild_search&search_fun=Search">' . $row['serial'] . '</a></td>' . "\n";
                        $out .= '		<td style="font-weight: bold;" class="alt1" nowrap>' . $row['id'] . '</td>' . "\n";
                        $out .= '		<td style="font-weight: bold;" class="alt1" nowrap>' . $row['MemberCount'] . '</td>' . "\n";
                        $out .= '		<td style="font-weight: bold;" class="alt1" nowrap>' . number_format($row['Gold']) . '</td>' . "\n";
                        $out .= '		<td style="font-weight: bold;" class="alt1" nowrap>' . number_format($row['Dalant']) . '</td>' . "\n";
                        $out .= '	</tr>' . "\n";
                        $out .= '	<tr>' . "\n";
                        $out .= '		<td style="font-weight: bold;" class="alt2" colspan="5">Guild Members</td>' . "\n";
                        $out .= '	</tr>' . "\n";
                        $out .= '	<tr>' . "\n";
                        $out .= '		<td style="font-weight: bold; padding: 4px;" class="alt1" colspan="5">' . "\n";

                        $char_result = mssql_query("SELECT B.Serial, B.Name, B.AccountSerial, B.Account, B.LastConnTime FROM tbl_base AS B INNER JOIN tbl_general as G ON B.Serial = G.Serial WHERE G.GuildSerial = '" . $row['serial'] . "' ORDER BY B.LastConnTime DESC");

                        $out .= '			<form name="user_list" method="post">' . "\n";
                        $out .= '			<table class="tborder" cellpadding="3" cellspacing="1" border="0" style="border: 0;" width="100%">' . "\n";
                        $out .= '				<tr>' . "\n";
                        $out .= '					<td class="thead">Account Serial</td>' . "\n";
                        $out .= '					<td class="thead">Account Name</td>' . "\n";
                        $out .= '					<td class="thead">Char Serial</td>' . "\n";
                        $out .= '					<td class="thead">Char Name</td>' . "\n";
                        $out .= '					<td class="thead">Last Conn Time</td>' . "\n";
                        $out .= '					<td class="thead" width="2%"><input name="allbox" id="checkAll" onclick="checkAllFields(1);" type="checkbox" name="check_all" value="0"/></td>' . "\n";
                        $out .= '				</tr>' . "\n";

                        $x = 0;
                        while ($char = mssql_fetch_array($char_result)) {

                            $class = ($char['Serial'] == $row['MasterSerial']) ? 'alt1 highlight' : 'alt1';

                            $lastconntime = $char['LastConnTime'];
                            if ($lastconntime > 0) {
                                if ((strlen($lastconntime)) <= 9) {
                                    $lastconntime = '0' . $lastconntime;
                                    $prepend_etime = '20';
                                } else {
                                    $prepend_etime = '20';
                                }
                                $lastconntime = str_split($lastconntime, 2);
                                $lastconntime = @mktime($lastconntime[3], $lastconntime[4], 0, (ltrim($lastconntime[1], '0')), $lastconntime[2], $prepend_etime . $lastconntime[0]);

                                $lastconntimex = $lastconntime;

                                //$lastconntime -= 60*60;
                                if (isset($config['gamecp_logs_url']) && !empty($config['gamecp_logs_url']) && $config['gamecp_logs_url'] != ' ') {
                                    $generate_item_url = $config['gamecp_logs_url'] . '?';
                                    $generate_item_url .= 'y=' . date('Y', $lastconntime) . '&';
                                    $generate_item_url .= 'm=' . date('m', $lastconntime) . '&';
                                    $generate_item_url .= 'd=' . date('d', $lastconntime) . '&';
                                    $generate_item_url .= 'h=' . date('G', $lastconntime) . '&';
                                    $generate_item_url .= 'serial=' . $row['Serial'];
                                    $lastconntimex = date('M d Y h:iA', $lastconntimex);
                                    $lastconntime = '<a href="' . $generate_item_url . '" target="logs">' . $lastconntimex . '</a>';
                                } else {
                                    $lastconntime = date('M d Y h:iA', $lastconntimex);
                                }
                            } else {
                                $lastconntime = '--';
                            }

                            $out .= '				<tr id="tr_' . $x . '" class="' . $class . '">' . "\n";
                            $out .= '					<td><a href="' . $script_name . '?do=support_desk&amp;account_serial=' . $char['AccountSerial'] . '&amp;search_fun=Search">' . $char['AccountSerial'] . '</a></td>' . "\n";
                            $out .= '					<td>' . $char['Account'] . '</td>' . "\n";
                            $out .= '					<td>' . $char['Serial'] . '</td>' . "\n";
                            $out .= '					<td>' . $char['Name'] . '</td>' . "\n";
                            $out .= '					<td style="font-size: 10px;">' . $lastconntime . '</td>' . "\n";
                            $out .= '					<td><input type="checkbox" name="ban_serial[]" class="boxes" onclick="JavaScript: checkAllFields(2); highlight(this,\'tr_' . $x . '\');" value="' . $char['AccountSerial'] . '"/></td>' . "\n";
                            $out .= '				</tr>' . "\n";

                            ++$x;

                        }
                        mssql_free_result($char_result);
                        $out .= '				<tr>' . "\n";
                        $out .= '					<td class="alt2" colspan="6" style="text-align: right;">' . "\n";
                        $out .= '						<b>Ban Rason:</b> <select class="form-control"name="ban_reason">' . "\n";
                        for ($i = 0; $i < $reasons_count; $i++) {
                            $out .= '							<option>' . $ban_reasons[$i] . '</option>' . "\n";
                        }
                        $out .= '						</select>' . "\n";
                        $out .= '						<b>Period:</b> ' . "\n";
                        $out .= '			<select class="form-control"name="ban_period">';
                        foreach ($banTimes as $time) {
                            $out .= '				<option value="' . $time['hours'] . '">' . $time['title'] . '</option>';
                        }
                        $out .= '			</select>';
                        $out .= '						<input type="hidden" name="page" value="ban_users"/><input type="submit"  class="btn btn-default" name="ban_users" value="Ban [0] Selected" id="removeChecked"/>' . "\n";
                        $out .= '					</td>' . "\n";
                        $out .= '				</tr>' . "\n";
                        $out .= '			</table>';
                        $out .= '			</form>' . "\n";

                        $out .= '		</td>' . "\n";
                        $out .= '	</tr>' . "\n";
                        $out .= '</table>' . "\n";
                        $out .= '<br/>' . "\n";

                    }
                    mssql_free_result($result);

                    // Writing an admin log :D
                    gamecp_log(0, $userdata['username'], "SUPPORT - GUILD SEARCH - Searched for: $guild_name or $guild_serial", 1);

                }

            }

        } elseif ($page == 'ban_users') {
            $ban_serial = (isset($_POST['ban_serial'])) ? $_POST['ban_serial'] : '';
            $ban_period = (isset($_POST['ban_period']) && is_int((int)$_POST['ban_period'])) ? antiject((int)$_POST['ban_period']) : 119988;
            $ban_period = ($ban_period > 119988) ? '119988' : $ban_period;
            $ban_reason = (isset($_POST['ban_reason'])) ? antiject($_POST['ban_reason']) : '';
            $do_process = 1;
            $exit_process = false;
            $exit_text = '';

            if (!is_array($ban_serial) && !is_int($ban_period) && $ban_reason == '') {
                $do_process = 0;
                $exit_process = true;
                $exit_text .= '&raquo; Failed: No IDEA WHY!';
            }

            # Process --- i---i-eraera--ing
            if ($do_process == 1) {
                # Error checking
                if ($ban_serial == '') {
                    $exit_process = true;
                    $exit_text .= '&raquo; You have not provided a user serial<br/>';
                }

                if ($ban_period == '') {
                    $exit_process = true;
                    $exit_text .= '&raquo; You must provide a ban period (i.e. 119988)<br/>';
                }

                if ($ban_reason == '') {
                    $exit_process = true;
                    $exit_text .= '&raquo; You must provide a reason for this ban<br/>';
                }

            }

            # So, if we got errors, display, else, continue
            if ($exit_process != 1) {
                foreach ($ban_serial as $serial) {

                    $serial = (is_int((int)$serial)) ? antiject((int)$serial) : 0;

                    if ($serial != 0) {
                        connectuserdb();
                        $users_select = "SELECT Serial FROM tbl_UserAccount WHERE Serial = '$serial'";
                        if (!($users_result = mssql_query($users_select))) {
                            $exit_process = true;
                            $exit_text .= '&raquo; SQL Error while trying to obtain account info';
                        } else {
                            if (mssql_num_rows($users_result) <= 0) {
                                $exit_process = true;
                                $exit_text .= '&raquo; No such account serial (#' . $serial . ') found in the database';
                            } else {
                                $insert_sql = "INSERT INTO tbl_UserBan (nAccountSerial, nPeriod, nKind, szReason, GMWriter) VALUES ('$serial', '$ban_period', '0', '$ban_reason','" . $userdata['username'] . "')";
                                if (!($insert_result = @mssql_query($insert_sql))) {
                                    $exit_process = true;
                                    $exit_text .= '&raquo; This account (#' . $serial . ') has already been banned<br/>';
                                } else {
                                    $exit_process = true;
                                    $exit_text .= '<b>&raquo; Successfully banned the account: ' . $serial . '</b><br/>';
                                    // Writing an admin log :D
                                    gamecp_log(4, $userdata['username'], "ADMIN - MANAGE BANS - ADDED - Account Serial: $serial", 1);
                                }
                            }
                        }
                    }
                }
                /*$insert_sql = "INSERT INTO tbl_UserBan (nAccountSerial, nPeriod, nKind, szReason, GMWriter) VALUES ('$ban_serial', '$ban_period', '$ban_chat', '$ban_reason','".$userdata['username']."')";
                if(!($insert_result = @mssql_query($insert_sql))) {
                    $exit_process = true;
                    $exit_text .= '&raquo; This account has already been banned';
                    #$out .= '<p style="text-align: center; font-weight: bold;">This account has already been banned</p>';
                } else {
                    $exit_process = true;
                    $exit_text .= '<b>&raquo; Successfully banned the account: '.$ban_serial.'</b>';
                    #$out .= '<p style="text-align: center; font-weight: bold;">Successfully banned the account: '.$ban_serial.'</p>';
                    // Writing an admin log :D
                    gamecp_log(4,$userdata['username'],"ADMIN - MANAGE BANS - ADDED - Account Serial: $ban_serial",1);
                    #$display_form = false;
                }*/
            }

            if ($exit_process == 1) {
                $out .= '<table class="table table-bordered">' . "\n";
                $out .= '		<tr>' . "\n";
                $out .= '			<td class="alt2">' . "\n";
                $out .= '				' . $exit_text . "\n";
                $out .= '			</td>' . "\n";
                $out .= '		</tr>' . "\n";
                $out .= '</table>' . "\n";

                header("Refresh: 1; URL=" . $script_name . '?do=' . $_GET['do']);
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