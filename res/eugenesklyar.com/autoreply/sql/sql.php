<?php

/**
 * Auto - Reply Plugin (for Goldfish)
 * SQL Driver File
 *
 * @version 0.1a
 *
 * Author(s): Yevgen Sklyar 
 * Date: May 10, 2010
 * License: GPL
 * www.eugenesklyar.com
 */

/**
 * Fetches the information to prefill the input fields
 */
function autoreply_get() 
{
    $rcmail = rcmail::get_instance();
  
    // Get config options
    $dsn = $rcmail->config->get('autoreply_db_dsn');
    $sql = $rcmail->config->get('get_query');
  
    // Connect
    if ($dsn) {
        if (is_array($dsn) && empty($dsn['new_link'])) { 
            $dsn['new_link'] = true;
        }
        elseif (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn)) {
            $dsn .= '?new_link=true';
            $db = new rcube_mdb2($dsn, '', FALSE);
            $db->db_connect('w');
        }
    } 
    else {
        $db = $rcmail->get_dbh();
    }

    if ($err = $db->is_error()) {
  	    return CONN_ERR;
    }
  
    // Get the username out of the session to fetch their info
    $sql = str_replace('%u', $db->quote($_SESSION['username'], 'text'), $sql);
  
    // Query the database and then save into an array
    $res = $db->query($sql);
    if (!$db->is_error()) {
        $result = $db->fetch_array($res);
    }
  
    return $result;
}

/**
 * Saves the information into the Goldfish table
 */
function autoreply_save($prefilled, $descname, $from, $to, $subject, $message, $enabled) 
{
    $rcmail = rcmail::get_instance();
    
    // Get config options
    $dsn = $rcmail->config->get('autoreply_db_dsn');
  
    // Connect
    if ($dsn) {
        if (is_array($dsn) && empty($dsn['new_link'])) { 
            $dsn['new_link'] = true;
        }
        elseif (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn)) {
            $dsn .= '?new_link=true';
            $db = new rcube_mdb2($dsn, '', FALSE);
            $db->db_connect('w');
        }
    } 
    else {
        $db = $rcmail->get_dbh();
    }

    if ($err = $db->is_error()) {
  	    return CONN_ERR;
    }
    
    // Check if it is an update or an insert statement (for selecting which SQL query to use)
    switch ($prefilled) {
        // Insert
        case 0:
            
            $sql = $rcmail->config->get('insert_query');
            
            // Replace for actual variables
            $sql = str_replace('%u', $db->quote($_SESSION['username'], 'text'), $sql);
            $sql = str_replace('%d', $db->quote($descname, 'text'), $sql);
            $sql = str_replace('%f', $db->quote($from, 'date'), $sql);
            $sql = str_replace('%t', $db->quote($to, 'date'), $sql);
            $sql = str_replace('%m', $db->quote($message, 'text'), $sql);
            $sql = str_replace('%e', $db->quote($enabled, 'text'), $sql);
            $sql = str_replace('%s', $db->quote($subject, 'text'), $sql);
            
            // Query the database and then save into an array
            $res = $db->query($sql);
            if (!$db->is_error()) {
                if ($db->affected_rows($res) == 1) {
                    return AUTOREPLY_SUCCESS;
                }
            } 
            else {
                return AUTOREPLY_INS_FAIL;
            }
            break;
        // Update
        case 1:
            
            $sql = $rcmail->config->get('update_query');
            
            // Replace for actual variables
            $sql = str_replace('%d', $db->quote($descname, 'text'), $sql);
            $sql = str_replace('%f', $db->quote($from, 'date'), $sql);
            $sql = str_replace('%t', $db->quote($to, 'date'), $sql);
            $sql = str_replace('%m', $db->quote($message, 'text'), $sql);
            $sql = str_replace('%e', $db->quote($enabled, 'text'), $sql);
            $sql = str_replace('%s', $db->quote($subject, 'text'), $sql);
            $sql = str_replace('%u', $db->quote($_SESSION['username'], 'text'), $sql);
            
            // Query the database and then save into an array
            $res = $db->query($sql);
            if (!$db->is_error()) {
                if ($db->affected_rows($res) == 1) {
                    return AUTOREPLY_SUCCESS;
                }
            } 
            else {
                return AUTOREPLY_UPD_FAIL;
            }
            break;
    }
}

?>
