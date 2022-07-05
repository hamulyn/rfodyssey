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
    $module[_l('Item Shop')][_l('Manage Items')] = $file;
    return;
}

$lefttitle = _l('Item Shop Admin - Manage Items');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        $menu_array = array();
        $prepend = '';

        connectgamecpdb();
        $query = mssql_query('SELECT cat_name, cat_id, cat_sub_id FROM gamecp_shop_categories', $gamecp_dbconnect);
        while ($row = mssql_fetch_assoc($query)) {
            $menu_array[$row['cat_id']] = array('name' => $row['cat_name'], 'parent' => $row['cat_sub_id'], 'id' => $row['cat_id']);
            $prepend[$row['cat_id']] = '';
        }
        // Free Result
        @mssql_free_result($query);

        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $cat_id = (isset($_GET['cat_id']) && is_int((int)$_GET['cat_id'])) ? (int)$_GET['cat_id'] : 0;
        $query_p1 = "";
        $exit_cat = 0;
        $exit_stage = 0;
        $upgradeable_array = array(0, 1, 2, 3, 4, 5, 6, 7);
        $base_code = 268435455;
        $j = 0;
        $k = 0;
        $ups = 0;
        $sub_name = '';


        $out .= '<table class="tborder" cellpadding="3" cellspacing="1" border="0" width="50%" align="center">' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt1" style="text-align: center;"><a href="./' . $script_name . '?do=' . $_GET['do'] . '">View Items</a></td>' . "\n";
        $out .= '		<td class="alt1" style="text-align: center;"><a href="./' . $script_name . '?do=' . $_GET['do'] . '&page=addedit">Add New Item</a></td>' . "\n";
        $out .= '		<td class="alt1" style="text-align: center;"><a href="./' . $script_name . '?do=' . $_GET['do'] . '&page=edit">Edit Item</a></td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '</table>' . "\n";

        $out .= '<br/>' . "\n";

        if (empty($page)) {

            # One crazy nav function we got here!
            function get_nav($catid, $nav = array())
            {
                global $gamecp_dbconnect, $script_name;

                if ($catid != 0) {
                    $select_cat = "SELECT cat_sub_id, cat_name FROM gamecp_shop_categories WHERE cat_id = '$catid'";
                    if (!($cat_result = mssql_query($select_cat, $gamecp_dbconnect))) {
                        return 'failed: ' . mssql_get_last_message();
                    } else {
                        if (($cat = mssql_fetch_array($cat_result))) {
                            $nav[] = ' / <a href="' . $script_name . '?do=' . $_GET['do'] . '&cat_id=' . $catid . '" style="text-decoration: none;">' . $cat['cat_name'] . '</a>';
                            return get_nav($cat['cat_sub_id'], $nav);
                        } else {
                            return '';
                        }
                    }
                } else {
                    if (is_array($nav)) {
                        $nav = array_reverse($nav);
                        $list_cats = '';
                        foreach ($nav as $rev) {
                            $list_cats .= $rev;
                        }
                        return $list_cats;
                    } else {
                        return;
                    }
                }
            }

            # Game CP DB Pleaseee!
            connectgamecpdb();

            # Categories ple- wait, im the programmer
            $cat_sql = "SELECT cat_id, cat_sub_id, cat_name, cat_description FROM gamecp_shop_categories WHERE cat_sub_id = '" . $cat_id . "' ORDER BY cat_order, cat_name, cat_id DESC";
            if (!($cat_result = mssql_query($cat_sql))) {
                $exit_cat = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to obtain category information</p>';
            }

            if ($exit_cat == 0) {
                # Category results
                $cat = array();
                while ($row = mssql_fetch_array($cat_result)) {
                    $cat[] = $row;
                }
                mssql_free_result($cat_result);
                $total_categories = count($cat);

                # Set our 'selected' category id
                if ($cat_id == '') {
                    $cat_id = 0;
                }

                if ($cat_id != '0') {
                    $by_category = " AND item_cat_id = '" . $cat_id . "'";
                } else {
                    $by_category = '';
                }

                # Lets do our sorting, shall we? SHALL WE?!!? OMG!
                $order = (isset($config['shop_order_by'])) ? $config['shop_order_by'] : 1;
                $sort = (isset($config['shop_sort'])) ? $config['shop_sort'] : 1;

                if ($order == 1) {
                    $order = 'item_name';
                } elseif ($order == 2) {
                    $order = 'item_price';
                } elseif ($order == 3) {
                    $order = 'item_race';
                } elseif ($order == 4) {
                    $order = 'item_buy_count';
                } elseif ($order == 5) {
                    $order = 'item_date_added';
                } elseif ($order == 6) {
                    $order = 'item_date_updated';
                } elseif ($order == 7) {
                    $order = 'item_dbcode';
                } else {
                    $order = 'item_name';
                }

                if ($sort == 1) {
                    $sort = 'ASC';
                } else {
                    $sort = 'DESC';
                }

                // Pageination
                include('./includes/pagination/ps_pagination.php');

                // Query the items db!
                $query_p1 = "SELECT item_id,item_name,item_dbcode,item_amount,item_upgrade,item_description,item_image_url,item_price,item_buy_count,item_date_added,item_date_updated,item_race FROM gamecp_shop_items WHERE item_delete = 0 $by_category";
                $query_p2 = " AND item_id NOT IN ( SELECT TOP [OFFSET] item_id FROM gamecp_shop_items WHERE item_delete = 0 $by_category ORDER BY item_dbcode $sort) ORDER BY item_dbcode $sort";

                // Create a PS_Pagination object
                $page_gen = (isset($_REQUEST['page_gen'])) ? $_REQUEST['page_gen'] : '0';
                $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
                $pager = new PS_Pagination($gamecp_dbconnect, $query_p1, $query_p2, 20, 10, $url);

                // The paginate() function returns a mysql
                // result set for the current page
                $rs = $pager->paginate();

                # Get current cat n'fo (name)
                $nav = get_nav($cat_id, $nav = '');

                # Main layout
                $out .= '<table class="table table-bordered" align="center">' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="thead" colspan="2" style="font-size: 12px;"><a href="' . $script_name . '?do=' . $_GET['do'] . '" style="text-decoration: none;">Categories</a>' . $nav . '</td>' . "\n";
                $out .= '	</tr>' . "\n";

                if ($total_categories > 0) {
                    #
                }

                for ($i = 0; $i < $total_categories; $i++) {

                    $sub_sql = "SELECT cat_id, cat_sub_id, cat_name FROM gamecp_shop_categories WHERE cat_sub_id = '" . $cat[$i]['cat_id'] . "' ORDER BY cat_name DESC";
                    if (!($sub_result = mssql_query($sub_sql, $gamecp_dbconnect))) {
                        #
                    } else {
                        while ($sub = mssql_fetch_array($sub_result)) {
                            if ($sub['cat_sub_id'] == $cat[$i]['cat_id']) {
                                if ($k != 0) {
                                    $sub_name .= ', ';
                                }

                                $sub_name .= '<a href="' . $script_name . '?do=' . $_GET['do'] . '&cat_id=' . $sub['cat_id'] . '" style="text-decoration: none;">' . $sub['cat_name'] . '</a>';
                                $k++;
                            }
                        }
                    }

                    if (($j % 2) == 0) {
                        $out .= '	<tr>' . "\n";
                        $out .= '		<td width="50%" valign="top" class="alt1">&#187; <a href="' . $script_name . '?do=' . $_GET['do'] . '&cat_id=' . $cat[$i]['cat_id'] . '" style="font-size: 13px; text-decoration: none; font-weight: bold;">' . $cat[$i]['cat_name'] . '</a><br/> ' . $sub_name . '</td>' . "\n";
                    } else {
                        $out .= '		<td width="50%" valign="top" class="alt1">&#187; <a href="' . $script_name . '?do=' . $_GET['do'] . '&cat_id=' . $cat[$i]['cat_id'] . '" style="font-size: 13px; text-decoration: none; font-weight: bold;">' . $cat[$i]['cat_name'] . '</a><br/> ' . $sub_name . '</td>' . "\n";
                        $out .= '	</tr>' . "\n";
                    }

                    # Reset values that need resetting and set counter
                    $j++;
                    $sub_name = '';
                    $k = 0;
                }

                if ($total_categories > 0) {
                    if ($j % 2) {
                        $out .= '<td class="alt1"></td>' . "\n";
                        $out .= '	</tr>' . "\n";
                    }
                }
                $out .= '</table>' . "\n";

                $out .= '<table class="table table-bordered" align="center">' . "\n";
                $out .= '		<tr>' . "\n";
                $out .= '			<td class="alt1" colspan="' . ($total_categories + 1) . '">' . "\n";
                $out .= '				<table class="tborder" width="100%" cellpadding="6" cellspacing="1">' . "\n";
                $out .= '					<tr>' . "\n";
                $out .= '						<td valign="top" class="thead" nowrap>ID</td>' . "\n";
                $out .= '						<td valign="top" class="thead" nowrap>Date Added</td>' . "\n";
                $out .= '						<td valign="top" class="thead" nowrap>Item Name</td>' . "\n";
                $out .= '						<td valign="top" class="thead" nowrap>Price</td>' . "\n";
                $out .= '						<td valign="top" class="thead" nowrap>Times bought</td>' . "\n";
                $out .= '						<td valign="top" class="thead" style="text-align: center;" colspan="2" nowrap>Options</td>' . "\n";
                $out .= '					</tr>' . "\n";

                connectitemsdb();
                while ($item = mssql_fetch_array($rs)) {

                    $u_value = $item['item_upgrade'];
                    $item_slots = $u_value;
                    $item_slots = $item_slots - $base_code;
                    $item_slots = $item_slots / ($base_code + 1);
                    $ceil_slots = ceil($item_slots);
                    $slots_code = ($base_code + (($base_code + 1) * $ceil_slots));
                    $slots = $ceil_slots;

                    if ($u_value != $base_code AND $ceil_slots > 0) {
                        if ($slots_code != $u_value) {
                            for ($m = 1; $m <= 7; $m++) {
                                $item_ups = $u_value;
                                $item_ups = ($base_code + (($base_code + 1) * $ceil_slots)) - $item_ups;
                                $talic_id = (pow(16, $m) - 1) / 15;
                                $talic_id = ceil($item_ups / $talic_id);
                                $raw_item_u = ceil($base_code + (($base_code + 1) * $ceil_slots)) - ($talic_id * ((pow(16, $m) - 1) / 15));
                                $talic_id = "";
                                if ($raw_item_u == $u_value) {
                                    $ups = $m;
                                }
                            }
                        }

                        $item_ups = $u_value;
                        $item_ups = ($base_code + (($base_code + 1) * $ceil_slots)) - $item_ups;
                        if ($ups > 0) {
                            $talic_id = (pow(16, $ups) - 1) / 15;
                            $item_talic = ceil($item_ups / $talic_id);
                        } else {
                            $talic_id = 0;
                            $item_talic = 0;
                        }
                        $item_slots = $ceil_slots;
                        $item_ups = $ups;

                        $talic_name = talic_name($item_talic);
                        $talic_name = ($talic_name != 0 || $talic_name != 'No Talic') ? $talic_name : 'No Talics';
                        $item_upgrade = " (+$item_ups/$ceil_slots $talic_name)";
                    } else {
                        $item_upgrade = '';
                    }

                    # Get item code
                    $k_value = $item['item_dbcode'];
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
                    $items_query = mssql_query("SELECT item_code FROM " . GetItemTableName($kn) . " WHERE item_id = '$item_id'", $items_dbconnect);
                    $items = mssql_fetch_array($items_query);
                    $item_code = $items['item_code'];

                    if ($item['item_race'] == 1) {
                        $race = '<span style="color: #CC6699;">Belleto</span>';
                    } elseif ($item['item_race'] == 2) {
                        $race = '<span style="color: #9933CC;">Cora</span>';
                    } elseif ($item['item_race'] == 3) {
                        $race = '<span style="color: grey;">Accreatian</span>';
                    } elseif ($item['item_race'] == 4) {
                        $race = '<span style="color: #CC6699;">Belleto</span> & <span style="color: #9933CC;">Cora</span>';
                    } else {
                        $race = 'All Races';
                    }

                    if ($userdata['credits'] < $item['item_price']) {
                        $disable_button = ' disabled="disabled"';
                    } else {
                        $disable_button = '';
                    }

                    if ($item['item_image_url'] != "") {
                        $item_image = $item['item_image_url'];
                    } else {
                        $item_image = './includes/images/items/unknown.gif';
                    }

                    $out .= '					<tr>' . "\n";
                    $out .= '						<td valign="top" class="alt2" nowrap>' . $item['item_id'] . '</td>' . "\n";
                    $out .= '						<td valign="top" class="alt2" nowrap>' . date("d/m/y h:i:s A", $item['item_date_added']) . '</td>' . "\n";
                    $out .= '						<td valign="top" class="alt2" nowrap>[' . $item_code . '] ' . $item['item_name'] . $item_upgrade . '</td>' . "\n";
                    $out .= '						<td valign="top" class="alt2" nowrap>' . number_format($item['item_price']) . ' GP</td>' . "\n";
                    $out .= '						<td valign="top" class="alt2" nowrap>' . $item['item_buy_count'] . '</td>' . "\n";
                    $out .= '						<td valign="top" class="alt2" style="text-align: center;" nowrap><a href="' . $script_name . '?do=' . $_GET['do'] . '&page=addedit&edit_item_id=' . $item['item_id'] . '">Edit Item</a></td>' . "\n";
                    $out .= '						<td valign="top" class="alt2" style="text-align: center;" nowrap><a href="' . $script_name . '?do=' . $_GET['do'] . '&page=delete&item_id=' . $item['item_id'] . '">Delete Item</a></td>' . "\n";
                    $out .= '					</tr>' . "\n";
                }
                $out .= '				</table>' . "\n";
                $out .= '			</td>' . "\n";
                $out .= '		</tr>' . "\n";

                if (mssql_num_rows($rs) <= 0) {
                    $out .= '		<tr>' . "\n";
                    $out .= '			<td class="alt1" colspan="' . ($total_categories + 1) . '" style="text-align: center; font-weight: bold;">No items were found in this category</td>' . "\n";
                    $out .= '		</tr>' . "\n";
                } else {
                    $out .= '		<tr>' . "\n";
                    $out .= '			<td class="alt2" colspan="' . ($total_categories + 1) . '" style="text-align: center; font-weight: bold;">' . $pager->renderFullNav() . '</td>' . "\n";
                    $out .= '		</tr>' . "\n";
                }

                $out .= '</table>' . "\n";

                // Free Result
                @mssql_free_result($rs);
            }

        } elseif ($page == 'addedit') {

            # Main variables
            $add_submit = (isset($_POST['add_submit'])) ? 1 : 0;
            $edit_submit = (isset($_POST['edit_submit'])) ? 1 : 0;
            $exit_process = 0;
            $exit_text = '';
            $display_form = 1;
            $do_process = 0;
            if (isset($_POST['edit_item_id']) || isset($_GET['edit_item_id'])) {
                $edit_item_id = (isset($_POST['edit_item_id'])) ? $_POST['edit_item_id'] : $_GET['edit_item_id'];
                if (!is_numeric($edit_item_id)) {
                    $edit_item_id = '';
                }
            } else {
                $edit_item_id = '';
            }

            # Form variables
            $item_status = (isset($_POST['item_status']) && is_int((int)$_POST['item_status'])) ? antiject($_POST['item_status']) : 1;
            $item_cat_id = (isset($_POST['item_cat_id']) && is_int((int)$_POST['item_cat_id'])) ? antiject($_POST['item_cat_id']) : 0;
            $item_price = (isset($_POST['item_price']) && is_numeric((int)$_POST['item_price'])) ? antiject($_POST['item_price']) : 0;
            $item_code = (isset($_POST['item_code'])) ? antiject($_POST['item_code']) : '';
            $item_name = (isset($_POST['item_name'])) ? antiject(trim($_POST['item_name'])) : '';
            $item_description = (isset($_POST['item_description'])) ? antiject(trim($_POST['item_description'])) : '';
            $item_amount = (isset($_POST['item_amount']) && is_int((int)$_POST['item_amount'])) ? antiject((int)$_POST['item_amount']) : 1;
            $item_custom_amount = (isset($_POST['item_custom_amount'])) ? antiject($_POST['item_custom_amount']) : 0;
            $item_upgrade = (isset($_POST['item_ups']) && is_int((int)$_POST['item_ups'])) ? antiject($_POST['item_ups']) : 0;
            $item_race = (isset($_POST['item_race']) && is_int((int)$_POST['item_race'])) ? antiject($_POST['item_race']) : 0;
            $item_image_url = (isset($_POST['item_image_url'])) ? antiject($_POST['item_image_url']) : '';
            $item_dbcode = 0;

            # Do we have to process data?
            if ($add_submit == 1 or $edit_submit == 1) {
                $do_process = 1;
            }

            # Okay, if 'editing' we should get the data from..say the database....? yes...? talk like this....?
            if ($edit_item_id != '') {
                $page_mode = 'edit_submit';
                $submit_name = 'Update Item';
                $this_mode_title = 'Editing a item';

                if ($do_process == 0) {
                    connectgamecpdb();
                    $select_sql = "SELECT TOP 1 item_cat_id,item_status,item_id,item_dbcode,item_upgrade,item_name,item_amount,item_custom_amount,item_description,item_image_url,item_price,item_buy_count,item_date_added,item_date_updated,item_race FROM gamecp_shop_items WHERE item_delete = 0 AND item_id = '$edit_item_id'";
                    if (!($data = mssql_query($select_sql))) {
                        $exit_cat = 1;
                        $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to obtain item information</p>';
                    }

                    if ($exit_cat == 0) {
                        if (!($item = mssql_fetch_array($data))) {
                            $display_form = 0;
                            $out .= '<p style="text-align: center; font-weight: bold;">Invalid item id supplied</p>';
                        } else {
                            $item_status = $item['item_status'];
                            $item_cat_id = $item['item_cat_id'];
                            $item_price = $item['item_price'];
                            $item_code = $item['item_dbcode'];
                            $item_name = $item['item_name'];
                            $item_description = $item['item_description'];
                            $item_amount = $item['item_amount'];
                            $item_custom_amount = $item['item_custom_amount'];
                            $item_upgrade = $item['item_upgrade'];
                            $item_race = $item['item_race'];
                            $item_image_url = $item['item_image_url'];

                            connectitemsdb();
                            $k_value = $item_code;
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
                            $items_query = mssql_query("SELECT item_code FROM " . GetItemTableName($kn) . " WHERE item_id = '$item_id'", $items_dbconnect);
                            $items = mssql_fetch_array($items_query);
                            $item_code = $items['item_code'];

                            $u_value = $item_upgrade;
                            $item_slots = $u_value;
                            $item_slots = $item_slots - $base_code;
                            $item_slots = $item_slots / ($base_code + 1);
                            $ceil_slots = ceil($item_slots);
                            $slots_code = ($base_code + (($base_code + 1) * $ceil_slots));
                            $slots = $ceil_slots;

                        }
                    } else {
                        $display_form = 0;
                        $do_process = 0;
                    }
                    // Free Result
                    @mssql_free_result($data);
                }
            } else {
                $page_mode = 'add_submit';
                $submit_name = 'Add Item';
                $this_mode_title = 'Adding a new item';
            }

            # Processing data...if called
            if ($do_process == 1) {

                // Obtain the item KIND and TABLE
                $item_kind = GetItemTableCode($item_code);
                $table_name = GetItemTableName($item_kind);
                $item_code = str_replace("%", "", $item_code);

                # Error checking here
                # If an error, set exit_process to 1
                if ($item_code == '') {
                    $exit_process = 1;
                    $exit_text .= '&raquo; Item Code was left blank<br/>';
                }

                if ($item_description == '') {
                    $exit_process = 1;
                    $exit_text .= '&raquo; Item Description was left blank<br/>';
                }

                if ($item_price == '') {
                    $exit_process = 1;
                    $exit_text .= '&raquo; Item Price was left blank<br/>';
                } elseif (!is_numeric($item_price)) {
                    $exit_process = 1;
                    $exit_text .= '&raquo; Item Price was not a pure number<br/>';
                }

                if (!is_numeric($item_amount)) {
                    $exit_process = 1;
                    $exit_text .= '&raquo; Item amount was not a pure number<br/>';
                } elseif ($item_amount < 1 && !in_array($item_kind, $upgradeable_array)) {
                    $exit_process = 1;
                    $exit_text .= '&raquo; Item amount must be greather than 1<br/>';
                }

                # Special error check
                # checks to see if supplied item code is valid
                if ($exit_process != 1) {

                    // Connect to the database
                    connectitemsdb();

                    // Query the database
                    // If the query fails, return invalid item/kind
                    if (!($table_query = mssql_query("SELECT item_id, item_name FROM $table_name WHERE item_code = '$item_code'", $items_dbconnect))) {
                        $exit_process = 1;
                        $exit_text .= '&raquo; The item ' . $item_code . ' does not have an existing item type<br/>';
                    } else {
                        // If query fails, return invalid item, right kind!
                        if ($table = @mssql_fetch_array($table_query)) {
                            $item_id = $table['item_id'];

                            // Okay, so if no item name supplied, lets use the databases!
                            if ($item_name == '') {
                                $item_name = str_replace("_", " ", $table['item_name']);
                                $pos = strrpos($item_name, '(');
                                if ($pos == true && $item_kind != tbl_code_weapon) {
                                    $race = explode("(", $item_name);
                                    $race = str_replace("(", "", $race[1]);
                                    $race = str_replace(")", "", $race);

                                    if ($race == 'A' OR $race == 'Accretia') {
                                        $item_race = '3';
                                    } elseif ($race == 'B' OR $race == 'Bellato') {
                                        $item_race = '1';
                                    } elseif ($race == 'C' OR $race == 'Cora') {
                                        $item_race = '2';
                                    } else {
                                        $item_race = $item_race;
                                    }

                                    $item_name = substr($item_name, 0, $pos);
                                }
                            }

                            // Special for amount: If upgradable, then no amount, right?
                            if (in_array($item_kind, $upgradeable_array)) {
                                $item_amount = 0;
                            }

                            // Item DB Code
                            $item_dbcode = 65536 * $item_id + ($item_kind * 256 + 0);

                            if (!in_array($item_kind, $upgradeable_array)) {
                                $item_upgrade = $base_code;
                            }

                            if ($item_upgrade != $base_code) {
                                $item_custom_amount = 0;
                            }
                        } else {
                            $exit_process = 1;
                            $exit_text .= '&raquo; The item ' . $item_code . ' does not exist<br/>';
                        }
                    }
                    // Free Result
                    @mssql_free_result($table_query);
                }

                if ($exit_process == 1) {
                    $out .= '<table class="table table-bordered">' . "\n";
                    $out .= '		<tr>' . "\n";
                    $out .= '			<td>' . "\n";
                    $out .= '				' . $exit_text . "\n";
                    $out .= '			</td>' . "\n";
                    $out .= '		</tr>' . "\n";
                    $out .= '</table>' . "\n";
                } else {
                    connectgamecpdb();
                    $display_form = 0;
                    $time_now = time();

                    if ($add_submit == 1) {
                        $insert_sql = "INSERT INTO gamecp_shop_items (item_status,item_cat_id,item_date_added,item_date_updated,item_race,item_price,item_dbcode,item_upgrade,item_name,item_description,item_image_url,item_amount,item_custom_amount) VALUES ('$item_status','$item_cat_id','$time_now','$time_now','$item_race','$item_price','$item_dbcode','$item_upgrade','$item_name','$item_description','$item_image_url','$item_amount','$item_custom_amount')";

                        if (!($query_insert = mssql_query($insert_sql))) {
                            $out .= '<p style="text-align: center; font-weight: bold;">SQL Error, cannot add item to the database</p>';
                        } else {
                            $out .= '<p style="text-align: center; font-weight: bold;">Successfully added the new item!</p>';
                            // Writing an admin log :D
                            gamecp_log(0, $userdata['username'], "ADMIN - MANAGE ITEMS - ADDED - New item: $item_name", 1);
                        }
                    } elseif ($edit_submit == 1) {
                        $update_sql = "UPDATE gamecp_shop_items SET item_status = '$item_status', item_date_updated = '$time_now', item_cat_id = '$item_cat_id', item_race = '$item_race', item_price = '$item_price', item_dbcode = '$item_dbcode', item_upgrade = '$item_upgrade', item_name = '$item_name', item_description = '$item_description', item_image_url = '$item_image_url', item_amount = '$item_amount', item_custom_amount = '$item_custom_amount' WHERE item_delete = 0 AND item_id = '$edit_item_id'";

                        if (!($update_insert = mssql_query($update_sql, $gamecp_dbconnect))) {
                            $out .= '<p style="text-align: center; font-weight: bold;">SQL Error, cannot update item to the database</p>';
                        } else {
                            if (mssql_rows_affected($gamecp_dbconnect) <= 0) {
                                $out .= _l('no_such_item');
                            } else {
                                $out .= '<p style="text-align: center; font-weight: bold;">Successfully updated this item!</p>';
                                $out .= '<meta http-equiv="REFRESH" content="1;url=./' . $script_name . '?do=' . $_GET['do'] . '&cat_id=' . $item_cat_id . '">';
                                // Writing an admin log :D
                                gamecp_log(0, $userdata['username'], "ADMIN - MANAGE ITEMS - UPDATED - Item ID: $edit_item_id | Item Name: $item_name", 1);
                            }
                        }
                    }
                }

            }

            # Display the main form
            if ($display_form == 1) {

                # Main database connection
                connectgamecpdb();

                generate_menu($menu_array, 0, '', $prepend, $item_cat_id);
                $subcategory_list = $options;

                # Categories ple- wait, im the programmer
                $cat_sql = "SELECT cat_id, cat_name, cat_description FROM gamecp_shop_categories ORDER BY cat_order, cat_name, cat_id DESC";
                if (!($cat_result = mssql_query($cat_sql))) {
                    $exit_cat = 1;
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to obtain category information</p>';
                }

                if ($exit_cat == 0) {

                    $cat = array();
                    while ($row = mssql_fetch_array($cat_result)) {
                        $cat[] = $row;
                    }
                    mssql_free_result($cat_result);
                    $total_categories = count($cat);

                    if ($total_categories > 0) {
                        $out .= '<form method="post">' . "\n";
                        $out .= '<table class="table table-bordered">' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="thead" colspan="2"><span style="font-weight: bold;">' . $this_mode_title . '</span><br/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Enable Item</span><br/><i>Enable/Disable this item from being bought</i></td>' . "\n";
                        if ($item_status == 0) {
                            $check_no = ' checked';
                            $check_yes = '';
                        } else {
                            $check_yes = ' checked';
                            $check_no = '';
                        }
                        $out .= '			<td class="alt1">Yes <input name="item_status" type="radio" value="1"' . $check_yes . '/> No <input name="item_status" type="radio" value="0"' . $check_no . '/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Category</span><br/><i>The price of game points for this item</i></td>' . "\n";
                        $out .= '			<td class="alt1">' . "\n";
                        $out .= '				<select class="form-control"name="item_cat_id">' . "\n";
                        $out .= $subcategory_list . "\n";
                        $out .= '				</select>' . "\n";
                        $out .= '			</td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Price</span><br/><i>The price of game points for this item</i></td>' . "\n";
                        $out .= '			<td class="alt1"><input name="item_price" type="text" value="' . $item_price . '"/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Item Code</span><br/><i>The GM Command code for the item (i.e. iwkna01)</i></td>' . "\n";
                        $out .= '			<td class="alt1"><input name="item_code" type="hidden" value="' . $item_code . '" class="item_code_ajax"/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Item Name</span><br/><i>Optional Name for this item (default name will be item name from the database)</i></td>' . "\n";
                        $out .= '			<td class="alt1"><input name="item_name" type="text" value="' . $item_name . '"/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Description</span><br/><i>A brief description that will show up on the item list</i></td>' . "\n";
                        $out .= '			<td class="alt1"><input name="item_description" type="text" value="' . $item_description . '"/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Amount</span><br/><i>Quantity of this item (i.e. 99 HP Pots). 0 for non-stackable items.</i></td>' . "\n";
                        $out .= '			<td class="alt1"><input name="item_amount" type="text" value="' . $item_amount . '"/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Custom Amount</span><br/><i>Allow users to select the amount they want to buy of this item</i></td>' . "\n";
                        if ($item_custom_amount == 0) {
                            $check_no = ' checked';
                            $check_yes = '';
                        } else {
                            $check_yes = ' checked';
                            $check_no = '';
                        }
                        $out .= '			<td class="alt1">Yes <input name="item_custom_amount" type="radio" value="1"' . $check_yes . '/> No <input name="item_custom_amount" type="radio" value="0"' . $check_no . '/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Upgrades</span><br/><i>If the item cannot be upgraded, select "no italic" and +0/0</i></td>' . "\n";
                        $out .= '			<td class="alt1">' . "\n";
                        $out .= upgradeTool(1, 'item_ups');
                        $out .= '<input type="hidden" name="item_ups" value="' . $item_upgrade . '" id="item_ups">';
                        $out .= '			</td>' . "\n";
                        $out .= '		</tr>' . "\n";

                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Race</span><br/><i>An external URL to display an image for the item</i></td>' . "\n";
                        $out .= '			<td class="alt1">' . "\n";
                        $out .= '			<select class="form-control"name="item_race">';
                        for ($m = 0; $m <= 4; $m++) {
                            if ($m == 0) {
                                $race = "All Races";
                            } elseif ($m == 1) {
                                $race = "Bellato";
                            } elseif ($m == 2) {
                                $race = "Cora";
                            } elseif ($m == 3) {
                                $race = "Accretia";
                            } elseif ($m == 4) {
                                $race = "Bellato & Cora";
                            } else {
                                $race = "All Races";
                            }
                            $select_race = '';
                            if ($m == $item_race) {
                                $select_race = ' selected';
                            }
                            $out .= '				<option value="' . $m . '"' . $select_race . '>' . $race . '</option>';
                        }
                        $out .= '				</select>';
                        $out .= '			</td>' . "\n";
                        $out .= '		</tr>' . "\n";

                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt2"><span style="font-weight: bold;">Item Image URL</span><br/><i>An external URL to display an image for the item</i></td>' . "\n";
                        $out .= '			<td class="alt1"><input name="item_image_url" type="text" value="' . $item_image_url . '"/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '		<tr>' . "\n";
                        $out .= '			<td class="alt1" colspan="2" style="text-align: center;">';
                        $out .= '			<input name="page" type="hidden" value="addedit"/>';
                        $out .= '			<input name="' . $page_mode . '" type="submit" value="' . $submit_name . '"/></td>' . "\n";
                        $out .= '		</tr>' . "\n";
                        $out .= '</table>' . "\n";
                        $out .= '</form>' . "\n";
                    } else {

                        $out .= "No categories found";

                    }

                }

            }

        } elseif ($page == 'delete') {

            $item_id = (isset($_GET['item_id']) && is_int((int)$_GET['item_id'])) ? antiject((int)$_GET['item_id']) : '';

            if ($item_id == '') {
                $out .= _l('no_such_item');
            } else {
                connectgamecpdb();
                $item_query = mssql_query("SELECT item_name,item_id FROM gamecp_shop_items WHERE item_id = '$item_id'");
                $item_info = mssql_fetch_array($item_query);

                if (mssql_num_rows($item_query) <= 0) {
                    $out .= _l('no_such_item');
                } else {
                    $out .= '<form method="post">' . "\n";
                    $out .= '<p style="text-align: center; font-weight: bold;">Are you sure you want the delete the Item: <u>' . $item_info['item_name'] . '</u>?</p>' . "\n";
                    $out .= '<p style="text-align: center;"><input type="hidden" name="item_id" value="' . $item_id . '"/><input type="hidden" name="page" value="delete_item"/><input type="submit"  class="btn btn-default" name="yes" value="Yes"/> <input type="submit"  class="btn btn-default" name="no" value="No"/></p>';
                    $out .= '</form>';
                }
            }

        } elseif ($page == 'delete_item') {

            $yes = (isset($_POST['yes'])) ? '1' : '0';
            $no = (isset($_POST['no'])) ? '1' : '0';
            if (isset($_POST['item_id']) && is_int((int)$_POST['item_id'])) {
                $item_id = antiject((int)$_POST['item_id']);
            } else {
                $item_id = 0;
            }

            if ($no != 1 && $item_id != 0) {
                connectgamecpdb();
                $item_query = mssql_query("SELECT item_id,item_name FROM gamecp_shop_items WHERE item_id = '$item_id'", $gamecp_dbconnect);
                $item = mssql_fetch_array($item_query);

                if (mssql_num_rows($item_query) <= 0) {
                    $out .= _l('no_such_item');
                } else {
                    $cquery = mssql_query("UPDATE gamecp_shop_items SET item_delete = 1 WHERE item_id = " . $item_id);

                    $out .= '<p style="text-align: center; font-weight: bold;">Deleted the item name: ' . $item['item_name'] . ' (#' . $item['item_id'] . ')</p>';
                    gamecp_log(3, $userdata['username'], "ADMIN - MANAGE ITEMS - DELETED - Item Name:  " . $item['item_name'] . " | Item ID: " . $item['item_id'], 1);
                }
            } else {
                header("Location: $script_name?do=" . $_GET['do']);
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