
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
