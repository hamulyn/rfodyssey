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
    $module[_l('Server Admin')][_l('GM Accounts')] = $file;
    return;
}

$lefttitle = _l('Support Desk - Admin GM Accounts/Characters');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";

        if (empty($page)) {

            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '<tr>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Status</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Serial</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Account Name</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Password</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Real Name</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Create Date</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Last Connect IP</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Last Login Date</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Last Logoff Date</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Grade</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Sub Grade</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Expire Date</b></td>';
            $out .= '</tr>';

            connectuserdb();
            $result = mssql_query("SELECT
					Serial, convert(varchar,id) as username,  convert(varchar,pw) as password, Grade, RealName, LastConnIP, CreateDT, LastLoginDT, LastLogoffDT, SubGrade, ExpireDT
					FROM 
					tbl_StaffAccount
					ORDER BY LastLoginDT DESC");

            connectdatadb();
            while ($row = mssql_fetch_array($result)) {

                $t_login = strtotime($row['LastLoginDT']);
                $t_logout = strtotime($row['LastLogoffDT']);
                $t_cur = time();
                $t_maxlogin = $t_login + 2592000;


                if (($t_login <= $t_logout)) {
                    $status = "offline";
                } elseif ($t_maxlogin < $t_cur) {
                    $status = "offline";
                } else {
                    $status = "online";
                }

                $username = ereg_replace(";$", "", $row['username']);
                $username = ereg_replace("\\\\", "", $username);
                $password = ereg_replace(";$", "", $row['password']);
                $password = ereg_replace("\\\\", "", $password);

                $out .= '<tr>';
                $out .= '<td class="alt2" width="5%" style="text-align: center; font-size: 10px; font-weight: bold;" nowrap><img src="./includes/images/' . $status . '.gif" /></td>';
                $out .= '<td class="alt2" width="5%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['Serial'] . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $username . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $password . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['RealName'] . '</td>';
                $out .= '<td class="alt2" width="15%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['CreateDT'] . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['LastConnIP'] . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['LastLoginDT'] . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['LastLogoffDT'] . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['Grade'] . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['SubGrade'] . '</td>';
                $out .= '<td class="alt2" width="10%" style="font-size: 10px; font-weight: bold;" nowrap>' . $row['ExpireDT'] . '</td>';
                $out .= '</tr>';

                $char_query = "SELECT Serial, Name, DeleteName FROM tbl_base WHERE AccountSerial = '" . $row['Serial'] . "' ORDER BY Serial DESC";
                $char_result = mssql_query($char_query);

                while ($char = mssql_fetch_array($char_result)) {
                    $out .= '<tr>';
                    $out .= '<td class="alt1" width="5%" style="font-size: 10px;" nowrap>' . $char['Serial'] . '</td>';
                    if ($char['DeleteName'] == "*") {
                        $out .= '<td colspan="11" class="alt1" width="5%" style="font-size: 10px;" nowrap>' . $char['Name'] . '</td>';
                    } else {
                        $out .= '<td colspan="11" class="alt1" width="5%" style="font-size: 10px;" nowrap><i>Deleted</i> ' . $char['DeleteName'] . '</td>';
                    }
                    $out .= '</tr>';
                }
                // Free Result
                @mssql_free_result($char_result);

            }

            $out .= '</table>';

            // Free Result
            @mssql_free_result($result);

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