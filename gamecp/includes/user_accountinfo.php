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
    $module[_l('Account')][_l('Account Info')] = $file;
    return;
}

$lefttitle = _l('Viewing ' . $userdata['username'] . '\'s Account Information and Characters');;

if ($this_script == $script_name) {

    if ($isuser) {

        # Set our main variables
        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $equipment_names = array("Upper", "Lower", "Gloves", "Shoes", "Head", "Shield", "Weapon", "Cloak");

        if (!isset($config['specialgp_gender'])) {
            $config['specialgp_gender'] = 1000000;
        }

        if ($page == "") {

            $out .= '<p>You must be logged out in order to make any changes (gender, delete, and etc) to your characters</p>' . "\n";
            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="thead" nowrap>Character Name</td>' . "\n";
            $out .= '		<td class="thead" style="text-align: center;" nowrap>Race</td>' . "\n";
            $out .= '		<td class="thead" nowrap>Level</td>' . "\n";
            $out .= '		<td class="thead" nowrap>Class</td>' . "\n";
            $out .= '		<td class="thead" nowrap>"Time Well Wasted"</td>' . "\n";
            $out .= '		<td class="thead" colspan="2" nowrap>Options</td>' . "\n";
            $out .= '	</tr>' . "\n";

            connectdatadb();
            $char_query = "SELECT TOP 3 B.Serial,B.Name,B.Lv,B.Class,B.Race,B.Dalant,B.Gold,B.EK0,B.EK1,B.EK2,B.EK3,B.EK4,B.EK5,B.EK6,B.EK7,B.EU0,B.EU1,B.EU2,B.EU3,B.EU4,B.EU5,B.EU6,B.EU7,G.PvpPoint,G.TotalPlayMin
			FROM tbl_base AS B
			INNER JOIN tbl_general AS G
			ON B.Serial = G.Serial
			WHERE B.DCK = 0 AND B.AccountSerial = '" . $userdata['serial'] . "'";
            if (!($char_result = mssql_query($char_query))) {
                $out .= '	<tr>' . "\n";
                $out .= '		<td colspan="7" class="alt2" style="text-align: center; font-width: bold;">Unable to query the character database</td>' . "\n";
                $out .= '	</tr>' . "\n";
            }
            connectitemsdb();
            while ($char = mssql_fetch_array($char_result)) {
                $class_info_result = @mssql_query("SELECT class_name FROM tbl_Classes WHERE class_code = '" . $char['Class'] . "'", $items_dbconnect)  or die("Error! Looks like you did not fill up your Items DB. Can't find the classes");
                $class_info = mssql_fetch_array($class_info_result);

                $change_gender = ' (<a href="' . $script_name . '?do=' . $_GET['do'] . '&page=change_gender&serial=' . $char['Serial'] . '">';
                if ($char['Race'] == 0) {
                    $gender = "Male";
                    $race = '<span style="color: #CC6699;">Bell</span>';
                    $change_gender .= 'Change to Female - ' . $config['specialgp_gender'] . ' GP</a>)';
                } elseif ($char['Race'] == 1) {
                    $gender = "Female";
                    $race = '<span style="color: #CC6699;">Bell</span>';
                    $change_gender .= 'Change to Male - ' . $config['specialgp_gender'] . ' GP</a>)';
                } elseif ($char['Race'] == 2) {
                    $gender = "Male";
                    $race = '<span style="color: #9933CC;">Cora</span>';
                    $change_gender .= 'Change to Female - ' . $config['specialgp_gender'] . ' GP</a>)';
                } elseif ($char['Race'] == 3) {
                    $gender = "Female";
                    $race = '<span style="color: #9933CC;">Cora</span>';
                    $change_gender .= 'Change to Male - ' . $config['specialgp_gender'] . ' GP</a>)';
                } elseif ($char['Race'] == 4) {
                    $gender = "Robot";
                    $race = '<span style="color: grey;">Acc</span>';
                    $change_gender = '';
                }

                if ($userdata['points'] < $config['specialgp_gender']) {
                    $change_gender = '';
                }

                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" style="font-weight: bold;" nowrap>' . $char['Name'] . '</td>' . "\n";
                $out .= '		<td class="alt2" style="text-align: center;" nowrap>' . $race . '</td>' . "\n";
                $out .= '		<td class="alt2" nowrap>' . $char['Lv'] . '</td>' . "\n";
                $out .= '		<td class="alt2" nowrap>' . $class_info['class_name'] . '</td>' . "\n";
                $out .= '		<td class="alt2" nowrap>' . number_format(round($char['TotalPlayMin'] / 60)) . ' Hours</td>' . "\n";
                $out .= '		<td class="alt2" style="text-align: center;" nowrap><a href="javascript:toggle_extra(\'' . $char['Serial'] . '\')">View More</a></td>' . "\n";
                $out .= '		<td class="alt2" style="text-align: center;" nowrap><a href="' . $script_name . '?do=' . $_GET['do'] . '&page=delete&serial=' . $char['Serial'] . '">Delete</a></td>' . "\n";
                $out .= '	</tr>' . "\n";

                $out .= '	<tr class="extra_' . $char['Serial'] . '" style="display: none;">' . "\n";
                $out .= '		<td class="alt1">Gender</td>' . "\n";
                $out .= '		<td class="alt1" colspan="6">' . $gender . $change_gender . '</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr class="extra_' . $char['Serial'] . '" style="display: none;">' . "\n";
                $out .= '		<td class="alt1">PVP Points</td>' . "\n";
                $out .= '		<td class="alt1" colspan="6">' . number_format(round($char['PvpPoint'])) . '</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr class="extra_' . $char['Serial'] . '" style="display: none;">' . "\n";
                $out .= '		<td class="alt1">Dalant</td>' . "\n";
                $out .= '		<td class="alt1" colspan="6">' . number_format($char['Dalant']) . '</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr class="extra_' . $char['Serial'] . '" style="display: none;">' . "\n";
                $out .= '		<td class="alt1">Gold</td>' . "\n";
                $out .= '		<td class="alt1" colspan="6">' . number_format($char['Gold']) . '</td>' . "\n";
                $out .= '	</tr>' . "\n";

                for ($i = 0; $i < 8; $i++) {
                    $k_value = antiject($char["EK$i"]);
                    $u_value = antiject($char["EU$i"]);

                    if ($k_value > '-1') {

                        if ($i == 0) {
                            $item_kind = tbl_code_upper;
                        } elseif ($i == 1) {
                            $item_kind = tbl_code_lower;
                        } elseif ($i == 2) {
                            $item_kind = tbl_code_gauntlet;
                        } elseif ($i == 3) {
                            $item_kind = tbl_code_shoe;
                        } elseif ($i == 4) {
                            $item_kind = tbl_code_helmet;
                        } elseif ($i == 5) {
                            $item_kind = tbl_code_shield;
                        } elseif ($i == 6) {
                            $item_kind = tbl_code_weapon;
                        } elseif ($i == 7) {
                            $item_kind = tbl_code_cloak;
                        } else {
                            $item_kind = tbl_code_helmet;
                        }

                        $table_name = GetItemTableName($item_kind);

                        $items_query = @mssql_query("SELECT item_code, item_id, item_name FROM " . $table_name . " WHERE item_id = '$k_value'", $items_dbconnect) or die("Error! Looks like you did not fill up your Items DB.");
                        $items = mssql_fetch_array($items_query);

                        if ($items['item_name'] != "") {
                            $item_name = str_replace("_", " ", $items['item_name']);
                        } else {
                            $item_name = "Not found in DB - " . $k_value . ":" . $item_kind;
                        }

                        $item_code = $items['item_code'];

                        // Free Result
                        @mssql_free_result($items_query);

                    } else {
                        $item_name = "<i>No item equipped</i>";
                        $item_code = "-";
                    }

                    $base_code = 268435455;
                    $ux_value = $u_value;
                    $item_slots = $u_value;
                    $item_slots = $item_slots - $base_code;
                    $item_slots = $item_slots / ($base_code + 1);
                    $upgrades = "";
                    $ceil_slots = ceil($item_slots);
                    $slots_code = ($base_code + (($base_code + 1) * $ceil_slots));
                    $slots = $ceil_slots;

                    $km_allorArray = array(0, 1, 2, 3, 4, 5, 6, 7);

                    if ($ceil_slots > 0 AND $k_value > '-1' AND in_array($item_kind, $km_allorArray)) {
                        $u_value = dechex($u_value);
                        $item_ups = $u_value[0];
                        $slots = 0;
                        $u_value = strrev($u_value);
                        for ($m = 0; $m < $item_ups; $m++) {
                            $talic_id = hexdec($u_value[$m]);
                            $upgrades .= '<img src="./includes/templates/assets/images/talics/t-' . sprintf("%02d", ($talic_id)) . '.png" width="12"/>';
                        }
                        $bgc = ' background-color: #10171f;';
                    } else {
                        $upgrades = "No Upgrades";
                        $bgc = "";
                    }

                    $out .= '<tr class="extra_' . $char['Serial'] . '" style="display: none;">' . "\n";
                    $out .= '	<td class="alt1" width="15%" nowrap>' . $equipment_names[$i] . '</td>' . "\n";
                    $out .= '	<td class="alt1" colspan="4" nowrap>' . $item_name . '</td>' . "\n";
                    $out .= '	<td class="alt1"' . $bgc . ' colspan="2" nowrap>' . $upgrades . '</td>' . "\n";
                    $out .= '</tr>' . "\n";
                }

                // Free Result
                @mssql_free_result($class_info_result);

            }

            if (mssql_num_rows($char_result) <= 0) {
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt1" colspan="7" style="text-align: center;" nowrap>No characters found for your account</td>' . "\n";
                $out .= '	</tr>' . "\n";
            }

            $out .= '</table>';

            // Free Result
            @mssql_free_result($char_result);

        } elseif ($page == 'change_gender') {


            $char_serial = (isset($_GET['serial']) && is_int((int)$_GET['serial'])) ? antiject((int)$_GET['serial']) : '';

            if ($char_serial == '') {
                $out .= _l('invalid_serial');
            } else {
                if ($userdata['points'] >= $config['specialgp_gender']) {
                    connectdatadb();
                    $char_query = mssql_query("SELECT Name,Race,AccountSerial FROM tbl_base WHERE Serial = '$char_serial'");
                    $char_info = mssql_fetch_array($char_query);

                    if (mssql_num_rows($char_query) <= 0) {
                        $out .= _l('invalid_serial');
                    } else {
                        if ($char_info['AccountSerial'] != $userdata['serial']) {
                            $out .= _l('invalid_serial');
                        } else {
                            if ($char_info['Race'] == 0) {
                                $race = 1;
                            } elseif ($char_info['Race'] == 1) {
                                $race = 0;
                            } elseif ($char_info['Race'] == 2) {
                                $race = 3;
                            } elseif ($char_info['Race'] == 3) {
                                $race = 2;
                            } else {
                                $race = $char_info['Race'];
                            }

                            $update_query = "UPDATE tbl_base SET Race = '$race' WHERE Serial = '$char_serial'";
                            if (!($update_result = mssql_query($update_query))) {
                                $out .= '<p style="text-align: center; font-weight: bold;">Unable to change this characters gender</p>' . "\n";
                            } else {
                                connectgamecpdb();
                                $minus_credits = $config['specialgp_gender'];
                                $username = $userdata['username'];
                                $update_credits = "UPDATE gamecp_gamepoints SET user_points = user_points-$minus_credits WHERE user_account_id = '" . $userdata['serial'] . "'";
                                if ($credits_result = mssql_query($update_credits)) {
                                    $out .= '<p style="text-align: center; font-weight: bold;">Successfully updated ' . $char_info['Name'] . '\'s gender</p>';
                                    gamecp_log(1, $userdata['username'], "GAMECP - ACCOUNT INFO - Updated Gender - Char Serial: " . $char_serial);
                                    header("Refresh: 1; URL=" . $script_name . '?do=' . $_GET['do']);
                                } else {
                                    $out .= '<p style="text-align: center; font-weight: bold;">Failed to update your credits!</p>';
                                    gamecp_log(1, $userdata['username'], "GAMECP - ACCOUNT INFO - FAILED - Updated Gender - Char Serial: " . $char_serial);
                                }
                            }

                        }
                    }
                } else {
                    $out .= '<p style="text-align: center; font-weight: bold;">You do not have enough game points to change your gender</p>';
                }
            }

        } elseif ($page == 'delete') {

            $char_serial = (isset($_GET['serial']) && is_int((int)$_GET['serial'])) ? antiject((int)$_GET['serial']) : '';

            if ($char_serial == '') {
                $out .= _l('invalid_serial');
            } else {
                connectdatadb();
                $char_query = mssql_query("SELECT Name,AccountSerial FROM tbl_base WHERE Serial = '$char_serial'");
                $char_info = mssql_fetch_array($char_query);

                if (mssql_num_rows($char_query) <= 0) {
                    $out .= _l('invalid_serial');
                } else {
                    if ($char_info['AccountSerial'] != $userdata['serial']) {
                        $out .= _l('invalid_serial');
                    } else {
                        $out .= '<form method="post">' . "\n";
                        $out .= '<p style="text-align: center; font-weight: bold;">Are you sure you want the delete the character: <u>' . $char_info['Name'] . '</u>?</p>' . "\n";
                        $out .= '<p style="text-align: center;"><input type="hidden" name="serial" value="' . $char_serial . '"/><input type="hidden" name="page" value="delete_char"/><input type="submit"  class="btn btn-default" name="yes" value="Yes"/> <input type="submit"  class="btn btn-default" name="no" value="No"/></p>';
                        $out .= '</form>';
                    }
                }
                // Free Result
                @mssql_free_result($char_query);
            }

        } elseif ($page == 'delete_char') {
            $yes = (isset($_POST['yes'])) ? '1' : '0';
            $no = (isset($_POST['no'])) ? '1' : '0';
            if (isset($_POST['serial']) && is_int((int)$_POST['serial'])) {
                $serial = antiject((int)$_POST['serial']);
            } else {
                $serial = '';
            }

            if ($no != 1 && $serial != '') {
                connectdatadb();
                $char_query = mssql_query("SELECT Name,AccountSerial FROM tbl_base WHERE Serial = '$serial'", $data_dbconnect);
                $char_info = mssql_fetch_array($char_query);

                if (mssql_num_rows($char_query) <= 0) {
                    $out .= _l('invalid_serial');
                } else {
                    if ($char_info['AccountSerial'] != $userdata['serial']) {
                        $out .= _l('invalid_serial');
                    } else {
                        $cquery = mssql_query("UPDATE tbl_base SET deletename=name  WHERE serial = " . $serial);
                        $cquery = mssql_query("UPDATE tbl_base SET name='*'+cast(serial as varchar)  WHERE serial = " . $serial);
                        $cquery = mssql_query("UPDATE tbl_base SET DCK = 1,  Arrange = 1, DeleteTime = getdate()  WHERE Serial = " . $serial);

                        $out .= '<p style="text-align: center; font-weight: bold;">Your character has been successfully deleted!</p>';
                        gamecp_log(3, $userdata['username'], "GAMECP - DELETE CHARACTER - Character Serial:  " . $serial, 1);
                    }
                }
                // Free Result
                @mssql_free_result($char_query);
            } else {
                header("Location: $script_name?do=" . $_GET['do']);
            }

        } else {
            $out .= _l('page_not_found');
        }

    } else {
        $out .= _l('no_permission');
    }

} else {
    $out .= _l('invalid_page_load');
}


?>