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
    $module[_l('Logs')][_l('Rented Items Logs')] = $file;
    return;
}

$lefttitle = _l('ItemCharge/Rented Items Logs');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        // ------------------------------------------------
        // Convert or general a database item number
        // @param: db code # or item id, input type, item type, slot
        // @result: returns either a db number or an array of the item info
        // ------------------------------------------------
        function itemcode($input_type = 'convert', $input, $type = false, $slot = false)
        {
            $item = array();
            if ($input_type == 'convert') {
                $dechex = dechex($input);

                $item_id_pos = strlen($dechex) - 4;
                $item_id_len = substr($dechex, 0, $item_id_pos);
                $item['id'] = hexdec($item_id_len);

                $item_type_pos = strlen($dechex) - strlen($item_id_len) - 2;
                $item_type_len = substr($dechex, $item_id_pos, $item_type_pos);
                $item['type'] = hexdec($item_type_len);

                $item_slot_pos = strlen($dechex) - strlen($item_id_len) - strlen($item_type_len);
                $item_slot_len = substr($dechex, $item_id_pos + $item_type_pos, $item_slot_pos);
                $item['slot'] = hexdec($item_slot_len);

                return $item;
            } elseif ($input_type == 'make') {
                if ($type) {
                    return 65536 * $input + ($type * 256 + $slot);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        connectdatadb();
        $result = mssql_query('SELECT TOP 100
		B.Name, C.nSerial, C.nAvatorSerial, C.nItemCode_K, C.nItemCode_D, C.nItemCode_U, C.DCK, C.T, C.[dtGiveDate], C.[dtTakeDate]
		FROM tbl_ItemCharge AS C
		INNER JOIN
		tbl_base AS B
		ON B.Serial = C.nAvatorSerial
		ORDER BY C.nSerial DESC');

        $out .= '<table class="table table-bordered">' . "\n";
        $out .= '<tr>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>nSerial</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>nAvatorSerial</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Name</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Item Name</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Item Amount</b></td>';
        #$out .= '<td class="thead"style="padding: 4px;" nowrap><b>nItemCode_U</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>In Use?</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Give Time</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Take Time</b></td>';
        $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Time</b></td>';
        $out .= '</tr>';

        connectitemsdb();
        while ($row = mssql_fetch_array($result)) {

            $iteminfo = itemcode('convert', $row['nItemCode_K']);
            $items_query = mssql_query("SELECT item_id,item_name,item_code FROM " . GetItemTableName($iteminfo['type']) . " WHERE item_id = '" . $iteminfo['id'] . "'", $items_dbconnect);
            $items = mssql_fetch_array($items_query);
            $rented_code = $items['item_code'];
            $rented_name = $items['item_name'];
            mssql_free_result($items_query);

            $out .= '<tr>';
            $out .= '<td class="alt2" nowrap>' . $row['nSerial'] . '</td>';
            $out .= '<td class="alt1" nowrap>' . $row['nAvatorSerial'] . '</td>';
            $out .= '<td class="alt1" nowrap>' . $row['Name'] . '</td>';
            $out .= '<td class="alt1" nowrap>' . str_replace("_", " ", $rented_name) . '</td>';
            $out .= '<td class="alt1" nowrap>' . $row['nItemCode_D'] . '</td>';
            #$out .= '<td class="alt1" nowrap>'.$row['nItemCode_U'].'</td>';
            $out .= '<td class="alt1" nowrap>' . (($row['DCK'] == 1) ? 'Yes' : 'No') . '</td>';
            $out .= '<td class="alt1" nowrap>' . $row['dtGiveDate'] . '</td>';
            $out .= '<td class="alt1" nowrap>' . (($row['DCK'] == 1) ? $row['dtTakeDate'] : '-') . '</td>';
            $out .= '<td class="alt1" nowrap>' . round($row['T'] / 3600) . ' Hr</td>';
            $out .= '</td>';
        }

        $out .= "</table>";
        // Free Results
        mssql_free_result($result);
    } else {

        $out .= _l('no_permission');

    }

} else {
    $out .= _l('invalid_page_load');
}
?>