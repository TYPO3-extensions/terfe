# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_extension"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_extension (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	ext_key tinytext,
	forge_link tinytext,
	hudson_link tinytext,
	last_upload int(11) unsigned DEFAULT '0',
	last_maintained int(11) unsigned DEFAULT '0',
	categories int(11) unsigned DEFAULT '0',
	tags int(11) unsigned DEFAULT '0' NOT NULL,
	versions int(11) unsigned DEFAULT '0' NOT NULL,
	last_version int(11) unsigned DEFAULT '0' NOT NULL,
	frontend_user tinytext,
	downloads int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_category"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_category (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	extensions int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	description text,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_tag"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_tag (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	extensions int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_version"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_version (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	extension int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	description text,
	file_hash varchar(50) DEFAULT '' NOT NULL,
	author int(11) unsigned DEFAULT '0' NOT NULL,
	version_number int(11) DEFAULT '0' NOT NULL,
	version_string tinytext,
	upload_date int(11) DEFAULT '0' NOT NULL,
	upload_comment text,
	download_counter int(11) DEFAULT '0' NOT NULL,
	frontend_download_counter int(11) DEFAULT '0' NOT NULL,
	state tinytext,
	em_category tinytext,
	load_order tinytext,
	priority tinytext,
	shy tinyint(4) unsigned DEFAULT '0' NOT NULL,
	internal tinyint(4) unsigned DEFAULT '0' NOT NULL,
	do_not_load_in_fe tinyint(4) unsigned DEFAULT '0' NOT NULL,
	uploadfolder tinyint(4) unsigned DEFAULT '0' NOT NULL,
	clear_cache_on_load tinyint(4) unsigned DEFAULT '0' NOT NULL,
	module tinytext,
	create_dirs tinytext,
	modify_tables tinytext,
	lock_type tinytext,
	cgl_compliance tinytext,
	cgl_compliance_note text,
	review_state int(11) DEFAULT '0' NOT NULL,
	manual tinytext,
	has_manual tinyint(4) unsigned DEFAULT '0' NOT NULL,
	media int(11) unsigned DEFAULT '0' NOT NULL,
	experiences int(11) unsigned DEFAULT '0' NOT NULL,
	software_relations int(11) unsigned DEFAULT '0' NOT NULL,
	extension_provider tinytext,
	has_zip_file tinyint(4) unsigned DEFAULT '0' NOT NULL,
	has_images tinyint(4) unsigned DEFAULT '0' NOT NULL,
	t3x_file_size bigint(15) unsigned DEFAULT '0' NOT NULL,
	zip_file_size bigint(15) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_media"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_media (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	version int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	type int(11) DEFAULT '0' NOT NULL,
	language tinytext,
	source tinytext,
	description text,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_experience"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_experience (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	version int(11) unsigned DEFAULT '0' NOT NULL,
	date_time int(11) DEFAULT '0' NOT NULL,
	comment text,
	rating int(11) DEFAULT '0' NOT NULL,
	frontend_user int(11) DEFAULT '0' NOT NULL,


	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_relation"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_relation (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	version int(11) unsigned DEFAULT '0' NOT NULL,
	relation_type tinytext,
	relation_key tinytext,
	minimum_version int(11) unsigned DEFAULT '0' NOT NULL,
	maximum_version int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_author"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_author (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name tinytext,
	email tinytext,
	company tinytext,
	forge_link tinytext,
	username tinytext,
	versions int(11) unsigned DEFAULT '0' NOT NULL,
	frontend_user int(11) unsigned DEFAULT '0' NOT NULL,
	author_type int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


# ======================================================================
# Table configuration for table "tx_terfe2_domain_model_search"
# ======================================================================
CREATE TABLE tx_terfe2_domain_model_search (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	extension_key tinytext,
	title tinytext,
	description text,
	author_list text,
	upload_comment text,
	version_string tinytext,
	state tinytext,
	em_category tinytext,
	software_relation_list text,
	category_list text,
	tag_list text,
	version_uid int(11) unsigned DEFAULT '0' NOT NULL,
	extension_uid int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	FULLTEXT (extension_key,title,description,author_list,upload_comment,version_string,state,em_category,software_relation_list,category_list,tag_list)
);


# ======================================================================
# Table configuration for table "tx_terfe2_extension_category_mm"
# ======================================================================
CREATE TABLE tx_terfe2_extension_category_mm (
	uid_local int(10) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(10) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(10) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


# ======================================================================
# Table configuration for table "tx_terfe2_extension_tag_mm"
# ======================================================================
CREATE TABLE tx_terfe2_extension_tag_mm (
	uid_local int(10) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(10) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(10) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);