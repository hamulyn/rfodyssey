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
    $module[_l('Item Shop')][_l('Item Redeem Logs')] = $file;
    return;
}

$lefttitle = _l('Item Redeem Logs');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    $max_pages = 10;
    $top_limit = 25;

    if ($isuser) {

        $gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';

        # Display Main Heading
            $out .= '<p style="font-weight: bold; font-size: 15px; text-align: center;">Your account currently has <span style="color: #8F92E8;">' . number_format($userdata['points'], 2) . '</span> Game Points <span style="color: #8F92E8;">' . number_format($userdata['vote_points'], 2) . '</span> Vote Points</p>' . "\n";

        $out .= '<table class="table table-bordered">' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="thead" style="text-align: center;" nowrap>#</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Date</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Item Name</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Character Name</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Item Price</td>' . "\n";
        $out .= '		<td class="thead" nowrap>VP After Purchase</td>' . "\n";
        $out .= '	</tr>' . "\n";

        connectgamecpdb();
        $query_p1 = "SELECT
		R.redeem_char_id, R.redeem_price, R.redeem_item_id, R.redeem_total_gp, R.redeem_time, R.redeem_item_name, I.item_name, I.item_delete
		FROM 
			gamecp_redeem_log AS R 
			LEFT JOIN 
			gamecp_shop_items AS I
			ON R.redeem_item_id = I.item_id
		WHERE R.redeem_account_id = '" . $userdata['serial'] . "'";
        $query_p2 = " AND R.redeem_id NOT IN ( SELECT TOP [OFFSET] R.redeem_id FROM
			gamecp_redeem_log AS R 
			LEFT JOIN 
			gamecp_shop_items AS I
				ON R.redeem_item_id = I.item_id
			WHERE R.redeem_account_id = '" . $userdata['serial'] . "' ORDER BY R.redeem_id DESC) ORDER BY R.redeem_id DESC";

        // Pageination
        include('./includes/pagination/ps_pagination.php');

        //Create a PS_Pagination object
        $pager = new PS_Pagination($gamecp_dbconnect, $query_p1, $query_p2, $top_limit, $max_pages, '' . $script_name . '?do=' . $_GET['do']);

        //The paginate() function returns a mysql
        //result set for the current page
        $rs = $pager->paginate();

        if ($gen == 1) {
            $i = 1;
        } else {
            $i += (($gen - 1) * $top_limit) + 1;
        }

        connectdatadb();
        while ($row = mssql_fetch_array($rs)) {

            $char_result = mssql_query("SELECT Name,DCK FROM tbl_base WHERE Serial = '" . $row['redeem_char_id'] . "'");
            $char = mssql_fetch_array($char_result);

            $char_name = ($char['Name'] != "") ? $char['Name'] : 'Unknown';
            if ($char['DCK'] == 1) {
                $char_name = '<i>' . $char_name . '</i>';
            }

            if ($row['item_delete'] == 1) {
                $item_name = '<i>' . $row['item_name'] . '</i>';
            } elseif ($row['redeem_item_name'] != '') {
                $item_name = $row['redeem_item_name'];
            } else {
                $item_name = '<i>Unknown</i>';
            }

            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt2" style="text-align: center;" nowrap>' . $i . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . date("d/m/y h:i:s A", $row['redeem_time']) . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . $item_name . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . $char_name . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . number_format($row['redeem_price'], 2) . ' VP</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . number_format($row['redeem_total_gp'], 2) . ' VP</td>' . "\n";
            $out .= '	</tr>' . "\n";
            // Free Results
            mssql_free_result($char_result);
            $i++;
        }

        if (mssql_num_rows($rs) <= 0) {
            $out .= '		<tr>' . "\n";
            $out .= '			<td class="alt1" colspan="6" style="text-align: center; font-weight: bold;">No redeem logs found for your account.</td>' . "\n";
            $out .= '		</tr>' . "\n";
        } else {
            $out .= '		<tr>' . "\n";
            $out .= '			<td class="alt2" colspan="6" style="text-align: center; font-weight: bold;">' . $pager->renderFullNav() . '</td>' . "\n";
            $out .= '		</tr>' . "\n";
        }
        $out .= "</table>";
        // Free Results
        @mssql_free_result($rs);

    } else {
        $out .= _l('no_permission');
    }

} else {
    $out .= _l('invalid_page_load');
}
?>