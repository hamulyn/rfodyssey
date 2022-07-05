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
    $module[_l('Server Stats')][_l('CW Stats')] = $file;
    return;
}

$lefttitle = _l('Chip War Stats');;
$time = date('F j Y G:i');

if ($this_script = $script_name) {
#if ($is_superadmin == true) {

    connectdatadb();

    $top_limit = 46;
    $max_pages = 10;
    $bcc_wins = 0;
    $ccc_wins = 0;
    $acc_wins = 0;
    $bcc_losses = 0;
    $ccc_losses = 0;
    $acc_losses = 0;
    $param = '';
    $param2 = '';

    $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
    $search_fun = (isset($_POST['search_fun'])) ? $_POST['search_fun'] : "";

    function raceByNumber($num)
    {
        if ($num == 0) {
            return '<span style="color: #CC6699;">BCC</span>';
        } elseif ($num == 1) {
            return '<span style="color: #9933CC;">CCC</span>';
        } elseif ($num == 2) {
            return '<span style="color: grey;">ACC</span>';
        } else {
            return "---";
        }
    }

    $out .= '<table class="table table-bordered">' . "\n";
    $out .= '	<tr>' . "\n";
    $out .= '		<td class="thead" style="padding: 4px;" colspan="3">' . date("l F j, Y g:i A T") . '</td>' . "\n";
    $out .= '	</tr>' . "\n";
    $out .= '	<tr>' . "\n";
    $out .= '		<td class="alt1" style="padding: 4px; text-align: center;"><div id="first_chip_war" style="font-size: 13px; font-weight: bold; font-family: Franklin Gothic Medium;">Count down 1</div></td>' . "\n";
    $out .= '		<td class="alt1" style="padding: 4px; text-align: center;"><div id="second_chip_war" style="font-size: 13px; font-weight: bold; font-family: Franklin Gothic Medium;">Count down 2</div></td>' . "\n";
    $out .= '		<td class="alt1" style="padding: 4px; text-align: center;"><div id="third_chip_war" style="font-size: 13px; font-weight: bold; font-family: Franklin Gothic Medium;">Count down 3</div></td>' . "\n";
    $out .= '	</tr>' . "\n";

    # Okay we need to make another query saddly
    $total_sql = "SELECT winrace, loserace FROM tbl_racebattle_log";
    if (!($total_result = mssql_query($total_sql))) {
        die("Error while trying to get total population");
    }
    while ($row = mssql_fetch_array($total_result)) {
        if ($row['winrace'] == 0) {
            $bcc_wins++;
        } elseif ($row['winrace'] == 1) {
            $ccc_wins++;
        } elseif ($row['winrace'] == 2) {
            $acc_wins++;
        }

        if ($row['loserace'] == 0) {
            $bcc_losses++;
        } elseif ($row['loserace'] == 1) {
            $ccc_losses++;
        } elseif ($row['loserace'] == 2) {
            $acc_losses++;
        }
    }
    mssql_free_result($total_result);

    # Get max/min
    // Wins
    if ($bcc_wins >= $ccc_wins && $bcc_wins >= $acc_wins) {
        $set_max1 = $bcc_wins;
    } elseif ($acc_wins >= $ccc_wins && $acc_wins >= $bcc_wins) {
        $set_max1 = $acc_wins;
    } elseif ($ccc_wins >= $bcc_wins && $ccc_wins >= $acc_wins) {
        $set_max1 = $ccc_wins;
    }

    if ($bcc_wins <= $ccc_wins && $bcc_wins <= $acc_wins) {
        $set_min1 = $bcc_wins;
    } elseif ($acc_wins <= $ccc_wins && $acc_wins <= $bcc_wins) {
        $set_min1 = $acc_wins;
    } elseif ($ccc_wins <= $bcc_wins && $ccc_wins <= $acc_wins) {
        $set_min1 = $ccc_wins;
    }

    // Losses
    if ($bcc_losses >= $ccc_losses && $bcc_losses >= $acc_losses) {
        $set_max2 = $bcc_losses;
    } elseif ($acc_losses >= $ccc_losses && $acc_losses >= $bcc_losses) {
        $set_max2 = $acc_losses;
    } elseif ($ccc_losses >= $bcc_losses && $ccc_losses >= $acc_losses) {
        $set_max2 = $ccc_losses;
    }

    if ($bcc_losses <= $ccc_losses && $bcc_losses <= $acc_losses) {
        $set_min2 = $bcc_losses;
    } elseif ($acc_losses <= $ccc_losses && $acc_losses <= $bcc_losses) {
        $set_min2 = $acc_losses;
    } elseif ($ccc_losses <= $bcc_losses && $ccc_losses <= $acc_losses) {
        $set_min2 = $ccc_losses;
    }

    # Adjust by a difference of min/2 ceil
    #$median = ceil(($set_min1+$set_max1)/2);
    $dif_1 = ceil($set_min1 / 2);
    $dif_2 = ceil($set_min2 / 2);
    $set_min1 -= $dif_1;
    $set_max1 += $dif_1;
    $set_min2 -= $dif_2;
    $set_max2 += $dif_2;

    # Total population
    $total_wars = ($bcc_wins + $ccc_wins + $acc_wins) + ($bcc_losses + $ccc_losses + $acc_losses);

    # Create charts, 2 urls please and thanks
    $win_chart_url = 'http://chart.apis.google.com/chart?chs=330x105';
    $win_chart_url .= '&amp;chtt=Chip War Wins';
    $win_chart_url .= '&amp;cht=bhg';
    $win_chart_url .= '&amp;chxr=0,0,' . $set_max1;
    $win_chart_url .= '&amp;chds=' . $set_min1 . ',' . $set_max1;
    $win_chart_url .= '&amp;chd=t:' . $bcc_wins . ',' . $ccc_wins . ',' . $acc_wins;
    $win_chart_url .= '&amp;chxt=x,y';
    $win_chart_url .= '&amp;chxl=1:|ACC|CCC|BCC|';
    $win_chart_url .= '&amp;chco=CC6699|9933CC|CCCCCC';
    $win_chart_url .= '&amp;chf=bg,s,0000FF00';
    $win_chart_url .= '&amp;chbh=15';
    $win_chart_url .= '&amp;chm=';
    $win_chart_url .= 't++' . $bcc_wins . ',C0C0C0,0,0,11,0';
    $win_chart_url .= '|t++' . $ccc_wins . ',C0C0C0,0,1,11,0';
    $win_chart_url .= '|t++' . $acc_wins . ',C0C0C0,0,2,11,0';

    $lose_chart_url = 'http://chart.apis.google.com/chart?chs=330x105';
    $lose_chart_url .= '&amp;chtt=Chip War Losses';
    $lose_chart_url .= '&amp;cht=bhg';
    $lose_chart_url .= '&amp;chxr=0,0,' . $set_max2;
    $lose_chart_url .= '&amp;chds=' . $set_min2 . ',' . $set_max2;
    $lose_chart_url .= '&amp;chd=t:' . $bcc_losses . ',' . $ccc_losses . ',' . $acc_losses;
    $lose_chart_url .= '&amp;chxt=x,y';
    $lose_chart_url .= '&amp;chxl=1:|ACC|CCC|BCC|';
    $lose_chart_url .= '&amp;chco=CC6699|9933CC|CCCCCC';
    $lose_chart_url .= '&amp;chf=bg,s,0000FF00';
    $lose_chart_url .= '&amp;chbh=15';
    $lose_chart_url .= '&amp;chm=';
    $lose_chart_url .= 't++' . $bcc_losses . ',C0C0C0,0,0,11,0';
    $lose_chart_url .= '|t++' . $ccc_losses . ',C0C0C0,0,1,11,0';
    $lose_chart_url .= '|t++' . $acc_losses . ',C0C0C0,0,2,11,0';

    $out .= '	<tr>' . "\n";
    $out .= '		<td colspan="3" class="alt2" style="text-align: center;"><img src="' . $win_chart_url . '" /><img src="' . $lose_chart_url . '" /></td>' . "\n";
    $out .= '	</tr>' . "\n";
    $out .= '</table>' . "\n";

    $out .= '<table class="table table-bordered">' . "\n";
    $out .= '	<tr>' . "\n";
    $out .= '		<td class="thead" style="padding: 4px; text-align: center;" width="84" nowrap><b>Date</b></td>' . "\n";
    $out .= '		<td class="thead" style="padding: 4px; text-align: center;" width="126" nowrap><b>Start Time</b></td>' . "\n";
    $out .= '		<td class="thead" style="padding: 4px; text-align: center;" width="126" nowrap><b>End Time</b></td>' . "\n";
    $out .= '		<td class="thead" style="padding: 4px; text-align: center;" width="126" nowrap><b>Run Time</b></td>' . "\n";
    $out .= '		<td class="thead" style="padding: 4px; text-align: center;" width="126" nowrap><b>Winners</b></td>' . "\n";
    #$out .= '		<td class="thead" style="padding: 4px; text-align: center;" width="126" nowrap><b>Chip Bearer</b></td>'."\n";
    $out .= '		<td class="thead" style="padding: 4px; text-align: center;" width="126" nowrap><b>Losers</b></td>' . "\n";
    $out .= '	</tr>' . "\n";

    // Pageination
    include('./includes/pagination/ps_pagination.php');
    $query_p1 = 'SELECT szdate, nth, endtime, winrace, loserace, regdate, bossserial0, bossserial1, bossserial2 FROM tbl_racebattle_log';
    $query_p2 = 'WHERE idx NOT IN ( SELECT TOP [OFFSET] idx FROM tbl_racebattle_log ORDER BY idx DESC) ORDER BY idx DESC';

    //Create a PS_Pagination object
    $pager = new PS_Pagination($data_dbconnect, $query_p1, $query_p2, $top_limit, $max_pages, '' . $script_name . '?do=' . $_GET['do']);

    //The paginate() function returns a mysql
    //result set for the current page
    $rs = $pager->paginate();

    while ($row = mssql_fetch_array($rs)) {
        $data[] = $row;
        $stats[$row['szdate']][] = $row;
    }

    $rowspan = 0;
    foreach ($stats as $date => $value) {

        $date = str_split($date);
        $date = $date[6] . $date[7] . '/' . $date[4] . $date[5] . '/' . $date[0] . $date[1] . $date[2] . $date[3];

        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt2" rowspan="' . (($rowspan <= 3) ? 4 : 4) . '">' . $date . '</td>' . "\n";
        $out .= '	</tr>' . "\n";

        $rowspan = 0;
        foreach ($value as $stats) {

            if ($stats['nth'] == 1) {
                $shour = '5';
            } elseif ($stats['nth'] == 2) {
                $shour = '13';
            } else {
                $shour = '21';
            }

            $starttime = str_split($stats['szdate']);
            $syear = $starttime[0] . $starttime[1] . $starttime[2] . $starttime[3];
            $smonth = $starttime[4] . $starttime[5];
            $sday = $starttime[6] . $starttime[7];
            $starttime = mktime($shour, 0, 0, $smonth, $sday, $syear);
            #$endtime = strtotime($stats['regdate']);
            $endtime = $stats['endtime'];
            if ((mb_strlen($endtime)) <= 9) {
                $endtime = '0' . $endtime;
                $prepend_etime = '20';
            } else {
                $prepend_etime = '2';
            }
            $endtime = str_split($endtime, 2);
            $endtime = mktime($endtime[3], $endtime[4], 0, (ltrim($endtime[1], '0')), $endtime[2], $prepend_etime . $endtime[0]);

            $runtime = (($endtime - $starttime) < 0) ? 0 : ($endtime - $starttime);
            $runtime = hrs_mins_secs($runtime);

            /*if($stats['winrace'] != 255) {
                $cb_serial = $stats['bossserial'.$stats['winrace']];
                $user_info = mssql_query("SELECT Name FROM tbl_base WHERE Serial = '".$cb_serial."'",$data_dbconnect);
                $user_info = mssql_fetch_array($user_info);
            } else {
                $user_info['Name'] = '---';
            }*/

            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>' . date("h:i A", $starttime) . '</td>' . "\n";
            $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>' . date("h:i A", $endtime) . '</td>' . "\n";
            $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>' . $runtime['hours'] . ':' . $runtime['minutes'] . '</td>' . "\n";
            $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>' . raceByNumber($stats['winrace']) . '</td>' . "\n";
            #$out .= '		<td class="alt2" nowrap>'.$user_info['Name'].'</td>'."\n";
            $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>' . raceByNumber($stats['loserace']) . '</td>' . "\n";
            $out .= '	</tr>' . "\n";

            $rowspan++;
        }

        if ((3 - $rowspan) > 0) {
            for ($x = 0; $x < (3 - $rowspan); $x++) {
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>-</td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>-</td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>-</td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>-</td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: center;" width="100" nowrap>-</td>' . "\n";
                $out .= '	</tr>' . "\n";
            }
        }

    }

    if (mssql_num_rows($rs) <= 0) {
        $out .= '<tr>' . "\n";
        $out .= '<td colspan="9" style="text-align: center;">No chip wars have been played yet</td>' . "\n";
        $out .= '</tr>' . "\n";
    } else {
        $out .= '<tr>' . "\n";
        $out .= '<td colspan="9" style="text-align: center;">' . $pager->renderFullNav() . '</td>' . "\n";
        $out .= '</tr>' . "\n";
    }
    // Free Result
    @mssql_free_result($rs);

    $out .= "</table>" . "\n";

} else {
    $out .= _l('invalid_page_load');
    #$out .= '<p style="text-align: center;">Sorry, this feature has been taken offline</p>';
}
?>