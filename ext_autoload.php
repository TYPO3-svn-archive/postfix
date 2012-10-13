<?php
/*
 * Register necessary class names with autoloader
 */
$extensionPath = t3lib_extMgm::extPath('postfix');
return array(
    'tx_scheduler_testtask' => $extensionPath . 'lib/scheduler/class.tx_scheduler_testtask.php',
    'tx_scheduler_testtask_additionalfieldprovider' => $extensionPath . 'lib/scheduler/class.tx_scheduler_testtask_additionalfieldprovider.php',
);
?>