<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$tempColumns = array (
  'tx_postfix_id' => array (
    'exclude' => 1,    
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_id',
    'config' => array (
      'type' => 'input',
      'size' => '30',
      'eval' => 'int',
    )
  ),
  'tx_postfix_gid' => array (
    'exclude' => 1,
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_gid',
    'config' => array (
      'type' => 'input',
      'size' => '30',
      'eval' => 'int',
    )
  ),
  'tx_postfix_password' => array (
    'exclude' => 1,
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_password',
    'config' => array (
      'type' => 'input',
      'size' => '30',
      'eval' => 'nospace',
    )
  ),
  'tx_postfix_maildir' => array (
    'exclude' => 1,
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_maildir',
    'config' => array (
      'type' => 'input',
      'size' => '30',
    )
  ),
  'tx_postfix_homedir' => array (
    'exclude' => 1,
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_homedir',
    'config' => array (
      'type' => 'input',
      'size' => '30',
    )
  ),
  'tx_postfix_quota' => array (
    'exclude' => 1,
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_quota',
    'config' => array (
      'type' => 'input',
      'size' => '30',
    )
  ),
  'tx_postfix_webmail' => array (
    'exclude' => 1,
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_webmail',
    'config' => array (
      'type' => 'check',
      'default' => 1,
    )
  ),
  'tx_postfix_internal' => array (
    'exclude' => 1,
    'label' => 'LLL:EXT:postfix/locallang_db.xml:fe_users.tx_postfix_internal',
    'config' => array (
      'type' => 'text',
      'cols' => '30', 
      'rows' => '5',
    )
  ),
);


t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('fe_users','--div--;Postfix,tx_postfix_id;;;;1-1-1, tx_postfix_gid, tx_postfix_password, tx_postfix_maildir, tx_postfix_homedir, tx_postfix_quota, tx_postfix_webmail, tx_postfix_internal');


t3lib_extMgm::allowTableOnStandardPages('tx_postfix_forwards');

$TCA['tx_postfix_forwards'] = array (
  'ctrl' => array (
    'title'     => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_forwards',
    'label'     => 'forward_from',
    'tstamp'    => 'tstamp',
    'crdate'    => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY forward_from',
    'delete' => 'deleted',
    'enablecolumns' => array (
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
    'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
    'searchFields'      =>  'forward_from,forward_to,'
  ),
  'feInterface' => array (
    'fe_admin_fieldList' => 'hidden, forward_from, forward_to, memo_txt',
  )
);


t3lib_extMgm::allowTableOnStandardPages('tx_postfix_domains');

$TCA['tx_postfix_domains'] = array (
  'ctrl' => array (
    'title'     => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_domains',
    'label'     => 'domain',
    'tstamp'    => 'tstamp',
    'crdate'    => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY domain',
    'delete' => 'deleted',
    'enablecolumns' => array (
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
    'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
    'searchFields'      =>  'domain,'
  ),
  'feInterface' => array (
    'fe_admin_fieldList' => 'hidden, domain',
  )
);


t3lib_extMgm::allowTableOnStandardPages('tx_postfix_canonical');

$TCA['tx_postfix_canonical'] = array (
  'ctrl' => array (
    'title'     => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical',
    'label'     => 'mail_srce',
    'tstamp'    => 'tstamp',
    'crdate'    => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY mail_srce',
    'delete' => 'deleted',
    'enablecolumns' => array (
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
    'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
    'searchFields'      =>  'mail_dest,mail_srce,'
  ),
  'feInterface' => array (
    'fe_admin_fieldList' => 'hidden, mail_srce, mail_dest',
  )
);


t3lib_extMgm::allowTableOnStandardPages('tx_postfix_canonical_sender');

$TCA['tx_postfix_canonical_sender'] = array (
  'ctrl' => array (
    'title'     => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical_sender',
    'label'     => 'mail_srce',
    'tstamp'    => 'tstamp',
    'crdate'    => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY mail_srce',
    'delete' => 'deleted',
    'enablecolumns' => array (
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
    'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
    'searchFields'      =>  'mail_dest,mail_srce,'
  ),
  'feInterface' => array (
    'fe_admin_fieldList' => 'hidden, mail_srce, mail_dest',
  )
);


t3lib_extMgm::allowTableOnStandardPages('tx_postfix_canonical_recipient');

$TCA['tx_postfix_canonical_recipient'] = array (
  'ctrl' => array (
    'title'     => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical_recipient',
    'label'     => 'mail_srce',
    'tstamp'    => 'tstamp',
    'crdate'    => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY mail_srce',
    'delete' => 'deleted',
    'enablecolumns' => array (
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
    'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
    'searchFields'      =>  'mail_dest,mail_srce,'
  ),
  'feInterface' => array (
    'fe_admin_fieldList' => 'hidden, mail_srce, mail_dest',
  )
);


t3lib_extMgm::allowTableOnStandardPages('tx_postfix_autoresponder');

$TCA['tx_postfix_autoresponder'] = array (
  'ctrl' => array (
    'title'     => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder',
    'label'     => 'descname',
    'tstamp'    => 'tstamp',
    'crdate'    => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY descname',
    'delete' => 'deleted',
    'enablecolumns' => array (
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
    'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
    'searchFields'      =>  'descname,email,subject,message,'
  ),
  'feInterface' => array (
    'fe_admin_fieldList' => 'hidden, email, descname, from_date, to_date, message, enabled, force_enabled, subject',
  )
);
?>