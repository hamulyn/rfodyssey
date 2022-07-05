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
    $module[_l('Super Admin')][_l('Check Version')] = $file;
    return;
}

$lefttitle = _l('Admin - Check Game CP Version');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        if (!isset($_file_properties)) {
            $_file_properties['version'] = '900000000';
        }

        $license_version = str_replace(".", "", $_file_properties['version']);
        if ($server_version = sendHeartBeat()) {
            $server_version = str_replace(".", "", $server_version);
            $license_info = ioncube_file_info();
            $license_expire_time = (isset($license_info['FILE_EXPIRY'])) ? $license_info['FILE_EXPIRY'] : time();
            $colours = '';

            $colours = '';
            if ($license_version < $server_version) {
                $colours = "red";
            } elseif ($license_version > $server_version) {
                $colours = "orange";
            } elseif ($license_version == $server_version) {
                $colours = "green";
            } else {
                $colours = "grey";
            }

            $license_version = substr(str_replace(".", "", $license_version), 1);
            $license_version = str_split($license_version, 2);
            $license_version = implode(".", $license_version);
            $a = array('/^0(\d+)/', '/\.0(\d+)/');
            $b = array('\1', '.\1');
            $license_version = preg_replace($a, $b, $license_version);

            $server_version = substr(str_replace(".", "", $server_version), 1);
            $server_version = str_split($server_version, 2);
            $server_version = implode(".", $server_version);
            $a = array('/^0(\d+)/', '/\.0(\d+)/');
            $b = array('\1', '.\1');
            $server_version = preg_replace($a, $b, $server_version);

            $out .= '<p>This page communicated with the intrepid-web.net servers to gather two sets of information:<br/>1. The current game cp version<br/>2. The latest news<br/><br/>No sensitive information is sent out!</p>';
            $out .= '<p><b>License Expire Date:</b> ' . date("F j, Y \a\\t g:i a T", $license_expire_time) . '<br/>' . "\n";
            $out .= '<b>Latest Game CP Version:</b> <span style="color: green;">' . $server_version . '</span><br/>' . "\n";
            $out .= '<b>Current Game CP Version:</b> <span style="color: ' . $colours . ';">' . $license_version . '</span></p>' . "\n";

            if (isset($_license_properties['features'])) {
                $out .= '<p><b>Licensed to PayPal E-Mail:</b> ' . $_license_properties['features']['value']['licensed_to'] . "</p>\n";
            }

            if (isset($_license_properties['features']) && isset($_license_properties['features']['value']['enable_restrictions']) && $_license_properties['features']['value']['enable_restrictions'] == 1) {
                $out .= '<p><b>License Restrictions:</b> <span style="color: red; font-weight: bold;">ENABLED</span> <em>This means the name of the Game CP databas has been restricted to "RF_GameCP" to prevent re-selling. Sorry!</em></p>' . "\n";
            }

            if ($colours == "red") {
                $out .= '<p style="font-weight: bold; color: red;">This copy of the RF Game Control Panel is out of date!</p>';
                $out .= "<p>" . "\n";
                $out .= "Get the latest version of the Game CP by <a href='http://www.intrepid-web.net/contact-us/'>contacting Intrepid-Web.</a>";
                $out .= "</p>" . "\n";
            } elseif ($colours == "green") {
                $out .= '<p style="color: green;">You have the latest RF Online Game Control Panel</p>';
            } elseif ($colours == "orange") {
                $out .= '<p>You have a BETA or ALPHA version of the RF Online Game Control Panel</p>';
            } else {
                $out .= '<p>Unknown version retrieved of the RF Online Game Control Panel</p>';
            }

            if (!function_exists('SimpleXMLElement')) {

                $news_xml = 'http://www.intrepid-web.net/feed/?hl=en';
                $news_xml = @file_get_contents($news_xml);

                if (is_bool($news_xml) && $news_xml == false) {
                    $out .= '<p>Cannot get the latest development logs from Intrepid-Web.NET due to being unable to connect using file_get_contents()</p>';
                } else {

                    $xml_news = iconv('UTF-8', 'ISO-8859-15//TRANSLIT', $news_xml);
                    $xml_news = new SimpleXMLElement($news_xml);
                    $xml_news = $xml_news->channel->item;

                    $out .= '<h2>Latest Development Logs</h2>';
                    // define the namespaces that we are interested in
                    $ns = array
                    (
                        'content' => 'http://purl.org/rss/1.0/modules/content/',
                        'wfw' => 'http://wellformedweb.org/CommentAPI/',
                        'dc' => 'http://purl.org/dc/elements/1.1/'
                    );

                    foreach ($xml_news as $news) {
                        $out .= '<div class="panel panel-info">';
                        $out .= '<div class="panel-heading"><strong>' . (trim($news->title)) . '</strong> <small>' . $news->pubDate . '</small></div>';
                        $out .= '<div class="panel-body">';
                        $content = $news->children($ns['content']);
                        $out .= str_replace('border="0"', '', (string)trim(nl2br($news->description)));
                        $out .= '<br/><br/><a href="' . $news->link . '">[ Read More ]</a>';
                        $out .= '</div>';
                        $out .= '</div>';

                    }


                }

            }

        } else {
            $out .= '<p>Error! Unable to communicate with Intrepid-Web\'s (method: fsockopen) server to check for game cp version updates!</p>';
        }

    } else {

        $out .= _l('no_permission');

    }

} else {
    $out .= _l('invalid_page_load');
}
?>