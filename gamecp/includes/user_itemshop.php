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
    $module[_l('Item Shop')][_l('Buy Item Shop')] = $file;
    return;
}

$lefttitle = _l('Buy Item Shop');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if ($isuser == true) {

        # This is not for public use, ensure it is disabled
        $config['shop_discount'] = 0;

        # Setting our main 'globally wanted' variables
        $page = (isset($_REQUEST['page'])) ? antiject($_REQUEST['page']) : "";
        $cat_id = (isset($_GET['cat_id'])) ? intval($_GET['cat_id']) : 0;
        $cat_id = (isset($cat_id) && is_int($cat_id)) ? antiject($cat_id) : "0";
        $today = time();
        $base_code = 268435455;
        $exit_cat = 0;
        $race = '';
        $query_p2 = '';
        $j = 0;
        $k = 0;
        $sub_name = '';
        $is_char = false;

        # Set sort and order vars
        if (isset($_POST['order']) && isset($_POST['sort'])) {
            $sortorder_data = $_POST['order'] . chr(255) . $_POST['sort'];
            setcookie("sortorder", $sortorder_data, time() + 31104000);
            $order = antiject($_POST['order']);
            $sort = antiject($_POST['sort']);
        } elseif (isset($_COOKIE['sortorder'])) {
            $sortorder = explode(chr(255), $_COOKIE['sortorder']);

            $order = $sortorder[0];
            $sort = $sortorder[1];
        } else {
            $order = (isset($config['shop_order_by'])) ? $config['shop_order_by'] : '1';
            $sort = (isset($config['shop_sort'])) ? $config['shop_sort'] : '1';
        }

        # Convert numbers to - taaabllleeeesss
        $order_raw = $order;
        $sort_raw = $sort;

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
                        @mssql_free_result($cat_result);
                        return get_nav($cat['cat_sub_id'], $nav);
                    } else {
                        @mssql_free_result($cat_result);
                        return '';
                    }
                }
                @mssql_free_result($cat_result);
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

        # Lets do our sorting, shall we? SHALL WE?!!? OMG!
        $config['shop_order_by'] = (isset($config['shop_order_by'])) ? $config['shop_order_by'] : 'item_name';
        $order = ($order == '') ? $config['shop_order_by'] : $order;

        $config['shop_sort'] = (isset($config['shop_sort'])) ? $config['shop_sort'] : 'ASC';
        $short = ($sort == '') ? $config['shop_sort'] : $sort;

        # Query the user baen list
        /*connectgamecpdb();
        $user_accounts_sql = "SELECT user_other_accounts FROM gamecp_gamepoints WHERE user_account_id = '".$userdata['serial']."'";
        if(!($user_accounts_result = mssql_query($user_accounts_sql))) {
            echo "Unable to select query the Vote Points table";
            exit;
        }
        $user_accounts = mssql_fetch_array($user_accounts_result);*/


        connectuserdb();
        /*$query_ban = mssql_query("SELECT nAccountSerial FROM tbl_UserBan WHERE nAccountSerial IN (".$user_accounts['user_other_accounts'].") AND GMWriter != 'WS0' AND szReason != 'Multiple Account Voting' AND szReason != 'Multiple Account Warning'");

        # Has the user been banned?
        if($userdata['serial'] != "" && mssql_num_rows($query_ban) > 0 && !in_array($userdata['username'],$super_admin)) {
            $page = 'ban';
        }
        // Free Results
        @mssql_free_result($user_accounts_result);
        @mssql_free_result($query_ban);*/

        if (empty($page)) {

            # Display main header
            $out .= '<p style="font-weight: bold; font-size: 15px; text-align: center;">Your account currently has <span style="color: #8F92E8;">' . number_format($userdata['points'], 2) . '</span> Game Points <span style="color: #8F92E8;">' . number_format($userdata['vote_points'], 2) . '</span> Vote Points</p>' . "\n";

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

                // Pageination
                include('./includes/pagination/ps_pagination.php');

                // Query the items db!
                $query_p1 = "SELECT item_id,item_name,item_dbcode,item_image_url,item_amount,item_upgrade,item_description,item_price,item_buy_count,item_date_added,item_date_updated,item_race FROM gamecp_shop_items WHERE item_delete = 0 AND item_status = 1 $by_category";
                $query_p2 = " AND item_id NOT IN ( SELECT TOP [OFFSET] item_id FROM gamecp_shop_items WHERE item_delete = 0 AND item_status = 1 $by_category ORDER BY $order $sort) ORDER BY $order $sort";

                // Create a PS_Pagination object
                $page_gen = (isset($_REQUEST['page_gen'])) ? $_REQUEST['page_gen'] : '0';
                $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
                $filename = $_GET['do'] . "_" . md5($by_category);
                if (!$query_count = readCache($filename . ".cache", 5)) {
                    $query_count = mssql_query("SELECT COUNT(*) AS Count FROM gamecp_shop_items WHERE item_delete = 0 AND item_status = 1 $by_category");
                    $query_count = mssql_fetch_array($query_count);
                    $query_count = $query_count['Count'];
                    writeCache($query_count, $filename . '.cache');
                }
                $pager = new PS_Pagination($gamecp_dbconnect, $query_p1, $query_p2, 20, 10, $url, $query_count);

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

                # Temp tbh
                $order_array = array('', 'Name', 'Price', 'Race', 'Popularity', 'Date Added', 'Date Updated', 'Item Group');
                $sort_array = array('', 'Ascending', 'Descending');

                # Draw sort/order options
                $out .= '<table cellpadding="3" cellspacing="1" border="0" width="100%">' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td style="text-align: right;">' . "\n";
                $out .= '			<form method="post" style="margin: 0 0 10px 0; padding: 0; float: right;">' . "\n";
                $out .= '           <div class="row">';
                $out .= '               <div class="col-xs-5">';
                $out .= '			<select class="form-control" name="order">' . "\n";
                for ($f = 1; $f < count($order_array); $f++) {
                    if ($order_raw == $f) {
                        $selected = ' selected="selected"';
                    } else {
                        $selected = '';
                    }
                    $out .= '						<option value="' . $f . '"' . $selected . '>' . $order_array[$f] . '</option>' . "\n";
                }
                $out .= '					  </select> ' . "\n";
                $out .= '                     </div>';
                $out .= '                       <div class="col-xs-4">';
                $out .= '					  <select class="form-control" name="sort">' . "\n";
                for ($s = 1; $s < count($sort_array); $s++) {
                    if ($sort_raw == $s) {
                        $selected = ' selected="selected"';
                    } else {
                        $selected = '';
                    }
                    $out .= '						<option value="' . $s . '"' . $selected . '>' . $sort_array[$s] . '</option>' . "\n";
                }
                $out .= '					  </select> ' . "\n";
                $out .= '                     </div>';
                $out .= '                       <div class="col-xs-2">';
                $out .= '			<input type="submit"  class="btn btn-default" name="Change" value="Change"/>' . "\n";
                $out .= '                     </div>';
                $out .= '           </div>';
                $out .= '			</form>' . "\n";
                $out .= '		</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '</table>' . "\n";

                $out .= '<table class="table table-bordered" align="center">' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="thead">Item List</td>' . "\n";
                $out .= '	</tr>' . "\n";

                connectitemsdb();
                while ($item = mssql_fetch_array($rs)) {

                    $u_value = $item['item_upgrade'];
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

                    if ($today < ($item['item_date_added'] + 172800)) {
                        $new = ' <span style="color: red;">*NEW*</span>';
                    } else {
                        $new = '';
                    }

                    $item_price = $item['item_price'];
                    $item_final_price = $item_price;

                    if ($item['item_race'] == 1) {
                        $race = '<span style="color: #CC6699;">Bellato</span>';
                    } elseif ($item['item_race'] == 2) {
                        $race = '<span style="color: #9933CC;">Cora</span>';
                    } elseif ($item['item_race'] == 3) {
                        $race = '<span style="color: grey;">Accreatian</span>';
                    } elseif ($item['item_race'] == 4) {
                        $race = '<span style="color: #CC6699;">Bellato</span> & <span style="color: #9933CC;">Cora</span>';
                    } else {
                        $race = 'All Races';
                    }

                    if ($userdata['vote_points'] < $item_final_price) {
                        $disable_button = ' DISABLED';
                    } else {
                        $disable_button = '';
                    }

                    if ($item['item_image_url'] != " ") {
                        $item_image = $item['item_image_url'];
                    } else {
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

                        $image_path = glob("./includes/images/items/$item_code{.jpg,.JPG,.gif,.GIF,.png,.PNG}", GLOB_BRACE);

                        if (file_exists(@$image_path[0])) {
                            $item_image = $image_path[0];
                        } else {
                            $item_image = './includes/images/items/unknown.gif';
                        }

                        // Free Results
                        @mssql_free_result($items_query);
                    }

                    $out .= '		<tr>' . "\n";
                    $out .= '			<td class="alt1" colspan="' . ($total_categories + 1) . '">' . "\n";
                    $out .= '				<div class="panel">' . "\n";
                    $out .= '				<form method="post">' . "\n";
                    $out .= '				<table width="100%" cellpadding="6" cellspacing="1">' . "\n";
                    $out .= '					<tr>' . "\n";
                    $out .= '						<td width="1" valign="top" nowrap><img src="' . $item_image . '"/></td>' . "\n";
                    $out .= '						<td valign="top">' . "\n";
                    $out .= '						<span style="font-size: 14px; font-weight: bold;">' . $item['item_name'] . $new . '</span><br/>' . "\n";
                    $out .= '						<span style="font-size: 13px;">' . $item['item_description'] . '<br/>' . "\n";
                    $out .= '						Race: ' . $race . '</span><br/>' . "\n";
                    if ($item['item_amount'] > 0) {
                        $out .= '						Amount: ' . $item['item_amount'] . '<br/>' . "\n";
                    }
                    $out .= '						Times bought: ' . number_format($item['item_buy_count']) . '<br/>' . "\n";
                    if ($upgrades != "") {
                        $out .= '						' . $upgrades . '<br/>' . "\n";
                    }
                    $out .= '						</td>' . "\n";
                    $out .= '						<td width="1%" style="text-align: right; font-size: 14px;" valign="top" nowrap="nowrap">';
                    $out .= '<b>' . number_format($item_final_price, 2, '.', '') . '</b> VP<br/>';
                    $out .= '<br/><input type="submit"  class="btn btn-success" name="submit" value="Buy now!"' . $disable_button . '/></td>' . "\n";
                    $out .= '					</tr>' . "\n";
                    $out .= '				</table>' . "\n";
                    $out .= '				<input type="hidden" name="page" value="buy"/><input type="hidden" name="item_id" value="' . $item['item_id'] . '"/>' . "\n";
                    $out .= '				</form>' . "\n";
                    $out .= '				</div>' . "\n";
                    $out .= '			</td>' . "\n";
                    $out .= '		</tr>' . "\n";
                }

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
                // Free Results
                @mssql_free_result($rs);
            }
            mssql_free_result($cat_result);

        } elseif ($page == 'buy') {

            # Main buy variables, and exit variable
            $exit_buy = 0;
            $item_id = (isset($_POST['item_id']) && is_int((int)$_POST['item_id'])) ? (int)$_POST['item_id'] : '';

            # Error checking
            if ($item_id == '') {
                $exit_buy = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">Invalid Item ID provided</p>' . "\n";
            }

            # We cannot allow them to buy items if they are logged in man!
            $t_login = strtotime($userdata['lastlogintime']);
            $t_logout = strtotime($userdata['lastlogofftime']);
            $t_cur = time();
            $t_maxlogin = $t_login + 3600;

            #if($t_maxlogin < $t_cur) {
            #	$status = "offline";
            #} else
            if ($t_login <= $t_logout) {
                $status = "offline";
            } else {
                $status = "online";
            }

            if ($status == 'online') {
                $exit_buy = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">You cannot buy items when logged into the game!<br/>If you have logged out and yet see this message, log back in and properly log out again (click the log out button!).</p>' . "\n";
            }

            # Special error check: Is this a real ID?
            if ($exit_buy != 1) {
                connectgamecpdb();
                $item_sql = "SELECT item_name, item_amount, item_custom_amount, item_upgrade, item_dbcode, item_price, item_race FROM gamecp_shop_items WHERE  item_delete = 0 AND item_status = 1 AND item_id = '$item_id'";
                if (!($item_result = mssql_query($item_sql))) {
                    $exit_buy = 1;
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to obtain item information</p>';
                }

                if (!($item = mssql_fetch_array($item_result))) {
                    $exit_buy = 1;
                    $out .= _l('no_such_item');
                } else {
                    $item_name = $item['item_name'];
                    $item_price = $item['item_price'];
                    $item_dbcode = $item['item_dbcode'];
                    $item_amount = ($item['item_amount'] < 1) ? 1 : $item['item_amount'];
                    $item_custom_amount = $item['item_custom_amount'];
                    $item_upgrade = $item['item_upgrade'];
                    $item_race = $item['item_race'];
                    $item_final_price = $item_price;
                    $item_price = $item_final_price;

                    # Just for checks, to make sure nothing goes wrong!
                    if ($item_name == '' || $item_price < 0 || $item_dbcode == '' || $item_upgrade == '') {
                        $exit_buy = 1;
                        $out .= '<p style="text-align: center; font-weight: bold;">Invalid data supplied by the database, contact the admin.</p>';
                    }

                    # Now, do we have enough "G" to buy this?
                    if ($userdata['vote_points'] < $item_price) {
                        $exit_buy = 1;
                        $out .= '<p style="text-align: center; font-weight: bold;">You do not have enough of Vote Points to purchase this item.</p>';
                    }
                }
                // Free Results
                @mssql_free_result($item_result);
            }

            # Now, working with the characters
            connectdatadb();

            $char_sql = "SELECT Serial, Name, Lv, Race FROM tbl_base WHERE DCK = 0 AND AccountSerial = '" . $userdata['serial'] . "'";
            if (!($char_result = mssql_query($char_sql))) {
                $exit_buy = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to character information</p>';
            }
            while ($row = mssql_fetch_array($char_result)) {
                $chars[] = $row;
            }
            // Free Results
            @mssql_free_result($char_result);

            if (!($num_chars = @count($chars))) {
                $exit_buy = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">You do not have any characters on your account</p>';
            }

            if ($exit_buy == 0) {
                # Set our new title :D
                $lefttitle .= ' - Buying an Item';

                # Get our race names
                if ($item['item_race'] == 1) {
                    $race = '<span style="color: #CC6699;">Bellato</span>';
                } elseif ($item['item_race'] == 2) {
                    $race = '<span style="color: #9933CC;">Cora</span>';
                } elseif ($item['item_race'] == 3) {
                    $race = '<span style="color: grey;">Accreatian</span>';
                } elseif ($item['item_race'] == 4) {
                    $race = '<span style="color: #CC6699;">Bellato</span> & <span style="color: #9933CC;">Cora</span>';
                } else {
                    $race = 'All Races';
                }

                # Display main header
                $out .= '<p style="font-weight: bold; font-size: 15px; text-align: center;">Your account currently has <span style="color: #8F92E8;">'.number_format($userdata['points']).'</span> Game Points <span style="color: #8F92E8;"> '.number_format($userdata['vote_points']).'</span> Vote Point</p>' . "\n";

                # Do we have an upgraded Item?!?
                $u_value = $item_upgrade;
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
                    $bgc = ' background-color: #10171f;';

                } else {
                    $upgrades = "";
                    $bgc = "";
                }

                # Colspan value
                $colspan = 4;
                if ($upgrades == "") {
                    $colspan -= 1;
                }

                if ($item_amount == "") {
                    $colspan -= 1;
                } else {
                    if ($ceil_slots <= 0 && $item_custom_amount == 1) {
                        $item_raw_amount = $item_amount;
                        $single_item_price = ceil($item_price / $item_raw_amount);
                        $item_amount = '<select class="form-control"name="item_amount" id="amount_1" onChange="calculate_amount(1,\'' . $item_price . '\',\'' . $item_amount . '\',\'' . $userdata['vote_points'] . '\');">' . "\n";
                        for ($p = 1; $p < 100; $p++) {
                            $max_amount = floor($p * $single_item_price);
                            if ($userdata['vote_points'] < $max_amount) {
                                continue;
                            }
                            if ($p == $item_raw_amount) {
                                $select = ' selected="selected"';
                            } else {
                                $select = '';
                            }
                            $item_amount .= '	<option' . $select . '>' . $p . '</option>' . "\n";
                        }
                        $item_amount .= '</select>';
                    }
                }


                # Display main layout
                $out .= '<form method="post">' . "\n";
                $out .= '<table class="table table-bordered" align="center">' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="thead">Buying the Item</td>' . "\n";
                if ($upgrades != "") {
                    $out .= '		<td class="thead">Upgrade</td>' . "\n";
                }
                if ($item_amount != "") {
                    $out .= '		<td class="thead">Amount</td>' . "\n";
                }
                $out .= '		<td class="thead">Race</td>' . "\n";
                $out .= '		<td class="thead">Price</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" style="font-weight: bold;">' . $item_name . '</td>' . "\n";
                if ($upgrades != "") {
                    $out .= '		<td class="alt1" style="' . $bgc . '">' . $upgrades . '</td>' . "\n";
                }
                if ($item_amount != "") {
                    $out .= '		<td class="alt1">' . $item_amount . '</td>' . "\n";
                }
                $out .= '		<td class="alt1">' . $race . '</td>' . "\n";
                $out .= '		<td class="alt1" style="font-weight: bold;" id="price_1">' . number_format($item['item_price'], 2) . ' VP</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" style="text-align: right;" colspan="' . $colspan . '">Total VP after purchase:</td>' . "\n";
                $out .= '		<td class="alt2" style="font-weight: bold;" id="gpafter_1">' . number_format(($userdata['vote_points'] - $item_final_price), 2) . ' VP</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '</table>' . "\n";

                $char_select = '<select class="form-control"name="char_serial">' . "\n";
                foreach ($chars as $char) {
                    if ($item_race == 1 && ($char['Race'] == 0 || $char['Race'] == 1)) {
                        $is_char = true;
                        $char_select .= '<option value="' . $char['Serial'] . '">Lv: ' . $char['Lv'] . ' - ' . $char['Name'] . '</option>' . "\n";
                    } elseif ($item_race == 2 && ($char['Race'] == 2 || $char['Race'] == 3)) {
                        $is_char = true;
                        $char_select .= '<option value="' . $char['Serial'] . '">Lv: ' . $char['Lv'] . ' - ' . $char['Name'] . '</option>' . "\n";
                    } elseif ($item_race == 3 && ($char['Race'] == 4)) {
                        $is_char = true;
                        $char_select .= '<option value="' . $char['Serial'] . '">Lv: ' . $char['Lv'] . ' - ' . $char['Name'] . '</option>' . "\n";
                    } elseif ($item_race == 4 && ($char['Race'] == 0 || $char['Race'] == 1 || $char['Race'] == 2 || $char['Race'] == 3)) {
                        $is_char = true;
                        $char_select .= '<option value="' . $char['Serial'] . '">Lv: ' . $char['Lv'] . ' - ' . $char['Name'] . '</option>' . "\n";
                    } elseif ($item_race == 0) {
                        $is_char = true;
                        $char_select .= '<option value="' . $char['Serial'] . '">Lv: ' . $char['Lv'] . ' - ' . $char['Name'] . '</option>' . "\n";
                    }
                }
                $char_select .= '</select>' . "\n";

                if ($is_char == false) {
                    $out .= '<table class="table table-bordered" align="center">' . "\n";
                    $out .= '	<tr>' . "\n";
                    $out .= '		<td class="alt2">No characters for the ' . $race . ' race has been found</td>' . "\n";
                    $out .= '	</tr>' . "\n";
                    $out .= '</table>';
                    return;
                }

                $out .= '<br/>' . "\n";

                $out .= '<table class="table table-bordered" align="center">' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2">Which character are you buying for?</td>' . "\n";
                $out .= '		<td class="alt1" style="text-align: right;">' . $char_select . '</td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" colspan="2" style="text-align: right;"><input type="hidden" name="page" value="buy_item"/><input type="hidden" name="item_id" value="' . $item_id . '"/><input type="submit"  class="btn btn-success" name="submit" value="Buy Now!"/></td>' . "\n";
                $out .= '	</tr>' . "\n";
                $out .= '</table>';
                $out .= '</form>' . "\n";
            }

        } elseif ($page == "buy_item") {

            # Main buy variables, and exit variable
            $exit_buy = 0;
            $empty_slot = "-1";
            $item_id = (isset($_POST['item_id']) && is_int((int)$_POST['item_id'])) ? antiject((int)$_POST['item_id']) : '';
            $item_post_amount = (isset($_POST['item_amount']) && is_int((int)$_POST['item_amount'])) ? antiject((int)$_POST['item_amount']) : '';
            $char_serial = (isset($_POST['char_serial']) && is_int((int)$_POST['char_serial'])) ? antiject((int)$_POST['char_serial']) : '';

            # Error checking
            if ($item_id == '') {
                $exit_buy = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">Invalid Item ID provided</p>' . "\n";
            }

            if ($char_serial == -1) {
                $exit_buy = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">No such character found</p>' . "\n";
            }

            # We cannot allow them to buy items if they are logged in man!
            $t_login = strtotime($userdata['lastlogintime']);
            $t_logout = strtotime($userdata['lastlogofftime']);
            $t_cur = time();
            $t_maxlogin = $t_login + 3600;

            #if($t_maxlogin < $t_cur) {
            #	$status = "offline";
            #} else
            if ($t_login <= $t_logout) {
                $status = "offline";
            } else {
                $status = "online";
            }

            if ($status == 'online') {
                $exit_buy = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">You cannot buy items when logged into the game!<br/>If you have logged out and yet see this message, log back in and properly log out again (click the log out button!).</p>' . "\n";
            }

            # Special error check: Is this a real ID?
            if ($exit_buy != 1) {
                connectgamecpdb();
                $item_sql = "SELECT item_name, item_amount, item_custom_amount, item_upgrade, item_dbcode, item_price FROM gamecp_shop_items WHERE item_delete = 0 AND item_status = 1 AND item_id = '$item_id'";
                if (!($item_result = mssql_query($item_sql))) {
                    $exit_buy = 1;
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to obtain item information</p>';
                }

                if (!($item = mssql_fetch_array($item_result))) {
                    $exit_buy = 1;
                    $out .= _l('no_such_item');
                } else {
                    $item_name = $item['item_name'];
                    $item_price = $item['item_price'];
                    $item_dbcode = $item['item_dbcode'];
                    $item_amount = ($item['item_amount'] < 1) ? 1 : $item['item_amount'];
                    $item_custom_amount = $item['item_custom_amount'];
                    $item_upgrade = $item['item_upgrade'];
                    $item_final_price = $item_price;
                    $item_price = $item_final_price;

                    # Just for checks, to make sure nothing goes wrong!
                    if ($item_name == '' || $item_price < 0 || $item_dbcode == '' || $item_upgrade == '') {
                        $exit_buy = 1;
                        $out .= '<p style="text-align: center; font-weight: bold;">Invalid data supplied by the database, contact the admin.</p>';
                    }

                    # Now, do we have enough "G" to buy this?
                    if ($userdata['vote_points'] < $item_price) {
                        $exit_buy = 1;
                        $out .= '<p style="text-align: center; font-weight: bold;">You do not have enough of Vote Points to purchase this item.</p>';
                    }
                }
                // Free Results
                @mssql_free_result($item_result);
            }

            # Do amount work
            if (isset($item_amount) && $item_amount != '') {
                if ($item_upgrade == $base_code && $item_custom_amount == 1 && $item_post_amount != '') {
                    $single_price = ceil($item_price / $item_amount);
                    $item_price = ceil($single_price * $item_post_amount);
                    $item_amount = $item_post_amount;

                    if ($item_post_amount > 99 or $item_post_amount < 1) {
                        $exit_buy = 1;
                        $out .= '<p style="text-align: center; font-weight: bold;">You have supplied an invalid amount.</p>';
                    }

                    if ($item_price > $userdata['vote_points']) {
                        $exit_buy = 1;
                        $out .= '<p style="text-align: center; font-weight: bold;">You do not have enough of Vote Points to make this purchase.</p>';
                    }
                }
            }


            # Done with error checking?
            # Lets do the main buying, shall we?
            if ($exit_buy == 0) {

                # Lets get the BAG names... all 99 of em, loops
                $bags = '';
                for ($i = 0; $i < 100; $i++) {
                    if ($i != 0) {
                        $bags .= ",";
                    }
                    $bags .= "I.K$i";
                }

                # Obtain bag details
                connectdatadb();
                $inven_select = "SELECT $bags FROM
				tbl_inven AS I
					INNER JOIN
				tbl_base AS B
					ON B.Serial = I.Serial
				WHERE
					I.Serial = '$char_serial'
				AND
					B.AccountSerial = '" . $userdata['serial'] . "'";
                if (!($inven_result = mssql_query($inven_select))) {
                    $exit_buy = 1;
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to obtain inventory information</p>';
                }
                $inven = mssql_fetch_array($inven_result);

                # Do we have any empty slots? If so, give me the first one!
                for ($i = 0; $i < 100; $i++) {
                    if ($inven["K$i"] == "-1") {
                        $empty_slot = $i;
                        break;
                    }
                }

                # Build the rest of the buying process
                if (@mssql_num_rows($inven_result) <= 0) {
                    $out .= '<p style="text-align: center; font-weight: bold;">No such character found</p>';
                } elseif ($empty_slot == "-1") {
                    $out .= '<p style="text-align: center; font-weight: bold;">No empty slots found in your characters inventory</p>';
                } else {

                    # Just to make sure we DO have values
                    if ($item_upgrade == '') {
                        $item_upgrade = $base_code;
                    }

                    # Same for this :D
                    if ($item_amount == '') {
                        $item_amount = 0;
                    }

                    #$update_inven = "UPDATE tbl_inven SET K$empty_slot = '$item_dbcode', U$empty_slot = '$item_upgrade', D$empty_slot = '$item_amount' WHERE Serial = '$char_serial'";
                    $update_inven = "INSERT tbl_ItemCharge ( nAvatorSerial, nItemCode_K, nItemCode_D, nItemCode_U )
							VALUES( '$char_serial', '$item_dbcode', '$item_amount', '$item_upgrade')";

                    if (!($inven_result = mssql_query($update_inven))) {
                        $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to update your characters inventory.</p>';

                        // Write Game CP Log
                        gamecp_log(1, $userdata['username'], "GAMECP - ITEM SHOP - Failed to update inventory", 1);
                    } else {
                        $time = time();
                        $total_gp = $userdata['vote_points'] - $item_price;

                        connectgamecpdb();
                        $redeem_insert = "INSERT INTO gamecp_redeem_log (redeem_account_id,redeem_char_id,redeem_price,redeem_item_id,redeem_item_name,redeem_item_amount,redeem_item_dbcode,redeem_total_gp,redeem_time) VALUES ('" . antiject($userdata['serial']) . "', '" . antiject($char_serial) . "', '" . antiject($item_price) . "', '" . antiject($item_id) . "', '" . antiject($item_name) . "', '" . antiject($item_amount) . "', '" . antiject($item_dbcode) . "', '" . $total_gp . "', '" . $time . "')";

                        if (!($redeem_result = mssql_query($redeem_insert))) {
                            $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to insert a redeem log.</p>';
                            // Write Game CP Log
                            gamecp_log(1, $userdata['username'], "GAMECP - ITEM SHOP - Failed to insert a redeem log", 1);
                        } else {
                            $update_gp = "UPDATE gamecp_gamepoints SET user_vote_points = user_vote_points-$item_price WHERE user_account_id = '" . $userdata['serial'] . "'";
                            if (!($updategp_result = mssql_query($update_gp))) {
                                $out .= '<p style="text-align: center; font-weight: bold;">SQL Error trying to upate your Vote Points.</p>';

                                // Write Game CP Log
                                gamecp_log(1, $userdata['username'], "GAMECP - ITEM SHOP - Failed to update Vote Points: -$item_price", 1);
                            } else {

                                # Im sick of error checking, its getting ugly, just do this $*#@
                                $update_buycount = "UPDATE gamecp_shop_items SET item_buy_count = item_buy_count+1 WHERE item_id = '" . $item_id . "'";
                                $update_buycount_result = @mssql_query($update_buycount);

                                # Display main header
                                $out .= '<p style="font-weight: bold; font-size: 15px; text-align: center;">Your account currently has <span style="color: #8F92E8;">'.number_format($userdata['points']).'</span> Game Points <span style="color: #8F92E8;"> '.number_format($userdata['vote_points']).'</span> Vote Point</p>' . "\n";
                                $out .= '<p style="text-align: center; font-weight: bold;">Successfully purchased the item: ' . $item_name . '</p>';
                            }
                        }
                    }
                    // Free Results
                    @mssql_free_result($inven_result);

                }
            }

        } elseif ($page == 'ban') {

            $out .= '<p style="text-align: center; font-weight: bold;">You have a blocked account, as a prevention method we have blocked all of you\'re accounts from making item purchases. Please contact an Administrator to get this resolved.</p>';

        } else {
            $out .= _l('invalid_page_id');
        }

    } else {
        $out .= _l('no_permission');
        #$out .= 'Hey, doing some upgrades to the item shop, sorry for the downtime but we will be back up soon and you might like whats being added :D';
    }

} else {
    $out .= _l('invalid_page_load');
}
?>