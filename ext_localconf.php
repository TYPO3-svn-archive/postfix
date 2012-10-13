<?php

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

  // Get the extensions's configuration
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['postfix']);

  // If sample tasks should be shown,
  // register information for the test and sleep tasks
if (!empty($extConf['showSampleTasks'])) {
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_postfix_TestTask'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'LLL:EXT:' . $_EXTKEY . '/lib/scheduler/locallang.xml:label.testTask.name',
    'description'      => 'LLL:EXT:' . $_EXTKEY . '/lib/scheduler/locallang.xml:label.testTask.description',
    'additionalFields' => 'tx_postfix_TestTask_AdditionalFieldProvider'
  );
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_postfix_QuotaTask'] = array(
  'extension'        => $_EXTKEY,
  'title'            => 'LLL:EXT:' . $_EXTKEY . '/lib/scheduler/locallang.xml:label.quotaTask.name',
  'description'      => 'LLL:EXT:' . $_EXTKEY . '/lib/scheduler/locallang.xml:label.quotaTask.description',
  'additionalFields' => 'tx_postfix_QuotaTask_AdditionalFieldProvider'
);

?>