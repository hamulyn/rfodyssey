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
    $module[_l('Account')][_l('Equipment Search')] = $file;
    return;
}

$lefttitle = _l('Account - Equipment Search');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if ($isuser) {

        # Set our main variables
        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $page_gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';
        $search_query = '';
        $top_limit = 10;
        $max_pages = 10;
        $enable_itemsearch = false;
        $enable_exit = false;
        $search_query = '';
        $query_p2 = '';
        $equipment_names = array("Upper", "Lower", "Gloves", "Shoes", "Head", "Shield", "Weapon", "Cloak");
        $accessores_names = array(0 => "-", 8 => "Ring", 9 => "Amulet", 10 => "Ammo");

        # Display 'constant' content (shows up on all pages)
        $out .= '<form method="GET" action="' . $script_name . '?do=' . $_GET['do'] . '">';
        $out .= '<table class="tborder" cellpadding="2" cellspacing="1" border="0" width="100%">';
        $out .= '<tr>';
        $out .= '<td class="alt1">Charcter Name:</td>';
        $out .= '<td class="alt2"><input type="text" class="form-control" name="character_name" /></td>';
        $out .= '</tr>';
        $out .= '<tr>';
        $out .= '<td colspan="2"><input type="hidden" name="do" value="' . $_GET['do'] . '" /><input type="submit"  class="btn btn-default" value="Search" name="page" /></td>';
        $out .= '</tr>';
        $out .= '</table>';
        $out .= '</form>';

        # Build the rest of the page
        if ($page == "") {
            #
        } elseif ($page == 'Search') {
            $out .= "<br/><br/>";

            $item_id = (isset($_GET['item_id']) && is_int((int)$_GET['item_id'])) ? trim((int)$_GET['item_id']) : 0;
            $character_name = (isset($_GET['character_name'])) ? $_GET['character_name'] : "";

            if ($item_id == 0 && $character_name == "") {
                $enable_exit = true;
                $out .= "<p align='center'><b>Sorry, make sure you filled in either the item id or character name</b></p>";
            }

            if ($enable_exit != true) {
                connectdatadb();
                $search_query .= " WHERE ";

                if ($character_name != "") {
                    $character_name = str_replace(" ", "", trim(antiject($character_name)));

                    $search_query .= "Name = '$character_name'";
                }

                if ($item_id != 0) {
                    connectitemsdb();
                    $enable_itemsearch = true;

                    $item_id = str_replace(" ", "", trim(antiject($item_id)));

                    if ($character_name != '') {
                        $search_query .= " OR ";
                    }

                    $item_kind = GetItemTableCode($item_id);
                    $table_name = GetItemTableName($item_kind);
                    $item_id = str_replace("%", "", $item_id);

                    $table_query = mssql_query("SELECT item_id FROM $table_name WHERE item_code = '$item_id'", $items_dbconnect);
                    $table = mssql_fetch_array($table_query);
                    $item_id = $table['item_id'];

                    // Free Result
                    @mssql_free_result($table_query);

                    if ($item_kind == tbl_code_upper) {
                        $search_query .= " B.EK0 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_lower) {
                        $search_query .= " B.EK1 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_shoe) {
                        $search_query .= " B.EK2 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_gauntlet) {
                        $search_query .= " B.EK3 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_helmet) {
                        $search_query .= " B.EK4 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_shield) {
                        $search_query .= " B.EK5 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_weapon) {
                        $search_query .= " B.EK6 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_cloak) {
                        $search_query .= " B.EK7 = '$item_id' ";
                    } elseif ($item_kind == tbl_code_ring || $item_kind == tbl_code_amulet || $item_kind == tbl_code_bullet) {
                        $item_id_start = ceil(65536 * $item_id + ($item_kind * 256 + 0));
                        $item_id_end = ceil(65536 * $item_id + ($item_kind * 256 + 99));
                        $search_query .= " ((G.EK0 >= '$item_id_start' AND G.EK0 <= $item_id_end) OR (G.EK1 >= '$item_id_start' AND G.EK1 <= $item_id_end) OR (G.EK2 >= '$item_id_start' AND G.EK2 <= $item_id_end) OR (G.EK3 >= '$item_id_start' AND G.EK3 <= $item_id_end) OR (G.EK4 >= '$item_id_start' AND G.EK4 <= $item_id_end) OR (G.EK5 >= '$item_id_start' AND G.EK5 <= $item_id_end)) ";
                    } else {
                        $search_query .= " B.EK0 = '$item_id'";
                    }

                    connectdatadb();
                }

                // Pageination
                if ($enable_itemsearch == true) {
                    include('./includes/pagination/ps_pagination.php');
                }

                $query_p1 = "SELECT
				B.Serial,B.Name,B.Race,B.Lv,B.EK0,B.EK1,B.EK2,B.EK3,B.EK4,B.EK5,B.EK6,B.EK7,B.EU0,B.EU1,B.EU2,B.EU3,B.EU4,B.EU5,B.EU6,B.EU7, G.EK0 AS GK0, G.EK1 AS GK1, G.EK2 AS GK2, G.EK3 AS GK3, G.EK4 AS GK4, G.EK5 AS GK5
				FROM tbl_base AS B
				INNER JOIN
				tbl_general AS G
				ON B.Serial = G.Serial
				$search_query";

                $query_p2 .= ' AND B.Serial NOT IN ( SELECT TOP [OFFSET] B.Serial FROM tbl_base AS B INNER JOIN tbl_general AS G ON B.Serial = G.Serial ' . $search_query . ' ORDER BY B.Serial DESC) ORDER BY B.Serial DESC';

                //Create a PS_Pagination object
                if ($enable_itemsearch == true) {
                    $filename = $_GET['do'] . "_" . md5($query_p1);
                    if (!$query_count = readCache($filename . ".cache", 60)) {
                        $query_count_result = mssql_query("SELECT COUNT(B.Serial) AS Count FROM tbl_base AS B INNER JOIN tbl_general AS G ON B.Serial = G.Serial $search_query");
                        $query_count = mssql_fetch_array($query_count_result);
                        $query_count = $query_count['Count'];
                        writeCache($query_count, $filename . '.cache');
                        // Free Result
                        @mssql_free_result($query_count_result);
                    }

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

                connectitemsdb();
                $out .= '<table class="table table-bordered">' . "\n";
                while ($row = mssql_fetch_array($rs)) {
                    $out .= '<tr>' . "\n";
                    $out .= '	<td class="thead" colspan="6">&nbsp;</td>' . "\n";
                    $out .= '</tr>' . "\n";
                    $out .= '<tr>' . "\n";
                    $out .= '	<td class="alt2" style="font-weight: bold;" width="15%" nowrap>Character Name</td>' . "\n";
                    $out .= '	<td class="alt2" colspan="5" nowrap>' . antiject($row['Name']) . ' (Level: ' . $row['Lv'] . ')</td>' . "\n";
                    $out .= '</tr>' . "\n";
                    $out .= '<tr>' . "\n";
                    $out .= '	<td class="alt2" style="font-weight: bold;" width="15%" nowrap>Race</td>' . "\n";
                    $out .= '	<td class="alt2" colspan="5" nowrap>' . getRaceByID($row['Race']) . '</td>' . "\n";                 
                    $out .= '</tr>' . "\n";

                    for ($i = 0; $i < 8; $i++) {
                        $k_value = antiject($row["EK$i"]);
                        $u_value = antiject($row["EU$i"]);

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
                            } elseif ($i == 8) {
                                $item_kind = tbl_code_ring;
                            } else {
                                $item_kind = tbl_code_helmet;
                            }

                            $table_name = GetItemTableName($item_kind);

                            $items_query = mssql_query("SELECT item_code, item_id, item_name FROM " . $table_name . " WHERE item_id = '$k_value'", $items_dbconnect);
                            $items = mssql_fetch_array($items_query);

                            if ($items['item_name'] != "") {
                                $item_name = str_replace("_", " ", $items['item_name']);
                            } else {
                                $item_name = "Not found in DB - " . $item_id . ":" . $item_kind;
                            }

                            $item_code = $items['item_code'];

                            // Free Result
                            @mssql_free_result($items_query);

                        } else {
                            $item_name = '<i>No item</i>';
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
                            $bgc = ' style="background-color: #10171f;"';

                        } else {
                            $upgrades = "No Upgrades";
                            $bgc = "";
                        }

                        $out .= '<tr>' . "\n";
                        $out .= '	<td class="alt2" style="font-weight: bold;" width="15%" nowrap>' . $equipment_names[$i] . '</td>' . "\n";
                        $out .= '	<td class="alt1" nowrap>' . $item_code . '</td>' . "\n";
                        $out .= '	<td class="alt1" nowrap>' . $item_name . '</td>' . "\n";
                        $out .= '	<td class="alt1"' . $bgc . ' nowrap colspan="3">' . $upgrades . '</td>' . "\n";
                        $out .= '</tr>' . "\n";
                    }

                    for ($i = 0; $i < 6; $i++) {
                        $k_value = antiject($row["GK$i"]);

                        $kn = 0;
                        if ($k_value > '0') {
                            $slot = 0;
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
                                $item_name = str_replace("_", " ", $items['item_name']);
                            } else {
                                $item_name = "Not found in DB - " . $item_id . ":" . $kn;
                            }

                            $item_code = $items['item_code'];

                            // Free Result
                            @mssql_free_result($items_query);

                        } else {
                            $item_name = '<i>No item</i>';
                            $item_code = "-";
                        }
                        $toggle = (($i + 1) % 2) ? true : false;

                        $out .= ($toggle) ? '<tr>' . "\n" : '';
                        $out .= '	<td class="alt2" style="font-weight: bold;" width="15%" nowrap>' . $accessores_names[$kn] . '</td>' . "\n";
                        $out .= '	<td class="alt1" nowrap>' . $item_code . '</td>' . "\n";
                        $out .= '	<td class="alt1" nowrap>' . $item_name . '</td>' . "\n";
                        $out .= (!$toggle) ? '</tr>' . "\n" : '';

                        $item_name = '';
                        $item_code = '-';
                        $kn = 0;
                    }
                }

                if (mssql_num_rows($rs) <= 0) {
                    $out .= '<tr>' . "\n";
                    $out .= '<td colspan="4" style="text-align: center;">No such user/item found in the database</td>' . "\n";
                    $out .= '</tr>' . "\n";
                } else {
                    if ($enable_itemsearch == true) {
                        $out .= '<tr>';
                        $out .= '<td colspan="4" style="text-align: center;">' . $pager->renderFullNav() . '</td>';
                        $out .= '</tr>';
                    }
                }

                $out .= '</table>' . "\n";

                // Free Result
                @mssql_free_result($rs);

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