<?php
/**
 * Game Control Panel v2
 * Copyright (c) www.intrepid-web.net
 *
 * The use of this product is subject to a license agreement
 * which can be found at http://www.intrepid-web.net/rf-game-cp-v2/license-agreement/
 */

if(!defined("IN_GAMECP_SALT58585")) {
	die("Hacking Attempt");
	exit;
	return;
}

# Administrative Options
$admin = array();
$admin['super_admin'] = 'skyze';
$admin['allowed_ips'] = ''; // Ugamecpge:  $admin['allowed_ips'] = '127.0.0.1,95.25.24.55,96.00';

# Get our list of possible ban reasons
$ban_reasons = array('Bugador ','Sell Item per Real Money','Problem in Donate Payment','Consil playing in another race','Bug Map','Bug Siege Kit','Bug Guard Tower','Use of Prohibited Programs','Insulting Players / GM / ADM','Contact a GM regarding BAN');
sort($ban_reasons);
$reasons_count = @count($ban_reasons);

# Ban times
$banTimes = array(
    array('hours' => 2, 'title' => '2 Hours'),
    array('hours' => 3, 'title' => '3 Hours'),
    array('hours' => 4, 'title' => '4 Hours'),
    array('hours' => 12, 'title' => '12 Hours'),
    array('hours' => 24, 'title' => '1 Day'),
    array('hours' => 48, 'title' => '2 Days'),
    array('hours' => 72, 'title' => '3 Days'),
    array('hours' => 720, 'title' => '1 Month'),
    array('hours' => 119988, 'title' => 'Forever'),
);

# Built the "Log Types" array
$logTypes_array = array("--- N/A ---",
    "GAMECP - CHANGE PASSWORD",
    "GAMECP - CHANGE EMAIL",	
    "GAMECP - DONATE",
    "GAMECP - DELETE CHARACTER",
    "SUPPORT - USER INFO",
    "SUPPORT - LOG OUT LOGS",
    "ADMIN - MAIL LOGS",
    "ADMIN - CHAR LOOK UP",
    'ADMIN - DELETE CHARACTER',
    "PAYPAL - <b>REVERSED</b>",
    "PAYPAL - <b>CANCELED</b>",
    "PAYPAL - SUCCESSFUL PAYMENT",
    "PAYPAL - ADDED CREDITS",
    "PAYPAL - DUPLICATE TXN ID",
    "PAYPAL - INVALID BUSINESS",
    "PAYPAL - <b>INCOMPLETE</b>",
    "PAYPAL - PAYMENT INVALID",
    "PAYPAL - PAYMENT FAILED",
    "PAYPAL - Unable to connect to www.paypal.com",
    "GAMECP - PASSWORD RECOVERY",
    "GAMECP - ACCOUNT INFO",
    "ADMIN - ITEM EDIT",
    "ADMIN - BANK EDIT",
    "ADMIN - ITEM SEARCH",
    "ADMIN - ITEM LIST",
    'ADMIN - GIVE ITEM',
    'ADMIN - DELETE ITEM',
    "ADMIN - MANAGE BANS - ADDED",
    "ADMIN - MANAGE BANS - UPDATED",
    "ADMIN - MANAGE BANS - UNBAN",
    "ADMIN - CHARACTER EDIT",
    "SUPER ADMIN - PERMISSIONS",
    'SUPER ADMIN - CONFIG',
    'ADMIN - MANAGE USERS',
    'ADMIN - MANAGE ITEMS - UPDATED',
    'ADMIN - MANAGE ITEMS - ADDED',
    'ADMIN - MANAGE ITEMS - DELETED',
    'ADMIN - MANAGE CATEGORIES',
    'GAMECP - CHANGE CHAR NAME',
    'ADMIN - MANAGE REDEEM',
    'ADMIN - VOTE SITES',
    'GAMECP - ACCOUNT INFO - UPDATED SIG - PHP CODE FOUND'
);
sort($logTypes_array);

# Configurable Variables [DON'T TOUCH IF YOU DONT KNOW WHATS GOING ON!]
$dont_allow = array(".","..","index.html","pagination","library","libchart","gamecp_license.txt","generated");

# Database Settings (BE ADVISED: MAKE NEW USERNAMES AND PASSWORDS FOR THE GAMECP, DO NOT USE YOUR MASTER)
$mssql = array();
$mssql['user']['host'] = '144.217.220.141';
$mssql['user']['db'] = 'RF_User';
$mssql['user']['username'] = 'sa';
$mssql['user']['password'] = 'RFonline123';

$mssql['data']['host'] = '144.217.220.141';
$mssql['data']['db'] = 'RF_World';
$mssql['data']['username'] = 'sa'; // If user has only 'read' access
$mssql['data']['password'] = 'RFonline123'; // Item Edit and Delete characters wont work

$mssql['gamecp']['host'] = '144.217.220.141';
$mssql['gamecp']['db'] = 'RF_GameCP';
$mssql['gamecp']['username'] = 'sa';
$mssql['gamecp']['password'] = 'RFonline123';

$mssql['items']['host'] = '144.217.220.141';
$mssql['items']['db'] = 'RF_ItemsDB';
$mssql['items']['username'] = 'sa';
$mssql['items']['password'] = 'RFonline123';

$mssql['cash']['host'] = '144.217.220.141';
$mssql['cash']['db'] = 'BILLING';
$mssql['cash']['username'] = 'sa';
$mssql['cash']['password'] = 'RFonline123';


?>
