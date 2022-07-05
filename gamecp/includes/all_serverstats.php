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
    $module[_l('Server Stats')][_l('Server Stats')] = $file;
    return;
}

$lefttitle = "Server Status";

if ($this_script == $script_name) {

    # Write out our basic information about this page
    $percent_inc = 1;

    # Include our char creation file- libchar
    include("./includes/libchart/classes/libchart.php");

    connectuserdb();

    $racenum = mssql_query('SELECT serial,nBellaUser,nCoraUser,nAccUser,nAverageUser,nMaxUser,ServerName FROM tbl_ServerUser_Log ORDER BY serial DESC');
    $racenum = mssql_fetch_array($racenum);

    $accs = mssql_result(mssql_query('SELECT count(Serial) FROM tbl_UserAccount'), 0, 0);

    connectdatadb();
    $chars = mssql_result(mssql_query('SELECT count(Serial) FROM tbl_base WHERE DCK=0'), 0, 0);
    $out .= "<p><em><strong>Server Stats</strong></em></p>
				<table border=\"0\" cells[acong=\"5\" cellpadding=\"5\">
					<tr>
						<td width=\"160\" align=\"left\">Average Users</td>
						<td colspan=\"2\">" . round($racenum['nAverageUser'] * $percent_inc) . "<td>
					</tr>
					<tr>
						<td width=\"160\" align=\"left\">Max Users Online</td>
						<td colspan=\"2\">" . round($racenum['nMaxUser'] * $percent_inc) . "<td>
					</tr>
					<tr>
						<td width=\"160\" align=\"left\">Online Users</td>
						<td colspan=\"2\">" . (round($racenum['nBellaUser'] * $percent_inc) + round($racenum['nCoraUser'] * $percent_inc) + round($racenum['nAccUser'] * $percent_inc)) . "<td>
					</tr>
					<tr>
						<td width=\"160\" align=\"left\">Total Accounts</td>
						<td colspan=\"2\">" . $accs . "<td>
					</tr>
					<tr>
						<td width=\"160\" align=\"left\">Total Characters</td>
						<td colspan=\"2\">" . $chars . "<td>
					</tr>
				</table>";

    if (function_exists('gd_info')) {
        $chart = new VerticalBarChart();
        $serie1 = new XYDataSet();
        $serie2 = new XYDataSet();

        connectuserdb();
        $graph_query = mssql_query("SELECT TOP 10 Convert(nvarchar,dtDate,108) AS Date, serial, nAverageUser, nMaxUser FROM tbl_ServerUser_Log ORDER BY serial DESC");

        $i = 1;
        while ($graph = mssql_fetch_array($graph_query)) {
            $graph['nMaxUser'] = round($graph['nMaxUser'] * $percent_inc);
            $graph['nAverageUser'] = round($graph['nAverageUser'] * $percent_inc);
            $serie1->addPoint(new Point($graph['Date'], $graph['nMaxUser']));
            $serie2->addPoint(new Point($graph['Date'], $graph['nAverageUser']));
            $i++;
        }
        // Free Result
        @mssql_free_result($graph_query);

        $dataSet = new XYSeriesDataSet();
        $dataSet->addSerie("Max", $serie1);
        $dataSet->addSerie("Avg", $serie2);
        $chart->setDataSet($dataSet);
        $chart->getPlot()->setGraphCaptionRatio(0.65);

        $chart->setTitle("Server Population Graph");
        $chart->render("./includes/cache/server.png");

        $out .= '<img alt="Line chart" src="./includes/cache/server.png" style="border: 1px solid gray;"/>';
    }

    $out .= "<p><em><strong>Race Population</strong></em></p>
				<table border=\"0\" cells[acong=\"5\" cellpadding=\"5\">
					<tr>
						<td width=\"160\" align=\"left\">Bells</td>
						<td colspan=\"2\">" . round($racenum['nBellaUser'] * $percent_inc) . "<td>
					</tr>
					<tr>
						<td width=\"160\" align=\"left\">Coras</td>
						<td colspan=\"2\">" . round($racenum['nCoraUser'] * $percent_inc) . "<td>
					</tr>
					<tr>
						<td width=\"160\" align=\"left\">Accretian</td>
						<td colspan=\"2\">" . round($racenum['nAccUser'] * $percent_inc) . "<td>
					</tr>
				</table>";
} else {
    $out .= _l('invalid_page_load');
}

?>