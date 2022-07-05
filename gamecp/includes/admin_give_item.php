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
    $module[_l('Support')][_l('Give Item')] = $file;
    return;
}

$lefttitle = _l('Server Admin - Give Item');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        # Global variables
        if (isset($_POST['page']) || isset($_GET['page'])) {
            $page = (isset($_POST['page'])) ? $_POST['page'] : $_GET['page'];
        } else {
            $page = '';
        }

        // Get our user entered values
        $character_serial = (isset($_POST['character_serial']) || isset($_GET['character_serial'])) ? ((isset($_POST['character_serial'])) ? $_POST['character_serial'] : $_GET['character_serial']) : '';
        $character_serial = (is_int((int)$character_serial)) ? antiject((int)$character_serial) : '';

        $character_name = (isset($_POST['character_name']) || isset($_GET['character_name'])) ? ((isset($_POST['character_name'])) ? $_POST['character_name'] : $_GET['character_name']) : '';
        $character_name = trim(antiject($character_name));

        # We will display the search HTML here since it will be 'globally displayed'
        $out .= '<form method="GET" action="' . $script_name . '?do=' . $_GET['do'] . '">' . "\n";
        $out .= '<table class="tborder" cellpadding="3" cellspacing="1" border="0">' . "\n";
        $out .= '    <tr>' . "\n";
        $out .= '        <td class="thead" colspan="2" style="padding: 4px;"><b>Search for a character</b></td>' . "\n";
        $out .= '    </tr>' . "\n";
        $out .= '    <tr>' . "\n";
        $out .= '        <td class="alt1">Character Serial:</td>';
        $out .= '        <td class="alt2"><input type="text" class="form-control" name="character_serial" value="' . (($character_serial != 0) ? $character_serial : '') . '"/></td>' . "\n";
        $out .= '    </tr>' . "\n";
        $out .= '    <tr>' . "\n";
        $out .= '        <td class="alt1">Character Name:</td>';
        $out .= '        <td class="alt2"><input type="text" class="form-control" name="character_name"  value="' . $character_name . '"/></td>' . "\n";
        $out .= '    </tr>' . "\n";
        $out .= '    <tr>' . "\n";
        $out .= '        <td colspan="2"><input type="hidden" name="page" value="useritems" /><input type="hidden" name="do" value="' . $_GET['do'] . '" /><input type="submit"  class="btn btn-default" value="Search" name="submit" /></td>' . "\n";
        $out .= '    </tr>' . "\n";
        $out .= '</table>' . "\n";
        $out .= '</form>' . "\n";

        # Start displaying our pages
        if ($page == '') {
            $out .= '<p style="text-align: center; font-weight: bold;">Please search for a character name or serial above</p>';
        } elseif ($page == 'useritems') {

            // Set our exit variable
            $page_exit = false;

            // Ensure that at least 1 of our variables are not empty
            if ($character_serial == 0 && $character_name == "") {
                $page_exit = true;
                $out .= '<p style="text-align: center; font-weight: bold;">Sorry, we require either a character serial or name to be entered</p>';
            }

            // Now check for a page exit and move on
            if (!$page_exit) {

                // Check if name exists or convert serial
                connectdatadb();
                $sql_search = ($character_serial != 0) ? "Serial = '" . $character_serial . "'" : "Name = '" . $character_name . "'";
                $sql_charserial = "SELECT TOP 1 Name, Serial FROM tbl_base WHERE $sql_search";

                if (!($result_charserial = mssql_query($sql_charserial))) {
                    $page_exit = true;
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL ERROR! Cannot check character serial or name</p>';
                    if ($config['security_enable_debug'] == 1) {
                        mssql_free_result($result_charserial);
                        die("DEBUG ON! Problems while trying to get the characer info" . "<br/>\n" . "SQL DEBUG: " . mssql_get_last_message());
                    }
                } else {
                    $char_info = mssql_fetch_array($result_charserial);

                    $character_serial = $char_info['Serial'];
                    $character_name = antiject($char_info['Name']);
                }
                mssql_free_result($result_charserial);

                // Check to see that the character exists!
                if ($character_serial == '') {
                    $page_exit = true;
                    $out .= '<p style="text-align: center; font-weight: bold;">Sorry, we are unable to the character</p>';
                }

                // Lets just display the character name we are ediitn
                $out .= '<h3>Character name: ' . $character_name . '</h3>';

                // Move forward only if we got no errors!
                if (!$page_exit) {
                    // Okay, now first get the items that have been delivered
                    $sql_itemlist = "SELECT top 100 nSerial, nItemCode_K, nItemCode_D, nItemCode_U, dtGiveDate, dtTakeDate from tbl_ItemCharge where nAvatorSerial = '" . $character_serial . "' and DCK = 0 order by dtGiveDate DESC";
                    if (!($result_itemlist = mssql_query($sql_itemlist))) {
                        $page_exit = true;
                        $out .= '<p style="text-align: center; font-weight: bold;">SQL ERROR! Cannot get item list (undelivered) for this character</p>';
                        if ($config['security_enable_debug'] == 1) {
                            mssql_free_result($result_charserial);
                            die("DEBUG ON! Cannot get (undelivered) item list for this character" . "<br/>\n" . "SQL DEBUG: " . mssql_get_last_message());
                        }
                    }

                    // Display our table first!
                    $out .= '<table class="table table-bordered">' . "\n";
                    $out .= '    <tr>' . "\n";
                    $out .= '        <td class="alt2" colspan="6">Waiting Item List</td>' . "\n";
                    $out .= '    </tr>' . "\n";
                    $out .= '    <tr>' . "\n";
                    $out .= '        <td class="thead" nowrap>Serial</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Item Name</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Amount</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Upgrade</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Give Date</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Take Date</td>' . "\n";
                    $out .= '    </tr>' . "\n";

                    // Display only items delivered
                    if (mssql_num_rows($result_itemlist) > 0) {
                        connectitemsdb();
                        while ($urow = mssql_fetch_array($result_itemlist)) {

                            // Break down DB Item Code
                            $k_value = $urow['nItemCode_K'];
                            $slot = 0;
                            $kn = 0;
                            for ($n = 9; $n < $item_tbl_num; $n++) {
                                $item_id = ($k_value - ($n * (256 + ($slot + 1)))) / 65536;
                                if ($item_id == $k_value) {
                                    $kn = $n;
                                }
                            }
                            $item_id = ceil($item_id);
                            $kn = floor(($k_value - ($item_id * 65536)) / 256);

                            // Get item name/code
                            $items_query = mssql_query("SELECT item_code, item_id, item_name FROM " . GetItemTableName($kn) . " WHERE item_id = '$item_id'", $items_dbconnect);
                            $items = mssql_fetch_array($items_query);

                            if ($items['item_name'] != "") {
                                $item_name = str_replace("_", " ", $items['item_name']);
                            } else {
                                $item_name = "Not found in DB - " . $item_id . ":" . $kn;
                            }
                            $item_code = $items['item_code'];

                            // Free Result
                            @mssql_free_result($items_query);

                            // Get upgrade data
                            $base_code = 268435455;
                            $u_value = $urow['nItemCode_U'];
                            $item_slots = $u_value;
                            $item_slots = $item_slots - $base_code;
                            $item_slots = $item_slots / ($base_code + 1);
                            $upgrades = "";
                            $ceil_slots = ceil($item_slots);
                            $slots_code = ($base_code + (($base_code + 1) * $ceil_slots));
                            $slots = $ceil_slots;

                            if ($ceil_slots > 0) {
                                $u_value = dechex($u_value);
                                $item_ups = $u_value[0];
                                $slots = 0;
                                $u_value = strrev($u_value);
                                for ($m = 0; $m < $item_ups; $m++) {
                                    $talic_id = hexdec($u_value[$m]);
                                    $upgrades .= '<img src="./includes/templates/assets/images/talics/t-' . sprintf("%02d", ($talic_id)) . '.png" width="12"/>';
                                }

                                $bgc = ' style="background-color: #10171f;"';

                            } else {
                                $upgrades = "";
                                $bgc = "";
                            }

                            $out .= '    <tr>' . "\n";
                            $out .= '        <td class="alt2" nowrap>' . $urow['nSerial'] . '</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . $item_name . ' (' . $item_code . ')</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . $urow['nItemCode_D'] . '</td>' . "\n";
                            $out .= '        <td class="alt1"' . $bgc . ' nowrap>' . $upgrades . '</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . date("d/m/Y h:i A", strtotime(preg_replace('/:[0-9][0-9][0-9]/', '', $urow['dtGiveDate']))) . '</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . date("d/m/Y h:i A", strtotime(preg_replace('/:[0-9][0-9][0-9]/', '', $urow['dtTakeDate']))) . '</td>' . "\n";
                            $out .= '    </tr>' . "\n";
                        }
                    } else {
                        $out .= '    <tr>' . "\n";
                        $out .= '        <td class="alt1" colspan="6" style="text-align: center;">No items waiting for delivery for this character</td>' . "\n";
                        $out .= '    </tr>' . "\n";
                    }
                    $out .= '</table>' . "\n";
                    mssql_free_result($result_itemlist);

                    $out .= '<br/>' . "\n";
                    // Now display the fields that allows the admin to give items
                    $out .= '<form action="?do=' . $_GET['do'] . '" method="post" id="form1">' . "\n";
                    $out .= '<table id="myTable" class="table table-bordered">' . "\n";
                    $out .= '    <tr>' . "\n";
                    $out .= '        <td class="thead" nowrap>#</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Item Code</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Amount</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Upgrade</td>' . "\n";
                    $out .= '        <td class="thead" nowrap>Rental Period</td>' . "\n";
                    $out .= '        <td class="thead" nowrap></td>' . "\n";
                    $out .= '    </tr>' . "\n";
                    $out .= '    <input type="hidden" id="id" value="1" />' . "\n";;
                    $out .= '    <tr id="row0">' . "\n";
                    $out .= '        <td class="alt2" nowrap>0</td>' . "\n";
                    $out .= '        <td class="alt1" nowrap><input type="hidden" class="item_code_ajax" size="5" name="item_code[]" onchange="check_itemname(\'item_code0\', \'results_div0\');" id="item_code0" /></td>' . "\n";
                    $out .= '        <td class="alt1" nowrap><input type="text" class="form-control" size="1" name="item_amount[]" value="0"/></td>' . "\n";
                    $out .= '        <td class="alt1" nowrap><select class="form-control"name="item_ups[]"><option value="0">+0</option><option value="1">+1</option><option value="2">+2</option><option value="3">+3</option><option value="4">+4</option><option value="5">+5</option><option value="6">+6</option><option value="7">+7</option></select>/<select class="form-control"name="item_slots[]"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option></select> <select class="form-control"name="item_talic[]"><option value="1">No Talic</option><option value="2">Rebirth</option><option value="3">Mercy</option><option value="4">Grace</option><option value="5">Glory</option><option value="6">Guard</option><option value="7">Belief</option><option value="8">Sacred Flame</option><option value="9">Wisdom</option><option value="10">Favor</option><option value="11">Hatred</option><option value="12">Chaos</option><option value="13">Darkness</option><option value="14">Destruction</option><option value="15">Ignorant</option></select></td>' . "\n";
                    $out .= '        <td class="alt1" nowrap><input type="text" class="form-control" size="5" name="item_rental_time[]" value="0"/></td>' . "\n";
                    $out .= '        <td class="alt1" nowrap></td>' . "\n";
                    $out .= '    </tr>' . "\n";
                    $out .= '    <tr id="last">' . "\n";
                    $out .= '        <td colspan="5"><input type="hidden" name="page" value="give_item" /><input type="hidden" name="character_serial" value="' . $character_serial . '" /><input type="submit"  class="btn btn-default" value="Submit" name="submit"></td>' . "\n";
                    $out .= '        <td style="text-align: right;" nowrap><a href="#" onClick="addFormField(); return false;">Add More Items</a></td>' . "\n";
                    $out .= '    </tr>' . "\n";
                    $out .= '</table>' . "\n";
                    $out .= '</form>' . "\n";
                    $out .= '<br/>' . "\n";

                    // Okay, now get the items that have already been delivered/used
                    connectdatadb();
                    $sql_itemlist = "SELECT top 32 nSerial, nItemCode_K, nItemCode_D, nItemCode_U, dtGiveDate, dtTakeDate from tbl_ItemCharge where nAvatorSerial = '" . $character_serial . "' and DCK = 1 order by dtGiveDate DESC";
                    if (!($result_itemlist = mssql_query($sql_itemlist))) {
                        $page_exit = true;
                        $out .= '<p style="text-align: center; font-weight: bold;">SQL ERROR! Cannot get item list (undelivered) for this character</p>';
                        if ($config['security_enable_debug'] == 1) {
                            mssql_free_result($result_charserial);
                            die("DEBUG ON! Cannot get (undelivered) item list for this character" . "<br/>\n" . "SQL DEBUG: " . mssql_get_last_message());
                        }
                    }

                    // Display our table first!
                    $out .= '<table class="table table-bordered">' . "\n";
                    $out .= '    <tr>' . "\n";
                    $out .= '        <td class="alt2" colspan="6">Delivered Item List</td>' . "\n";
                    $out .= '    </tr>' . "\n";
                    $out .= '    <tr>' . "\n";
                    $out .= '        <td class="thead">Serial</td>' . "\n";
                    $out .= '        <td class="thead">Item Name</td>' . "\n";
                    $out .= '        <td class="thead">Amount</td>' . "\n";
                    $out .= '        <td class="thead">Upgrade</td>' . "\n";
                    $out .= '        <td class="thead">Give Date</td>' . "\n";
                    $out .= '        <td class="thead">Take Date</td>' . "\n";
                    $out .= '    </tr>' . "\n";

                    // Display only items delivered
                    if (mssql_num_rows($result_itemlist) > 0) {
                        connectitemsdb();
                        while ($urow = mssql_fetch_array($result_itemlist)) {

                            // Break down DB Item Code
                            $k_value = $urow['nItemCode_K'];
                            $slot = 0;
                            $kn = 0;
                            for ($n = 9; $n < $item_tbl_num; $n++) {
                                $item_id = ($k_value - ($n * (256 + ($slot + 1)))) / 65536;
                                if ($item_id == $k_value) {
                                    $kn = $n;
                                }
                            }
                            $item_id = ceil($item_id);
                            $kn = floor(($k_value - ($item_id * 65536)) / 256);

                            // Get item name/code
                            $items_query = mssql_query("SELECT item_code, item_id, item_name FROM " . GetItemTableName($kn) . " WHERE item_id = '$item_id'", $items_dbconnect);
                            $items = mssql_fetch_array($items_query);

                            if ($items['item_name'] != "") {
                                $item_name = str_replace("_", " ", $items['item_name']);
                            } else {
                                $item_name = "Not found in DB - " . $item_id . ":" . $kn;
                            }
                            $item_code = $items['item_code'];

                            // Free Result
                            @mssql_free_result($items_query);

                            // Get upgrade data
                            $base_code = 268435455;
                            $u_value = $urow['nItemCode_U'];
                            $item_slots = $u_value;
                            $item_slots = $item_slots - $base_code;
                            $item_slots = $item_slots / ($base_code + 1);
                            $upgrades = "";
                            $ceil_slots = ceil($item_slots);
                            $slots_code = ($base_code + (($base_code + 1) * $ceil_slots));
                            $slots = $ceil_slots;

                            if ($ceil_slots > 0) {
                                $u_value = dechex($u_value);
                                $item_ups = $u_value[0];
                                $slots = 0;
                                $u_value = strrev($u_value);
                                for ($m = 0; $m < $item_ups; $m++) {
                                    $talic_id = hexdec($u_value[$m]);
                                    $upgrades .= '<img src="./includes/templates/assets/images/talics/t-' . sprintf("%02d", ($talic_id)) . '.png" width="12"/>';
                                }

                                $bgc = ' style="background-color: #10171f;"';

                            } else {
                                $upgrades = "";
                                $bgc = "";
                            }

                            $out .= '    <tr>' . "\n";
                            $out .= '        <td class="alt2" nowrap>' . $urow['nSerial'] . '</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . $item_name . ' (' . $item_code . ')</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . $urow['nItemCode_D'] . '</td>' . "\n";
                            $out .= '        <td class="alt1"' . $bgc . ' nowrap>' . $upgrades . '</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . date("d/m/Y h:i A", strtotime(preg_replace('/:[0-9][0-9][0-9]/', '', $urow['dtGiveDate']))) . '</td>' . "\n";
                            $out .= '        <td class="alt1" nowrap>' . date("d/m/Y h:i A", strtotime(preg_replace('/:[0-9][0-9][0-9]/', '', $urow['dtTakeDate']))) . '</td>' . "\n";
                            $out .= '    </tr>' . "\n";
                        }
                    } else {
                        $out .= '    <tr>' . "\n";
                        $out .= '        <td class="alt1" colspan="6" style="text-align: center;">There are no items charged and delivered for this character</td>' . "\n";
                        $out .= '    </tr>' . "\n";
                    }
                    $out .= '</table>' . "\n";
                    mssql_free_result($result_itemlist);
                }

            }

        } elseif ($page == 'give_item') { #

            // Set our exit var
            $page_exit = false;
            $process_exit = false;

            // We also need this, for our error messages!
            $pexit_message = '';

            // And this for our upgrade usage
            $base_code = 268435455;

            // We already got our character_serial from the top of the file
            // No need to re-do it!

            // Get our item data. Remember, these are arrays coming in, no antiject yet - but we can walk through them :S
            $item_code_x = (isset($_POST['item_code'])) ? $_POST['item_code'] : '';
            $item_amount_x = (isset($_POST['item_amount'])) ? $_POST['item_amount'] : '';
            $item_ups_x = (isset($_POST['item_ups'])) ? $_POST['item_ups'] : '';
            $item_slots_x = (isset($_POST['item_slots'])) ? $_POST['item_slots'] : '';
            $item_talic_x = (isset($_POST['item_talic'])) ? $_POST['item_talic'] : '';
            $item_rental_time_x = (isset($_POST['item_rental_time'])) ? $_POST['item_rental_time'] : '';

            // Okay, fist we'll check to make sure we have data!
            if ($item_code_x == "" || $item_amount_x == "" || $item_amount_x == "" || $item_ups_x == "" || $item_slots_x == "" || $item_talic_x == "" || $item_rental_time_x == "") {
                $page_exit = true;
                $out .= '<p style="text-align: center; font-weight: bold;">Sorry, you have left a field empty</p>';
            }

            // Before we go on, check to make sure our character serial is legit!
            connectdatadb();
            $char_sql = "SELECT Name FROM tbl_base WHERE Serial = '$character_serial'";
            if (!($char_result = mssql_query($char_sql))) {
                $page_exit = true;
                $out .= '<p style="text-align: center; font-weight: bold;">SQL ERROR! Cannot get character data/p>';
                if ($config['security_enable_debug'] == 1) {
                    mssql_free_result($result_charserial);
                    die("DEBUG ON! Cannot get character data with provided serial" . "<br/>\n" . "SQL DEBUG: " . mssql_get_last_message());
                }
            }
            if (mssql_num_rows($char_result) <= 0) {
                $page_exit = true;
                $out .= '<p style="text-align: center; font-weight: bold;">Sorry, the character serial (#' . $character_serial . ') you have selected does not exist!</p>';
            }

            // Now we must ensure that we have _all_ data, and that 1 isn't missing
            $itemcode_count = count($item_code_x);
            $itemamount_count = count($item_amount_x);
            $itemups_count = count($item_ups_x);
            $itemslots_count = count($item_slots_x);
            $itemtalic_count = count($item_talic_x);
            $itemrentaltime_count = count($item_rental_time_x);

            if ($itemcode_count != $itemamount_count || $itemcode_count != $itemups_count || $itemcode_count != $itemslots_count || $itemcode_count != $itemtalic_count || $itemcode_count != $itemrentaltime_count) {
                $page_exit = true;
                $out .= '<p style="text-align: center; font-weight: bold;">Error! Seems you are missing a field somewhere (firefox html glitch?)</p>';
            }

            // Now if we got no errors, lets move along
            if (!$page_exit) {

                // Because all the counts match, we will juts use our base count from itemcode_count
                for ($i = 0; $i < $itemcode_count; $i++) {

                    // Lets split these up into their individuals
                    $item_code = (!empty($item_code_x[$i])) ? antiject($item_code_x[$i]) : '';
                    $item_amount = (!empty($item_amount_x[$i]) && is_int((int)$item_amount_x[$i])) ? antiject($item_amount_x[$i]) : 0;
                    $item_ups = (!empty($item_ups_x[$i]) && is_int((int)$item_ups_x[$i])) ? antiject($item_ups_x[$i]) : 0;
                    $item_slots = (!empty($item_slots_x[$i]) && is_int((int)$item_slots_x[$i])) ? antiject($item_slots_x[$i]) : 0;
                    $item_talic = (!empty($item_talic_x[$i]) && is_int((int)$item_talic_x[$i])) ? antiject($item_talic_x[$i]) : 1;
                    $item_rental_time = (!empty($item_rental_time_x[$i]) && is_int((int)$item_rental_time_x[$i])) ? antiject($item_rental_time_x[$i]) : 0;

                    // And we again check to see that they are not empty!
                    if ($item_code == "" && $item_amount == "" && $item_ups == "" && $item_slots == "" && $item_talic == "" && $item_rental_time == "") {
                        $page_exit = true;
                        $out .= '<p style="text-align: center; font-weight: bold;">Sorry, you have left a field empty</p>';
                    }

                    // Can we...move on?
                    if (!$page_exit) {
                        // We need this kind for checks
                        $item_kind = GetItemTableCode($item_code);

                        // Now first make sure we got an existing item!
                        connectitemsdb();

                        $itemdata_query = mssql_query("SELECT item_code, item_name, item_id FROM " . GetItemTableName($item_kind) . " WHERE item_code = '" . $item_code . "'", $items_dbconnect);
                        $itemdata = mssql_fetch_array($itemdata_query);
                        if (mssql_num_rows($itemdata_query) > 0) {
                            $item_id = $itemdata['item_id'];
                        } else {
                            $item_id = -1;
                            $process_exit = true;
                            $pexit_message .= 'Invalid ITEM CODE provided: ' . $item_code . ' (this item has not been added!)<br/>';
                        }
                        mssql_free_result($itemdata_query);

                        // We will only move forward if we got no errors tbh
                        if (!$process_exit) {

                            // Ensure amount < 100
                            if ($item_amount > 99) {
                                $item_amount = 99;
                            } elseif ($item_amount < 0) {
                                $item_amount = 0;
                            }

                            // Ensure our slots and not less than upgrades
                            if ($item_slots < $item_ups) {
                                $item_ups = $item_slots;
                            }

                            // Now generate our item_db_code value
                            $item_db_code = 65536 * $item_id + ($item_kind * 256 + 0);

                            // Now get our Item Upgrade value
                            $km_allorArray = array(0, 1, 2, 3, 4, 5, 6, 7);
                            if (!in_array($item_kind, $km_allorArray)) {
                                $item_upgrade = $base_code;

                                if ($item_amount == 0) {
                                    $item_amount = 1;
                                }

                            } else {
                                // These items need the amount set to 0 auto
                                $item_amount = 0;

                                // With + upgrades or no?
                                if ($item_talic == 1) {
                                    $item_upgrade = $base_code + (($base_code + 1) * $item_slots);
                                } else {
                                    $item_upgrade = ($base_code + (($base_code + 1) * $item_slots)) - ($item_talic * ((pow(16, $item_ups) - 1) / 15));
                                }
                            }

                            // For the SQL Statement, do we append rental time?
                            if ($item_rental_time > 0) {
                                $column_append = ", T";
                                $value_append = ", '$item_rental_time'";
                            } else {
                                $column_append = '';
                                $value_append = '';
                            }

                            // Now lets produce our SQL statement
                            connectdatadb();
                            $sql_insert_itemcharge = "INSERT tbl_ItemCharge ( nAvatorSerial, nItemCode_K, nItemCode_D, nItemCode_U" . $column_append . " )
                            VALUES( '$character_serial', '$item_db_code', '$item_amount', '$item_upgrade'" . $value_append . " )";
                            if (!($result_inser_itemcharge = mssql_query($sql_insert_itemcharge))) {
                                $process_exit = true;
                                $pexit_message .= 'Error while inserting item, ' . $item_code . ' . This item was not added.<br/>';
                            }
                            // Writing an admin log :D
                            gamecp_log(2, $userdata['username'], "ADMIN - GIVE ITEM - Character Serial: $character_serial | Item Code: $item_code [$item_ups]/$item_slots [talic: $item_talic]", 1);

                        } // !process_exit

                    } // for
                }


                if ($process_exit) {
                    $out .= '<p style="text-align: center;">' . $pexit_message . '</p>';
                } else {
                    header("Location: ?do=" . $_GET['do'] . "&page=useritems&character_serial=" . $character_serial . "&submit=Search");
                }

            } // !page_exit

        } elseif ($page == "getcode") {

            connectitemsdb();

            $item_code = (isset($_GET['itemcode'])) ? antiject(trim($_GET['itemcode'])) : "";

            if ($item_code != "") {
                if ($item_code != "-") {
                    $kn = GetItemTableCode($item_code);
                    $items_query = mssql_query("SELECT TOP 1 item_name FROM " . GetItemTableName($kn) . " WHERE item_code LIKE '$item_code'", $items_dbconnect);
                    $items = mssql_fetch_array($items_query);

                    if ($items['item_name'] != "") {
                        echo '<b>' . str_replace("_", " ", $items['item_name']) . '</b>';
                    } else {
                        echo "<b>No such item for: " . $item_code . "</b><br/>";
                    }
                    // Free Result
                    @mssql_free_result($items_query);
                } else {
                    echo '-1';
                }
            } else {
                echo "<b>No code provided</b>";
            }

            exit;


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