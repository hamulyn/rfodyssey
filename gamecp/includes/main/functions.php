<?php
/**
 * Game Control Panel v2
 * Copyright (c) www.intrepid-web.net
 *
 * The use of this product is subject to a license agreement
 * which can be found at http://www.intrepid-web.net/rf-game-cp-v2/license-agreement/
 */

$talics = array(
    0 => 'Ignorant',
    1 => 'Destruction',
    2 => 'Darkness',
    3 => 'Chaos',
    4 => 'Hatred',
    5 => 'Favor',
    6 => 'Wisdom',
    7 => 'Sacred Flame',
    8 => 'Belief',
    9 => 'Guard',
    10 => 'Glory',
    11 => 'Grace',
    12 => 'Mercy',
    13 => 'Restoration',
    15 => '-- Empty Slot --',
);

// ------------------------------------------------
// Item-Kind Definations
// DO NOT CHANGE OR DELETE OR YOUR ITEM SEARCH WILL BE ROYALLY SCREWED
// IF ADDING: ADD ONLY AFTER THE LAST (LASTID+1), AND ADD IT TO THE GetItemKind FUNCTION
// ------------------------------------------------
$item_tbl_num = 37; // Total number (including 0!)

define("tbl_code_upper", "0");
define("tbl_code_lower", "1");
define("tbl_code_gauntlet", "2");
define("tbl_code_shoe", "3");
define("tbl_code_helmet", "4");
define("tbl_code_shield", "5");
define("tbl_code_weapon", "6");
define("tbl_code_cloak", "7");
define("tbl_code_ring", "8");
define("tbl_code_amulet", "9");
define("tbl_code_bullet", "10");
define("tbl_code_maketool", "11");
define("tbl_code_bag", "12");
define("tbl_code_potion", "13");
define("tbl_code_face", "14");
define("tbl_code_force", "15");
define("tbl_code_battery", "16");
define("tbl_code_ore", "17");
define("tbl_code_resource", "18");
define("tbl_code_unitkey", "19");
define("tbl_code_booty", "20");
define("tbl_code_map", "21");
define("tbl_code_town", "22");
define("tbl_code_battledungeon", "23");
define("tbl_code_animus", "24");
define("tbl_code_guardtower", "25");

define("tbl_code_trap", "26");
define("tbl_code_siegekit", "27");
define("tbl_code_ticket", "28");
define("tbl_code_event", "29");
define("tbl_code_recovery", "30");
define("tbl_code_box", "31");
define("tbl_code_firecracker", "32");
define("tbl_code_miningtool", "33");
define("tbl_code_radar", "34");
define("tbl_code_npclink", "35");
define("tbl_code_coupon", "36");



// ------------------------------------------------
// Generates an HTML input field
// @param:	string	name of field
// @param:	mixed	value
// @param:	string	type of field
// @param:	fields	actually is options... for drop downs and etc
// @result: string	HTML
// ------------------------------------------------
function generate_field($name, $value, $type = 'textbox', $fields = '')
{

    // First check to see if this type exists, if not assume textbox
    if ($type != 'textbox' && $type != 'textarea' && $type != 'radio' && $type != 'dropdown' && $type != 'checkbox' && $type != 'password') {
        $type = 'textbox';
    }

    // Checks first
    $fields = @unserialize($fields);
    if (empty($fields) && ($type == 'dropdown' || $type == 'checkbox' || $type == 'radio')) {
        $type = 'textbox';
    }

    $type = trim($type);

    // Now lets generate each by case
    switch ($type) {
        case 'checkbox':

            // Initial return string
            $return = '';

            // Split the fields by | and then :
            $options = $fields;
            foreach ($options as $db_value => $option) {
                $selected = ($db_value == $value) ? ' checked="checked"' : '';
                $return .= '<input type="checkbox" name="config[' . $name . ']" value="' . $db_value . '"' . $selected . ' class="form-control"> ' . $option . ' ';
            }

            // return
            return $return;

        case 'radio':

            // Initial return string
            $return = '';

            // Split the fields by | and then :
            $options = $fields;
            foreach ($options as $db_value => $option) {
                $selected = ($db_value == $value) ? ' checked="checked"' : '';
                $return .= '<input type="radio" name="config[' . $name . ']" value="' . $db_value . '"' . $selected . '> ' . $option . ' ';
            }

            // return
            return $return;

        case 'dropdown':

            // Initial return string
            $return = '<select class="form-control"name="config[' . $name . ']" class="form-control">' . "\n";

            // Split the fields by | and then :
            $options = $fields;
            foreach ($options as $db_value => $option) {
                $selected = ($db_value == $value) ? ' selected="selected"' : '';
                $return .= '<option value="' . $db_value . '"' . $selected . '> ' . $option . '</option>' . "\n";
            }
            $return .= '</select>' . "\n";

            // return
            return $return;

        case 'password':

            // Make sure the "fields" is a numeirc, if not set default
            $fields = (is_numeric($fields) && $fields > 0) ? $fields : '';

            // Add size or not?
            $size = ($fields != '') ? ' size="' . $fields . '"' : '';

            // Generate HTML
            return '<input type="password" name="config[' . $name . ']" value="' . $value . '"' . $size . '" class="form-control">';

        case 'textarea':

            // Make sure the "fields" is a numeirc, if not set default
            $fields = (is_numeric($fields) && $fields > 0) ? $fields : '';

            // Add size or not?
            $size = ($fields != '') ? ' size="' . $fields . '"' : '';

            // Generate HTML
            return '<textarea name="config[' . $name . ']" rows="4" cols="50" class="form-control">' . $value . '</textarea>';


        default:

            // Make sure the "fields" is a numeirc, if not set default
            $fields = (is_numeric($fields) && $fields > 0) ? $fields : '';

            // Add size or not?
            $size = ($fields != '') ? ' size="' . $fields . '"' : '';

            // Generate HTML
            return '<input type="text" class="form-control" name="config[' . $name . ']" value="' . $value . '"' . $size . '" class="form-control">';

    }

}

// -------------------------------------------------
// Destory cookie
// -------------------------------------------------
function logoutUser()
{
    global $notuser, $isuser, $is_superadmin;

    $_SESSION = array(); // destroy all $_SESSION data
    setcookie("gamecp_userdata", "", time() - 3600, '/');
    if (isset($_COOKIE["gamecp_userdata"])) {
        unset($_COOKIE["gamecp_userdata"]);
    }
    $notuser = true;
    $isuser = false;
    $is_superadmin = false;
    session_destroy();
}


// -------------------------------------------------
// Add our custom changes to the MSSQL_QUERY function
// -------------------------------------------------
/*override_function('mssql_query', '$sql', 'return gamecp_mssql_query($query);');

function gamecp_mssql_query($sql) {
	global $out;

	$mssql_query = mssql_query($sql);

	if($mssql_query) {
		return $mssql_query;
	} else {
		if($is_superadmin == 1) {
			$out .= '<p class="panelsurround" style="border: 1px solid black;">';
			$out .= '	<b>DEBUG(?):</b> '.mssql_get_last_message()."<br/>\n";
			$out .= '	<b>SQL<:/b> <i>'.$sql.'</i>';
			$out .= '</p>';
		}
		return false;
	}


}*/


// ------------------------------------------------
// Obtains the table name by the item "id" or "number"
// ------------------------------------------------
function GetItemTableName($szPreFix)
{
    if ($szPreFix == "0") {
        return "tbl_code_upper";
    } elseif ($szPreFix == "1") {
        return "tbl_code_lower";
    } elseif ($szPreFix == "2") {
        return "tbl_code_gauntlet";
    } elseif ($szPreFix == "3") {
        return "tbl_code_shoe";
    } elseif ($szPreFix == "4") {
        return "tbl_code_helmet";
    } elseif ($szPreFix == "5") {
        return "tbl_code_shield";
    } elseif ($szPreFix == "6") {
        return "tbl_code_weapon";
    } elseif ($szPreFix == "7") {
        return "tbl_code_cloak";
    } elseif ($szPreFix == "8") {
        return "tbl_code_ring";
    } elseif ($szPreFix == "9") {
        return "tbl_code_amulet";
    } elseif ($szPreFix == "10") {
        return "tbl_code_bullet";
    } elseif ($szPreFix == "11") {
        return "tbl_code_maketool";
    } elseif ($szPreFix == "12") {
        return "tbl_code_bag";
    } elseif ($szPreFix == "13") {
        return "tbl_code_potion";
    } elseif ($szPreFix == "14") {
        return "tbl_code_face";
    } elseif ($szPreFix == "15") {
        return "tbl_code_force";
    } elseif ($szPreFix == "16") {
        return "tbl_code_battery";
    } elseif ($szPreFix == "17") {
        return "tbl_code_ore";
    } elseif ($szPreFix == "18") {
        return "tbl_code_resource";
    } elseif ($szPreFix == "19") {
        return "tbl_code_unitkey";
    } elseif ($szPreFix == "20") {
        return "tbl_code_booty";
    } elseif ($szPreFix == "21") {
        return "tbl_code_map";
    } elseif ($szPreFix == "22") {
        return "tbl_code_town";
    } elseif ($szPreFix == "23") {
        return "tbl_code_battledungeon";
    } elseif ($szPreFix == "24") {
        return "tbl_code_animus";
    } elseif ($szPreFix == "25") {
        return "tbl_code_guardtower";
    } elseif ($szPreFix == "26") {
        return "tbl_code_trap";
    } elseif ($szPreFix == "27") {
        return "tbl_code_siegekit";
    } elseif ($szPreFix == "28") {
        return "tbl_code_ticket";
    } elseif ($szPreFix == "29") {
        return "tbl_code_event";
    } elseif ($szPreFix == "30") {
        return "tbl_code_recovery";
    } elseif ($szPreFix == "31") {
        return "tbl_code_box";
    } elseif ($szPreFix == "32") {
        return "tbl_code_firecracker";
    } elseif ($szPreFix == "33") {
        return "tbl_code_unmannedminer";
    } elseif ($szPreFix == "34") {
        return "tbl_code_radar";
    } elseif ($szPreFix == "35") {
        return "tbl_code_npclink";
    } elseif ($szPreFix == "36") {
        return "tbl_code_coupon";		
    } else {
        return false;
    }
    // 6
}

// ------------------------------------------------
// Obtains the table "code" which are defined above
// ------------------------------------------------
function GetItemTableCode($psItemCode)
{
    $szPreFix = substr($psItemCode, 0, 2);

    if ($szPreFix == "iu") return tbl_code_upper;
    else if ($szPreFix == "il") return tbl_code_lower;
    else if ($szPreFix == "ig") return tbl_code_gauntlet;
    else if ($szPreFix == "is") return tbl_code_shoe;
    else if ($szPreFix == "ih") return tbl_code_helmet;
    else if ($szPreFix == "id") return tbl_code_shield;
    else if ($szPreFix == "iw") return tbl_code_weapon;
    else if ($szPreFix == "im") return tbl_code_maketool;
    else if ($szPreFix == "ie") return tbl_code_bag;
    else if ($szPreFix == "ip") return tbl_code_potion;
    else if ($szPreFix == "ib") return tbl_code_bullet;
    else if ($szPreFix == "if") return tbl_code_face;
    else if ($szPreFix == "ic") return tbl_code_force;
    else if ($szPreFix == "it") return tbl_code_battery;
    else if ($szPreFix == "io") return tbl_code_ore;
    else if ($szPreFix == "ir") return tbl_code_resource;
    else if ($szPreFix == "in") return tbl_code_unitkey;
    else if ($szPreFix == "iy") return tbl_code_booty;
    else if ($szPreFix == "ik") return tbl_code_cloak;
    else if ($szPreFix == "ii") return tbl_code_ring;
    else if ($szPreFix == "ia") return tbl_code_amulet;
    else if ($szPreFix == "iz") return tbl_code_map;
    else if ($szPreFix == "iq") return tbl_code_town;
    else if ($szPreFix == "ix") return tbl_code_battledungeon;
    else if ($szPreFix == "ij") return tbl_code_animus;
    else if ($szPreFix == "gt") return tbl_code_guardtower;
    else if ($szPreFix == "tr") return tbl_code_trap;
    else if ($szPreFix == "sk") return tbl_code_siegekit;
    else if ($szPreFix == "ti") return tbl_code_ticket;
    else if ($szPreFix == "ev") return tbl_code_event;
    else if ($szPreFix == "re") return tbl_code_recovery;
    else if ($szPreFix == "bx") return tbl_code_box;
    else if ($szPreFix == "fi") return tbl_code_firecracker;
    else if ($szPreFix == "un") return tbl_code_miningtool;
    else if ($szPreFix == "rd") return tbl_code_radar;
    else if ($szPreFix == "lk") return tbl_code_npclink;
    else if ($szPreFix == "cu") return tbl_code_coupon;	
    return -1;
}

/**
 * Upgrade Tool
 * @param $id
 * @param $inputId
 * @return string
 */
function upgradeTool($id, $inputId)
{
    global $talics;

    $html = '<div class="upgrade-tool" id="upgrade-tool-' . $id . '">';
    $html .= '<div class="select-talic-type">';
    $html .= '<select class="form-control"name="talic_type" class="talic-type" style="width: 150px;">';
    foreach ($talics as $key => $talic) {
        $html .= '<option value="' . $key . '">' . $talic . '</option>';
    }
    $html .= '</select>';
    $html .= '</div>';
    $html .= '<div class="talics"></div>';
    $html .= '<div class="buttons">';
    $html .= '<a href="#" data-action="plus" data-id="' . $id . '" class="upgrade-plus"><span class="glyphicon glyphicon-plus"></span></a>';
    $html .= '<a href="#" data-action="minus" data-id="' . $id . '" class="upgrade-minus"><span class="glyphicon glyphicon-minus"></span></a>';
    $html .= '</div>';
    $html .= '<input type="hidden" id="upgrade-tool-' . $id . '-inputid" value="' . $inputId . '"/>';
    $html .= '</div>';

    return $html;
}

// ------------------------------------------------
// MSSQL Server Anti-Inject Function
// ------------------------------------------------
function antiject($str)
{
    #$escape = "/([\x00\n\r\,\'\"\x1a])/ig";
    #$str = eregi_replace($escape,'\$1',$str);

    $str = stripslashes($str);
    $str = htmlspecialchars($str);
    $str = trim($str);
    $str = preg_replace("/'/", "''", $str);
    $str = preg_replace('/"/', '""', $str);
    $str = str_replace("`", "", $str);
    #$str = preg_replace("/;$/", "", $str);
    #$str = preg_replace("/\\\\/", "", $str);

    return $str;
}

// ------------------------------------------------
// Add comas, number format?
// ------------------------------------------------
function commas($str)
{
    return number_format(floor($str));
}

// ------------------------------------------------
// True for a valid E-Mail address
// ------------------------------------------------
function IsEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ------------------------------------------------
// User DB Connection Function
// ------------------------------------------------
function connectuserdb()
{
    global $mssql, $user_dbconnect, $userdb;
    $user_dbconnect = @mssql_connect($mssql['user']['host'], $mssql['user']['username'], $mssql['user']['password']) or quick_msg("Couldn't connect to user database server. " . mssql_get_last_message());
    $userdb = mssql_select_db($mssql['user']['db'], $user_dbconnect) or quick_msg(mssql_get_last_message());

    return $user_dbconnect;
}

// ------------------------------------------------
// User 2 DB Connection Function
// ------------------------------------------------
function connectuser2db()
{
    global $mssql, $user2_dbconnect, $user2db;
    $user2_dbconnect = @mssql_connect($mssql['user2']['host'], $mssql['user2']['username'], $mssql['user2']['password']) or quick_msg("Couldn't connect to user2 database server. " . mssql_get_last_message());
    $user2db = mssql_select_db($mssql['user2']['db'], $user2_dbconnect) or quick_msg(mssql_get_last_message());
}

// ------------------------------------------------
// Data DB Connection Function
// ------------------------------------------------
function connectdatadb()
{
    global $mssql, $data_dbconnect, $datadb;
    $data_dbconnect = @mssql_connect($mssql['data']['host'], $mssql['data']['username'], $mssql['data']['password']) or quick_msg("Couldn't connect to data database server. " . mssql_get_last_message());
    $datadb = @mssql_select_db($mssql['data']['db'], $data_dbconnect) or quick_msg(mssql_get_last_message());

    return $data_dbconnect;
}

function connectbillsdb()
{
    global $mssql, $data_dbconnect, $datadb;
    $data_dbconnect = @mssql_connect($mssql['cash']['host'], $mssql['cash']['username'], $mssql['cash']['password']) or quick_msg("Couldn't connect to data database server. " . mssql_get_last_message());
    $datadb = @mssql_select_db($mssql['cash']['db'], $data_dbconnect) or quick_msg(mssql_get_last_message());

    return $data_dbconnect;
}

// ------------------------------------------------
// Game CP DB Connection Function
// ------------------------------------------------
function connectgamecpdb()
{
    global $mssql, $gamecp_dbconnect, $gamecpdb, $_license_properties;

    $database = $mssql['gamecp']['db'];
    if (isset($_license_properties['features']) && isset($_license_properties['features']['value']['enable_restrictions']) && $_license_properties['features']['value']['enable_restrictions'] == 1) {
        $database = 'RF_GameCP';
    }

    $gamecp_dbconnect = @mssql_connect($mssql['gamecp']['host'], $mssql['gamecp']['username'], $mssql['gamecp']['password'])
        or quick_msg("Couldn't connect to the Game CP database server. " . mssql_get_last_message());
    $gamecpdb = @mssql_select_db($database, $gamecp_dbconnect)
        or quick_msg(mssql_get_last_message());

    return $gamecp_dbconnect;
}

// ------------------------------------------------
// Donation DB Connection Function
// ------------------------------------------------
function connectdonationsdb()
{
    global $mssql, $donate_dbconnect, $donatedb;
    $donate_dbconnect = @mssql_connect($mssql['gamecp']['host'], $mssql['gamecp']['username'], $mssql['gamecp']['password']) or quick_msg("Couldn't connect to donations database server. " . mssql_get_last_message());
    $donatedb = @mssql_select_db($mssql['gamecp']['db'], $donate_dbconnect) or quick_msg(mssql_get_last_message());

    return $donate_dbconnect;
}

// ------------------------------------------------
// Items DB Connection Function
// ------------------------------------------------
function connectitemsdb()
{
    global $mssql, $items_dbconnect, $itemsdb;
    $items_dbconnect = @mssql_connect($mssql['items']['host'], $mssql['items']['username'], $mssql['items']['password']) or quick_msg("Couldn't connect to items database server. " . mssql_get_last_message());
    $itemsdb = @mssql_select_db($mssql['items']['db'], $items_dbconnect) or quick_msg(mssql_get_last_message());

    return $items_dbconnect;
}

// ------------------------------------------------
// Cash DB Connection Function
// ------------------------------------------------
function connectcashdb()
{
    global $mssql, $cash_dbconnect, $itemsdb;
    $cash_dbconnect = @mssql_connect($mssql['cash']['host'], $mssql['cash']['username'], $mssql['cash']['password']) or quick_msg("Couldn't connect to cash database server. " . mssql_get_last_message());
    $itemsdb = @mssql_select_db($mssql['cash']['db'], $cash_dbconnect) or quick_msg(mssql_get_last_message());

    return $cash_dbconnect;
}

// ------------------------------------------------
// Get full race name by letters (A,B,C)
// ------------------------------------------------
function getRace($strrace)
{
    $race = 'Unknown';
    $racechar = substr($strrace, 0, 1);

    if ($racechar == 'C') {
        $race = 'Cora';
    } elseif ($racechar == 'B') {
        $race = 'Bellato';
    } elseif ($racechar == 'A') {
        $race = 'Accretia';
    }
    return $race;
}

// ------------------------------------------------
// Obtains the full race name by ID (0,1,2,3,4)
// ------------------------------------------------
function getRaceByID($racechar)
{
    $race = 'Unknown';
    if ($racechar == 0 OR $racechar == 1) {
        $race = 'Bellato';
    } elseif ($racechar == 2 OR $racechar == 3) {
        $race = 'Cora';
    } elseif ($racechar == 4) {
        $race = 'Accretia';
    }
    return $race;
}

// ------------------------------------------------
// Obtain character information by Account Serial
// ------------------------------------------------
function getCharacters($id)
{
    $charinfo = "";
    $i = 0;

    connectdatadb();

    $cquery = mssql_query('SELECT
	B.Serial,B.Name,B.AccountSerial,B.Account,B.Class,B.Lv,B.Race,G.TotalPlayMin
	FROM tbl_base AS B
	RIGHT JOIN
	tbl_general AS G
	ON B.Serial = G.Serial
	WHERE B.AccountSerial="' . $id . '" and B.DCK=0');
    while ($character = mssql_fetch_array($cquery)) {
        $charinfo[$i]['Serial'] = $character['Serial'];
        $charinfo[$i]['Name'] = $character['Name'];
        $charinfo[$i]['AccountSerial'] = $character['AccountSerial'];
        $charinfo[$i]['Account'] = $character['Account'];
        $charinfo[$i]['Race'] = getrace($character['Class']);
        $charinfo[$i]['Level'] = $character['Lv'];
        $charinfo[$i]['Sex'] = $character['Race'];
        $charinfo[$i]['TotalPlay'] = $character['TotalPlayMin'];
        $i++;
    }

    return $charinfo;
}

// ------------------------------------------------
// Checks if character exists by doing a count
// ------------------------------------------------
function charexists($name)
{
    $charexists = false;
    connectdatadb();

    $cquery = mssql_result(mssql_query('SELECT count(*) FROM tbl_base WHERE Name="' . $name . '"'), 0, 0);
    if ($cquery > 0) {
        return true;
    } else {
        return false;
    }
}

// ------------------------------------------------
// Return account data by ID
// ------------------------------------------------
function getaccount($id)
{
    connectuserdb();
    $acc = mssql_fetch_row(mssql_query('SELECT * FROM usertbl WHERE UID="' . $id . '"'));
    return $acc;
}

// ------------------------------------------------
// Check if is user by Character Name
// ------------------------------------------------
function isusers($id, $charid)
{
    $isusers = false;

    connectdatadb();

    $query = mssql_query('SELECT Name,Account FROM tbl_base WHERE Name="' . $charid . '"');
    $query = mssql_fetch_array($query);

    if ($query != "") {
        if ($query['Account'] == $id) {
            $isusers = true;
        }
    }

    return $isusers;
}

// ------------------------------------------------
// Check is user by Character Serial
// ------------------------------------------------
function isusers2($id, $charid)
{
    $isusers = false;

    connectdatadb();

    $query = mssql_query('SELECT Name,Account FROM tbl_base WHERE Serial="' . $charid . '"');
    $query = mssql_fetch_array($query);

    if ($query != "") {
        if ($query['Account'] == $id) {
            $isusers = true;
        }
    }

    return $isusers;
}

// ------------------------------------------------
// Get a single charcters information
// ------------------------------------------------
function getonecharacter($id)
{
    connectdatadb();

    $cquery = mssql_query('SELECT
	B.Serial, B.Name, B.AccountSerial, B.Account, B.Class, B.Lv, G.TotalPlayMin
	FROM tbl_base AS B
	RIGHT JOIN
	tbl_general AS G
	ON B.Serial = G.Serial
	WHERE B.Name="' . $id . '"');
    $i = 0;
    while ($character = mssql_fetch_array($cquery)) {
        $charinfo['Serial'] = $character['Serial'];
        $charinfo['Name'] = $character['Name'];
        $charinfo['AccountSerial'] = $character['AccountSerial'];
        $charinfo['Account'] = $character['Account'];
        $charinfo['Race'] = getrace($character['Class']);
        $charinfo['Level'] = $character['Lv'];
        $charinfo['TotalPlay'] = $character['TotalPlayMin'];

        $i++;
    }
    return $charinfo;
}

// ------------------------------------------------
// Game Control Panel Log Generator
// When run, a log is written with the given text
// User IP and Browser are automatically recorded
// ------------------------------------------------
function gamecp_log($log_level, $log_account, $log_message, $disable_return = 0)
{
    global $userdata;
    global $userdb, $gamecpdb, $datadb, $user2db, $itemsdb, $cashdb;
    global $gamecp_dbconnect, $data_dbconnect, $user_dbconnect, $items_dbconnect, $cash_dbconnect;

    $return_user = false;
    $return_gamecp = false;
    $return_data = false;
    $return_event = false;
    $return_user2 = false;
    $return_items = false;
    $return_cash = false;	

    if ($disable_return == 0) {
        if ($userdb == 1) {
            $return_user = true;
            mssql_close($user_dbconnect);
        }
        if ($gamecpdb == 1) {
            $return_gamecp = true;
            mssql_close($gamecp_dbconnect);
        }
        if ($datadb == 1) {
            $return_data = true;
            mssql_close($data_dbconnect);
        }
        if ($itemsdb == 1) {
            $return_items = true;
            mssql_close($items_dbconnect);
        }
        if ($cashdb == 1) {
            $return_cash = true;
            mssql_close($cash_dbconnect);			
        }
    }

    $log_time = time();
    $log_ip = $userdata['ip'];
    $log_browser = antiject($_SERVER["HTTP_USER_AGENT"]);
    $log_page = antiject($_SERVER["REQUEST_URI"]);

    $log_account = ereg_replace(";$", "", $log_account);
    $log_account = ereg_replace("\\\\", "", $log_account);

    $log_message = ereg_replace(";$", "", $log_message);
    $log_message = ereg_replace("\\\\", "", $log_message);

    $log_message = htmlentities($log_message);

    if ($log_account != "" && $log_message != "") {
        connectgamecpdb();

        $gamecploq_query = "INSERT INTO gamecp_log (log_level, log_time, log_account, log_message, log_ip, log_page, log_browser) VALUES ('$log_level','$log_time','$log_account','$log_message','$log_ip','$log_page','$log_browser')";
        $gamecploq_query = mssql_query($gamecploq_query);

        mssql_close($gamecp_dbconnect);

        if ($return_user == true) {
            connectuserdb();
        }
        if ($return_data == true) {
            connectdatadb();
        }
        if ($return_event == true) {
            connecteventsdb();
        }
        if ($return_gamecp == true) {
            connectgamecpdb();
        }
        if ($return_items == true) {
            connectitemsdb();
        }
        if ($return_cash == true) {
            connectitemsdb();			
        }
    }
}

// ------------------------------------------------
// time, days from time to go back
// ------------------------------------------------
function get_date($time, $hrs = 0)
{
    if ($hrs == 0) {
        $hrs = 1;
    } else {
        $hrs = 86400 * $hrs;
    }

    return date("Ymd", $time - $hrs);
}

// ------------------------------------------------
// Generates the code for "rented items"
// Items are disabled if "credits are insufficient"
// ------------------------------------------------
function jades_puke($i, $name, $desc, $time, $price)
{
    global $out, $bgcolor, $userdata, $mescript;

    if ($price > $userdata['points']) {
        $enable_disable = "disabled";
    } else {
        $enable_disable = "";
    }

    $out .= '<tr>' . "\n";
    $out .= '<form method="post" action="' . $mescript . '?do=user_rented_items&amp;page=select">' . "\n";
    $out .= '<input type="hidden" name="item_id" value="' . $i . '">' . "\n";
    $out .= '<td class="' . $bgcolor . '"><b>' . $name . '</b><br/>' . $desc . '</td>' . "\n";
    $out .= '<td class="' . $bgcolor . '"><b>' . $time . '</b> Hours</td>' . "\n";
    $out .= '<td class="' . $bgcolor . '" align="center">' . $price . ' GP</td>' . "\n";
    $out .= '<td class="' . $bgcolor . '" align="center"><input type="submit"  class="btn btn-default" value="Select Item" ' . $enable_disable . '></td>' . "\n";
    $out .= '</form>' . "\n";
    $out .= '</tr>' . "\n";
}

// ------------------------------------------------
// Countdown function (returns minutes, hours, days, seconds)
// ------------------------------------------------
function countdown($timestamp)
{
    global $return;
    $diff = $timestamp;

    if ($diff < 0) {
        $diff = 0;
    }
    $dl = floor($diff / 60 / 60 / 24);
    $hl = floor(($diff - $dl * 60 * 60 * 24) / 60 / 60);
    $ml = floor(($diff - $dl * 60 * 60 * 24 - $hl * 60 * 60) / 60);
    $sl = floor(($diff - $dl * 60 * 60 * 24 - $hl * 60 * 60 - $ml * 60));
    $return = array($dl, $hl, $ml, $sl);
    return $return;
}

// ------------------------------------------------
// Character name change log
// ------------------------------------------------
function write_log($username_charid, $username_oldchar, $username_newchar, $username_ip)
{
    global $mssql, $gamecp_dbconnect;

    if ($username_charid != "" or $username_oldchar != "" or $username_newchar != "" or $username_ip != "") {
        connectgamecpdb();
        $loq_query = "INSERT INTO gamecp_username_log (username_charid,username_oldname,username_newname,username_ip) VALUES ('$username_charid','$username_oldchar','$username_newchar','$username_ip')";
        $loq_query = mssql_query($loq_query, $gamecp_dbconnect);
        return true;
    } else {
        return false;
    }
}

// ------------------------------------------------
// Math Eval by David Schumann (http://ca.php.net/eval)
// ------------------------------------------------
function matheval($equation)
{
    $equation = preg_replace("/[^0-9+\-.*\/()%]/", "", $equation);
    $equation = preg_replace("/([+-])([0-9]+)(%)/", "*(1\$1.\$2)", $equation);
    // you could use str_replace on this next line
    // if you really, really want to fine-tune this equation
    $equation = preg_replace("/([0-9]+)(%)/", ".\$1", $equation);
    if ($equation == "") {
        $return = 0;
    } else {
        eval("\$return=" . $equation . ";");
    }
    return $return;
}

// ------------------------------------------------
// Donations Credits & Payment Calculator
// ------------------------------------------------
function calculate_credits($mty = 1, $num_of_payments = 20, $price_value = 5, $credits_value = 25, $check_price = false)
{
    global $c_price, $c_credits, $c_bonus, $c_total, $config;

    $muntiplier = $mty;

    $c_price = array();
    $c_credits = array();
    $c_bonus = array();
    $c_total = array();

    for ($i = 0; $i < $num_of_payments; $i++) {
        $raw_price = $price_value * $i;
        $raw_credits = ($credits_value * $i) * $muntiplier;
        $raw_bonus = round((($raw_credits * 1) + (10 * (($raw_credits - 25) / 25))) - $raw_credits);

        if (isset($config['gamecp_bonus_formula'])) {
            $formula = trim($config['gamecp_bonus_formula']);
            if (!empty($formula)) {
                $formula = antiject($formula);
                $formula = str_replace("$", "", $formula);
                $formula = str_replace("x", $raw_credits, $formula);
                $formula = @matheval($formula);
                if (is_numeric($formula) && $formula >= 0) {
                    $raw_bonus = $formula;
                } else {
                    $raw_bonus = 0;
                }
            }
        }

        $c_price[] = $raw_price;
        $c_credits[] = $raw_credits;
        $c_bonus[] = $raw_bonus;
        $c_total[] = $raw_credits + $raw_bonus;
    }

    if ($check_price === false) {
        return false;
    } else {
        $key = array_search($check_price, $c_price);
        if ($key === FALSE) {
            return 0;
        } else {
            return $c_total[$key];
        }
    }
}

// ------------------------------------------------
// Generates the paypal buttons USD
// ------------------------------------------------
function paypal_buttons($price, $credits, $custom)
{
    global $config;

    $return = '			<input type="hidden" name="cmd" value="_xclick">' . "\n";
    $return .= '			<input type="hidden" name="business" value="' . $config['paypal_email'] . '">' . "\n";
    $return .= '			<input type="hidden" name="custom" value="' . $custom . '">' . "\n";
    $return .= '			<input type="hidden" name="item_name" value="' . $credits . ' Web Credits">' . "\n";
    $return .= '			<input type="hidden" name="item_number" value="' . $credits . ' Web Credits">' . "\n";
    $return .= '			<input type="hidden" name="amount" value="' . $price . '">' . "\n";
    $return .= '			<input type="hidden" name="no_shipping" value="1">' . "\n";
    $return .= '			<input type="hidden" name="no_note" value="1">' . "\n";
    $return .= '			<input type="hidden" name="currency_code" value="' . ((isset($config['paypal_currency'])) ? $config['paypal_currency'] : 'USD') . '">' . "\n";
    $return .= '			<input type="hidden" name="notify_url" value="' . $config['paypal_ipn_url'] . '">' . "\n";
    $return .= '			<input type="hidden" name="return" value="' . $config['paypal_return_url'] . '">' . "\n";
    $return .= '			<input type="hidden" name="cancel_return" value="' . $config['paypal_cancel_url'] . '">' . "\n";
    $return .= '			<input type="submit"  class="btn btn-default" border="0" class="btn btn-success" name="submit" value="Donate Now">' . "\n";

    return $return;
}

// ------------------------------------------------
// Generates the paypal buttons BRL
// ------------------------------------------------
function paypal_buttons_brl($price, $credits, $custom)
{
    global $config;

    $return = '			<input type="hidden" name="cmd" value="_xclick">' . "\n";
    $return .= '			<input type="hidden" name="business" value="' . $config['paypal_email'] . '">' . "\n";
    $return .= '			<input type="hidden" name="custom" value="' . $custom . '">' . "\n";
    $return .= '			<input type="hidden" name="item_name" value="' . $credits . ' Web Credits">' . "\n";
    $return .= '			<input type="hidden" name="item_number" value="' . $credits . ' Web Credits">' . "\n";
    $return .= '			<input type="hidden" name="amount" value="' . $price . '">' . "\n";
    $return .= '			<input type="hidden" name="no_shipping" value="1">' . "\n";
    $return .= '			<input type="hidden" name="no_note" value="1">' . "\n";
    $return .= '			<input type="hidden" name="currency_code" value="' . ((isset($config['paypal_currency_brl'])) ? $config['paypal_currency_brl'] : 'USD') . '">' . "\n";
    $return .= '			<input type="hidden" name="notify_url" value="' . $config['paypal_ipn_brl_url'] . '">' . "\n";
    $return .= '			<input type="hidden" name="return" value="' . $config['paypal_return_url'] . '">' . "\n";
    $return .= '			<input type="hidden" name="cancel_return" value="' . $config['paypal_cancel_url'] . '">' . "\n";
    $return .= '			<input type="submit"  class="btn btn-default" border="0" class="btn btn-success" name="submit" value="Doe Agora">' . "\n";

    return $return;
}

// ------------------------------------------------
// Generates the paypal buttons PHP
// ------------------------------------------------
function paypal_buttons_php($price, $credits, $custom)
{
    global $config;

    $return = '			<input type="hidden" name="cmd" value="_xclick">' . "\n";
    $return .= '			<input type="hidden" name="business" value="' . $config['paypal_email'] . '">' . "\n";
    $return .= '			<input type="hidden" name="custom" value="' . $custom . '">' . "\n";
    $return .= '			<input type="hidden" name="item_name" value="' . $credits . ' Web Credits">' . "\n";
    $return .= '			<input type="hidden" name="item_number" value="' . $credits . ' Web Credits">' . "\n";
    $return .= '			<input type="hidden" name="amount" value="' . $price . '">' . "\n";
    $return .= '			<input type="hidden" name="no_shipping" value="1">' . "\n";
    $return .= '			<input type="hidden" name="no_note" value="1">' . "\n";
    $return .= '			<input type="hidden" name="currency_code" value="' . ((isset($config['paypal_currency_php'])) ? $config['paypal_currency_php'] : 'USD') . '">' . "\n";
    $return .= '			<input type="hidden" name="notify_url" value="' . $config['paypal_ipn_php_url'] . '">' . "\n";
    $return .= '			<input type="hidden" name="return" value="' . $config['paypal_return_url'] . '">' . "\n";
    $return .= '			<input type="hidden" name="cancel_return" value="' . $config['paypal_cancel_url'] . '">' . "\n";
    $return .= '			<input type="submit"  class="btn btn-default" border="0" class="btn btn-success" name="submit" value="Donate Now">' . "\n";

    return $return;
}
// ------------------------------------------------
// New User Registration log
// ------------------------------------------------
function write_reg_log($reg_account, $reg_time, $reg_ip, $reg_browser)
{
    global $user_dbconnect;

    $reg_ip = antiject($reg_ip);
    $reg_browser = antiject($reg_browser);

    if (isset($user_dbconnect)) {
        $return_user = true;
    }

    if ($reg_account != "" or $reg_ip != "" or $reg_time != "" or $reg_browser != "") {
        connectgamecpdb();

        $loq_query = mssql_query(sprintf("INSERT INTO gamecp_registration_log (reg_account,reg_ip,reg_time,reg_browser) VALUES ('%s','%s','%s','%s')", $reg_account, $reg_ip, $reg_time, $reg_browser));

        if (isset($return_user)) {
            connectuserdb();
        }

        return true;
    } else {
        return false;
    }
}

// ------------------------------------------------
// Get Talic Name by ID
// ------------------------------------------------
function talic_name($talic_id)
{

    if ($talic_id == 15) {
        $talic_name = "Ignorant";
    } elseif ($talic_id == 14) {
        $talic_name = "Destruction";
    } elseif ($talic_id == 13) {
        $talic_name = "Darkness";
    } elseif ($talic_id == 12) {
        $talic_name = "Chaos";
    } elseif ($talic_id == 11) {
        $talic_name = "Hatred";
    } elseif ($talic_id == 10) {
        $talic_name = "Favor";
    } elseif ($talic_id == 9) {
        $talic_name = "Wisdom";
    } elseif ($talic_id == 8) {
        $talic_name = "Sacred Flame";
    } elseif ($talic_id == 7) {
        $talic_name = "Belief";
    } elseif ($talic_id == 6) {
        $talic_name = "Guard";
    } elseif ($talic_id == 5) {
        $talic_name = "Glory";
    } elseif ($talic_id == 4) {
        $talic_name = "Grace";
    } elseif ($talic_id == 3) {
        $talic_name = "Mercy";
    } elseif ($talic_id == 2) {
        $talic_name = "Rebirth";
    } elseif ($talic_id == 1) {
        $talic_name = "No Talic";
    } else {
        $talic_name = 0;
    }

    return $talic_name;
}

// ------------------------------------------------
// Header for the error handler
// ------------------------------------------------
function error_header()
{
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">' . "\n";
    echo '<HTML>' . "\n";
    echo '' . "\n";
    echo '<HEAD>' . "\n";
    echo '	<style type="text/css">' . "\n";
    echo '		body {' . "\n";
    echo '			font-size: 12px;' . "\n";
    echo '			font-family: Verdana, Arial, Helvetica, sans-serif;' . "\n";
    echo '			background-color: #FFFFF;' . "\n";
    echo '		}' . "\n";
    echo '	</style>' . "\n";
    echo '</HEAD>' . "\n";
    echo '' . "\n";
    echo '<BODY>' . "\n";
}

// ------------------------------------------------
// Footer for the error handler
// ------------------------------------------------
function error_footer()
{
    echo '</BODY>' . "\n";
    echo '</HTML>' . "\n";
}

// ------------------------------------------------
// Game CP Error Handler
// Handles the php errors, this helps debug and prevent screen
// white outs
// ------------------------------------------------
function errorHandler($errno, $errstr, $errfile, $errline)
{
    global $_SERVER;

    // Do not display notices if we suppress them via @
    if (error_reporting() == 0) {
        return;
    }

    $errfile = str_replace("\\", "/", $errfile);
    $errfile = str_replace($_SERVER["DOCUMENT_ROOT"], "", $errfile);

    switch ($errno) {
        case E_ERROR:
        case E_USER_ERROR:
            error_header();
            if ($errstr == "(SQL)") {
                // handling an sql error
                echo "<b>[GameCP Debug] PHP SQL Error:</b> " . SQLMESSAGE . "<br />\n";
                echo "Query : " . SQLQUERY . "<br />\n";
                echo "On line " . SQLERRORLINE . " in file " . SQLERRORFILE . " ";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br /><br />" . "\n\n";
                echo "Aborting...<br />\n";
            } else {
                echo "<b>[Game CP]</b> PHP Error: $errstr<br />\n";
                echo "  Fatal error on line $errline in file $errfile";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br /><br />" . "\n\n";
            }
            error_footer();
            exit(1);
            break;

        case E_WARNING:
        case E_USER_WARNING:
            error_header();
            echo '<b>[GameCP Debug] PHP Warning:</b> in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $errstr . '</b><br /><br />' . "\n\n";
            error_footer();
            break;

        case E_NOTICE:
        case E_USER_NOTICE:
            error_header();
            echo '<b>[GameCP Debug] PHP Notice:</b> in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $errstr . '</b><br /><br />' . "\n\n";
            error_footer();
            break;

        default:
            //echo "<b>[Game CP]</b> PHP Unknown: $errstr<br />\n";
            break;
    }

    /* Don't execute PHP internal error handler */
    return true;
} // end of errorHandler()

// ------------------------------------------------
// Writes a cache file
//
// @param string $ contents of the buffer
// @param string $ filename to use when creating cache file
// @return void
// ------------------------------------------------
function writeCache($content, $filename)
{
    global $out, $_SERVER;
    $absolute_path = dirname(__FILE__) . '/';
    if (DIRECTORY_SEPARATOR == '\\') {
        $absolute_path = str_replace('\\', '/', $absolute_path);
    }

    $absolute_path = str_replace('main/', '', $absolute_path);

    $fp = fopen($absolute_path . 'cache/' . $filename, 'w');
    fwrite($fp, $content);
    fclose($fp);
}

// ------------------------------------------------
// Checks for cache files
//
// @param string $ filename of cache file to check for
// @param int $ maximum age of the file in seconds
// @return mixed either the contents of the cache or false
// ------------------------------------------------
function readCache($filename, $expiry)
{
    global $out;
    global $out, $_SERVER;
    $absolute_path = dirname(__FILE__) . '/';
    if (DIRECTORY_SEPARATOR == '\\') {
        $absolute_path = str_replace('\\', '/', $absolute_path);
    }

    $absolute_path = str_replace('main/', '', $absolute_path);

    if (file_exists($absolute_path . 'cache/' . $filename)) {
        if ((time() - $expiry) > filemtime($absolute_path . 'cache/' . $filename)) {
            #echo (time() - $expiry). ' - '.filemtime($absolute_path . 'cache/' . $filename);
            return false;
        }
        $cache = file($absolute_path . 'cache/' . $filename);
        return implode('', $cache);
    }
    return false;
}

// ------------------------------------------------
// Intrepid-Web.NET Heart Beat Function
// When run, the function sends a "heart beat" or "information"
// to intrepid-web.net  with the: server path, name, ip and game cp version
// ------------------------------------------------
function sendHeartBeat()
{
    global $out, $_file_properties, $_SERVER;
    // Data Info
    $data = "server_path=" . $_SERVER["SCRIPT_NAME"] . "&server_ip=" . $_SERVER['SERVER_ADDR'] . "&server_domain=" . $_SERVER['SERVER_NAME'] . "&server_version=" . $_file_properties['version'];
    // Pre-set Vars
    $host = 'www.rf.intrepid-web.net';
    $method = 'GET';
    $path = '/gamecp_v2/heartbeat.php';
    $useragent = true;

    $method = strtoupper($method);

    if ($method == "GET") {
        $path .= '?' . $data;
    }

    if (function_exists("fsockopen")) {
        if ($filePointer = @fsockopen($host, 80, $errorNumber, $errorString, 10)) {

            /*if (!$filePointer) {
                // logEvent('debug', 'Failed opening http socket connection: '.$errorString.' ('.$errorNumber.')<br/>\n');
                return true;
            }*/

            $requestHeader = $method . " " . $path . "  HTTP/1.1\r\n";
            $requestHeader .= "Host: " . $host . "\r\n";
            $requestHeader .= "User-Agent:      Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1) Gecko/20061010 Firefox/2.0\r\n";
            $requestHeader .= "Content-Type: application/x-www-form-urlencoded\r\n";

            if ($method == "POST") {
                $requestHeader .= "Content-Length: " . strlen($data) . "\r\n";
            }

            $requestHeader .= "Connection: close\r\n\r\n";

            if ($method == "POST") {
                $requestHeader .= $data;
            }

            @fwrite($filePointer, $requestHeader);

            $responseHeader = '';
            $responseContent = '';

            do {
                $responseHeader .= @fread($filePointer, 1);
            } while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));

            if (!strstr($responseHeader, "Transfer-Encoding: chunked")) {
                while (!feof($filePointer)) {
                    $responseContent .= @fgets($filePointer, 128);
                }
            } else {
                while ($chunk_length = hexdec(@fgets($filePointer))) {
                    $responseContentChunk = '';
                    // logEventToTextFile('debug', $chunk_length);
                    $read_length = 0;

                    while ($read_length < $chunk_length) {
                        $responseContentChunk .= @fread($filePointer, $chunk_length - $read_length);
                        $read_length = strlen($responseContentChunk);
                    }

                    $responseContent .= $responseContentChunk;
                    @fgets($filePointer);
                }
            }
            // logEventToTextFile('debug', $responseContent);
            $responseContent = chop($responseContent);
            if ($responseContent != "failed") {
                return $responseContent;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// ------------------------------------------------
// Returns true or false
// Checks the users allowed/disallowed permissions
// ------------------------------------------------
function hasPermissions($page = '')
{
    global $out, $config, $admin, $userdata, $isuser, $user_access;

    $return = 0;

    if ($isuser && ($userdata != ''or $page == '')) {
        if (isset($userdata['username']) and isset($userdata['serial'])) {
            $super_admin = explode(",", $admin['super_admin']);

            $page = strtolower($page);
            if (strpos($page, ".php")) {
                $page = substr($page, 0, strrpos($page, '.'));
            }

            if (!in_array($userdata['username'], $super_admin)) {

                if (strpos($user_access['admin_permission'], $page) === FALSE) {
                    $return = 0;
                } else {
                    $return = 1;
                }

            } else {
                $return = 1;
            }

        } else {
            $return = 0;
        }
    } else {
        $return = 0;
    }

    if ($return == 1) {
        return true;
    } else {
        return false;
    }
}

// ------------------------------------------------
// Returns the map "name"
// Check the map id or number from 0-26, ep2.1.7
// ------------------------------------------------
function getMapByCode($map)
{
    if ($map == 0) {
        $map = 'Bell HQ';
    } elseif ($map == 1) {
        $map = 'Cora HQ';
    } elseif ($map == 2) {
        $map = 'Crag Mine';
    } elseif ($map == 3) {
        $map = 'Acc HQ';
    } elseif ($map == 4) {
        $map = 'Neutral BS1';
    } elseif ($map == 5) {
        $map = 'Neutral BS2';
    } elseif ($map == 6) {
        $map = 'Neutral CS1';
    } elseif ($map == 7) {
        $map = 'Neutral CS2';
    } elseif ($map == 8) {
        $map = 'Neutral AS1';
    } elseif ($map == 9) {
        $map = 'Neutral AS2';
    } elseif ($map == 10) {
        $map = 'Platform 01';
    } elseif ($map == 11) {
        $map = 'Sette';
    } elseif ($map == 12) {
        $map = 'Cauldron 01';
    } elseif ($map == 13) {
        $map = 'Elan';
    } elseif ($map == 14) {
        $map = 'Dungeon 00';
    } elseif ($map == 15) {
        $map = 'Transport 01';
    } elseif ($map == 16) {
        $map = 'Dungeon 01';
    } elseif ($map == 17) {
        $map = 'Acc GSD';
    } elseif ($map == 18) {
        $map = 'Bella GSD';
    } elseif ($map == 19) {
        $map = 'Cora GSD';
    } elseif ($map == 20) {
        $map = 'Acc GSP';
    } elseif ($map == 21) {
        $map = 'Bella GSP';
    } elseif ($map == 22) {
        $map = 'Cora GSP';
    } elseif ($map == 23) {
        $map = 'Dungeon 02';
    } elseif ($map == 24) {
        $map = 'Exile Land';
    } elseif ($map == 25) {
        $map = 'Beasts Mountain';
    } elseif ($map == 26) {
        $map = 'Medical Lab';
    } elseif ($map == 27) {
        $map = 'Elven';
    } elseif ($map == 28) {
        $map = 'Dungeon 03';		
    } elseif ($map == 29) {
        $map = 'Medicallab2';
    } elseif ($map == 30) {
        $map = 'Dungeon 04';
    } elseif ($map == 31) {
        $map = 'Olimpus';
    } elseif ($map == 32) {
        $map = 'Toy Land';
    } elseif ($map == 33) {
        $map = 'Factory';
    } elseif ($map == 34) {
        $map = 'Beast Eart';		
    } else {
        $map = 'Unknown';
    }

    return $map;
}

// -------------------------------------------------
// Borrowed from creator at mindcreations dot com
// Gets multi dimension array keys
// -------------------------------------------------
function multiarray_keys($ar)
{
    $keys = array();
    foreach ($ar as $k => $v) {
        $keys[] = $k;
        if (is_array($ar[$k]))
            $keys = array_merge($keys, multiarray_keys($ar[$k]));
    }
    return $keys;
}

// -------------------------------------------------
// Borrowed from PHP.NET Documents
// Since strsts(x,y,true) only exists in 5.3.0
// -------------------------------------------------
function strstr_new($haystack, $needle, $before_needle = FALSE)
{
    //Find position of $needle or abort
    if (($pos = strpos($haystack, $needle)) === FALSE) return FALSE;

    if ($before_needle) return substr($haystack, 0, $pos + strlen($needle));
    else return substr($haystack, $pos);
}

// -------------------------------------------------
// This navigation build was borrowed from phpBB 2.0
// Generates the navigation panel
// -------------------------------------------------
function gamecp_nav($isuser = false)
{
    global $module, $do;
    global $is_superadmin, $user_access, $admin, $userdata, $_file_properties, $out, $gamecp_nav, $script_name, $program_name, $dont_allow, $admin, $config;

    $phpEx = 'php';
    $gamecp_nav  = '';
    $gamecp_nav .= '<ul class="nav navbar-nav">';
    if (!($fileinfo = readCache("fileinfo.cache", 86400))) {
        $fileinfo = "";
        $dir = @opendir("./includes/");

        $setmodules = 1;
        while ($file = @readdir($dir)) {
            if (is_file('./includes/' . $file)) {
                if ($file != "main" or $file != "." or $file != "..") {
                    if (!in_array($file, $dont_allow)) {
                        $this_script = '';
                        include('./includes/' . $file);
                        if (isset($module)) {
                            $keys = multiarray_keys($module);
                            if (!isset($keys[0]) || !isset($keys[1])) {
                                continue;
                            }
                            $fileinfo .= $file . "," . $keys[0] . "," . $keys[1] . "\n";
                            $module = array();
                            $keys = array();
                        }
                    }
                }
            }
        }

        @closedir($dir);
        unset($setmodules);

        writeCache($fileinfo, 'fileinfo.cache');
    }

    $fileinfo = explode("\n", $fileinfo);
    $filelist = '';
    $module = array();

    foreach ($fileinfo as $moduleinfo) {
        $info = explode(",", $moduleinfo);
        $filelist = $info[0] . ",";
        if (!empty($info[1]) && !empty($info[2])) {
            if (preg_match("/^superadmin_.*?\." . $phpEx . "$/", $info[0])) {
                if ($isuser == true && $is_superadmin == true) {
                    $module[$info[1]][$info[2]] = $info[0];
                }
            } elseif ((preg_match("/^admin_.*?\." . $phpEx . "$/", $info[0])) || (preg_match("/^support_.*?\." . $phpEx . "$/", $info[0]))) {
                if ($is_superadmin == true) {
                    $module[$info[1]][$info[2]] = $info[0];
                } elseif ($isuser == true && $user_access != false) {
                    $match = str_replace(".php", "", $info[0]);
                    if (strpos($user_access['admin_permission'], $match) !== FALSE) {
                        $module[$info[1]][$info[2]] = $info[0];
                    }
                }
            } elseif (preg_match("/^user_.*?\." . $phpEx . "$/", $info[0])) {
                if ($isuser == true) {
                    $module[$info[1]][$info[2]] = $info[0];
                }
            } elseif (preg_match("/^all_.*?\." . $phpEx . "$/", $info[0])) {
                $module[$info[1]][$info[2]] = $info[0];
            }
        }
    }

    ksort($module);
    $k = 1;
    while (list($cat, $action_array) = each($module)) {
        $cat = preg_replace("/_/", " ", $cat);

        ksort($action_array);

        $gamecp_nav .= '<li class="dropdown">';
        $gamecp_nav .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $cat . ' <b class="caret"></b></a>';
        $gamecp_nav .= '<ul class="dropdown-menu">';
        while (list($action, $file) = each($action_array)) {
            $action = preg_replace("/_/", " ", $action);
            $this_do = substr($file, 0, -4);
            $active = ($this_do == $do)? true : false;
            $gamecp_nav .= '<li class="'.( ($active == true) ? ' active' : '' ).'"><a href="./' . $script_name . '?do=' . $this_do . '">' . $action . '</a></li>';
        }
        $gamecp_nav .= '</ul>';
        $gamecp_nav .= '</li>';

        $k++;
    }

    $gamecp_nav .= "</ul>";

    $gamecp_nav .= '<ul class="nav navbar-nav pull-right">' . "\n";
    if ($isuser == true) {
        $gamecp_nav .= '<li><a href="./gamecp_logout.php">Logout</a></li>';
    } else {
        $gamecp_nav .= '<li><a href="./' . $script_name . '">Login</a></li>';
        $gamecp_nav .= '<li><a href="./gamecp_register.php">Register</a></li>';
    }
    $gamecp_nav .= "</ul>";

    return true;
}


// -------------------------------------------------
// A static php-based count down script, returns
// time in an array of hours, minutes and seconds
// -------------------------------------------------
function hrs_mins_secs($seconds)
{
    $time['hours'] = floor($seconds / 3600);
    $seconds -= $time['hours'] * 3600;
    $time['minutes'] = floor($seconds / 60);
    $time['seconds'] = $seconds - ($time['minutes'] * 60);

    $time['hours'] = sprintf("%02d", $time['hours']);
    $time['minutes'] = sprintf("%02d", $time['minutes']);
    $time['seconds'] = sprintf("%02d", $time['seconds']);

    return $time;
}


# Construct sub-categories menu
function generate_menu($menu_array, $parent, $options = '', $prepend = array(), $item_cat_id = 0)
{
    //this prevents printing 'ul' if we don't have subcategories for this category
    global $options;
    //use global array variable instead of a local variable to lower stack memory requierment

    $prepend[0] = '';

    foreach ($menu_array as $key => $value) {
        if ($value['parent'] == $parent) {

            $prepend[$value['id']] = $prepend[$parent] . '&nbsp;&nbsp;';

            if ($parent == 0) {
                $add_bg = 'style="background-color: #E7E7E7; font-weight: bold;"';
            } else {
                $add_bg = 'style="background-color: #EBEBEB;"';
            }

            if ($value['id'] == $item_cat_id) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }

            $options .= '<option value="' . $value['id'] . '"' . $add_bg . $selected . '>' . $prepend[$parent] . $value['name'] . '</option>';
            generate_menu($menu_array, $key, $options, $prepend, $item_cat_id);

            //call function again to generate nested list for subcategories belonging to this category
        }
    }
}

// -------------------------------------------------
// This just displays the template data really
// It will be used to phrase more stuff later
// More security will be added
// -------------------------------------------------
function print_outputs($data)
{

    unset($mssql);
    echo stripslashes($data);

}

// -------------------------------------------------
// Special Game CP Template
// Created since we went stand alone!
// -------------------------------------------------
function gamecp_template($template_name)
{
    global $config, $is_superadmin, $_file_properties;

    if ($template_name == '') {
        return false;
    }

    $contents = '';

    $header = './includes/templates/header.html';
    $footer = './includes/templates/footer.html';
    $filename = './includes/templates/' . $template_name . '.html';

    if (file_exists($filename)) {

        if (file_exists($header)) {
            $handle = fopen($header, "rb");
            $contents .= fread($handle, filesize($header)) . "\n";
            fclose($handle);
        } else {
            return "Unable to load the header.html file";
        }

        $handle = fopen($filename, "rb");
        $contents .= fread($handle, filesize($filename)) . "\n";
        fclose($handle);


        $year_next = date("Y") + 2;

        $_file_properties['version'] = substr(str_replace(".", "", $_file_properties['version']), 1);
        $_file_properties['version'] = str_split($_file_properties['version'], 2);
        $_file_properties['version'] = implode(".", $_file_properties['version']);
        $a = array('/^0(\d+)/', '/\.0(\d+)/');
        $b = array('\1', '.\1');
        $_file_properties['version'] = preg_replace($a, $b, $_file_properties['version']);

        if (isset($config['security_show_version']) && $config['security_show_version'] == '1') {
            $version_text = 'v2.3.0.1' . $_file_properties['version'];
        } else {
            $version_text = '';
        }

        $contents .= '<footer><div class="container-fluid">';
        $contents .= 'Game Control Panel ' . $version_text . '<br>Copyright &copy; 2018 - ' . $year_next . ', '.intrepidweb();
        $contents .= '</div></footer>';

          if (((time() + 604800) >= ($license_expire_time)) && $is_superadmin) {
            $extime = hrs_mins_secs($license_expire_time - time());
            
        }

        if (file_exists($footer)) {
            $handle = fopen($footer, "rb");
            $contents .= fread($handle, filesize($footer));
            fclose($handle);
        } else {
            return "Unable to load the footer.html file";
        }

        return addslashes($contents);
    } else {
        return "Invalid load of template";
    }
}

// -------------------------------------------------
// Get last updated time of table
// -------------------------------------------------
function table_lastupdatetime($table_name, $database)
{
    connectuserdb();
    $sql = "SELECT last_user_update
			FROM sys.dm_db_index_usage_stats
			WHERE database_id = DB_ID('" . antiject($database) . "') AND OBJECT_ID=OBJECT_ID('" . antiject($table_name) . "')";
    if (!($lastupdate_result = mssql_query($sql))) {
        $return = false;
        die("Unable to select last update time!");
    } elseif ($timenfo = mssql_fetch_row($lastupdate_result)) {
        $return = strtotime(preg_replace('/:[0-9][0-9][0-9]/', '', $timenfo[0]));
    } else {
        $return = false;
    }
    mssql_free_result($lastupdate_result);

    return $return;
}

// -------------------------------------------------
// Allow the browser to Cache Data
// Condtional GET
// -------------------------------------------------
function checkBrowserCache($last_modified, $identifier)
{
    global $_SERVER;

    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = (getenv("HTTP_IF_MODIFIED_SINCE") != '') ? getenv("HTTP_IF_MODIFIED_SINCE") : false;
    $_SERVER['HTTP_IF_NONE_MATCH'] = (getenv("HTTP_IF_NONE_MATCH") != '') ? getenv("HTTP_IF_NONE_MATCH") : false;

    $etag = '"' . md5($last_modified . $identifier) . '"';
    $client_etag = @ $_SERVER['HTTP_IF_NONE_MATCH'] ? trim(@ $_SERVER['HTTP_IF_NONE_MATCH']) : false;
    $client_last_modified_date = @$_SERVER['HTTP_IF_MODIFIED_SINCE'] ? trim(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
    $client_last_modified = date('D, d M Y H:i:s \G\M\T', strtotime($client_last_modified_date));

    $etag_match = true;

    if (!$client_last_modified || !$client_etag) {
        $etag_match = false;
    }

    if ($etag_match && $client_last_modified > $last_modified) {
        $etag_match = false;
    }

    if ($etag_match && $client_etag != $etag) {
        $etag_match = false;
    }

    header('Cache-Control:public, must-revalidate', true);
    header('Pragma:cache', true);
    header('ETag: ' . $etag);

    if ($etag_match) {
        header('HTTP/1.0 304 Not Modified');
        die();
    }

    header('Last-Modified:' . date('D, d M Y H:i:s \G\M\T', $last_modified));
}


/**
 * Fetch the contents of a remote fle.
 * Copyright (C) myBB (mybboard.net)
 *
 * @param string The URL of the remote file
 * @return string The remote file contents.
 */
function fetch_remote_file($url, $post_data = array())
{
    $post_body = '';
    if (!empty($post_data)) {
        $post_body = http_build_query($post_data);
    }

    if (function_exists("curl_init")) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($post_body)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    } else if (function_exists("fsockopen")) {
        $url = @parse_url($url);

        if (!isset($url['host'])) {
            return false;
        }
        if (!isset($url['port'])) {
            $url['port'] = 80;
        }
        if (!isset($url['path'])) {
            $url['path'] = "/";
        }
        if (isset($url['query'])) {
            $url['path'] .= "?{$url['query']}";
        }
        $fp = @fsockopen($url['host'], $url['port'], $error_no, $error, 5);
        @stream_set_timeout($fp, 5);
        if (!$fp) {
            return false;
        }
        $headers = array();
        if (!empty($post_body)) {
            $headers[] = "POST {$url['path']} HTTP/1.0";
            $headers[] = "Content-Length: " . strlen($post_body);
            $headers[] = "Content-Type: application/x-www-form-urlencoded";
        } else {
            $headers[] = "GET {$url['path']} HTTP/1.0";
        }

        $headers[] = "Host: {$url['host']}";
        $headers[] = "Connection: Close";
        $headers[] = "\r\n";

        if (!empty($post_body)) {
            $headers[] = '&' . $post_body;
        }

        $headers = implode("\r\n", $headers);
        if (!@fwrite($fp, $headers)) {
            return false;
        }
        $data = '';
        while (!feof($fp)) {
            $data .= fgets($fp, 12800);
        }
        fclose($fp);
        $data = explode("\r\n\r\n", $data, 2);
        return $data[1];
    } /*else if(empty($post_data))
    {
        return @implode("", @file($url));
    }*/
    else {
        return false;
    }
}

function checkIP($ip_to_match, $ip_array)
{

    // make sure this is an array before we use foreach
    if (is_array($ip_array)) {

        // loop through ip array
        foreach ($ip_array as $ip) {

            // first test if there is a match, then test if the match starts at the beginning
            if (strpos($ip_to_match, $ip) === 0) {
                return true;
            }

        }
    }

    return false;
}

# Send e-mail function
function sendEmail($to_email, $subject, $message)
{
    global $config;

    if (isset($config['gamecp_smtp_enable']) && $config['gamecp_smtp_enable'] == 1) {
        $mail = new PHPMailer();

        $mail->SetLanguage('en', './includes/main/');
        $mail->SMTPAuth = true;
        if ($config['gamecp_smtp_enable_ssl'] == 1) {
            $mail->SMTPSecure = "ssl";
        }
        $mail->Host = $config['gamecp_smtp_server'];
        $mail->Port = $config['gamecp_smtp_port'];

        $mail->Username = $config['gamecp_smtp_username'];
        $mail->Password = $config['gamecp_smtp_password'];

        $mail->From = $config['lostpass_email'];
        $mail->FromName = $config['server_name'];

        $mail->Subject = $subject;

        $mail->Body = $message;

        $mail->AddAddress($to_email, $subject);

        return $mail->Send();
    } else {
        return @mail($to_email, $subject, $message);
    }
}


function register_account($username, $password, $email, $user_ip, &$success, &$message)
{
    global $lang, $_SERVER;
    global $userdata;
    global $gamecp_dbconnect, $data_dbconnect, $user_dbconnect, $items_dbconnect;

    # Trim our data..
    $username = trim($username);
    $password = trim($password);
    $email = trim($email);
    $user_ip = trim($user_ip);

    # UserDB
    connectuserdb();

    # LU Account SQL
    $lu_sql = sprintf("INSERT INTO %s (id,password,BCodeTU,Email,accounttype,birthdate) VALUES ((CONVERT (binary,'%s')),(CONVERT (binary,'%s')),1,'%s',0,'2011-11-11 00:00:00')", TABLE_LUACCOUNT, $username, $password, $email);
    if (!($lu_query = mssql_query($lu_sql))) {
        $success = false;
        $message[] = "Failed to insert the RF Account data into the database. Contact an administrator.";
        if (isset($config['security_enable_debug']) && $config['security_enable_debug'] == 1) {
            $message[] = "SQL: " . mssql_get_last_message();
        }
    }

    if ($success == true) {
        $user_sql = sprintf("INSERT INTO tbl_UserAccount (id, createip) VALUES(convert(binary,'%s'), '%s')", $username, $user_ip);
        if (!($user_query = mssql_query($user_sql))) {
            $success = false;
            $message[] = "Failed to insert the User Account data into the database. Contact an administrator.";
            if (isset($config['security_enable_debug']) && $config['security_enable_debug'] == 1) {
                $message[] = "SQL: " . mssql_get_last_message();
            }
            # Delete the LU Info
            $delete_sql = sprintf("DELETE FROM %s WHERE id = convert(binary,'%s')", TABLE_LUACCOUNT, $username);
            $delete = mssql_query($delete_sql);

        } else {
            # GameCPDB
            connectgamecpdb();

            # Delete the confirm email data now
            $delete_sql = sprintf("DELETE FROM %s WHERE username = '%s'", TABLE_CONFIRM_EMAIL, $username);
            $delete = mssql_query($delete_sql);

            # Create registration log
            write_reg_log($username, time(), $userdata['ip'], $_SERVER["HTTP_USER_AGENT"]);

            # Success!
            $success = true;
            $message[] = _l('successfully_registered_account_short');
        }
    }

    return array($success, $message);
}

function get_url($exclude_script = true)
{
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80" && preg_match('/:/', $_SERVER["HTTP_HOST"]) === false) {
        $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER['SCRIPT_NAME'];
    } else {
        $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER['SCRIPT_NAME'];
    }

    if ($exclude_script) {
        $pageURL = strstr($pageURL, basename($_SERVER['PHP_SELF']), true);
    }

    return $pageURL;
}

function message($message = '',  $title = 'Message', $type='success')
{
    $return  = '';
    $return .= '<div class="alert alert-'.$type.'">';
    $return .= '<h4>'.$title.'</h4>';
    $return .= '<p>'.$message.'</p>';
    $return .= '</div>';

    return $return;
}

function sql_error($message = '')
{
    $return  = '';
    $return .= '<div class="alert alert-danger">';
    $return .= '<h4>SQL ERROR</h4>';
    $return .= '<p>'.$message.'</p>';
    $return .= '<p>Error Message: '.mssql_get_last_message().'</p>';
    $return .= '</div>';

    return $return;
}

function intrepidweb()
{
    return '<a href="http://www.rfonline.epizy.com/" target = "_blank">RF Epizy</a>';
}

function random_float($min, $max)
{
    return ($min + lcg_value() * (abs($max - $min)));
}

function log_vote($account_serial, $ip, $gained, $total)
{
    global $gamecp_dbconnect;

    $time = time();

    $insert_log = "INSERT INTO gamecp_vote_log (log_account_serial, log_time, log_ip, log_points_gained, log_total_points) VALUES ('$account_serial', '$time', '$ip', '$gained', '$total')";
    if (!($log_result = mssql_query($insert_log, $gamecp_dbconnect))) {
        #echo "EXIT ".mssql_get_last_message();
        #exit;
    }
}

// ------------------------------------------------
// Set variable, used by {@link request_var the request_var function}
//
// @access private
// @copyright phpBB Group - phpBB 3
// ------------------------------------------------
function set_var(&$result, $var, $type, $multibyte = false)
{
    settype($var, $type);
    $result = antiject($var);

    if ($type == 'string')
    {
        $result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result), ENT_COMPAT, 'UTF-8'));

        if (!empty($result))
        {
            // Make sure multibyte characters are wellformed
            if ($multibyte)
            {
                if (!preg_match('/^./u', $result))
                {
                    $result = '';
                }
            }
            else
            {
                // no multibyte, allow only ASCII (0-127)
                $result = preg_replace('/[\x80-\xFF]/', '?', $result);
            }
        }

        $result = (STRIP) ? stripslashes($result) : $result;;
    }
}

// ------------------------------------------------
// Used to get passed variable
// @copyright phpBB Group - phpBB 3
// ------------------------------------------------
function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
    if (!$cookie && isset($_COOKIE[$var_name]))
    {
        if (!isset($_GET[$var_name]) && !isset($_POST[$var_name]))
        {
            return (is_array($default)) ? array() : $default;
        }
        $_REQUEST[$var_name] = isset($_POST[$var_name]) ? antiject($_POST[$var_name]) : antiject($_GET[$var_name]);
    }

    if (!isset($_REQUEST[$var_name]) || (is_array($_REQUEST[$var_name]) && !is_array($default)) || (is_array($default) && !is_array($_REQUEST[$var_name])))
    {
        return (is_array($default)) ? array() : $default;
    }

    $var = $_REQUEST[$var_name];
    if (!is_array($default))
    {
        $type = gettype($default);
    }
    else
    {
        list($key_type, $type) = each($default);
        $type = gettype($type);
        $key_type = gettype($key_type);
        if ($type == 'array')
        {
            reset($default);
            $default = current($default);
            list($sub_key_type, $sub_type) = each($default);
            $sub_type = gettype($sub_type);
            $sub_type = ($sub_type == 'array') ? 'NULL' : $sub_type;
            $sub_key_type = gettype($sub_key_type);
        }
    }

    if (is_array($var))
    {
        $_var = $var;
        $var = array();

        foreach ($_var as $k => $v)
        {
            set_var($k, $k, $key_type);
            if ($type == 'array' && is_array($v))
            {
                foreach ($v as $_k => $_v)
                {
                    if (is_array($_v))
                    {
                        $_v = null;
                    }
                    set_var($_k, $_k, $sub_key_type);
                    set_var($var[$k][$_k], $_v, $sub_type, $multibyte);
                }
            }
            else
            {
                if ($type == 'array' || is_array($v))
                {
                    $v = null;
                }
                set_var($var[$k], $v, $type, $multibyte);
            }
        }
    }
    else
    {
        set_var($var, $var, $type, $multibyte);
    }

    return $var;
}


// ------------------------------------------------
// Convert item db code to id/type/slot or make a db code
// @param: db code # or item id, input type, item type, slot
// @result: returns either a db number or an array of the item info
// ------------------------------------------------
function gen_item_code($input, $type = false, $slot = false) {
    $item = array();
    if($type === false) {
        $hex = dechex($input);
        $len = strlen($hex);

        if($len < 4)
        {
            $hex = "0{$hex}";
            ++$len;
        }

        $item['id'] = hexdec(substr($hex,0,($len-4)));
        $item['type'] = hexdec(substr($hex,($len-4),2));
        $item['slot'] = hexdec(substr($hex,$len-2));
        return $item;
    } elseif($type !== false && $slot !== false) {
        return 65536*$input+($type*256+$slot);
    } else {
        return false;
    }
}


// ------------------------------------------------
// Convert db upgrade codes , or the other way around
// @param: convert/make, input
// @result: images or dbode
// ------------------------------------------------
function gen_item_upgrade($input, $upgrade_talics = false) {

    if(!$upgrade_talics) {
        $u_value = dechex($input);
        $u_value = strrev($u_value);
        $u_value = preg_split('//', $u_value, -1, PREG_SPLIT_NO_EMPTY);
        array_pop($u_value);
        $u_value = array_map('hexdec',$u_value);
    } else {
        $u_value = explode(",", $input);
        $total_slots = sizeof($u_value);
        $u_value = array_map('dechex',$u_value);
        $u_value = array_reverse($u_value);
        $u_value = dechex($total_slots).implode("",$u_value);
        $u_value = hexdec($u_value);
    }

    return $u_value;
}


function getShopRace($id) {
    switch($id) {
        case 0:
            return "Any Race";
        case 1:
            return "Bellato";
        case 2:
            return "Cora";
        case 3:
            return "Accretia";
        case 4:
            return "Bellato &amp; Cora";
        default:
            return "Unknown";
    }
}

/**
 * Translation
 * @return string
 */
function _l()
{
    global $lang;

    $numargs = func_num_args();
    $args = func_get_args();

    $string = trim(array_shift($args));

    if (isset($lang[$string])) {
        return (!empty($args)) ? trim(vsprintf($lang[$string], $args)) : trim($lang[$string]);
    } else {
        return (!empty($args)) ? trim(vsprintf($string, $args)) : trim($string);
    }
}