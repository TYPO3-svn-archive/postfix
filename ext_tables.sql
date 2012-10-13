#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
  tx_postfix_id int(11) DEFAULT '0' NOT NULL,
  tx_postfix_gid int(11) DEFAULT '0' NOT NULL,
  tx_postfix_password tinytext NOT NULL,
  tx_postfix_maildir tinytext NOT NULL,
  tx_postfix_homedir tinytext NOT NULL,
  tx_postfix_quota tinytext NOT NULL,
  tx_postfix_webmail tinyint(3) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_postfix_forwards'
#
CREATE TABLE tx_postfix_forwards (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  forward_from varchar(240) DEFAULT '' NOT NULL,
  forward_to varchar(240) DEFAULT '' NOT NULL,
  memo_txt varchar(255) DEFAULT '' NOT NULL,
  
  PRIMARY KEY (uid),
  KEY parent (pid)
);



#
# Table structure for table 'tx_postfix_domains'
#
CREATE TABLE tx_postfix_domains (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  domain tinytext NOT NULL,
  
  PRIMARY KEY (uid),
  KEY parent (pid)
);



#
# Table structure for table 'tx_postfix_canonical'
#
CREATE TABLE tx_postfix_canonical (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  mail_srce tinytext NOT NULL,
  mail_dest tinytext NOT NULL,
  
  PRIMARY KEY (uid),
  KEY parent (pid)
);



#
# Table structure for table 'tx_postfix_canonical_sender'
#
CREATE TABLE tx_postfix_canonical_sender (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  mail_srce tinytext NOT NULL,
  mail_dest tinytext NOT NULL,
  
  PRIMARY KEY (uid),
  KEY parent (pid)
);



#
# Table structure for table 'tx_postfix_canonical_recipient'
#
CREATE TABLE tx_postfix_canonical_recipient (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  mail_srce tinytext NOT NULL,
  mail_dest tinytext NOT NULL,
  
  PRIMARY KEY (uid),
  KEY parent (pid)
);



#
# Table structure for table 'tx_postfix_autoresponder'
#
CREATE TABLE tx_postfix_autoresponder (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  descname tinytext,
  email tinytext,
  subject tinytext,
  message text,
  from_date tinytext,
  to_date tinytext,
  enabled tinyint(3) NOT NULL default '0',
  force_enabled tinyint(3) NOT NULL default '0',
  PRIMARY KEY (uid),
  KEY parent (pid)
#  PRIMARY KEY (email),
  FULLTEXT KEY message (message)
);