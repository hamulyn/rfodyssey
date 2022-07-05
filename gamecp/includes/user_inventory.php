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
    $module[_l('Account')][_l('Inventory Search')] = $file;
    return;
}

$lefttitle = _l('Account - Inventory Search');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

     if ($isuser) {

        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $search_fun = (isset($_GET['search_fun'])) ? $_GET['search_fun'] : "";
        $page_gen = (isset($_GET['page_gen']) && is_int((int)$_GET['page_gen'])) ? (int)$_GET['page_gen'] : '1';
        $item_ups = (isset($_GET['item_ups']) && is_int((int)$_GET['item_ups'])) ? (int)$_GET['item_ups'] : 0;
        $item_slots = (isset($_GET['item_slots']) && is_int((int)$_GET['item_slots'])) ? (int)$_GET['item_slots'] : 0;
        $item_talic = (isset($_GET['item_talic']) && is_int((int)$_GET['item_talic'])) ? (int)$_GET['item_talic'] : 0;
        $item_id = (isset($_GET['item_id'])) ? trim($_GET['item_id']) : "";
        $item_amount_max = (isset($_GET['item_amount_max']) && is_int((int)$_GET['item_amount_max'])) ? trim((int)$_GET['item_amount_max']) : 0;
        $item_amount_min = (isset($_GET['item_amount_min']) && is_int((int)$_GET['item_amount_min'])) ? trim((int)$_GET['item_amount_min']) : 0;
        $character_name = (isset($_GET['character_name'])) ? antiject($_GET['character_name']) : "";
        $character_serial = (isset($_GET['character_serial']) && is_int((int)$_GET['character_serial'])) ? antiject((int)$_GET['character_serial']) : 0;
        $enable_exit = false;
        $search_query = '';
        $top_limit = 5;
        $max_pages = 10;
        $num_of_bags = 100;
        $query_p2 = '';
        $enable_itemsearch = false;


        if (empty($page)) {

            $out .= '<form method="GET" action="' . $script_name . '?do=' . $_GET['do'] . '">';
            $out .= '<table class="tborder" cellpadding="2" cellspacing="1" border="0" width="100%">' . "\n";
            $out .= '	<tr>';
            $out .= '		<td class="alt1">Charcter Name:</td>';
            $out .= '		<td class="alt2"><input type="text" class="form-control" name="character_name" /></td>';
            $out .= '	</tr>';
            $out .= '</select>';
            $out .= '		</td>' . "\n";
            $out .= '	</tr>';
            $out .= '	<tr>';
            $out .= '		<td colspan="2"><input type="hidden" name="do" value="' . $_GET['do'] . '" /><input type="submit"  class="btn btn-default" value="Search" name="search_fun" /></td>';
            $out .= '	</tr>';
            $out .= '</table>';
            $out .= '</form>';

            if ($search_fun != "") {

                $out .= "<br/><br/>";

                if ($item_id == "" && $character_name == "" && $character_serial == 0 && ($item_talic == 0 || $item_talic == 1)) {
                    $enable_exit = true;
                    $out .= "<p align='center'><b>Sorry, make sure you filled in either the item id or character name</b></p>";
                }

                if ($enable_exit != true) {

                    connectdatadb();
                    $bag_numbers = '';
                    for ($i = 0; $i < 100; $i++) {
                        $bag_numbers .= ", k$i, u$i, d$i";
                    }

                    if ($character_name != "" || $character_serial != 0 || $item_id != "" || ($item_talic != 0 || $item_talic != 1)) {
                        $search_query .= " WHERE ";
                    }

                    if ($character_name != "" || $character_serial != 0) {
                        if ($character_serial != 0) {
                            $search_query .= "Serial = '$character_serial'";
                        } else {
                            $character_name = ereg_replace(";$", "", $character_name);
                            $character_name = ereg_replace("\\\\", "", $character_name);
                            $character_name = trim($character_name);

                            $char_query = mssql_query("SELECT Serial FROM tbl_base WHERE Name = '$character_name'");
                            $char_query = mssql_fetch_array($char_query);
                            $char_serial = $char_query['Serial'];

                            $search_query .= "Serial = '$char_serial'";
                        }
                    }

                    if ($item_id != "") {
                        connectitemsdb();

                        $item_id = str_replace(" ", "", $item_id);
                        $item_id = trim($item_id);

                        if ($character_name != "" || $character_serial != 0) {
                            $search_query .= " AND ";
                        }

                        $item_idq = $item_id;
                        $item_kind = GetItemTableCode($item_id);
                        $table_name = GetItemTableName($item_kind);
                        $item_id = str_replace("%", "", $item_id);
                        $table_query = @mssql_query("SELECT item_code, item_id, item_name FROM $table_name WHERE item_code = '$item_id'", $items_dbconnect) or die("Make sure you filled up your items DB...because I cannot seem to find the table $table_name");
                        $table = mssql_fetch_array($table_query);
                        $item_id = $table['item_id'];


                        if ($table['item_name'] != "") {
                            $search_query .= "(";
                            $item_id_start = ceil(65536 * $table['item_id'] + ($item_kind * 256 + 0));
                            $item_id_end = ceil(65536 * $table['item_id'] + ($item_kind * 256 + 99));
                            for ($i = 0; $i < 100; $i++) {
                                if ($i != 0) {
                                    $search_query .= ' OR ';
                                }

                                if ($item_amount_min != 0) {
                                    if ($item_amount_max == 0) {
                                        $item_amount_max = $item_amount_min;
                                    }
                                    $append_amount = " AND d$i >= $item_amount_min AND d$i <= $item_amount_max";
                                } else {
                                    $append_amount = '';
                                }

                                $search_query .= "k$i >= $item_id_start AND k$i <= $item_id_end" . $append_amount;
                            }
                            $search_query .= ")";
                        } else {
                            $search_query = "";
                        }
                        @mssql_free_result($table_query);
                        connectdatadb();

                        $enable_itemsearch = true;
                    }

                    if ($item_talic != 0 && $item_talic != 1) {
                        if ($character_name != "" || $character_serial != 0 || $enable_itemsearch) {
                            $search_query .= " AND ";
                        }

                        $search_query .= "(";

                        $base_code = 268435455;
                        $item_u = ($base_code + (($base_code + 1) * $item_slots)) - ($item_talic * ((pow(16, $item_ups) - 1) / 15));


                        for ($i = 0; $i < 100; $i++) {
                            if ($i != 0) {
                                $search_query .= ' OR ';
                            }
                            if (!$enable_itemsearch) {
                                $append_k = " AND k$i != '-1'";
                            } else {
                                $append_k = '';
                            }
                            $search_query .= "u$i = $item_u" . $append_k;
                        }
                        $search_query .= ")";
                        $enable_itemsearch = true;
                    }

                    if ($enable_itemsearch) {
                        #$search_query .= " AND DCK = 0";
                    }


                    $item_id = antiject($item_id);

                    // Pageination
                    if ($enable_itemsearch == true) {
                        include('./includes/pagination/ps_pagination.php');
                    }

                    $query_p1 = "SELECT
					Serial$bag_numbers
					FROM 
					tbl_inven
					$search_query";

                    $query_p2 .= ' AND Serial NOT IN ( SELECT TOP [OFFSET] Serial
					FROM 
					tbl_inven
					' . $search_query . ' ORDER BY Serial DESC) ORDER BY Serial DESC';

                    #echo $query_p1.$query_p2;
                    #exit;

                    //Create a PS_Pagination object
                    if ($enable_itemsearch == true) {
                        $filename = $_GET['do'] . "_" . md5($query_p1);
                        #if(!$query_count = readCache($filename.".cache", 3600)) {
                        #$query_count = mssql_query("SELECT COUNT(B.Serial) AS Count FROM tbl_base as B INNER JOIN tbl_inven AS I ON I.Serial = B.Serial $search_query AND B.AccountSerial NOT IN (SELECT X.nAccountSerial FROM RF_USER.dbo.tbl_UserBan AS X WHERE X.nAccountSerial = B.AccountSerial)");
                        #$query_count = mssql_fetch_array($query_count);
                        #$query_count = $query_count['Count'];
                        $query_count = '1000';
                        #writeCache($query_count,$filename.'.cache');
                        #}

                        $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
                        $pager = new PS_Pagination($data_dbconnect, $query_p1, $query_p2, $top_limit, $max_pages, $url, $query_count);
                    }

                    //The paginate() function returns a mysql
                    //result set for the current page
                    if ($enable_itemsearch == true) {
                        $rs = $pager->paginate();
                    } else {
                        $rs = mssql_query($query_p1);
                    }

                    $out .= '<table class="table table-bordered">' . "\n";

                    $base_code = 268435455;
                    $item_umx = ($base_code + (($base_code + 1) * $item_slots)) - ($item_talic * ((pow(16, $item_ups) - 1) / 15));
                    while ($row = mssql_fetch_array($rs)) {

                        connectdatadb();
                        $select_info = "SELECT Name, AccountSerial FROM tbl_base WHERE Serial = '" . $row['Serial'] . "'";
                        $select_result = mssql_query($select_info);
                        $charinfo = mssql_fetch_array($select_result);
                        $accountserial = $charinfo['AccountSerial'];
                        $name = $charinfo['Name'];
                        mssql_free_result($select_result);

                        if ($name[0] != "*") {
                            $out .= '<tr>' . "\n";
                            $out .= '	<td class="thead" colspan="5">&nbsp;</td>' . "\n";
                            $out .= '</tr>' . "\n";
                            $out .= '<tr>';
                            $out .= '<td class="alt2" colspan="5" style="font-size: 10px;"><span style="font-weight: bold;">Character Serial:</span> ' . $row['Serial'] . '</td>';
                            $out .= '</tr>';
                            $out .= '<tr>';
                            $out .= '<td class="alt2" colspan="5" style="font-size: 10px;"><span style="font-weight: bold;">Character Name:</span> ' . $name . '</td>';
                            $out .= '</tr>';
                            $out .= '<tr>';
                            $out .= '<td class="alt2" colspan="5" style="font-size: 10px;"><span style="font-weight: bold;">Account Serial:</span> ' . $accountserial . '</td>';
                            $out .= '</tr>';
                            #$out .= '<td class="alt2" colspan="5" style="font-size: 10px;"><span style="font-weight: bold;">Race:</span> '.getRaceByID($row['Race']).'</td>';
                            #$out .= '</tr>'
                            $out .= '<tr>';
                            $out .= '<td class="thead" nowrap>Slot #</td>';
                            $out .= '<td class="thead" nowrap>Item Code</td>';
                            $out .= '<td class="thead" nowrap>Item Name</td>';
                            $out .= '<td class="thead" nowrap>Amount</td>';
                            $out .= '<td class="thead" nowrap>Upgrades</td>';
                            $out .= '</tr>';

                            for ($i = 0; $i < $num_of_bags; $i++) {

                                $k_value = $row["k$i"];
                                $u_value = $row["u$i"];

                                if ($k_value > '-1') {

                                    $slot = $i;
                                    $kn = 0;
                                    for ($n = 9; $n < $item_tbl_num; $n++) {
                                        $item_id = ($k_value - ($n * (256 + $slot))) / 65536;
                                        if ($item_id == $k_value) {
                                            $kn = $n;
                                        }
                                    }

                                    $item_id = ceil($item_id);
                                    $kn = floor(($k_value - ($item_id * 65536)) / 256);

                                    connectitemsdb();
                                    $items_query = mssql_query("SELECT item_code, item_id, item_name FROM " . GetItemTableName($kn) . " WHERE item_id = '$item_id'", $items_dbconnect);
                                    $items = mssql_fetch_array($items_query);

                                    if ($items['item_name'] != "") {
                                        $item_id = str_replace("_", " ", $items['item_name']);
                                    } else {
                                        $item_id = "Not found in DB - " . $item_id . ":" . $kn;
                                    }

                                    $item_code = $items['item_code'];
                                    // Free Results
                                    mssql_free_result($items_query);

                                } else {
                                    $item_id = $k_value;
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

                                if ($ceil_slots > 0 AND $k_value > '-1' AND in_array($kn, $km_allorArray)) {
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

                                $text_color = '';

                                if ($search_fun != "" && $enable_itemsearch) {

                                    #$out .= '<tr><td class="alt1" colspan="5">'.$ux_value.' // '.$item_u.'</td></tr>';

                                    if ($item_code == trim($_GET['item_id'])) {
                                        $out .= '<tr>';
                                        $out .= '<td class="item_highlight" nowrap>' . $i . '</td>';
                                        $out .= '<td class="item_highlight" nowrap>' . $item_code . '</td>';
                                        $out .= '<td class="item_highlight" nowrap>' . $item_id . '</td>';
                                        $out .= '<td class="item_highlight" nowrap>' . $row["d$i"] . '</td>';
                                        $out .= '<td class="item_highlight"' . $bgc . ' nowrap>' . $upgrades . '</td>';
                                        $out .= '</tr>';
                                    } elseif ($ux_value == $item_umx && $item_talic != 0 && $item_talic != 1) {
                                        $out .= '<tr>';
                                        $out .= '<td class="alt1" nowrap>' . $i . '</td>';
                                        $out .= '<td class="alt1" nowrap>' . $item_code . '</td>';
                                        $out .= '<td class="alt1" style="' . $text_color . '" nowrap>' . $item_id . '</td>';
                                        $out .= '<td class="alt1" nowrap>' . $row["d$i"] . '</td>';
                                        $out .= '<td class="alt1" style="' . $bgc . '" nowrap>' . $upgrades . '</td>';
                                        $out .= '</tr>';
                                    }
                                    #$out .= $row["k$i"];
                                } else {
                                    $bgcolor = "";
                                    $out .= '<tr>';
                                    $out .= '<td class="alt2" style="font-size: 10px;' . $bgcolor . '" nowrap>' . $i . '</td>';
                                    $out .= '<td class="alt1" style="font-size: 10px;' . $bgcolor . '" nowrap>' . $item_code . '</td>';
                                    $out .= '<td class="alt1" style="font-size: 10px;' . $bgcolor . $text_color . '" nowrap>' . $item_id . '</td>';
                                    $out .= '<td class="alt1" style="font-size: 10px;' . $bgcolor . '" nowrap>' . $row["d$i"] . '</td>';
                                    $out .= '<td class="alt1" style="font-size: 10px; ' . $bgc . '" nowrap>' . $upgrades . '</td>';
                                    $out .= '</tr>';
                                }
                            }

                        }

                    }


                    if (mssql_num_rows($rs) <= 0) {
                        $out .= '<tr>' . "\n";
                        $out .= '<td colspan="5" style="text-align: center;">No such user/item found in the database</td>' . "\n";
                        $out .= '</tr>' . "\n";
                    } else {
                        if ($enable_itemsearch == true) {
                            $out .= '<tr>';
                            $out .= '<td colspan="5" style="text-align: center;">' . $pager->renderFullNav() . '</td>';
                            $out .= '</tr>';
                        }
                    }
                    // Free Results
                    @mssql_free_result($rs);
                    $out .= "</table>";

                    // Writing an admin log :D
                    if (!isset($item_idq)) {
                        $item_idq = '';
                    }
                    gamecp_log(0, $userdata['username'], "ADMIN - ITEM SEARCH - Searched for: $character_name or $item_idq", 1);

                }

            }

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