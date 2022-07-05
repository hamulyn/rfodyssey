<?php
/**
 * Game Control Panel v2
 * Copyright (c) www.intrepid-web.net
 *
 * The use of this product is subject to a license agreement
 * which can be found at http://www.intrepid-web.net/rf-game-cp-v2/license-agreement/
 */

# Set definations
define('THIS_SCRIPT', 'gamecp');
define('IN_GAMECP_SALT58585', true);

# Include Main files
include('./gamecp_common.php');

# Draw the navigation bits
$navbits = array($config['gamecp_filename'] => $config['gamecp_programname']);

# Out...Obtain these variables...variables...
$do = (isset($_REQUEST['do'])) ? antiject($_REQUEST['do']) : '';
$forum_username = 'N/A';

# Set a default page (not logged in)
if (($do == "") && ($notuser)) {
    $lefttitle = $program_name;
    $title = $program_name;
    $navbits = array($script_name => $program_name, '' => $lefttitle);

    # HOOK: User not found
    @include('./includes/hook/index-start-user_logged_out.php');
    # HOOK;

    $out .= '<form method="post" action="gamecp_login.php">' . "\n";
    $out .= '<fieldset>';
    $out .= '<h3>' . _l('Welcome to the Game CP please login with your game info.') . '</h3>' . "\n";
    $out .= '<div class="form-group">';
    $out .= '  <label for="username">Username</label>';
    $out .= '  <input name="username" type="text" class="form-control" id="username" placeholder="Enter your username">';
    $out .= '</div>';
    $out .= '<div class="form-group">';
    $out .= '  <label for="password">Password</label>';
    $out .= '  <input name="password" type="password" class="form-control" id="password" placeholder="Password">';
    $out .= '</div>';
    if (isset($config['security_recaptcha_enable']) && $config['security_recaptcha_enable'] == 1) {
        $out .= recaptcha_get_html($publickey, $error);
    }
    $out .= '<button type="submit" class="btn btn-primary">Login</button>';
    $out .= '</fieldset>';
    $out .= '</form>' . "\n";
    $out .= '<br>' . "\n";
    $out .= _l('Don\'t have a game account? Get one <a href="%s">Here</a>', 'gamecp_register.php') . '<br/>' . "\n";
    $out .= _l('Lost or forgot your password? Recover it <a href="%s">Here</a>', $script_name . '?do=user_passwordrecover') . "\n";

    # HOOK: User not found
    @include('./includes/hook/index-end-user_logged_out.php');
    # HOOK;

# Set a default page (is logged in)
} elseif (($do == "") && ($isuser)) {
    $lefttitle = _l($userdata['username']);
    $title = $program_name;
    $navbits = array($script_name => $program_name, '' => $lefttitle);

    connectbillsdb();
	$bill = mssql_query("SELECT * FROM tbl_UserStatus WHERE id = '" .$userdata['username']. "'");
    $bill = mssql_fetch_array($bill);
	if ($bill['Status'] == 1) 
	{ 
		$statprem = '<font face="verdana" color="red">Desactive</font>'; 
	} else if ($bill['Status'] == 2) 
	{ 
		$statprem = '<font face="verdana" color="green">Active</font>'; 
	} else 
	{ 
		$statprem = 'Error';
	}
    # HOOK: User not found
    @include('./includes/hook/index-start-user_logged_in.php');
    # HOOK;
     $out .= '<p><b><small><h2>ACCOUNT INFORMATION</h2></small></p></p>' . "\n";
    $out .= '<p><b>' . _l('&raquo; Account E-Mail') . ':</b> <i>' . $userdata['email'] . '</i></p>' . "\n";   
	$out .= '<p><b>' . _l('&raquo; Last Log In Time') . ':</b> <i>' . $userdata['lastlogintime'] . '</i></p>' . "\n";
    $out .= '<p><b>' . _l('&raquo; Last Log Off Time') . ':</b> <i>' . $userdata['lastlogofftime'] . '</i></p>' . "\n";	
    $out .= '<p><b>' . _l('&raquo; Last Connect IP Address') . ':</b> <i>' . ($userdata['lastconnectip'] != 0 ? $userdata['lastconnectip'] : _l('None')) . '</i></p>' . "\n";
    $out .= '<p><b>' . _l('&raquo; Current State') . ':</b> <i>' . (($userdata['status']) ? _l('Online') : _l('Offline')) . '</i></p>' . "\n";
	
	$out .= '<p><b><small><h2>GAMECP POINTS INFORMATION</h2></small></p></p>' . "\n";
	$out .= '<p><b>' . _l('&raquo; Vote Points') . ':</b> <i>' . number_format($userdata['vote_points']) . '</i></p>' . "\n";
	$out .= '<p><b>' . _l('&raquo; Game Point') . ':</b> <i>' . number_format($userdata['points']) . '</i></p>' . "\n";	
   
	$out .= '<p><b><small><h2>PREMIUM INFORMATION</h2></small></p></p>' . "\n";
    $out .= '<p><b>' . _l('&raquo; Cash Points') . ':</b> <i>' . number_format($bill['Cash']) . '</i></p>' . "\n";
	$out .= '<p><b>' . _l('&raquo; Premium Status') . ':</b> <i>' . $statprem . '</i></p>' . "\n";
    $out .= '<p><b>' . _l('&raquo; Premium Start') . ':</b> <i>' . $bill['DTStartPrem'] . '</i></p>' . "\n";
    $out .= '<p><b>' . _l('&raquo; Premium End') . ':</b> <i>' . $bill['DTEndPrem'] . '</i></p>' . "\n";


    # HOOK: User not found
    @include('./includes/hook/index-end-user_logged_in.php');
    # HOOK;
} else {
# Include the pages given by $do

    // Security checks
    $do = str_replace('.', '', $do);
    $do = str_replace('\\', '', $do);
    $do = str_replace('/', '', $do);
    $do = trim($do);

    if (!preg_match('/^([a-zA-Z0-9\-\_]+)$/', $do)) {
        echo 'Invalid ' . $do;
        exit;
    }

    # HOOK: User not found
    @include('./includes/hook/index-start_include_module.php');
    # HOOK;

    if (!file_exists('./includes/' . $do . '.php')) {
        $out .= _l('page_not_found');
        $lefttitle = _l("Page Not Found");
    } else {
        include('./includes/' . $do . '.php');
    }

    # HOOK: User not found
    @include('./includes/hook/index-end_include_module.php');
    # HOOK;

    $title = $program_name . ' - ' . $lefttitle;
    $navbits = array($script_name => $program_name, '' => $lefttitle);

    // Close all MSSQL connections, they are not needed after this point tbh
    if (isset($gamecp_dbconnect)) {
        @mssql_close($gamecp_dbconnect);
    }
    if (isset($items_dbconnect)) {
        @mssql_close($items_dbconnect);
    }
    if (isset($donate_dbconnect)) {
        @mssql_close($donate_dbconnect);
    }
    if (isset($user_dbconnect)) {
        @mssql_close($user_dbconnect);
    }
    if (isset($data_dbconnect)) {
        @mssql_close($data_dbconnect);
    }
}

# HOOK: User not found
@include('./includes/hook/index-start_out_var_output.php');
# HOOK;

# Draw the end of this script
gamecp_nav($isuser); // From phpBB 2.x
eval('print_outputs("' . gamecp_template('gamecp') . '");');

# HOOK: User not found
@include('./includes/hook/index-end_out_var_output.php');
# HOOK;
?>