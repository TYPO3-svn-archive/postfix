<?php

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

  // Get the extensions's configuration
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['scheduler']);

  // If sample tasks should be shown,
  // register information for the test and sleep tasks
if (!empty($extConf['showSampleTasks'])) {
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_scheduler_TestTask'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:testTask.name',
    'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:testTask.description',
    'additionalFields' => 'tx_scheduler_TestTask_AdditionalFieldProvider'
  );
}
?>