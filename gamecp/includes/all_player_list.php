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
    $module[_l('Server Stats')][_l('Player List')] = $file;
    return;
}

$lefttitle = _l('Top 20 Players');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    $gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';
    $top_limit = 20;
    $max_pages = 10;

    $out .= '<table class="table table-bordered table-hover table-condensed">' . "\n";
    $out .= '<tr>';
    $out .= '<th>Rank</th>';
    $out .= '<th>Status</th>';
    $out .= '<th>Race</th>';
    $out .= '<th>Level</th>';
    $out .= '<th>Player Name</th>';
    $out .= '<th>Class</th>';
    $out .= '<th>Total Time</th>';
    $out .= '<th>PVP Points</th>';
    $out .= '<th>Guild</th>';
    $out .= '</tr>';

    connectdatadb();

    $query_p1 = "SELECT TOP 50 B.AccountSerial, B.Account, B.Serial, B.Class, B.LastConnTime, G.TotalPlayMin, B.Name, B.lv, B.race, P.PvpPoint, P.GuildName
	FROM
	tbl_base AS B
	INNER JOIN
	tbl_general AS G
	ON B.Serial = G.Serial
	INNER JOIN
	tbl_PvpRankToday AS P
	ON B.Serial = P.Serial	
	WHERE B.DCK = '0'
	ORDER BY P.PvpPoint DESC";

    $query = mssql_query($query_p1, $data_dbconnect);

    # Players
    $players = array();
    while ($row = mssql_fetch_array($query)) {
        $players[] = $row;
    }

    # Get classes...
    connectitemsdb();
    $classes = array();
    $class_result = mssql_query("SELECT class_name, class_code FROM tbl_Classes");
    while ($row = mssql_fetch_array($class_result)) {
        $classes[$row['class_code']] = $row['class_name'];
    }

    $i = 1;

    foreach ($players as $row) {
        $t_login = $row['LastConnTime'];
        #$t_logout = strtotime($time_info['lastlogintime']);
        $t_cur = time();
        $t_maxlogin = $t_login + 2592000;

        # Beta: Get user online/offline status
        connectuserdb();
        $user_select = "SELECT TOP 1 LastLogOffTime, LastLoginTime FROM tbl_UserAccount WHERE Serial = '" . $row['AccountSerial'] . "'";
        if (!($user_result = mssql_query($user_select, $user_dbconnect))) {
            $status = "offline";
        } else {
            if (!($user_fetch = mssql_fetch_array($user_result))) {
                $status = "offline";
            } else {
                $status = "online";
            }
        }

        connectdatadb();
        $date = date("Ym");
        $select_log = "SELECT TOP 1 CharacSerial FROM tbl_characterselect_log_$date WHERE AccountSerial = '" . $row['AccountSerial'] . "' ORDER BY ID DESC";
        if (!($select_result = @mssql_query($select_log, $data_dbconnect))) {
            $status = "offline";
        } else {
            if (!($log_fetch = mssql_fetch_array($select_result))) {
                $status = "offline";
            } else {
                $status = "online";
            }
        }

        // Do it do it doooo itttt!
        $t_login = strtotime($user_fetch['LastLoginTime']);
        $t_logout = strtotime($user_fetch['LastLogOffTime']);
        $t_cur = time();
        $t_maxlogin = $t_login + 2592000;

        if (($t_login <= $t_logout)) {
            $status = "offline";
        } elseif ($t_maxlogin < $t_cur) {
            $status = "offline";
        } else {
            if ($log_fetch['CharacSerial'] == $row['Serial']) {
                $status = "online";
            } else {
                $status = "offline";
            }
        }

        if ($row['race'] == 0 OR $row['race'] == 1) {
            $race = '<span style="color: #CC6699;">Bell</span>';
        } elseif ($row['race'] == 2 OR $row['race'] == 3) {
            $race = '<span style="color: #9933CC;">Cora</span>';
        } elseif ($row['race'] == 4) {
            $race = '<span style="color: grey;">Acc</span>';
        }

        if ($row['GuildName'] == '') {
            $row['GuildName'] = "*";
        }

        $out .= '<tr>';
        $out .= '<td class="alt2" style="padding: 4px; text-align: center;" width="5%">' . $i . '</td>';
        $out .= '<td class="alt2" style="padding: 4px; text-align: center;" width="5%"><img src="./includes/images/' . $status . '.gif" /></td>';
        $out .= '<td class="alt1" style="padding: 4px; text-align: center;" width="5%">' . $race . '</td>';
        $out .= '<td class="alt1" style="padding: 4px; text-align: center;" width="5%">' . $row['lv'] . '</td>';
        $out .= '<td class="alt1" style="padding: 4px;" width="40%">' . utf8_encode($row['Name']) . '</td>';
        $out .= '<td class="alt1" style="padding: 4px; text-align: center;" width="5%">' . $classes[$row['Class']] . '</td>';
        $out .= '<td class="alt1" style="padding: 4px; text-align: center;" width="10%">' . number_format(round($row['TotalPlayMin'] / 60)) . ' h</td>';
        $out .= '<td class="alt1" style="padding: 4px; text-align: center;" width="10%">' . number_format(round($row['PvpPoint'])) . '</td>';
        $out .= '<td class="alt1" style="padding: 4px;" width="20%">' . $row['GuildName'] . '</td>';
        $out .= '</tr>';

        $i++;
    }

    $out .= "</table>";

    // Free Result
    @mssql_free_result($rs);

} else {

    $out .= _l('invalid_page_load');

}

?>