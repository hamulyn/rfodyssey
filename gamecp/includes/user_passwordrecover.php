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
    return;
}


$lefttitle = _l('Password Recovery');;
if ($this_script == $script_name) {
    if (!$isuser) {

        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        if ($page == "recover") {
            $iemail = antiject($_REQUEST['iemail']);
            if (!isEmail($iemail)) {
                $out .= '<center>Invalid e-mail address<br><a href="' . $script_name . '?do=' . $_GET['do'] . '">Return</a></center>';
            } else {
                $iemail = antiject($iemail);
                connectuserdb();
                $query = mssql_query('SELECT CONVERT(varchar,ID) AS account, Email, CONVERT(varchar,Password) AS passwd FROM ' . TABLE_LUACCOUNT . ' WHERE Email="' . $iemail . '"');

                if ($iemail == '') {
                    $query = '';
                }

                if (@mssql_num_rows($query) <= 0) {
                    $out .= '<center>This email does not exist in our database <br><a href="' . $script_name . '?do=' . $_GET['do'] . '">Return</a></center>';
                } else {

                    $to = $iemail;
                    $subject = $config['lostpass_subject'];

                    $message = str_replace('[servername]', $config['server_name'], $config['lostpass_message']) . "\n\n";
                    while ($query2 = mssql_fetch_array($query)) {
                        $username = ereg_replace(";$", "", $query2['account']);
                        $username = ereg_replace("\\\\", "", $username);
                        $password = ereg_replace(";$", "", $query2['passwd']);
                        $password = ereg_replace("\\\\", "", $password);

                        $message .= "Account: " . $username . "\n";
                        $message .= "Password: " . $password . "\n";
                        $message .= "\n";
                    }
                    // Free Result
                    @mssql_free_result($query);
                    // Okay, new implimentation here
                    // We are going to be using PHP Mailer to do this
                    // We need to add support for SMTP servers (external) since mail() doesn't always work :(
                    if (isset($config['gamecp_smtp_enable']) && $config['gamecp_smtp_enable'] == 1) {

                        // So it begins
                        include "./includes/main/class.phpmailer.php";

                        $mail = new PHPMailer();
                        $body = eregi_replace("[\]", '', $message);

                        $mail->SetLanguage('en', './includes/main/');
                        $mail->SMTPAuth = true; // enable SMTP authentication
                        if ($config['gamecp_smtp_enable_ssl'] == 1) {
                            $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
                        }
                        $mail->Host = $config['gamecp_smtp_server']; // sets GMAIL as the SMTP server
                        $mail->Port = $config['gamecp_smtp_port']; // set the SMTP port for the GMAIL server

                        $mail->Username = $config['gamecp_smtp_username']; // GMAIL username
                        $mail->Password = $config['gamecp_smtp_password']; // GMAIL password

                        $mail->From = $config['lostpass_email'];
                        $mail->FromName = $config['server_name'];

                        $mail->Subject = $subject;

                        $mail->Body = $body;

                        $mail->AddAddress($iemail, "Lost Password Recovery");

                        #$mail->IsHTML(true); // send as HTML

                        if (!$mail->Send()) {
                            $out .= '<center>Could not send mail. Please contact a administrator.<br>' . $mail->ErrorInfo . '<br/><a href="' . $script_name . '">Return</a></center>';
                        } else {
                            $out .= '<center>Your password has been sent to: <b>' . $iemail . '</b>.<br><a href="' . $script_name . '">Return</a></center>';
                        }

                    } else {

                        $mime_boundary = "----RFGameCP----" . md5(time());

                        $headers = "From: " . $config['lostpass_email'] . "\n";
                        $headers .= "MIME-Version: 1.0\n";
                        $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";

                        # -=-=-=- TEXT EMAIL PART

                        $mess = "--$mime_boundary\n";
                        $mess .= "Content-Type: text/plain; charset=UTF-8\n";
                        $mess .= "Content-Transfer-Encoding: 8bit\n\n";

                        $mess .= $message;

                        # -=-=-=- FINAL BOUNDARY

                        $mess .= "--$mime_boundary--\n\n";

                        if (mail($iemail, $config['lostpass_subject'], $mess, $headers)) {
                            $out .= '<center>Your password has been sent to: <b>' . $iemail . '</b>.<br><a href="' . $script_name . '">Return</a></center>';
                        } else {
                            $out .= '<center>Could not send mail. Please contact a administrator.<br><a href="' . $script_name . '">Return</a></center>';
                        }
                    }

                    if (!empty($forum_username)) {
                        $username = 'Forum: ' . $forum_username;
                    } else {
                        $username = '*';
                    }

                    gamecp_log(1, $username, "GAMECP - PASSWORD RECOVERY - EMAIL: " . $iemail, 1);

                }

            }
        } else {
            $navbits = array('' . $script_name . '' => 'Game CP', '' => 'Password Recovery');
            $out .= '<center>Enter your email address below and we will send you an email address with your current password.<br /> If you have not updated your email address previously, you will not be able to use this form.<br>'
                . '<form method="POST" action="' . $script_name . '?do=' . $_GET['do'] . '">'
                . '<table border="0">'
                . '<tr><td align="left"><strong>E-mail: </td><td align="left"> <input type="text" class="form-control" name="iemail"></td></tr>'
                . '<tr><td colspan="2" align="center"><input type="hidden" name="page" value="recover"><input type="submit"  class="btn btn-primary" value="Send Password"></td></tr>'
                . '</table></form></center>';
        }

    } else {
        $out .= _l('no_permission');
    }

} else {
    $out .= _l('invalid_page_load');
}

?>