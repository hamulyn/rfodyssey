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
    $file = basename(__FILE__);
    $module[_l('Vote')][_l('Vote for GP')] = $file;
    return;
}

# Title
$lefttitle = _l('Vote for Game Points');

# To make life simpler, we'll capture all the print
# output from here on out and append it to the $out variable
# at the end of this script
ob_start();

# Security check
if ($this_script == $script_name) {

    # Only logged in users can access this page
    if($isuser) {

        # Setup some variables
        $page = request_var('page', '');
        $enabled = (isset($config['vote_enabled'])) ? (boolean)$config['vote_enabled'] : true;
        $max_gp = (isset($config['vote_max_gp'])) ? $config['vote_max_gp'] : 4;
        $min_gp = (isset($config['vote_min_gp'])) ? $config['vote_min_gp'] : 1;
        $minimum_level = (isset($config['vote_minimum_level'])) ? (int)$config['vote_minimum_level'] : 0;
        $minimum_play_min = (isset($config['vote_minimum_play_min'])) ? (int)$config['vote_minimum_play_min'] : 0;

        # Get the game points information
        $giveGamePoints = 0;

        if ($max_gp <= 0) {
            $max_gp = 4;
        }

        if ($min_gp <= 0) {
            $min_gp = 1;
        }

        if ($max_gp < $min_gp) {
            $max_gp = $min_gp;
        }

        if ($max_gp < 1 || $min_gp < 1) {
            $giveGamePoints = random_float($min_gp, $max_gp);
        } else {
            $giveGamePoints = rand($min_gp, $max_gp);
        }

        # Enable or disable this feature
        if(!$enabled) {
            $out .= message(_l('Sorry, this feature has been disabled by the administrator.'), _l('Disabled'), 'warning');
            return 0;
        }

        # Let's check the character's level and play min
        if($minimum_level > 0 || $minimum_play_min > 0) {
            connectdatadb();
            $char_sql = sprintf("SELECT B.Lv, G.TotalPlayMin FROM tbl_base AS B INNER JOIN tbl_general as G ON B.serial = G.serial WHERE B.DCK = 0 AND B.AccountSerial = %d", $userdata['serial']);
            if(!($char_query = mssql_query($char_sql))) {
                $out .= sql_error('An SQL ERROR Occurred query character database');
                return 0;
            }

            # Accumulated variables
            $totalPlayMin = 0;
            $highestLevel = 0;

            # Loop through data
            while($row = mssql_fetch_array($char_query)) {
                if($row['Lv'] > $highestLevel) {
                    $highestLevel = (int)$row['Lv'];
                }
                $totalPlayMin += $row['TotalPlayMin'];
            }

            # Do checks now: Minimum play min
            if($minimum_play_min > 0 && $totalPlayMin < $minimum_play_min) {
                $out .= message(_l('Sorry, you need a total accumulated play time of %d minutes to vote. You currently only have %d minutes.', $minimum_play_min, $totalPlayMin), _l('Vote Requirements Not Meet'), 'warning');
                return 0;
            }

            # Minimum level
            if($minimum_level > 0 && $highestLevel < $minimum_level) {
                $out .= message(_l('Sorry, you need at least one level %d character to vote. Your highest character currently is only level %d.', $minimum_level, $highestLevel), _l('Vote Requirements Not Meet'), 'warning');
                return 0;
            }
        }

        # Fetch our classes...
        connectgamecpdb();
        $site_sql = "SELECT vote_id, vote_site_name, vote_site_url, vote_site_image, vote_reset_time FROM gamecp_vote_sites";
        if(!($site_query = mssql_query($site_sql, $gamecp_dbconnect))) {
            $out .= sql_error('An SQL ERROR Occurred query the vote sites database');
            return 0;
        }

        # Loop through our class data
        $voteSites = array();
        while ($site = mssql_fetch_array($site_query)) {
            $voteSites[$site['vote_id']] = $site;
        }

        # Total vote sites
        $totalVoteSites = count($voteSites);
        if($totalVoteSites == 0) {
            $out .= message(_l('Sorry, no vote sites have been added so this feature has been disabled.'), _l('Disabled'), 'warning');
            return 0;
        }

        # Fetch user's vote info
        $vote_sql = sprintf("SELECT user_id, user_vote_points, user_vote_timestamp FROM gamecp_gamepoints WHERE user_account_id = '%d'", $userdata['serial']);
        if(!($vote_query = mssql_query($vote_sql, $gamecp_dbconnect))) {
            $out .= sql_error('An SQL ERROR Occurred query the user vote sites database');
            return 0;
        }

        if(!($userVote = mssql_fetch_array($vote_query))) {
            $out .= message(_l('Error occurred when attempting to fetch your vote site information.'), _l('Error'), 'warning');
            return 0;
        }

        # Gather the user info
        $userVoteTimeStamp = (isset($userVote['user_vote_timestamp'])) ? trim($userVote['user_vote_timestamp']) : '';

        # Split the user's vote timestamps into an array
        $split_raw_timestamp = explode(",", $userVoteTimeStamp);

        $userSplitTimeStamps = array();
        for ($i = 0; $i < count($split_raw_timestamp); $i++) {
            if($split_raw_timestamp[$i] == '') {
                continue;
            }
            $split_info = explode(":", $split_raw_timestamp[$i]);
            $split_id[] = $split_info[0];
            $userSplitTimeStamps[$split_info[0]] = $split_info[1];
        }

        # Vote for site
        if($page == 'vote-now') {

            # User data
            $vote_id = request_var('vote_id', 0);

            # Triggers / Data
            $voted = false;
            $insertTimestamp = '';
            $redirectUrl = '';

            # We may have a first time user, so the timestamp data might be empty
            # Thus, we need to treat them differently (no comparison and checks needed)
            if(empty($userSplitTimeStamps)) {

                foreach($voteSites as $site) {
                    # If the vote id matches, we'll add the current timestamp, else 0
                    if($vote_id == $site['vote_id']) {
                        $insertTimestamp .= $site['vote_id'].':'.time();
                        $redirectUrl = $site['vote_site_url'];
                        $voted = true;
                    } else {
                        $insertTimestamp .= $site['vote_id'].':'.'0';
                    }

                    # Add ending comma
                    $insertTimestamp .= ', ';
                }

                # If voted is true
                if($voted == true) {
                    # Remove last comma in the statement
                    $insertTimestamp = substr(trim($insertTimestamp), 0, -1);

                    # Update the game cp database
                    $update_sql = sprintf("UPDATE gamecp_gamepoints SET user_vote_timestamp = '%s', user_vote_points = user_vote_points + %d WHERE user_account_id = '%d'", $insertTimestamp, $giveGamePoints, $userdata['serial']);
                    if(!($update_query = mssql_query($update_sql, $gamecp_dbconnect))) {
                        $out .= sql_error('An SQL ERROR Occurred update the game points database');
                        return 0;
                    } else {
                        log_vote($user_serial, $ip, $giveGamePoints, ($userdata['user_points'] + $giveGamePoints));
                        header("Location: ".$redirectUrl);
                    }
                } else {
                    echo message(_l('Could not find the vote site you selected.'), _l('Vote Failed'), 'warning');
                }

            } else {

                foreach($voteSites as $site) {
                    # If the vote id matches, we'll add the current timestamp, else 0
                    if($vote_id == $site['vote_id']) {
                        # Get time difference
                        $time_difference = (time() - $userSplitTimeStamps[$site['vote_id']]);

                        # Have we voted?
                        if($time_difference >= $site['vote_reset_time']) {
                            $insertTimestamp .= $site['vote_id'].':'.time();
                            $redirectUrl = $site['vote_site_url'];
                            $voted = true;
                        }
                    } else {
                        $timestamp = (isset($userSplitTimeStamps[$site['vote_id']])) ? $userSplitTimeStamps[$site['vote_id']] : '0';
                        $insertTimestamp .= $site['vote_id'].':'.$timestamp;
                    }

                    # Add ending comma
                    $insertTimestamp .= ',';
                }

                # If voted is true
                if($voted == true) {
                    # Remove last comma in the statement
                    $insertTimestamp = substr(trim($insertTimestamp), 0, -1);

                    # Update the game cp database
                    $update_sql = sprintf("UPDATE gamecp_gamepoints SET user_vote_timestamp = '%s', user_vote_points = user_vote_points + %d WHERE user_account_id = '%d'", $insertTimestamp, $giveGamePoints, $userdata['serial']);
                    if(!($update_query = mssql_query($update_sql, $gamecp_dbconnect))) {
                        $out .= sql_error('An SQL ERROR Occurred update the game points database');
                        return 0;
                    } else {
                        log_vote($user_serial, $ip, $giveGamePoints, ($userdata['user_points'] + $giveGamePoints));
                        header("Location: ".$redirectUrl);
                    }

                } else {
                    echo message(_l('You have either already voted for this site or the site you selected does not exist.'), _l('Vote Failed'), 'warning');
                }

            }

        # Default page
        } else {

            echo '<div class="row">';
            $inc = 0;
            foreach($voteSites as $site) {
                $userTimeStamp = (isset($userSplitTimeStamps[$site['vote_id']])) ? $userSplitTimeStamps[$site['vote_id']] : 0;
                $time_difference = (time() - $userTimeStamp);
                echo '<form method="POST" action="?do='.$do.'&page=vote-now">';
                echo '  <div class="col-sm-6 col-md-4">';
                echo '      <div class="thumbnail">';
                echo '          <div class="caption">';
                echo '              <h3 class="text-center">'.$site['vote_site_name'].'</h3>';
                echo '              <p class="text-center">';
                if($time_difference < $site['vote_reset_time']) {
                    $time_remaining = hrs_mins_secs($site['vote_reset_time'] - $time_difference);
                    echo '                  <button name="vote_id" value="'.$site['vote_id'].'" class="btn btn-primary" disabled="disabled" role="button">' . $time_remaining['hours'] . ' Hr ' . $time_remaining['minutes'] . ' Min ' . $time_remaining['seconds'] . ' Sec</button>';
                } else {
                    echo '                  <button type="submit" name="vote_id" value="'.$site['vote_id'].'" class="btn btn-primary" role="button">Vote Now</button>';
                }
                echo '              </p>';
                echo '          </div>';
                echo '      </div>';
                echo '  </div>';
                echo '</form>';
                $inc++;
            }
            echo '</div>';

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