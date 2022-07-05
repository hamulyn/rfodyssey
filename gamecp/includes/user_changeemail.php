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
    $module[_l('Account')][_l('Change Email')] = $file;
    return;
}

$lefttitle = _l('Change Account EMail');

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
            $current_email = (isset($_POST['current_email'])) ? antiject($_POST['current_email']) : '';
            $email = (isset($_POST['email'])) ? antiject($_POST['email']) : '';
            $confirm_email = (isset($_POST['confirm_email'])) ? antiject($_POST['confirm_email']) : '';

            # Our error checking variables
            $success = true;
            $message = array();

            # Cannot have any fields empty
            if ($current_email == '' || $email == '' || $confirm_email == '') {
                $success = false;
                $message[] = _l("Some fields were left blank. All fields must be filled");
            }
            # Between 4 and 24
            if (strlen($email) < 4 || strlen($email) > 40) {
                $success = false;
                $message[] = _l("email must be between 4 to 40 characters in length.");
            }

            # New email cannot match current email
            if($email == $current_email) {
                $success = false;
                $message[] = _l("Your current email cannot match your current email.");
            }

            # Special: Make sure current email matches
            if ($success) {
                connectuserdb();
                $sql = sprintf("SELECT TOP 1 convert(varchar, email) as email FROM " . TABLE_LUACCOUNT . " WHERE id = convert(binary,'%s')", $userdata['username']);
                if (!($email_check = mssql_query($sql))) {
                    $success = false;
                    echo '<p style="text-align: center; font-weight: bold;">' . _l('Failed look up your current email. Contact an administrator.') . '</p>';
                    if (isset($config['security_enable_debug']) && $config['security_enable_debug'] == 1) {
                        echo '<p style="text-align: center; font-weight: bold;">SQL: ' . mssql_get_last_message() . '</p>';
                    }
                } else {
                    if (mssql_num_rows($email_check) > 0) {
                        $row = mssql_fetch_row($email_check);
                        $check_email = trim($row[0]);

                        if ($current_email != $check_email) {
                            $success = false;
                            $message[] = _l("Sorry, your current email did not match the databases. Check for typos and try again.");
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
                # No errors, let's update the users email
                $sql = sprintf("UPDATE " . TABLE_LUACCOUNT . " SET email = '%s' WHERE ID = CONVERT(binary,'%s')", $email, $userdata['username']);

                if (!($result = mssql_query($sql))) {
                    echo '<p style="text-align: center; font-weight: bold;">' . _l('Failed to update your email in the database. Contact an administrator.') . '</p>';
                    if (isset($config['security_enable_debug']) && $config['security_enable_debug'] == 1) {
                        echo '<p style="text-align: center; font-weight: bold;">SQL: ' . mssql_get_last_message() . '</p>';
                    }
                } else {
                    echo '<p style="text-align: center; font-weight: bold; padding: 10px;">' . _l('You have successfully changed your email. You will now have to re-login to the Game CP.') . '</p>';

                    # Disable the form
                    $show_form = false;

                    # Create log entry
                    gamecp_log(1, $userdata['username'], "GAMECP - CHANGE email", 1);

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
        # Here we display the change email form
        if ($show_form) {
            echo '<form method="post">';
            echo '<table class="table table-bordered">';
            echo '<tr>';
            echo '      <td class="alt1"><strong>' . _l('Current email') . ':</strong><br/><small>' . _l('Enter your current email') . '</small></td>';
            echo '      <td class="alt2"><input type="email" name="current_email" maxlength="32"></td>';
            echo '</tr>';
            echo '<tr>';
            echo '  <td colspan="2" class="alt1"><br/></td>';
            echo '</tr>';
            echo '<tr>';
            echo '      <td class="alt1"><strong>' . _l('New email') . ':</strong><br/><small>' . _l('Enter a new valid email. Must be between %d and %d characters, alphanumeric.', 4, 40) . '</small></td>';
            echo '      <td class="alt2"><input type="email" name="email" maxlength="32"></td>';
            echo '</tr>';
            echo '<tr>';
            echo '      <td class="alt1"><strong>' . _l('Confirm email') . ':</strong><br/><small>' . _l('Please re-type your new email') . '</small></td>';
            echo '      <td class="alt2"><input type="email" name="confirm_email" maxlength="32"></td>';
            echo '</tr>';
            echo '<tr>';
            echo '      <td class="alt1" align="center" colspan="2"><input type="hidden" name="page" value="confirm"><input type="submit"  class="btn btn-default" name="register" value="' . _l('Change email') . '"></td>';
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