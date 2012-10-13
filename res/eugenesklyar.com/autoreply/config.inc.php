<?php

/**
 * Auto - Reply Plugin (for Goldfish)
 * Configuration file
 *
 * @version 0.1a
 *
 * Author(s): Yevgen Sklyar 
 * Date: May 10, 2010
 * License: GPL
 * www.eugenesklyar.com
 */

/**
 * Connection string for this plugin (alternatively it will use the default RoundCube connection)
 */
// dwildt, 100818
//$rcmail_config['autoreply_db_dsn'] = 'mysql://myuser:mypassword@localhost/mail_db';
$rcmail_config['autoreply_db_dsn'] = 'mysql://postfix:Trag*Koen@localhost/typo3_40';

// dwildt, 100818
// Pid of the records in the fe_users table (uid of the TYPO3 sysfolder)
$pid = 372;

/**
 * Query for fetching the current autoreply message for the particular email address
 */
// dwildt, 100818
//$rcmail_config['get_query'] = 'SELECT `descname`, `from`, `to`, `message`, `force_enabled`, `subject` FROM autoresponder WHERE `email` = %u LIMIT 1';
$rcmail_config['get_query'] = 'SELECT `descname`, `from_mail`, `to_mail`, `message`, `force_enabled`, `subject` FROM tx_postfix_autoresponder WHERE `email` = %u AND `hidden` = 0 AND `deleted` = 0 AND `pid` = '.$pid.' LIMIT 1';

/**
 * Query for adding a new autoreply message for a particular address
 */
// dwildt, 100818
//$rcmail_config['insert_query'] = 'INSERT INTO autoresponder(`email`, `descname`, `from`, `to`, `message`, `enabled` , `force_enabled`, `subject`) VALUES (%u, %d, %f, %t, %m, 0, %e, %s)';
$rcmail_config['insert_query'] = 'INSERT INTO tx_postfix_autoresponder(`pid`, `email`, `descname`, `from_mail`, `to_mail`, `message`, `enabled` , `force_enabled`, `subject`) VALUES ('.$pid.', %u, %d, %f, %t, %m, 0, %e, %s)';

/**
 * Query for updating an autoreply message that is already in the database
 */
// dwildt, 100818
//$rcmail_config['update_query'] = 'UPDATE autoresponder SET `descname` = %d, `from` = %f, `to` = %t, `message` = %m, `force_enabled` = %e, `subject` = %s WHERE `email` = %u LIMIT 1';
$rcmail_config['update_query'] = 'UPDATE tx_postfix_autoresponder SET `descname` = %d, `from_mail` = %f, `to_mail` = %t, `message` = %m, `force_enabled` = %e, `subject` = %s WHERE `email` = %u AND `pid` = '.$pid.' LIMIT 1';