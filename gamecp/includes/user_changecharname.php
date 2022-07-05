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
    $module[_l('Account')][_l('Change Char Name')] = $file;
    return;
}

if ($this_script == $script_name) {
    if ($isuser) {
        $lefttitle = _l('Change Character Name');;
        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $echodata = '';
        if ($page == "") {
            $count = 0;

            if (!($chars = getcharacters($userdata['serial']))) {
                $out .= '<p style="text-align: center; font-weight: bold;">According to our database, you have no characters on your account</p>';
            } else {
                if ($userdata['points'] < $config['specialgp_charname']) {
                    $out .= '<strong><font color="#e90000">Not enough gamepoints. It costs ' . $config['specialgp_charname'] . ' Game Points and you have ' . $userdata['points'] . '.</font></strong><br><br><strong>Change your name to anything that has Admin or GM in it will get you banned. DONT do it!</strong><br><br>';

                    $out .= '<form method="POST" action="' . $script_name . '?do=' . $_GET['do'] . '&amp;page=comf">';
                    $out .= '<table border="0">'
                        . '<tr><td><strong>Character</strong></td><td><select class="form-control"name="oldchar">';

                    $chardata = getcharacters($userdata['serial']);
                    if (!empty($chardata)) {
                        foreach ($chardata as $character) {
                            $out .= '<option value="' . $character['Name'] . '">' . $character['Name'];
                        }
                    }


                    $out .= '</select></td></tr>'
                        . '<tr><td><strong>New Character Name: </strong></td><td> <input type="text" class="form-control" name="newname"></td></tr>'
                        . '<tr><td><strong>Comfirm Name: </strong></td><td> <input type="text" class="form-control" name="comfname"></td></tr>'
                        . '<tr><td colspan="2" align="center"><input type="submit"  class="btn btn-default" disabled value="Change Name"></td></tr>'
                        . '</table></form>';
                } elseif (empty($chars)) {
                    $out .= '<center><strong>No characters made</strong></center>';
                } else {
                    $out .= '<strong>It costs ' . $config['specialgp_charname'] . ' Game Points to change your character name.</strong><br><br><strong>Change your name to anything that has Admin or GM in it will get you banned. DONT do it!</strong><br><br>';

                    $out .= '<form method="POST" action="' . $script_name . '?do=' . $_GET['do'] . '&amp;page=comf">';
                    $out .= '<table border="0">'
                        . '<tr><td><strong>Character</strong></td><td><select class="form-control"name="oldchar">';

                    $chardata = getcharacters($userdata['serial']);
                    if (!empty($chardata)) {
                        foreach ($chardata as $character) {
                            $out .= '<option value="' . $character['Name'] . '">' . $character['Name'];
                        }
                    }


                    $out .= '</select></td></tr>'
                        . '<tr><td><strong>New Character Name: </strong></td><td> <input type="text" class="form-control" name="newname"></td></tr>'
                        . '<tr><td><strong>Comfirm Name: </strong></td><td> <input type="text" class="form-control" name="comfname"></td></tr>'
                        . '<tr><td colspan="2" align="center"><input type="submit"  class="btn btn-default" value="Change Name"></td></tr>'
                        . '</table></form>';
                }
            }
        } elseif ($page == "comf") {
            $oldchar = antiject($_REQUEST['oldchar']);
            $newname = antiject($_REQUEST['newname']);
            $comfname = antiject($_REQUEST['comfname']);


            if (!(preg_match("/^([a-zA-Z0-9]+)$/", $newname)) || !(preg_match("/^([a-zA-Z0-9]+)$/", $comfname))) {
                $echodata .= 'Invalid name. Names can only contain letters and numbers.';
            } elseif ($newname != $comfname) {
                $echodata .= 'New Character Name and Comfirm Name fields must match.';
            } elseif ((empty($newname)) || (empty($comfname))) {
                $echodata .= 'You left a blank field.';
            } elseif (!isusers($userdata['username'], $oldchar)) {
                $echodata .= 'This character does not belong to you or name has already been changed.';

            } elseif ($userdata['points'] < $config['specialgp_charname']) {
                $echodata .= 'You do not have enough Game Points for this';
            } elseif ((strlen($newname) < 4) || (strlen($newname) > 15)) {
                $echodata .= 'Character name must be 4 to 15 characters long';
            } else {

                $oldchar = ereg_replace(";$", "", $oldchar);
                $oldchar = ereg_replace("\\\\", "", $oldchar);
                $newname = ereg_replace(";$", "", $newname);
                $newname = ereg_replace("\\\\", "", $newname);
                $comfname = ereg_replace(";$", "", $comfname);
                $comfname = ereg_replace("\\\\", "", $comfname);
                $oldchar = antiject($oldchar);
                $newname = antiject($newname);

                if (charexists($newname)) {
                    $echodata .= 'A character with this name already exists';
                } else {
                    $chardata = getonecharacter($oldchar);

                    write_log($chardata['Serial'], $oldchar, $newname, $userdata['ip']);

                    connectdatadb();
                    mssql_query(sprintf("UPDATE tbl_base SET Name = '%s' WHERE AccountSerial = '%d' AND Name = '%s'", $newname, $userdata['serial'], $oldchar));
                    $echodata .= 'Your Character name has been sucessfully changed to ' . $newname . '.';

                    $creditsleft = $userdata['points'] - $config['specialgp_charname'];
                    connectgamecpdb();
                    mssql_query(sprintf('UPDATE gamecp_gamepoints SET user_points="%d" WHERE user_account_id="%d"', $creditsleft, $userdata['serial']));

                    gamecp_log(1, $userdata['username'], "GAMECP - CHANGE CHAR NAME - Char Serial: " . $chardata['Serial'] . " | Old Name: $oldchar | New Name: $newname | GP: -" . $config['specialgp_charname'], 1);

                }

            }
            $out .= '<center>' . $echodata . '<br><a href="' . $script_name . '?do=' . $_GET['do'] . '">Return</a></center>';
        }
    }
}


?>