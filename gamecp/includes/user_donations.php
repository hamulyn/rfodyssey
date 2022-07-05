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
    $module[_l('Donations')][_l('Donate for Game Point Credit Card (USD)')] = $file;
    return;
}

# HOOK: Beginning
@include "./includes/hook/user_donations-start-of-file.php";
# HOOK;

# Set the page title
$lefttitle = "Donate for Game Point Credit Card (USD)";

if ($this_script == $script_name) {

    $exit_stage = 0;

    if (isset($isuser) && $isuser == true) {

        # HOOK: End of file
        @include "./includes/hook/user_donations-start-is-user.php";
        # HOOK;

        # IPN URL/
        $ipn_url = (isset($config['paypal_ipn_url'])) ? $config['paypal_ipn_url'] : '';

        # Ensure that we have a valid PayPal IPN URL
        if (filter_var($ipn_url, FILTER_VALIDATE_URL) === FALSE) {
            $url = get_url() . 'paypal_ipn.php';
            $out .= '<p style="text-align: center; font-weight: bold;">' . _l('You have entered an invalid PayPal IPN URL. Please set it to %s', $url) . '</p>';
            return 0;
        }

        $tottime = time() - $userdata['createtime'];
        if ($tottime <= 604800 or $userdata['createtime'] == '') {
            #gamecp_log(1,$userdata['username'], "GAMECP - DONATE - Suspicious user: Account less than a week old", 1);
        }

        $username = (isset($_POST['username'])) ? $_POST['username'] : '';

        # HOOK: Beginning
        @include "./includes/hook/user_donations-start-friend-search.php";
        # HOOK;

        connectuserdb();
        if ($username == '') {
            $custom = $userdata['serial'];
        } elseif (eregi("[^a-zA-Z0-9_-]", $username)) {
            $custom = $userdata['serial'];
        } else {
            $username = ereg_replace(";$", "", $username);
            $username = ereg_replace("\\\\", "", $username);
            $username = antiject($username);
            $select_query = "SELECT Serial, convert(varchar,id) AS Name FROM tbl_UserAccount WHERE id = convert(binary,'$username')";
            if (!($result = mssql_query($select_query))) {
                $exit_stage = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">SQL Error while trying to query the database</p>';
            }

            if ($exit_stage == 0) {
                $data = mssql_fetch_array($result);

                $username = antiject($data['Name']);
                $custom = $data['Serial'];

                if ($custom == '') {
                    $username = '[USER NOT FOUND]';
                    $custom = $userdata['serial'];
                }

            }
            // Free Result
            @mssql_free_result($result);

        }
        $custom = antiject($custom);

        # HOOK: Beginning
        @include "./includes/hook/user_donations-end-friend-search.php";
        # HOOK;

        if ($exit_stage == 0) {

            # HOOK: Beginning
            @include "./includes/hook/user_donations-start-display-content.php";
            # HOOK;

            $out .= '<center>' . "\n";
            $out .= '<h2>Your account currently has <span style="color: #8F92E8;">'.number_format($userdata['points']).'</span> Game Points <span style="color: #8F92E8;"> '.number_format($userdata['vote_points']).'</span> Vote Point </h2>'."\n";
            $out .= '<form method="post" action="' . $script_name . '?do=' . $_GET['do'] . '">' . "\n";
            $out .= '	If your purchasing Item Mall Credits for a friend, enter their <b><u>username</u></b> below.<br /><br />' . "\n";
            $out .= '	<input type="text" name="username"> <input type="submit" class="submit" value="Change">' . "\n";
            $out .= '</form>' . "\n";
            $out .= '</center>' . "\n";
            $out .= '<br>' . "\n";

            calculate_credits($config['donations_credit_muntiplier'], $config['donations_number_of_pay_options'], $config['donations_start_price'], $config['donations_start_credits'], false);

            $out .= '<table class="tborder" cellpadding="3" cellspacing="1" border="0" width="60%" align="center">' . "\n";
            if ($username != '') {
                $out .= '		<tr>' . "\n";
                $out .= '			<td colspan="5" style="text-align: center; font-size: 15px;">You are purchasing these credits for <b>' . $username . '</b></td>' . "\n";
                $out .= '		</tr>' . "\n";
            }
            $out .= '		<tr>' . "\n";
            $out .= '			<td class="thead">Price</td>' . "\n";
            $out .= '			<td class="thead">Credits</td>' . "\n";
            $out .= '			<td class="thead">Bonus</td>' . "\n";
            $out .= '			<td class="thead" style="text-align: center;">Total</td>' . "\n";
            $out .= '			<td class="thead" style="text-align: center;">Donate Now!</td>' . "\n";
            $out .= '		</tr>' . "\n";
            for ($i = 1; $i < count($c_price); $i++) {
                $bgcolor = ($i % 2) ? 'alt1' : 'alt2';

                $out .= '	<tr>' . "\n";
                $out .= '		<td class="' . $bgcolor . '">$' . number_format($c_price[$i], 2, '.', '') . '</td>' . "\n";
                $out .= '		<td class="' . $bgcolor . '">' . number_format($c_credits[$i]) . '</td>' . "\n";
                $out .= '		<td class="' . $bgcolor . '">' . number_format($c_bonus[$i]) . '</td>' . "\n";
                $out .= '		<td class="' . $bgcolor . '" align="center"><b>' . number_format($c_total[$i]) . '</b></td>' . "\n";
                $out .= '		<td class="' . $bgcolor . '" align="center"><form action="https://www.paypal.com/cgi-bin/webscr" method="post">' . paypal_buttons(number_format($c_price[$i], 2, '.', ''), $c_total[$i], $custom) . '</form></td>' . "\n";
                $out .= '	</tr>' . "\n";
            }
            $out .= '	</table>';


            # HOOK: Beginning
            @include "./includes/hook/user_donations-end-display-content.php";
            # HOOK;

        }

        # HOOK: End of file
        @include "./includes/hook/user_donations-end-is-user.php";
        # HOOK;

    } else {

        $out .= _l('no_permission');

    }

} else {

    $out .= _l('invalid_page_load');

}

# HOOK: End of file
@include "./includes/hook/user_donations-end-of-file.php";
# HOOK;
?>