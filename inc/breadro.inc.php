<?php
/**
 *
 * This file is part of BreadRO HESK.
 *
 */

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

function breadro_dbSetNames()
{
	global $breadro_db_link;

    mysqli_set_charset($breadro_db_link, 'gbk');

} // END breadro_dbSetNames()

function breadro_dbEscape($in)
{
	global $breadro_db_link;

    $in = mysqli_real_escape_string($breadro_db_link, stripslashes($in));
    $in = str_replace('`','&#96;',$in);

    return $in;
} // END breadro_dbEscape()

function breadro_dbConnect()
{
	global $hesk_settings;
	global $breadro_db_link;
    global $hesklang;

    // Do we have an existing active link?
    if ($breadro_db_link)
    {
        return $breadro_db_link;
    }

    // Is mysqli supported?
    if ( ! function_exists('mysqli_connect') )
    {
    	die($hesklang['emp']);
    }

    // We want pre-PHP 8.1 behavior for now
    mysqli_report(MYSQLI_REPORT_OFF);

	// Do we need a special port? Check and connect to the database
	if ( strpos($hesk_settings['breadro_db_host'], ':') )
	{
		list($hesk_settings['breadro_db_host_no_port'], $hesk_settings['breadro_db_port']) = explode(':', $hesk_settings['breadro_db_host']);
		$breadro_db_link = @mysqli_connect($hesk_settings['breadro_db_host_no_port'], $hesk_settings['breadro_db_user'], $hesk_settings['breadro_db_pass'], $hesk_settings['breadro_db_name'], intval($hesk_settings['breadro_db_port']) );
	}
	else
	{
		$breadro_db_link = @mysqli_connect($hesk_settings['breadro_db_host'], $hesk_settings['breadro_db_user'], $hesk_settings['breadro_db_pass'], $hesk_settings['breadro_db_name']);
	}

	// Errors?
    if ( ! $breadro_db_link)
    {
    	if ($hesk_settings['debug_mode'])
        {
			hesk_error("$hesklang[cant_connect_db]</p><p>$hesklang[mysql_said]:<br />(".mysqli_connect_errno().") ".mysqli_connect_error()."</p>");
        }
        else
        {
			hesk_error("$hesklang[cant_connect_db]</p><p>$hesklang[contact_webmsater] <a href=\"mailto:$hesk_settings[webmaster_mail]\">$hesk_settings[webmaster_mail]</a></p>");
        }
    }

    // Check MySQL/PHP version and set encoding to gbk
    breadro_dbSetNames();

    // Set the correct timezone
    // breadro_dbSetTimezone();

    return $breadro_db_link;

} // END breadro_dbConnect()


function breadro_dbClose()
{
	global $breadro_db_link;

    return @mysqli_close($breadro_db_link);

} // END breadro_dbClose()

function breadro_dbQuery($query)
{
    global $breadro_last_query;
    global $breadro_db_link;
    global $hesklang, $hesk_settings;

    if ( ! $breadro_db_link && ! breadro_dbConnect())
    {
        return false;
    }

    $breadro_last_query = $query;

    # echo "<p>EXPLAIN $query</p>\n";

    if ($res = @mysqli_query($breadro_db_link, $query))
    {
    	return $res;
    } else {
        breadro_dbHandleFailure($query);
    }
} // END breadro_dbQuery()

function breadro_dbHandleFailure($query) {
    global $hesk_settings, $hesklang, $breadro_db_link;

    $valid_response_types = array('json', 'throw');

    if (!isset($hesk_settings['db_failure_response']) || !in_array($hesk_settings['db_failure_response'], $valid_response_types)) {
        if ($hesk_settings['debug_mode']) {
            hesk_error("$hesklang[cant_sql]: $query</p><p>$hesklang[mysql_said]:<br />".mysqli_error($breadro_db_link)."</p>");
        } else {
            hesk_error("$hesklang[cant_sql]</p><p>$hesklang[contact_webmsater] <a href=\"mailto:$hesk_settings[webmaster_mail]\">$hesk_settings[webmaster_mail]</a></p>");
        }
    } elseif ($hesk_settings['db_failure_response'] === 'json') {
        header('Content-Type: application/json');
        http_response_code(500);
        if ($hesk_settings['debug_mode']) {
            print json_encode(array(
                'status' => 'failure',
                'title' => $hesklang['cant_sql'],
                'message' => mysqli_error($breadro_db_link),
                'query' => $query
            ));
        } else {
            print json_encode(array(
                'status' => 'failure',
                'title' => $hesklang['cant_sql'],
                'message' => sprintf('%s: %s', $hesklang['contact_webmsater'], $hesk_settings['webmaster_mail'])
            ));
        }
        exit();
    } elseif ($hesk_settings['db_failure_response'] === 'throw') {
        $message = $hesk_settings['debug_mode'] ? mysqli_error($breadro_db_link) : $hesklang['cant_sql'];
        throw new Exception($message);
    }
}

function breadro_dbFetchAssoc($res)
{

    return @mysqli_fetch_assoc($res);

} // END hesk_FetchAssoc()


function breadro_dbFetchRow($res)
{

    return @mysqli_fetch_row($res);

} // END hesk_FetchRow()


function breadro_dbResult($res, $row = 0, $column = 0)
{
	$i=0;
	$res->data_seek(0);

	while ($tmp = @mysqli_fetch_array($res, MYSQLI_NUM))
    {
		if ($i==$row)
        {
        	return $tmp[$column];
        }
		$i++;
	}

	return '';

} // END breadro_dbResult()


function breadro_dbInsertID()
{
	global $breadro_db_link;

    if ($lastid = @mysqli_insert_id($breadro_db_link))
    {
        return $lastid;
    }

} // END breadro_dbInsertID()


function breadro_dbFreeResult($res)
{

    return @mysqli_free_result($res);

} // END breadro_dbFreeResult()


function breadro_dbNumRows($res)
{

    return @mysqli_num_rows($res);

} // END breadro_dbNumRows()


function breadro_dbAffectedRows()
{
	global $breadro_db_link;

    return @mysqli_affected_rows($breadro_db_link);

} // END breadro_dbAffectedRows()
