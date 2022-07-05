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
    $module[_l('Super Admin')][_l('Mass Delete Items')] = $file;
    return;
}

$lefttitle = _l('Super Admin - Mass Delete Items');

# To make life simpler, we'll capture all the print
# output from here on out and append it to the $out variable
# at the end of this script
ob_start();

# Security check
if ($this_script == $script_name) {

    $super_admin = explode(",", $admin['super_admin']);

    # Must be logged in an a super admin
    if ($isuser = true && in_array($userdata['username'], $super_admin)) {

        $page = request_var('page','home');
        $item_code = request_var('item_code', '');
        $delete_inven = request_var('delete_inven', false);
        $delete_trunk = request_var('delete_trunk', false);
        $delete_extended_trunk = request_var('delete_extended_trunk', false);
        $delete_mail = request_var('delete_mail', false);
        $delete_ah = request_var('delete_ah', false);

        if ($page == 'home') {

            if($item_code != '') {
                # Connect to the items db
                connectitemsdb();

                # Check to make sure this item exists..and get the item data
                $item_type = GetItemTableCode($item_code);
                $table_name = GetItemTableName($item_type);
                $sql = sprintf("SELECT TOP 1 * FROM %s WHERE item_code = '%s'", $table_name, $item_code);
                if(!($item_result = mssql_query($sql))) {
                    $out .= sql_error("SQL Error occurred when attempting to verify item code");
                    return 0;
                }

                # Fetch item info
                $item_info = mssql_fetch_array($item_result);

                # Make sure we have an item
                if($item_info === false) {
                    $success = false;
                    echo message('Unable to find the item based on the code you specified. Check for typos.', 'Invalid Item Code', 'danger');
                } else {

                    # Calculate slot 0
                    $item_db_code_start = gen_item_code($item_info['item_id'], $item_type, 0);
                    $item_db_code_end = gen_item_code($item_info['item_id'], $item_type, 99);

                    echo '<div class="well">';
                    echo '  <h4>Copy &amp; Paste the contents into the Studio Manager and Run the queries (Select your WORLD databases)</h4>';
                    echo '<span class="label label-warning">Warning</span> Running the queries will take up CONSIDERABLE amount of time depending on the size of your databases!';
                    echo '<br/><br/>';

                    # Queries to delete the inventory items
                    if($delete_inven) {
                        echo '  <strong>Inventory</strong>';
                        echo '  <textarea class="form-control" rows="10" onClick="this.select();" >';
                        echo '--- Inventory';
                        echo "\n";
                        for($slot = 0; $slot < 100; $slot++) {
                            echo sprintf("UPDATE tbl_inven SET K%d = -1, D%d = 0, U%d = 0x0fffffff WHERE K%d >= %d AND K%d <= %d;", $slot, $slot, $slot, $slot, $item_db_code_start, $slot, $item_db_code_end);
                            echo "\n".'GO'."\n";
                        }
                        echo '  </textarea>';
                    }

                    # Queries to delete the trunk items
                    if($delete_trunk) {
                        echo '  <strong>Trunk (bank)</strong>';
                        echo '  <textarea class="form-control" rows="10" onClick="this.select();" >';
                        echo '--- Trunk';
                        echo "\n";
                        for($slot = 0; $slot < 100; $slot++) {
                            echo sprintf("UPDATE tbl_AccountTrunk SET K%d = -1, D%d = 0, U%d = 0x0fffffff WHERE K%d >= %d AND K%d <= %d;", $slot, $slot, $slot, $slot, $item_db_code_start, $slot, $item_db_code_end);
                            echo "\n".'GO'."\n";
                        }
                        echo '  </textarea>';
                    }

                    # Queries to delete the extended trunk items
                    if($delete_extended_trunk) {
                        echo '  <strong>Extended Trunk (bank)</strong>';
                        echo '  <textarea class="form-control" rows="10" onClick="this.select();" >';
                        echo '--- Extended Trunk';
                        echo "\n";
                        for($slot = 0; $slot < 40; $slot++) {
                            echo sprintf("UPDATE tbl_AccountTrunk_Extend SET K%d = -1, D%d = 0, U%d = 0x0fffffff WHERE K%d >= %d AND K%d <= %d;", $slot, $slot, $slot, $slot, $item_db_code_start, $slot, $item_db_code_end);
                            echo "\n".'GO'."\n";
                        }
                        echo '  </textarea>';
                    }

                    # Queries to delete AH
                    if($delete_ah) {
                        echo '  <strong>Auction House</strong>';
                        echo '  <textarea class="form-control" rows="10" onClick="this.select();" >';
                        echo '--- Post Registry & Storage';
                        echo "\n";
                        echo sprintf("DELETE FROM [tbl_utsingleiteminfo] WHERE k >= %d AND k <= %d;", $item_db_code_start, $item_db_code_end);
                        echo "\n".'GO'."\n";
                        echo '  </textarea>';
                    }

                    # Queries to delete from mail
                    if($delete_mail) {
                        echo '  <strong>Mail</strong>';
                        echo '  <textarea class="form-control" rows="10" onClick="this.select();" >';
                        echo '--- Post Registry & Storage';
                        echo "\n";
                        echo sprintf("UPDATE [dbo].[tbl_PostStorage] SET [postinx] = 255,[owner] = 0 ,[dck] = 1 ,[poststate] = 0 ,[sendname] = NULL ,[recvname] = NULL ,[title] = NULL ,[content] = NULL ,[k] = -1 ,[d] = 0 ,[u] = 268435455 ,[gold] = 0 ,[err] = 0 ,[sindex] = 255 ,[uid] = 0 WHERE k >= %d AND k <= %d;", $item_db_code_start, $item_db_code_end);
                        echo "\n".'GO'."\n";
                        echo sprintf("UPDATE [dbo].[tbl_PostRegistry] SET [dck] = 1 ,[sendserial] = 0 ,[sendname] = NULL ,[recvname] = NULL ,[title] = NULL ,[content] = NULL ,[k] = -1 ,[d] = 0 ,[u] = 268435455 ,[gold] = 0, [sendrace] = 255 ,[userdgr] = 0, [uid] = 0 WHERE k >= %d AND k <= %d;", $item_db_code_start, $item_db_code_end);
                        echo "\n".'GO'."\n";
                        echo '  </textarea>';
                    }
                    echo '</div>';

                }
            }

            echo '<p>'._l('Enter the item code you want <strong>erased</strong> from the server.').'</p>';
            echo '<p>'._l('Once you have generated the SQL statements, you are required to copy them to your <em>Microsoft SQL Management Studio</em> and manually run them on the appropriate database.').'</p>';
            echo '<span class="label label-warning">NOTICE</span> <strong>THIS SCRIPT DOES NOT ACTUALLY RUN THE DELETE QUERIES FOR SAFETY REASONS...</strong><br/><br/>';
            echo '<form role="form" method="GET" action="?do='.$do.'&page=home">';
            echo '  <div class="form-group">';
            echo '      <label for="item-code">'._l('Item Code').':</label>';
            echo '      <input type="text" class="form-control" name="item_code" id="item-code" placeholder="e.g. irtal01" value="'.htmlspecialchars($item_code).'">';
            echo '  </div>';
            echo '  <div class="form-group">';
            echo '  <label class="checkbox-inline">';
            echo '    <input type="checkbox" name="delete_inven" id="delete-inven" value="1" checked> Delete from Inventory';
            echo '  </label>';
            echo '  <label class="checkbox-inline">';
            echo '    <input type="checkbox" name="delete_trunk" id="delete-trunk" value="1" checked> Delete from Trunk (Bank)';
            echo '  </label>';
            echo '  <label class="checkbox-inline">';
            echo '    <input type="checkbox" name="delete_extended_trunk" id="delete-extended-trunk" value="1" checked> Delete from Extended Trunk (Extended Bank)';
            echo '  </label>';
            echo '  <label class="checkbox-inline">';
            echo '    <input type="checkbox" name="delete_mail" id="delete-mail" value="1" checked> Delete from Mail';
            echo '  </label>';
            echo '  <label class="checkbox-inline">';
            echo '    <input type="checkbox" name="delete_ah" id="delete-ah" value="1" checked> Delete from Auction House';
            echo '  </label>';
            echo '</div>';
            echo '    <input type="hidden" name="do" value="'.$do.'">';
            echo '    <button type="submit" class="btn btn-primary">Generate SQL Statements</button>';
            echo '</form>';

        } else {
            echo _l('page_not_found');
        }

    } else {
        echo _l('no_permission');
    }

} else {
    echo _l('invalid_page_load');
}

# Append data to the $out variable
$out .= ob_get_contents();
ob_end_clean();