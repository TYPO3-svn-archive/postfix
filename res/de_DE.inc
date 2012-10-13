<?php

// Labels used for different portions of the plugin
$labels = array();
$labels['autoreply']      = 'Auto-Antwort';
$labels['maindiv_title']  = 'Auto Responder';
$labels['enabled']        = 'Aktiviert';
$labels['descname']       = 'Beschreibung';
$labels['from']           = 'Von (YYYY-MM-DD)';
$labels['to']             = 'Bis (YYYY-MM-DD)';
$labels['subject']        = 'Betreff';
$labels['msg']            = 'Nachricht';

// Messages used for the different portions of the plugin
$messages = array();
$messages['internal_error']     = 'Auto Reply: Interner Fehler.';
$messages['update_error']       = 'Auto Reply: Update-Fehler für diesen Benutzer';
$messages['insert_error']       = 'Auto Reply: Insert-Fehler für diesen Benutzer.';
$messages['db_conn_err']        = 'Auto Reply: Verbindung zur Datenbank kann nicht hergestellt werden. Bitte prüfe config.inc.php.';
$messages['successfully_saved'] = 'Sie haben Ihre automatische Antwort erfolgreich gespeichert für';
$messages['missing_subject']    = 'Bitte geben Sie ein Betreff an, damit Ihre automatische Antwort gespeichert werden kann.';
$messages['missing_message']    = 'Bitte geben Sie eine Nachricht an, damit Ihre automatische Antwort gespeichert werden kann.';
?>