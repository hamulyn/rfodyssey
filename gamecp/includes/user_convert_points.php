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
    $module[_l('Donations')][_l('Convert GP to Gold Point')] = $file;
    return;
}

$lefttitle = _l('Convert Game Points');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if ($isuser == true) {

        # Setting our main 'globally wanted' variables
        $page = (isset($_POST['page'])) ? $_POST['page'] : "";
        $exit_1 = false;
        $exit_buy = false;
        $currency = '';

        if (!isset($config['gamecp_money_conversion_rate']) && !isset($config['gamecp_gold_conversion_rate'])) {
            $out .= 'Configuration values cannot be found, unable to enable this script.';
            return 0;
        } else {
            $money_exchange_rate = ceil($config['gamecp_money_conversion_rate']);
            $gold_exchange_rate = ceil($config['gamecp_gold_conversion_rate']);
        }

        if ($money_exchange_rate == 0 && $gold_exchange_rate == 0) {
            $out .= '<p style="text-align: center; font-weight: bold;">This feature has been disabled by the admins</p>';
            return 0;
        }

        if (empty($page)) {

            # Lets just make this global, meh - almost g
            $out .= '<p style="font-weight: bold; font-size: 15px; text-align: center;">Your account currently has <span style="color: #8F92E8;">' . number_format($userdata['points'], 2) . '</span> Game Points <span style="color: #8F92E8;">' . number_format($userdata['vote_points'], 2) . '</span> Vote Points</p>' . "\n";

            # We first need all the characterss
            connectdatadb();
            $user_sql = "SELECT B.Name, B.Serial, B.Race, B.Dalant, B.Gold, G.ActionPoint_2 FROM tbl_base AS B LEFT JOIN tbl_supplement AS G ON B.Serial = G.Serial WHERE B.DCK = 0 AND B.AccountSerial = '" . $userdata['serial'] . "'";
            if (!($user_result = @mssql_query($user_sql))) {
                $exit_1 = true;
                $out .= '<p style="text-align: center; font-weight: bold;">SQL Error while trying to get your characters data</p>' . "\n";
                if ($is_superadmin == true) {
                    $out .= '<p>' . "\n";
                    $out .= 'SQL: ' . $user_sql . '<br/>' . "\n";
                    $out .= 'MSSQL ERROR: ' . mssql_get_last_message() . "\n";
                    $out .= '' . "\n";
                    $out .= '</p>' . "\n";
                }
            }

            $out .= '<div class="panel">' . "\n";
            $out .= '<p><b><font color=red size=4>Money exchange rate:</b></font>  1 Game Point for ' . number_format($money_exchange_rate) . ' Gold Point<br/></p>' . "\n";

            $i = 1;
            while ($user = @mssql_fetch_array($user_result)) {

                # Get max possible values
                $max_money = floor(999999 - $user['ActionPoint_2']);
                $max_gold = floor(1000000 - $user['Gold']);

                # Get race currency
                if ($user['Race'] == 0 || $user['Race'] == 1) {
                    $currency = 'Gold Point';
                } elseif ($user['Race'] == 2 || $user['Race'] == 3) {
                    $currency = 'Gold Point';
                } elseif ($user['Race'] == 4) {
                    $currency = 'Gold Point';
                } else {
                    $currency = 'Unknown!';
                }

                # Some spacing plox
                if ($i != 1) {
                    $out .= '<br/>' . "\n";
                }

                $keyup_money = "convert('$i',$money_exchange_rate,$max_money," . $user['ActionPoint_2'] . "," . $userdata['points'] . ",'$currency');";
                $keyup_gold = "convert('1$i',$gold_exchange_rate,$max_gold," . $user['Gold'] . "," . $userdata['points'] . ",'Gold');";

                # Draw me now!
                $out .= '<form method="post">' . "\n";
                $out .= '<table class="table table-bordered">' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" valign="top">Character Name:</td>' . "\n";
                $out .= '		<td class="alt1" valign="top" colspan="2">' . antiject($user['Name']) . '</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" valign="top">Exchange Game Points for ' . $currency . ':</td>' . "\n";
                $out .= '		<td class="alt1" valign="top"><input id="exchange_' . $i . '" type="text" name="exchange_money" value="" onKeyUp="' . $keyup_money . '" size="4"></td>' . "\n";
                $out .= '		<td class="alt1" valign="top" id="result_' . $i . '">Current: ' . number_format($user['ActionPoint_2']) . ' ' . $currency . '</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" valign="top" colspan="3"><input type="hidden" name="page" value="buy"/><input type="hidden" name="char_serial" value="' . $user['Serial'] . '"/><input type="submit"  class="btn btn-primary" name="submit" value="Buy Now!"/></td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '</table>' . "\n";
                $out .= '</form>' . "\n";

                $i++;
            }
            $out .= '</div>' . "\n";

            if (mssql_num_rows($user_result) <= 0) {
                $out .= '<p style="text-align: center; font-weight: bold;">You do not have any characters</p>' . "\n";
            }

            // Free Results
            mssql_free_result($user_result);
        } elseif ($page == 'buy') {

            # Lets just make this global, meh - almost really global
            $out .= '<p style="font-weight: bold; font-size: 15px; text-align: center;">Your account currently has <span style="color: #8F92E8;">' . number_format($userdata['points'], 2) . '</span> Game Points</p>' . "\n";

            # Get variables
            $char_serial = (isset($_POST['char_serial']) && is_int((int)$_POST['char_serial'])) ? antiject((int)$_POST['char_serial']) : 0;
            $exchange_money = (isset($_POST['exchange_money']) && is_int((int)$_POST['exchange_money'])) ? antiject((int)$_POST['exchange_money']) : 0;
            $exchange_gold = (isset($_POST['exchange_gold']) && is_int((int)$_POST['exchange_gold'])) ? antiject((int)$_POST['exchange_gold']) : 0;

            # We cannot allow them to buy items if they are logged in man!
            $t_login = strtotime($userdata['lastlogintime']);
            $t_logout = strtotime($userdata['lastlogofftime']);
            $t_cur = time();
            $t_maxlogin = $t_login + 3600;

            #if($t_maxlogin < $t_cur) {
            #	$status = "offline";
            #} else
            if (($t_login <= $t_logout)) {
                $status = "offline";
            } else {
                $status = "online";
            }

            # Error Checking: Make sure the user is not logged in
            if ($status == 'online') {
                $exit_buy = true;
                $out .= '<p style="text-align: center; font-weight: bold;">You cannot buy items when logged into the game!<br/>If you have logged out and yet see this message, log back in and properly log out again (click the log out button!).</p>' . "\n";
            }

            # Error Checking: Char serial provided?
            if ($char_serial == 0) {
                $exit_buy = true;
                $out .= '<p style="text-align: center; font-weight: bold;">This should not happen, no character serial was provided</p>' . "\n";
            }

            # Error Checking: Need an amount to exchange!
            if ($exchange_money == 0 && $exchange_gold == 0) {
                $exit_buy = true;
                $out .= '<p style="text-align: center; font-weight: bold;">You must fill in an amount for the exchange.</p>' . "\n";
            }

            # Error Checking: Cannot have negative values
            if ($exchange_money < 0 or $exchange_gold < 0) {
                $exit_buy = true;
                $out .= '<p style="text-align: center; font-weight: bold;">You must exchange points greather than 1</p>';
            }

            # Error Checking: Cannot go over our max
            if ((floor($exchange_money * $money_exchange_rate)) > 999999) {
                $exit_buy = true;
                $out .= '<p style="text-align: center; font-weight: bold;">You cannot buy money greater than 999,999</p>';
            }

            # Error Checking: Cannot go over our max
            if ((floor($exchange_gold * $gold_exchange_rate)) > 1000000) {
                $exit_buy = true;
                $out .= '<p style="text-align: center; font-weight: bold;">You cannot buy gold greater than 500,000</p>';
            }

            # Error Checking: Cannot buy more than you can afford!
            if ($exchange_money > $userdata['points'] or $exchange_gold > $userdata['points'] or ($exchange_money + $exchange_gold) > $userdata['points']) {
                $exit_buy = true;
                $out .= '<p style="text-align: center; font-weight: bold;">You do not have enough game points to make this exchange (' . number_format($exchange_money + $exchange_gold) . ')</p>';
            }

            # - PAUSE - Do some calculations
            $money = floor($exchange_money * $money_exchange_rate);
            $gold = floor($exchange_gold * $gold_exchange_rate);

            # Error Checking: Special checking & get character data
            if ($exit_buy == false) {
                # We first need all the characterss
                connectdatadb();

                $char_sql = "SELECT B.Name, B.Serial, B.Race, B.Dalant, G.ActionPoint_2 FROM tbl_base AS B RIGHT JOIN tbl_supplement AS G ON B.Serial = G.Serial WHERE B.Serial = '" . $char_serial . "'";
                if (!($char_result = @mssql_query($char_sql))) {
                    $exit_buy = true;
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL Error while trying to get character data</p>';
                    $out .= '<p><b>Debug:</b><br/><i>SQL: </i>' . $char_sql . '<br/><i>SQL Return:</i> ' . mssql_get_last_message() . '</p>';
                }

                $char = @mssql_fetch_array($char_result);

                # More Error checking
                if (($char['ActionPoint_2'] + $money) > 999999) {
                    $exit_buy = true;
                    $out .= '<p style="text-align: center; font-weight: bold;">You cannot exchange for gold point over 999,999</p>';
                }

                if (($char['Gold'] + $gold) > 1000000) {
                    $exit_buy = true;
                    $out .= '<p style="text-align: center; font-weight: bold;">You cannot exchange for gold over 500,000 ' . ($gold) . '</p>';
                }
            }

            // Free Results
            mssql_free_result($char_result);

            # NOWWWW that we are error free!
            if ($exit_buy == false) {
                $update_char = "UPDATE tbl_supplement SET ActionPoint_2 = ActionPoint_2+$money WHERE Serial = '$char_serial'";
                if (!($update_result = @mssql_query($update_char))) {
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL Error while trying to update your character</p>';
                    $out .= '<p><b>Debug:</b><br/><i>SQL: </i>' . $update_char . '<br/><i>SQL Return:</i> ' . mssql_get_last_message() . '</p>';
                } else {
                    $delete_npc = "DELETE FROM tbl_NpcData WHERE Serial = '$char_serial'";
                    $result2 = mssql_query($delete_npc) or $stage2_exit = true;

                    # Connect to the user database
                    connectgamecpdb();

                    $subtract = $exchange_money + $exchange_gold;
                    $update_points = "UPDATE gamecp_gamepoints SET user_points = user_points-$subtract WHERE user_account_id = '" . $userdata['serial'] . "'";
                    if (!($update_p_result = @mssql_query($update_points))) {
                        #$out .= '<p><b>Debug:</b><br/><i>SQL: </i>'.$update_points.'<br/><i>SQL Return:</i> '.mssql_get_last_message().'</p>';
                        gamecp_log(1, $userdata['username'], "GAMECP - CONVERT POINTS - Failed to update Game Points: -$subtract", 1);
                    }

                    $out .= '<p style="text-align: center; font-weight: bold;">Successfully exchanged: <u>' . number_format($exchange_money, 2, '.', '') . '</u> points into <u>' . number_format($money) . '</u> Gold Point</p>';

                    gamecp_log(1, $userdata['username'], "GAMECP - CONVERT POINTS - Char Serial: $char_serial | Exchanged: " . number_format($exchange_money, 2, '.', '') . " (GP) -> +" . number_format($money) . " (M) & " . number_format($exchange_gold, 2, '.', '') . " (GP) -> +" . number_format($gold) . " (G)", 1);
                }
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