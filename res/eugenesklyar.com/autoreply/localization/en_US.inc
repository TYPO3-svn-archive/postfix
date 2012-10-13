<?php

// Labels used for different portions of the plugin
$labels = array();
$labels['autoreply'] = 'Auto Reply';
$labels['maindiv_title'] = 'Auto Responder';
$labels['enabled'] = 'Enabled';
$labels['descname'] = 'Description';
$labels['from'] = 'From (YYYY-MM-DD)';
$labels['to'] = 'Until (YYYY-MM-DD)';
$labels['subject'] = 'Subject';
$labels['msg'] = 'Message';

// Messages used for the different portions of the plugin
$messages = array();
$messages['internal_error'] = 'Auto Reply: An internal error has occured.';
$messages['update_error'] = 'Auto Reply: The update action for this user failed.';
$messages['insert_error'] = 'Auto Reply: The insert action on this user failed.';
$messages['db_conn_err'] = 'Auto Reply: The connection to the database was refused. Please check your config.inc.php file.';
$messages['successfully_saved'] = 'You have successfully saved your automatic reply for';
$messages['missing_subject'] = 'You require a subject before you can save the auto reply.';
$messages['missing_message'] = 'You require a message to send out with your auto reply.';
?>