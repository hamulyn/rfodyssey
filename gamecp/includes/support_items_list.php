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
    $module[_l('Support')][_l('Items List')] = $file;
    return;
}

$lefttitle = _l('Items List');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $search_fun = (isset($_GET['search_fun'])) ? $_GET['search_fun'] : "";
        $page_gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : "";
        $item_kind = 60;
        $query_p2 = "";
        $search_query = "";
        $enable_exit = false;

        if (empty($page)) {

            function GetItemTableName2($id)
            {
                $sql_tables = "SELECT table_name from information_schema.tables where table_type='Base table' AND table_name != 'tbl_Classes' order by table_name";
                if (!($tables_result = mssql_query($sql_tables))) {
                    if ($config['security_enable_debug'] == 1) {
                        die("Debug enabled: SQL Error when getting data from the information_scheme.tables table: " . mssql_get_last_message());
                    }
                }
                $x = 0;
                $return = 'tbl_code_weapon';
                while ($row = mssql_fetch_array($tables_result)) {
                    if ($x == $id) {
                        $return = $row['table_name'];
                        break;
                    }
                    $x++;
                }
                mssql_free_result($tables_result);
                return $return;
            }

            $out .= '<form method="GET" action="' . $script_name . '">';
            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '<tr>';
            $out .= '<td class="thead" colspan="2" style="padding: 4px;"><b>Look up an item</b></td>';
            $out .= '</tr>';
            $out .= '<tr>';
            $out .= '<td class="alt1">Item Code:</td>';
            $out .= '<td class="alt2"><input type="text" class="form-control" name="item_code" /></td>';
            $out .= '</tr>';
            $out .= '<tr>';
            $out .= '<td class="alt1">Item Name:</td>';
            $out .= '<td class="alt2"><input type="text" class="form-control" name="item_name" /></td>';
            $out .= '</tr>';
            $out .= '<tr>';
            $out .= '<td class="alt1">Item Kind:</td>';
            $out .= '<td class="alt2">';
            $out .= '<select class="form-control"name="item_kind">';
            connectitemsdb();
            $sql_tables = "SELECT table_name from information_schema.tables where table_type='Base table'  AND table_name != 'tbl_Classes' order by table_name";
            if (!($tables_result = mssql_query($sql_tables))) {
                $out .= "Bad query on seelcting table info!";
                if ($config['security_enable_debug'] == 1) {
                    die("Debug Info: " . mssql_get_last_message());
                }
            }
            while ($row = mssql_fetch_array($tables_result)) {
                $table[] = $row;
            }
            for ($i = 0; $i < count($table); $i++) {
                if (isset($_GET['item_kind']) && ($i == $_GET['item_kind'])) {
                    $selected = "selected";
                } else {
                    $selected = "";
                }

                $out .= '<option value="' . $i . '"' . $selected . '>' . ucfirst(str_replace("tbl_code_", "", $table[$i]['table_name'])) . '</option>';
            }
            mssql_free_result($tables_result);
            $out .= '</select>';
            $out .= '</td>';
            $out .= '</tr>';
            $out .= '<tr>';
            $out .= '<td colspan="2"><input type="hidden" name="do" value="' . $_GET['do'] . '" /><input type="submit"  class="btn btn-default" value="Search" name="search_fun" /></td>';
            $out .= '</tr>';
            $out .= '</table>';
            $out .= '</form>';

            connectitemsdb();
            if ($search_fun != "") {

                $item_kind = (isset($_GET['item_kind'])) ? $_GET['item_kind'] : $item_kind;
                $item_code = (isset($_GET['item_code'])) ? $_GET['item_code'] : "";
                $item_name = (isset($_GET['item_name'])) ? $_GET['item_name'] : "";

                if ($item_kind == "" && $item_code == "" && $item_name == "") {
                    $enable_exit = true;
                    $out .= "<p align='center'><b>Sorry, make sure you filled in either the item code, name and kind</b></p>";
                }

                if ($enable_exit != true) {

                    $item_kind = antiject($item_kind);
                    $item_code = antiject($item_code);
                    $item_name = antiject($item_name);

                    if ($item_name != "" or $item_code != "") {
                        $search_query = " WHERE ";
                        $query_p2 .= " AND ";
                    } else {
                        $query_p2 .= "WHERE ";
                    }

                    if ($item_code != "") {
                        if (!preg_match("/%/", $item_code)) {
                            $search_query .= " item_code = '$item_code'";
                        } else {
                            $search_query .= " item_code LIKE '$item_code'";
                        }
                    }

                    if ($item_name != "") {
                        if ($item_code != "") {
                            $search_query .= " OR ";
                        }

                        $item_name = str_replace(" ", "_", $item_name);

                        if (!preg_match("/%/", $item_name)) {
                            $search_query .= " item_name = '$item_name'";
                        } else {
                            $search_query .= " item_name LIKE '$item_name'";
                        }
                    }


                }

                // Writing an admin log :D
                gamecp_log(0, $userdata['username'], "ADMIN - ITEM LIST - Searched for: " . GetItemTableName2($item_kind) . " or $item_code or $item_name", 0);

            } else {
                $query_p2 .= " WHERE ";
            }

            $item_kind = GetItemTableName2($item_kind);
            // Pageination
            include('./includes/pagination/ps_pagination.php');

            $query_p1 = "SELECT item_id, item_code, item_name FROM $item_kind" . $search_query;
            $query_p2 .= "item_id NOT IN ( SELECT TOP [OFFSET] item_id FROM $item_kind $search_query ORDER BY item_id) ORDER BY item_id ASC";

            //Create a PS_Pagination object
            $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
            $pager = new PS_Pagination($items_dbconnect, $query_p1, $query_p2, 50, 10, $url);

            //The paginate() function returns a mysql
            //result set for the current page
            $rs = $pager->paginate();

            $out .= "<br/><br/>";
            $out .= '<table class="table table-bordered">' . "\n";
            $out .= "<tr>";
            $out .= '<td colspan="3" nowrap>Total number of items found: ' . number_format($pager->totalResults()) . '</td>';
            $out .= "</tr>";
            $out .= '<tr>';
            $out .= '<td class="thead" style="padding: 4px;" nowrap><b>Item ID</b></td>';
            $out .= '<td class="thead" style="padding: 4px;" nowrap><b>Item Code</b></td>';
            $out .= '<td class="thead" style="padding: 4px;" nowrap><b>Item Hex Code</b></td>';
            $out .= '<td class="thead" style="padding: 4px;" nowrap><b>Item Name</b></td>';
            $out .= '</tr>';

            function strhex($string)
            {
                $hex = "";
                for ($i = 0; $i < strlen($string); $i++)
                    $hex .= (strlen(dechex(ord($string[$i]))) < 2) ? "0" . dechex(ord($string[$i])) : dechex(ord($string[$i]));
                return $hex;
            }

            while ($row = mssql_fetch_array($rs)) {
                $hex_code = strtoupper(dechex(hexdec('C0070001') + $row['item_id']));

                $out .= '<tr>';
                $out .= '<td class="alt2" nowrap>' . $row['item_id'] . '</td>';
                $out .= '<td class="alt1" nowrap>' . $row['item_code'] . '</td>';
                $out .= '<td class="alt1" nowrap>' . $hex_code . '</td>';
                $out .= '<td class="alt1" nowrap>' . str_replace("_", " ", $row['item_name']) . '</td>';
                $out .= '</td>';
            }

            $out .= "</table>";
            $out .= "<br/>";
            $out .= $pager->renderFullNav();

            // Free Results
            @mssql_free_result($rs);

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