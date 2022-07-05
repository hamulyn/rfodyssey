<?php

define('MSSQL_ASSOC', 1);
define('MSSQL_BOTH', 3);
define('MSSQL_NUM', 2);


function mssql_connect($servername, $username, $password, $new_link = false)
{
    global $conn;

    $serverName = $servername;
    $connectionInfo = array("UID" => $username, "PWD" => $password, 'ReturnDatesAsStrings' => true);
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    return $conn;
}

function mssql_select_db($database_name, $conn)
{
    return sqlsrv_query($conn, "USE [" . $database_name . "]");
}

function mssql_query($query, $link_identifier = null, $batch_size = 0)
{
    global $conn;

    $query = preg_replace('/\"(.*)\"/', "'$1'", $query);

    $options = array(
        'Scrollable' => SQLSRV_CURSOR_CLIENT_BUFFERED
    );
    if (stripos($query, '{call') !== FALSE) {
        $options['Scrollable'] = SQLSRV_CURSOR_FORWARD;
    }

    $link_identifier = ($link_identifier == null) ? $conn : $link_identifier;

    $sqlsrv_query_result = sqlsrv_query($link_identifier, $query, array(), $options);

    return $sqlsrv_query_result;
}

function mssql_fetch_array($res, $result_type = MSSQL_BOTH)
{
    if ($result_type == MSSQL_BOTH) {
        $result_type = SQLSRV_FETCH_BOTH;
    } elseif ($result_type == MSSQL_ASSOC) {
        $result_type = SQLSRV_FETCH_NUMERIC;
    } else {
        $result_type = SQLSRV_FETCH_ASSOC;
    }

    return sqlsrv_fetch_array($res, $result_type, SQLSRV_SCROLL_NEXT);
}

function mssql_free_result($resource)
{
    return sqlsrv_free_stmt($resource);
}

function mssql_num_rows($res)
{
    return sqlsrv_num_rows($res);
    //$num_rows = sqlsrv_num_rows($res);
    //return $num_rows ? $num_rows : TRUE;
}

function mssql_fetch_row($res)
{
    return @sqlsrv_fetch_array($res, SQLSRV_FETCH_NUMERIC);
}

function mssql_fetch_assoc($res)
{
    return @sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
}

function mssql_rows_affected($res)
{
    global $sqlsrv_query_result;

    $affected = sqlsrv_rows_affected($sqlsrv_query_result);
    return $affected;
}

function mssql_get_last_message()
{
    return '<pre>' . var_export(sqlsrv_errors(), true) . '</pre>';
}

function mssql_result($result, $row, $field)
{
    sqlsrv_fetch($result);

    return sqlsrv_get_field($result, $row);
}

function mssql_close($conn)
{
    return sqlsrv_close($conn);
}