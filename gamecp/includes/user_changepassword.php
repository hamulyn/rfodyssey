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
    $module[_l('Account')][_l('Change Password')] = $file;
    return;
}

$lefttitle = _l('Change Account Password');

# To make life simpler, we'll capture all the
# output (print/echo) from the buffer
# and append it to the $out variable
ob_start();

if ($this_script == $script_name) {

    if ($isuser) {

        # Form toggle
        $show_form = true;

        # Get our page
        $page = (isset($_GET['page']) || isset($_POST['page'])) ? (isset($_GET['page'])) ? $_GET['page'] : $_POST['page'] : "";

        # Switch our page
        if ($page == "confirm") {
            # Get user data
            $current_password = (isset($_POST['current_password'])) ? antiject($_POST['current_password']) : '';
            $password = (isset($_POST['password'])) ? antiject($_POST['password']) : '';
            $confirm_password = (isset($_POST['confirm_password'])) ? antiject($_POST['confirm_password']) : '';

            # Our error checking variables
            $success = true;
            $message = array();

            # Cannot have any fields empty
            if ($current_password == '' || $password == '' || $confirm_password == '') {
                $success = false;
                $message[] = _l("Some fields were left blank. All fields must be filled");
            }

            # Check current password
            if (!preg_match(REGEX_PASSWORD, $password)) {
                $success = false;
                $message[] = _l("Invalid current password provided. Password can only contain letters and numbers.");
            }

            # Check new password
            if ($password != $confirm_password) {
                $success = false;
                $message[] = _l("Confirmation password does not match. Please check for typos.");
            } elseif (!preg_match(REGEX_PASSWORD, $password)) {
                $success = false;
                $message[] = _l("Invalid password provided. Password can only contain letters and numbers.");
            }

            # Between 4 and 24
            if (strlen($password) < 4 || strlen($password) > 24) {
                $success = false;
                $message[] = _l("Password must be between 4 to 24 characters in length.");
            }

            # New password cannot match current password
            if($password == $current_password) {
                $success = false;
                $message[] = _l("Your current password cannot match your current password.");
            }

            # Special: Make sure current password matches
            if ($success) {
                connectuserdb();
                $sql = sprintf("SELECT TOP 1 convert(varchar, password) as password FROM " . TABLE_LUACCOUNT . " WHERE id = convert(binary,'%s')", $userdata['username']);
                if (!($password_check = mssql_query($sql))) {
                    $success = false;
                    echo '<p style="text-align: center; font-weight: bold;">' . _l('Failed look up your current password. Contact an administrator.') . '</p>';
                    if (isset($config['security_enable_debug']) && $config['security_enable_debug'] == 1) {
                        echo '<p style="text-align: center; font-weight: bold;">SQL: ' . mssql_get_last_message() . '</p>';
                    }
                } else {
                    if (mssql_num_rows($password_check) > 0) {
                        $row = mssql_fetch_row($password_check);
                        $check_password = trim($row[0]);

                        if ($current_password != $check_password) {
                            $success = false;
                            $message[] = _l("Sorry, your current password did not match the databases. Check for typos and try again.");
                        }
                    } else {
                        $success = false;
                        $message[] = _l("Odd, we could not find your username in the database...");
                    }
                }
            }

            # Errors?
            if (!$success) {
                # Display errors
                echo '<div style="color: red; font-weight: bold; padding: 10px; border: 1px solid #C0C0C0; margin-bottom: 5px;">';
                echo _l('Whoops! Looks like we have some errors:');
                echo '  <ul>';
                foreach ($message as $text) {
                    echo '      <li>' . $text . '</li>';
                }
                echo '  </ul>';
                echo '</div>';
            } else {
                connectuserdb();
                # No errors, let's update the users password
                $sql = sprintf("UPDATE " . TABLE_LUACCOUNT . " SET Password = CONVERT(binary,'%s') WHERE ID = CONVERT(binary,'%s')", $password, $userdata['username']);

                if (!($result = mssql_query($sql))) {
                    echo '<p style="text-align: center; font-weight: bold;">' . _l('Failed to update your password in the database. Contact an administrator.') . '</p>';
                    if (isset($config['security_enable_debug']) && $config['security_enable_debug'] == 1) {
                        echo '<p style="text-align: center; font-weight: bold;">SQL: ' . mssql_get_last_message() . '</p>';
                    }
                } else {
                    echo '<p style="text-align: center; font-weight: bold; padding: 10px;">' . _l('You have successfully changed your password. You will now have to re-login to the Game CP.') . '</p>';

                    # Disable the form
                    $show_form = false;

                    # Create log entry
                    gamecp_log(1, $userdata['username'], "GAMECP - CHANGE PASSWORD", 1);

                    # Log the user out
                    $_SESSION = array(); // destroy all $_SESSION data
                    setcookie("gamecp_userdata", "", time() - 3600);
                    if (isset($_COOKIE["gamecp_userdata"])) {
                        unset($_COOKIE["gamecp_userdata"]);
                    }
                    @session_destroy();

                }
            }

        }
        # Here we display the change password form
        if ($show_form) {
            echo '<form method="post">';
            echo '<table class="table table-bordered">';
            echo '<tr>';
            echo '      <td class="alt1"><strong>' . _l('Current password') . ':</strong><br/><small>' . _l('Enter your current password') . '</small></td>';
            echo '      <td class="alt2"><input type="password" name="current_password" maxlength="24"></td>';
            echo '</tr>';
            echo '<tr>';
            echo '  <td colspan="2" class="alt1"><br/></td>';
            echo '</tr>';
            echo '<tr>';
            echo '      <td class="alt1"><strong>' . _l('New password') . ':</strong><br/><small>' . _l('Enter a new valid password. Must be between %d and %d characters, alphanumeric.', 4, 24) . '</small></td>';
            echo '      <td class="alt2"><input type="password" name="password" maxlength="24"></td>';
            echo '</tr>';
            echo '<tr>';
            echo '      <td class="alt1"><strong>' . _l('Confirm password') . ':</strong><br/><small>' . _l('Please re-type your new password') . '</small></td>';
            echo '      <td class="alt2"><input type="password" name="confirm_password" maxlength="24"></td>';
            echo '</tr>';
            echo '<tr>';
            echo '      <td class="alt1" align="center" colspan="2"><input type="hidden" name="page" value="confirm"><input type="submit"  class="btn btn-default" name="register" value="' . _l('Change Password') . '"></td>';
            echo '</tr>';
            echo '</table>';
        }

    } else {

        echo _l('no_permission');

    }

} else {

    echo _l('invalid_page_load');

}

# Append data to the $out variable
$out .= ob_get_contents();
ob_end_clean();