<?php
/**
 * Game Control Panel v2
 * Copyright (c) www.intrepid-web.net
 *
 * The use of this product is subject to a license agreement
 * which can be found at http://www.intrepid-web.net/rf-game-cp-v2/license-agreement/
 */

if(!defined('COMMON_INITIATED')) {
    die("Hacking attempt! Logged");
}

if( !empty($setmodules) )
{
    return;
}

# Title
$lefttitle = _l('Admin Ajax');

# To make life simpler, we'll capture all the print
# output from here on out and append it to the $out variable
# at the end of this script
ob_start();

# Security check
if ($this_script == $script_name) {

    # Setup some variables
    $page = request_var('page', 'home');

     # Set permission, duh
    $permission = ! empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $do;
    $permission = preg_replace('/^(.*?)do=(.*?)\&(.*?)$/', '$2', $permission);

    # Admins with permission only
    if (hasPermissions($permission)) {

        # Setup some variables
        $page = request_var('page', 'home');

        # Manage an item
        if($page == 'item-code') {

            connectitemsdb();

            $item_code = request_var('code', '');
            $page_limit = request_var('page_limit', 1);

            if($item_code == '') {
                echo json_encode(array('items' => array()));
                exit;
            }

            $kn = GetItemTableCode($item_code);
            if($kn == -1) {
                echo json_encode(array('items' => array()));
                exit;
            }

            $sql_text = sprintf("SELECT TOP %d * FROM %s WHERE item_code LIKE '%%%s%%'",  $page_limit, GetItemTableName($kn), $item_code);
            $items_query = mssql_query($sql_text);

            $items = array();
            while($row = mssql_fetch_array($items_query)) {
                $items[] = array('id' => $row['item_code'], 'index' => $row['item_id'], 'name' => $row['item_name']);
            }

            echo json_encode(array('items' => $items));

            // Free Result
            @mssql_free_result($items_query);

            exit;

        } else {

            echo message(_l('The page you selected was not found'), 'Page Not Found', 'danger');

        }
    } else {

        echo message(_l('no_permission'), 'Access Denied', 'danger');

    }

} else {

    echo message(_l('invalid_page_load'), 'Error', 'danger');

}

# Append data to the $out variable
$out .= ob_get_contents();
ob_end_clean();