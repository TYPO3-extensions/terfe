
#
# Table structure for table 'tx_terfe_extensions'
#
CREATE TABLE tx_terfe_extensions ( 
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  extensionkey varchar(30) DEFAULT '' NOT NULL,
  version varchar(11) DEFAULT '' NOT NULL,
  title varchar(50) DEFAULT '' NOT NULL,
  description varchar(255) DEFAULT '' NOT NULL,
  state varchar(15) DEFAULT '' NOT NULL,
  reviewstate int(11) DEFAULT '0' NOT NULL,
  category varchar(30) DEFAULT '' NOT NULL,
  lastuploaddate int(11) DEFAULT '0' NOT NULL,
  uploadcomment varchar(255) DEFAULT '' NOT NULL,
  dependencies text NOT NULL,
  authorname tinytext NOT NULL,
  authoremail tinytext NOT NULL,
  authorcompany tinytext NOT NULL,
  ownerusername varchar(30) DEFAULT '' NOT NULL,
  t3xfilemd5 varchar(32) DEFAULT '' NOT NULL,

  PRIMARY KEY (uid),
  KEY extkey (extensionkey),
  KEY extversion (version),
  KEY exttitle (title)
);

#
# Table structure for table 'tx_terfe_extensiondetails'
#
CREATE TABLE tx_terfe_extensiondetails ( 
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  extensionkey varchar(30) DEFAULT '' NOT NULL,
  version varchar(11) DEFAULT '' NOT NULL,
  files text NOT NULL,
  t3xfilemd5 varchar(32) DEFAULT '' NOT NULL,

  PRIMARY KEY (uid),
  KEY extkey (extensionkey),
  KEY extversion (version),
);

#
# Table structure for table 'tx_terfe_extensiondependencies'
#
CREATE TABLE tx_terfe_extensiondependencies ( 
  extensionkey varchar(30) DEFAULT '' NOT NULL,
  dependingextensions text NOT NULL,

  PRIMARY KEY (extensionkey),
);


#
# Table structure for table 'tx_terfe_reviews'
#
CREATE TABLE tx_terfe_reviews ( 
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  reviewer text NOT NULL,
  extensionkey varchar(30) DEFAULT '' NOT NULL,
  version varchar(11) DEFAULT '' NOT NULL,
  t3xfilemd5 varchar(32) DEFAULT '' NOT NULL,
  reviewstate int(11) DEFAULT '0' NOT NULL,
  objections varchar(255) NOT NULL default '',
  notes text NOT NULL,
  tstamp int(11) NOT NULL default '0',
  PRIMARY KEY (uid),
);

#
# Table structure for table 'tx_terfe_reviewnotes'
#
CREATE TABLE tx_terfe_reviewnotes (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  reviewuid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  note text NOT NULL,
  reviewer varchar(30) DEFAULT '' NOT NULL,
  PRIMARY KEY (uid)
);

#
# Table structure for table 'tx_terfe_reviewemails'
#
CREATE TABLE tx_terfe_reviewemails (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  reviewuid int(11) unsigned DEFAULT '0' NOT NULL,
  reviewer varchar(30) DEFAULT '' NOT NULL,
  mailcontent mediumblob NOT NULL,
  from_email varchar(80) DEFAULT '' NOT NULL,
  to_email varchar(80) DEFAULT '' NOT NULL,
  reply_to_email varchar(80) DEFAULT '' NOT NULL,
  sender_email varchar(80) DEFAULT '' NOT NULL,
  message_id varchar(80) DEFAULT '' NOT NULL,
  subject tinytext NOT NULL,
  PRIMARY KEY (uid)
);
