-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- 
-- Table `tl_form`
-- 

CREATE TABLE `tl_form` (
	`inxServer` varchar(255) NOT NULL default '',
	`inxListName` varchar(255) NOT NULL default '',
	`inxUser` varchar(255) NOT NULL default '',
	`inxPass` varchar(255) NOT NULL default '',
	`inxMailField` varchar(255) NOT NULL default '',
	`inxAdditionalFields` text NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_newsletter`
-- 

CREATE TABLE `tl_newsletter_channel` (
	`inxServer` varchar(255) NOT NULL default '',
	`inxListName` varchar(255) NOT NULL default '',
	`inxUser` varchar(255) NOT NULL default '',
	`inxPass` varchar(255) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;