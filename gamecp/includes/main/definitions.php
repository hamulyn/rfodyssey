<?php
/**
 * RF Online Game Control Panel v2
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

// Regex
define('REGEX_USERNAME', '/^(^[a-zA-Z][a-zA-Z0-9]+)$/');
define('REGEX_PASSWORD', '/^([a-zA-Z0-9]+)$/');

// Table Definitions
define('TABLE_CONFIRM_EMAIL', 'gamecp_confirm_email');
define("TABLE_LUACCOUNT", "tbl_rfaccount");  // i.e. tbl_LUAccount or tbl_rfaccount, the latter for 2.2.3+


?>