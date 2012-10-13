<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_postfix_forwards'] = array (
  'ctrl' => $TCA['tx_postfix_forwards']['ctrl'],
  'interface' => array (
    'showRecordFieldList' => 'hidden,forward_from,forward_to,memo_txt'
  ),
  'feInterface' => $TCA['tx_postfix_forwards']['feInterface'],
  'columns' => array (
    'hidden' => array (
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'forward_from' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_forwards.forward_from',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'max' => '240',
        'eval' => 'trim',
      )
    ),
    'forward_to' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_forwards.forward_to',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'max' => '240',
        'eval' => 'trim',
      )
    ),
    'memo_txt' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_forwards.memo_txt',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'trim',
      )
    ),
  ),
  'types' => array (
    '0' => array('showitem' => 'hidden;;1;;1-1-1, forward_from, forward_to, memo_txt')
  ),
  'palettes' => array (
    '1' => array('showitem' => '')
  )
);



$TCA['tx_postfix_domains'] = array (
  'ctrl' => $TCA['tx_postfix_domains']['ctrl'],
  'interface' => array (
    'showRecordFieldList' => 'hidden,domain'
  ),
  'feInterface' => $TCA['tx_postfix_domains']['feInterface'],
  'columns' => array (
    'hidden' => array (
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'domain' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_domains.domain',
      'config' => array (
        'type' => 'input',
        'size' => '30',
      )
    ),
  ),
  'types' => array (
    '0' => array('showitem' => 'hidden;;1;;1-1-1, domain')
  ),
  'palettes' => array (
    '1' => array('showitem' => '')
  )
);



$TCA['tx_postfix_canonical'] = array (
  'ctrl' => $TCA['tx_postfix_canonical']['ctrl'],
  'interface' => array (
    'showRecordFieldList' => 'hidden,mail_srce,mail_dest'
  ),
  'feInterface' => $TCA['tx_postfix_canonical']['feInterface'],
  'columns' => array (
    'hidden' => array (
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'mail_srce' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical.mail_srce',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'mail_dest' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical.mail_dest',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
  ),
  'types' => array (
    '0' => array('showitem' => 'hidden;;1;;1-1-1, mail_srce, mail_dest')
  ),
  'palettes' => array (
    '1' => array('showitem' => '')
  )
);



$TCA['tx_postfix_canonical_sender'] = array (
  'ctrl' => $TCA['tx_postfix_canonical_sender']['ctrl'],
  'interface' => array (
    'showRecordFieldList' => 'hidden,mail_srce,mail_dest'
  ),
  'feInterface' => $TCA['tx_postfix_canonical_sender']['feInterface'],
  'columns' => array (
    'hidden' => array (
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'mail_srce' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical_sender.mail_srce',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'mail_dest' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical_sender.mail_dest',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
  ),
  'types' => array (
    '0' => array('showitem' => 'hidden;;1;;1-1-1, mail_srce, mail_dest')
  ),
  'palettes' => array (
    '1' => array('showitem' => '')
  )
);



$TCA['tx_postfix_canonical_recipient'] = array (
  'ctrl' => $TCA['tx_postfix_canonical_recipient']['ctrl'],
  'interface' => array (
    'showRecordFieldList' => 'hidden,mail_srce,mail_dest'
  ),
  'feInterface' => $TCA['tx_postfix_canonical_recipient']['feInterface'],
  'columns' => array (
    'hidden' => array (
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'mail_srce' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical_recipient.mail_srce',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'mail_dest' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_canonical_recipient.mail_dest',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
  ),
  'types' => array (
    '0' => array('showitem' => 'hidden;;1;;1-1-1, mail_srce, mail_dest')
  ),
  'palettes' => array (
    '1' => array('showitem' => '')
  )
);



$TCA['tx_postfix_autoresponder'] = array (
  'ctrl' => $TCA['tx_postfix_autoresponder']['ctrl'],
  'interface' => array (
    'showRecordFieldList' => 'hidden, email, descname, from_date, to_date, subject, message, enabled, force_enabled'
  ),
  'feInterface' => $TCA['tx_postfix_autoresponder']['feInterface'],
  'columns' => array (
    'hidden' => array (
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'descname' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.descname',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'email' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.email',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'from_date' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.from_date',
      'config' => array (
        'type' => 'input',
        'size' => '10',
        'eval' => 'required',
      )
    ),
    'to_date' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.to_date',
      'config' => array (
        'type' => 'input',
        'size' => '10',
        'eval' => 'required',
      )
    ),
    'subject' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.subject',
      'config' => array (
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'message' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.message',
      'config' => array (
        'type' => 'text',
        'cols' => '30',
        'rows' => '5',
        'eval' => 'required',
      )
    ),
    'enabled' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.enabled',
      'config' => array (
        'type' => 'check',
        'default' => 1,
      )
    ),
    'force_enabled' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:postfix/locallang_db.xml:tx_postfix_autoresponder.force_enabled',
      'config' => array (
        'type' => 'check',
        'default' => 1,
      )
    ),
  ),
  'types' => array (
    '0' => array('showitem' => 'hidden;;1;;1-1-1, email, descname, from_date, to_date, subject, message, enabled, force_enabled')
  ),
  'palettes' => array (
    '1' => array('showitem' => '')
  )
);
?>