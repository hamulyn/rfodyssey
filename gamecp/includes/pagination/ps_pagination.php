<?php
/**
 * Game Control Panel v2
 * Copyright (c) www.intrepid-web.net
 *
 * The use of this product is subject to a license agreement
 * which can be found at http://www.intrepid-web.net/rf-game-cp-v2/license-agreement/
 */
/**
 * PHPSense Pagination Class
 *
 * PHP tutorials and scripts
 *
 * @package        PHPSense
 * @author        Jatinder Singh Thind
 * @copyright    Copyright (c) 2006, Jatinder Singh Thind
 * @link        http://www.phpsense.com
 */

// ------------------------------------------------------------------------

class PS_Pagination
{
    var $php_self;
    var $rows_per_page; //Number of records to display per page
    var $total_rows; //Total number of rows returned by the query
    var $links_per_page; //Number of links to display per page
    var $sql;
    var $sql_p2;
    var $debug = true;
    var $conn;
    var $page;
    var $max_pages;
    var $offset;
    var $page_name;
    var $absolute_path;
    var $query_id;
    var $query_count;

    /**
     * Constructor
     *
     * @param resource $connection Mysql connection link
     * @param string $sql SQL query to paginate. Example : SELECT * FROM users
     * @param integer $rows_per_page Number of records to display per page. Defaults to 10
     * @param integer $links_per_page Number of links to display per page. Defaults to 5
     */

    function PS_Pagination($connection, $sql, $sql_p2, $rows_per_page = 10, $links_per_page = 5, $url, $query_count = '')
    {
        $this->conn = $connection;
        $this->sql = $sql;
        $this->sql_p2 = $sql_p2;
        $this->rows_per_page = $rows_per_page;
        $this->links_per_page = $links_per_page;
        $this->php_self = $url;
        $this->absolute_path = str_replace("pagination", "", dirname(__FILE__));
        if (DIRECTORY_SEPARATOR == '\\') {
            $this->absolute_path = str_replace('\\', '/', $this->absolute_path);
        }
        $this->query_id = $_GET['do'] . "_" . md5($sql);
        $this->query_count = $query_count;

        if (isset($_GET['page_gen'])) {
            $this->page = intval($_GET['page_gen']);
        }
    }

    /**
     * Writes a cache file
     * @param string contents of the buffer
     * @param string filename to use when creating cache file
     * @return void
     */
    function writeCache($content, $filename)
    {
        $fp = fopen($this->absolute_path . 'cache/' . $filename, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    /**
     * Checks for cache files
     * @param string filename of cache file to check for
     * @param int maximum age of the file in seconds
     * @return mixed either the contents of the cache or false
     */
    function readCache($filename, $expiry)
    {

        if (file_exists($this->absolute_path . 'cache/' . $filename)) {
            if ((time() - $expiry) > filemtime($this->absolute_path . 'cache/' . $filename)) {
                return FALSE;
            }
            $cache = file($this->absolute_path . 'cache/' . $filename);
            return implode('', $cache);
        }
        return FALSE;
    }

    /**
     * Executes the SQL query and initializes internal variables
     *
     * @access public
     * @return resource
     */
    function paginate()
    {
        global $out;

        if (!$this->conn) {
            if ($this->debug) $out .= "MSSQL connection missing<br />";
            return false;
        }

        if ($this->query_count == '') {
            if (!$this->total_rows = $this->readCache($this->query_id . ".cache", 600)) {
                $query = preg_replace('/^(SELECT\s?(DISTINCT\s?)?)(.*)\sFROM\s(.*)/s', '\1 COUNT(*) as count FROM \4', $this->sql);

                $all_rs = @mssql_query($query);
                if (!$all_rs) {
                    if ($this->debug) $out .= "SQL query failed. Check your query.<br />";
                    return false;
                }
                $rows = mssql_fetch_row($all_rs);
                $this->total_rows = (isset($rows[0])) ? $rows[0] : 0;
                $this->writeCache($this->total_rows, $this->query_id . '.cache');
                @mssql_close($all_rs);
            }
        } else {
            $this->total_rows = $this->query_count;
        }

        $this->max_pages = ceil($this->total_rows / $this->rows_per_page);
        //Check the page value just in case someone is trying to input an aribitrary value
        if ($this->page > $this->max_pages || $this->page <= 0) {
            $this->page = 1;
        }

        //Calculate Offset
        $this->offset = $this->rows_per_page * ($this->page - 1);

        //Write our full 'proper' query
        $query = $this->sql;

        # Get order by
        $orderBy = stristr($query, 'ORDER BY');
        if ($orderBy == '') {
            if (preg_match('/.*(ORDER BY .*)/i', $this->sql_p2, $matches)) {
                $orderBy = $matches[1];
            } else {
                $orderBy = '';
            }
        }

        $explode = explode(",", $orderBy);
        if (isset($explode[0])) {
            $orderBy = $explode[0];
        }

        if ($this->offset == 0) {
            $query = preg_replace('/^(SELECT\s(DISTINCT\s)?)/i', '\1TOP ' . $this->rows_per_page . ' ', $query);
            $query = $query . ' ' . $orderBy;
        } else {
            # Bad hack lol so bad :(
            if (preg_match('/^[WHERE|AND|OR]+\s+(.*) NOT IN\s+\(/i', trim($this->sql_p2), $matches)) {
                $id = $matches[1];
            } else {
                $id = 'B.Serial';
            }

            # Modify our order by
            if (!$orderBy) {
                $over = 'ORDER BY ' . $id;
            } else {
                $over = $orderBy . ', ' . $id . ' DESC';
                #$over = preg_replace('/\sDESC|ASC/', '', $orderBy);
            }

            // Remove ORDER BY clause from $query
            $query = preg_replace('/\s+ORDER BY(.*)/', '', $query);
            $query = preg_replace('/^(SELECT\s(DISTINCT\s)?)/i', '', $query);

            $where = stristr($query, 'WHERE');
            $from = stristr($query, 'FROM');
            $from = str_replace($where, '', $from);

            $rows = str_replace($from, '', $query);
            $rows = str_replace($where, '', $rows);


            $start = $this->offset + 1;
            $end = $this->offset + $this->rows_per_page;

            $query = "WITH RowData(RowDataId, RowNum) AS
(
	SELECT
		${id} as RowDataId,
		row_number() OVER (${over}) AS RowNum ${from} ${where}
)
SELECT
	RowInfo.RowNum, ${rows} ${from}
INNER JOIN
	RowData as RowInfo
	ON RowInfo.RowDataId = ${id}
WHERE RowInfo.RowNum BETWEEN ${start} AND ${end}
${orderBy}";
        }

        //Fetch the required result set
        $rs = @mssql_query($query, $this->conn);
        if (!$rs) {
            if ($this->debug) $out .= "Pagination query failed. Check your query.<br />" . mssql_get_last_message();
            return false;
        }
        return $rs;
    }

    /**
     * Display the link to the first page
     *
     * @access public
     * @param string $tag Text string to be displayed as the link. Defaults to 'First'
     * @return string
     */
    function renderFirst($tag = 'First')
    {
        if ($this->page == 1) {
            return '';
        } else {
            return '<li><a href="' . $this->php_self . '&page_gen=1">' . $tag . '</li>';
        }
    }

    /**
     * Display the link to the last page
     *
     * @access public
     * @param string $tag Text string to be displayed as the link. Defaults to 'Last'
     * @return string
     */
    function renderLast($tag = 'Last')
    {
        if ($this->page == $this->max_pages) {
            return '';
        } else {
            return '<li><a href="' . $this->php_self . '&page_gen=' . $this->max_pages . '">' . $tag . '</li>';
        }
    }

    /**
     * Display the next link
     *
     * @access public
     * @param string $tag Text string to be displayed as the link. Defaults to '>>'
     * @return string
     */
    function renderNext($tag = ' &gt;&gt;')
    {
        if ($this->page < $this->max_pages) {
            return '<li><a href="' . $this->php_self . '&page_gen=' . ($this->page + 1) . '">' . $tag . '</a><li>';
        } else {
            return '';
        }
    }

    /**
     * Display the previous link
     *
     * @access public
     * @param string $tag Text string to be displayed as the link. Defaults to '<<'
     * @return string
     */
    function renderPrev($tag = '&lt;&lt;')
    {
        if ($this->page > 1) {
            return '<li><a href="' . $this->php_self . '&page_gen=' . ($this->page - 1) . '">' . $tag . '</a></li>';
        } else {
            return '';
        }
    }

    /**
     * Display the page links
     *
     * @access public
     * @return string
     */
    function renderNav()
    {
        $start = 0;
        for ($i = 1; $i <= $this->max_pages; $i += $this->links_per_page) {
            if ($this->page >= $i) {
                $start = $i;
            }
        }

        if ($this->max_pages > $this->links_per_page) {
            $end = $start + $this->links_per_page;
            if ($end > $this->max_pages) $end = $this->max_pages + 1;
        } else {
            $end = $this->max_pages + 1;
        }

        $links = '';

        for ($i = $start; $i < $end; $i++) {
            if ($i == $this->page) {
                $links .= " <li class='active'><a href='#'>$i</a></li> ";
            } else {
                $links .= ' <li><a href="' . $this->php_self . '&page_gen=' . $i . '">' . $i . '</a></li> ';
            }
        }

        return $links;
    }

    /**
     * Display full pagination navigation
     *
     * @access public
     * @return string
     */
    function renderFullNav()
    {
        global $rs;

        if (isset($rs)) {
            @mssql_free_result($rs);
        }

        return '<ul class="pagination" style="margin-right: 10px;">'."<li><a href='#'>[Page $this->page of $this->max_pages]</a></li>" . $this->renderFirst() . $this->renderPrev() . $this->renderNav() . $this->renderNext() . $this->renderLast().'</ul>';
    }

    /**
     * Set debug mode
     *
     * @access public
     * @param bool $debug Set to TRUE to enable debug messages
     * @return void
     */
    function setDebug($debug)
    {
        $this->debug = $debug;
    }

    function totalResults()
    {
        return $this->total_rows;
    }
}

?>