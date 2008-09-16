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
  extensiondownloadcounter int(11) DEFAULT '0' NOT NULL,
  versiondownloadcounter int(11) DEFAULT '0' NOT NULL,
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
  reviewers text NOT NULL,
  extensionkey varchar(30) DEFAULT '' NOT NULL,
  version varchar(11) DEFAULT '' NOT NULL,
  t3xfilemd5 varchar(32) DEFAULT '' NOT NULL,
  tstamp int(11) NOT NULL default '0',
  lastmodified int(11) NOT NULL default '0',
  PRIMARY KEY (uid),
);

#
# Table structure for table 'tx_terfe_reviewratings'
#
CREATE TABLE tx_terfe_reviewratings (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  extensionkey varchar(30) DEFAULT '' NOT NULL,
  version varchar(11) DEFAULT '' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  reviewer text NOT NULL,
  rating int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid)
  KEY extkey (extensionkey)
  KEY extversion (version)  
);


#
# Table structure for table 'tx_terfe_reviewnotes'
#
CREATE TABLE tx_terfe_reviewnotes (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  extensionkey varchar(30) DEFAULT '' NOT NULL,
  version varchar(11) DEFAULT '' NOT NULL,
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


# Table structure for ratings
CREATE TABLE tx_terfe_ratings (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  tstamp int(11) NOT NULL default '0',
  extensionkey varchar(255) NOT NULL default '',
  version varchar(255) NOT NULL default '',
  username varchar(255) NOT NULL default '',
  funcrating int(1) NOT NULL default '0',
  docrating int(1) NOT NULL default '0',
  coderating int(1) NOT NULL default '0',
  overall float NOT NULL default '0',
  notes text NOT NULL,
  PRIMARY KEY  (uid)
);

CREATE TABLE tx_terfe_ratingscache (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  extensionkey varchar(255) NOT NULL default '',
  version varchar(255) NOT NULL default '',
  rating float NOT NULL default '0',
  votes int(11) NOT NULL default '0',
  PRIMARY KEY  (uid)
);
