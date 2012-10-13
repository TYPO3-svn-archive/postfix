<?php
/*
 * Register necessary class names with autoloader
 */
$extensionPath = t3lib_extMgm::extPath('postfix');
return array(
    'tx_postfix_quotatask'                          => $extensionPath . 'lib/scheduler/class.tx_postfix_quotatask.php',
    'tx_postfix_quotatask_additionalfieldprovider'  => $extensionPath . 'lib/scheduler/class.tx_postfix_quotatask_additionalfieldprovider.php',
    'tx_postfix_testtask'                           => $extensionPath . 'lib/scheduler/class.tx_postfix_testtask.php',
    'tx_postfix_testtask_additionalfieldprovider'   => $extensionPath . 'lib/scheduler/class.tx_postfix_testtask_additionalfieldprovider.php',
);
?>