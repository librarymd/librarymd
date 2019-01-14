SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

CREATE TABLE IF NOT EXISTS `adminslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime DEFAULT NULL,
  `txt` text,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `an_nou_fulgi` (
  `user_id` int(11) NOT NULL,
  `fulgi_url` varchar(255) NOT NULL,
  `fulgi_no` tinyint(4) NOT NULL,
  `fulgi_enable` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `an_nou_jucarii` (
  `img_id` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `leftpx` smallint(6) DEFAULT NULL,
  `toppx` smallint(6) DEFAULT NULL,
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `annonces` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `body_ru` text COLLATE utf8_unicode_ci NOT NULL,
  `body_ro` text COLLATE utf8_unicode_ci NOT NULL,
  `until` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `avps` (
  `arg` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`arg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title_ro` varchar(100) NOT NULL,
  `title_ru` varchar(100) NOT NULL,
  `desc_ro` text NOT NULL,
  `desc_ru` text NOT NULL,
  `img` varchar(150) NOT NULL,
  `url` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `banned_email` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `domain` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `first` int(11) DEFAULT NULL,
  `last` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `first_last` (`first`,`last`),
  KEY `last` (`last`),
  KEY `first` (`first`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `bans_browser` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `browser_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `until` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `browser_hash` (`browser_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `bans_ip_hash` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `blockid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `blockid` (`blockid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Movies','cat_movie.gif'),(2,'Music','cat_music.gif'),(3,'Appz','cat_apps_misc.gif'),(4,'Games','cat_games.gif'),(5,'TV','cat_tv.gif'),(7,'Other','cat_misc.gif'),(8,'Books','cat_e_book.gif'),(9,'Music Video','cat_music_video.gif'),(10,'Anime','cat_anime.gif'),(11,'Animation','cat_animation.gif'),(12,'DVD','cat_dvd.gif'),(13,'Movies Documentary','cat_movie_doc.gif'),(14,'Books Audio','cat_book_audio.gif'),(15,'Video Lessons','cat_video_lessons.gif'),(16,'Photos','cat_photos.gif'),(17,'Sport','cat_sport.gif'),(18,'HDTV','cat_hdtv.gif');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;


CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `censored` enum('y','n') DEFAULT NULL,
  `audio_rate` tinyint(1) DEFAULT NULL,
  `video_rate` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `forum_admin_answer` (
  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `topicid` int(10) NOT NULL,
  `postid` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `forum_moderators` (
  `forum_category_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `statut` enum('moderator_primar','moderator_secundar') NOT NULL,
  KEY `forum_category_id` (`forum_category_id`,`user_id`,`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `forums` (
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_ro` varchar(60) NOT NULL DEFAULT '',
  `name_ru` varchar(60) NOT NULL DEFAULT '',
  `description_ro` varchar(200) DEFAULT NULL,
  `description_ru` varchar(200) DEFAULT NULL,
  `minclassread` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `minclasswrite` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `postcount` int(10) unsigned NOT NULL DEFAULT '0',
  `topiccount` int(10) unsigned NOT NULL DEFAULT '0',
  `minclasscreate` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `lastPost` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`),
  KEY `lastPost` (`lastPost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `forums` VALUES
(0,1,'Categoria administratiei','Categoria administratiei','Categorie privatta unde discuta >=moderatorii','Categorie privatta unde discuta >=moderatorii',8,8,4,1,4,0),
(1,2,'Library.MD','Library.MD','Evenimente, sugestii, buguri','Evenimente, sugestii, buguri',0,0,0,0,0,0);
(2,2,'Mesaje catre staff','Mesaje catre staff','Mesaje catre staff','Mesaje catre staff',0,0,0,0,0,0);


CREATE TABLE IF NOT EXISTS `forums_tags` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `forum` smallint(6) unsigned DEFAULT NULL,
  `name_ro` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_ru` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `total` mediumint(9) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `forum` (`forum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `forumslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` text NOT NULL,
  `forumid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `forumid` (`forumid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `friend_requests` (
  `sender_user_id` int(10) unsigned NOT NULL,
  `receiver_user_id` int(10) unsigned NOT NULL,
  `status` enum('awaits','refused','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'awaits',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`receiver_user_id`,`sender_user_id`),
  KEY `sender_user_id` (`sender_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `friends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `friendid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `friendid` (`friendid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `global_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime DEFAULT NULL,
  `msg` text,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `imdb_tt` (
  `id` mediumint(9) unsigned NOT NULL,
  `votes` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `rating` tinyint(3) NOT NULL DEFAULT '0',
  `year` smallint(6) NOT NULL DEFAULT '0',
  `date_published` date NOT NULL DEFAULT '0000-00-00',
  `bayesian_rating` tinyint(3) NOT NULL DEFAULT '0',
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torrents` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rating_votes` (`rating`,`votes`),
  KEY `year_bayesian_rating` (`year`,`bayesian_rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `imdb_tt_to_process` (
  `id` mediumint(9) unsigned NOT NULL,
  `verified` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `imdb_tt_top250` (
  `imdb` mediumint(9) unsigned NOT NULL,
  `rank` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `invites` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(80) NOT NULL DEFAULT '',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `inviter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `msg` text,
  `unread` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'yes',
  `location` enum('in','out','both') CHARACTER SET latin1 NOT NULL DEFAULT 'in',
  PRIMARY KEY (`id`),
  KEY `receiver` (`receiver`),
  KEY `unread` (`unread`),
  KEY `location` (`location`),
  KEY `sender` (`sender`),
  KEY `added` (`added`),
  KEY `sender_loc_id` (`sender`,`location`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `moderslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime DEFAULT NULL,
  `txt` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `body_ru` text CHARACTER SET utf8,
  `body_ro` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `msg` text,
  `unread` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `user_id_id` (`user_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `peers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `peer_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ip` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `to_go` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `started` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_action` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `connectable` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `agent` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `finishedat` int(10) unsigned NOT NULL DEFAULT '0',
  `downloadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  `uploadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `torrent_peer_id` (`torrent`,`peer_id`),
  KEY `torrent` (`torrent`),
  KEY `torrent_seeder` (`torrent`,`seeder`),
  KEY `last_action` (`last_action`),
  KEY `connectable` (`connectable`),
  KEY `userid` (`userid`),
  KEY `torrent_passkey` (`torrent`),
  KEY `torrent_connectable` (`torrent`,`connectable`),
  KEY `userid_seeder` (`userid`,`seeder`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `pollanswers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `selection` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pollid` (`pollid`),
  KEY `selection` (`selection`),
  KEY `userid` (`userid`),
  KEY `pollid_userid` (`pollid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `polls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `question_ro` varchar(255) NOT NULL DEFAULT '',
  `question_ru` varchar(255) NOT NULL DEFAULT '',
  `options` text NOT NULL,
  `sort` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topicid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `body` text,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `image` varchar(7) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ori_text` text,
  `censored` enum('y','n') CHARACTER SET latin1 DEFAULT NULL,
  `page` mediumint(10) unsigned NOT NULL DEFAULT '1',
  `forumid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `likes` smallint(10) NOT NULL DEFAULT '0',
  `unlikes` smallint(10) NOT NULL DEFAULT '0',
  `mod_approved` enum('yes','no','not_needed','awaiting') NOT NULL DEFAULT 'not_needed',
  PRIMARY KEY (`id`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`),
  KEY `page` (`page`),
  KEY `forumid` (`forumid`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `posts_for_review` (
  `post_id` int(10) unsigned NOT NULL,
  `forum_id` int(10) unsigned NOT NULL,
  `approved` enum('todo','yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'todo',
  `reviewer_user_id` int(10) unsigned DEFAULT NULL,
  `reason` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`post_id`),
  KEY `approved` (`approved`,`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `posts_likes` (
  `postid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `type` enum('plus','minus') NOT NULL DEFAULT 'plus',
  PRIMARY KEY (`postid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `raportedmsg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `type` enum('forum','comment') COLLATE utf8_unicode_ci DEFAULT NULL,
  `postId` int(11) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `status` enum('waiting','reviewed') COLLATE utf8_unicode_ci DEFAULT NULL,
  `forumid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `postId` (`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `readposts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `topicid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpostread` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`id`),
  KEY `topicid` (`topicid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `regions` (
  `id` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (10,'Chişinău'),(1,'Botanica'),(2,'Buiucani'),(3,'Centru'),(4,'Ciocana'),(6,'Poşta Veche'),(5,'Rîşcani'),(7,'Telecentru'),(11,'Municipiul Chişinău'),(12,'Codru'),(13,'Cricova'),(14,'Durleşti'),(15,'Sîngera'),(16,'Vadul lui Vodă'),(17,'Vatra'),(20,'Băcioi'),(21,'Bubuieci'),(22,'Budeşti'),(23,'Ciorescu'),(24,'Coloniţa'),(25,'Condriţa'),(26,'Dumbrava'),(27,'Cruzeşti'),(28,'Ghidighici'),(29,'Grătieşti'),(30,'Stăuceni'),(31,'Tohatin'),(32,'Truşeni'),(40,'Municipiul Bălţi'),(50,'Municipiul Tighina'),(60,'Municipiul Comrat'),(70,'Municipiul Tiraspol'),(80,'Raionul Anenii Noi'),(90,'R. Basarabeasca'),(100,'R. Briceni'),(110,'R. Cahul'),(120,'R. Cantemir'),(130,'R. Călăraşi'),(140,'R. Căuşeni'),(150,'R. Cimişlia'),(160,'R. Criuleni'),(170,'R. Donduşeni'),(180,'R. Drochia'),(190,'R. Dubăsari'),(200,'R. Edineţ'),(210,'R. Faleşti'),(220,'R. Floreşti'),(230,'R. Glodeni'),(240,'R. Hînceşti'),(250,'R. Ialoveni'),(260,'R. Leova'),(270,'R. Nisporeni'),(280,'R. Ocniţa'),(290,'R. Orhei'),(300,'R. Rezina'),(310,'R. Rîşcani'),(320,'R. Sîngerei'),(330,'R. Soroca'),(340,'R. Străşeni'),(350,'R. Şoldăneşti'),(360,'R. Ştefan Vodă'),(370,'R. Taraclia'),(380,'R. Teleneşti'),(390,'R. Ungheni'),(400,'R. Vulcăneşti');
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;


CREATE TABLE IF NOT EXISTS `releasers_groups` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8_unicode_ci,
  `description` tinytext COLLATE utf8_unicode_ci,
  `logo_url` tinytext COLLATE utf8_unicode_ci,
  `owner` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `searchindex` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `sitelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime DEFAULT NULL,
  `txt` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `sysopslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime DEFAULT NULL,
  `txt` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(11) DEFAULT NULL,
  `member` int(11) DEFAULT NULL,
  `confirmed` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member` (`member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `description` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `owner` int(11) DEFAULT NULL,
  `logo` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `minFilesSizeMb` smallint(6) unsigned DEFAULT NULL,
  `initials` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `owner` (`owner`),
  KEY `name` (`name`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(130) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  `forumid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `posts` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `subcat` smallint(6) unsigned NOT NULL DEFAULT '0',
  `mod_approval` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `lastpost` (`lastpost`),
  KEY `created` (`created`),
  KEY `forumid` (`forumid`),
  KEY `forum_lastpost` (`forumid`,`lastpost`),
  KEY `forum_subcat_lastpost` (`forumid`,`subcat`,`lastpost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `torrent_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `solvedby` int(10) unsigned NOT NULL DEFAULT '0',
  `level` tinyint(5) NOT NULL DEFAULT '0',
  `reason` varchar(256) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `solved` enum('yes','no') DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` varchar(40) DEFAULT NULL,
  `info_hash_sha1` varchar(40) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `save_as` varchar(255) DEFAULT NULL,
  `category` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` enum('single','multi') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'single',
  `numfiles` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `comments` smallint(6) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `times_completed` smallint(6) unsigned NOT NULL DEFAULT '0',
  `leechers` smallint(6) NOT NULL DEFAULT '0',
  `seeders` smallint(6) NOT NULL DEFAULT '0',
  `last_action` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `visible` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  `numratings` int(10) unsigned NOT NULL DEFAULT '0',
  `ratingsum` int(10) unsigned NOT NULL DEFAULT '0',
  `image` varchar(7) DEFAULT NULL,
  `torrent_opt` int(10) unsigned NOT NULL DEFAULT '0',
  `team` smallint(6) unsigned DEFAULT '0',
  `lastcomment` int(10) unsigned NOT NULL DEFAULT '0',
  `moder_status` enum('neverificat','se_verifica','inchis','verificat','necomplet','parital_necomplet','dublare','absorbit','copyright','dubios','temporar') DEFAULT NULL,
  `thanks` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dht_peers_updated` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `dht_peers_update_scheduled` enum('yes','no') DEFAULT 'no',
  `dht_peers_job_started` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `dht_peers` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`),
  KEY `category` (`category`),
  KEY `added` (`added`),
  KEY `team` (`team`),
  KEY `info_hash` (`info_hash`),
  KEY `dht_peers_update_scheduled` (`dht_peers_update_scheduled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `torrents_added` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addedUnix` int(11) NOT NULL DEFAULT '0',
  `visible` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `addedUnix` (`addedUnix`),
  KEY `addedVisible` (`addedUnix`,`visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `torrents_catetags` (
  `id` smallint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name_ro` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_ru` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_en` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `father` mediumint(8) unsigned NOT NULL,
  `desc_ro` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc_ru` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `orderi` smallint(8) DEFAULT '0',
  `visible` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'yes',
  `checkable` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'yes',
  `total` mediumint(3) DEFAULT '0',
  `dependendOnCategTagCSV` text,
  `native_form_id` smallint(8) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `torrents_catetags`
--

LOCK TABLES `torrents_catetags` WRITE;
/*!40000 ALTER TABLE `torrents_catetags` DISABLE KEYS */;
INSERT INTO `torrents_catetags` VALUES (26,'Limba','Язык','',0,'','',1,'yes','no',0,NULL,NULL),(27,'Română','Румынский','Romanian,Rom',26,'','',-110,'yes','yes',1,NULL,NULL),(28,'Rusă','Русский','Russian,Rus,Ru',26,'','',-100,'yes','yes',2,NULL,NULL),(29,'Cu subtitrări','C субтитрами','',0,'','',0,'yes','yes',0,'89,94,97,98,100,313,314',NULL),(35,'Română','Румынский',NULL,29,'','',0,'yes','yes',2,NULL,NULL),(36,'Rusă','Русский',NULL,29,'','',0,'yes','yes',1,NULL,NULL),(45,'Seriale','Cериалы','',0,'','',0,'yes','yes',1,'94,98',NULL),(46,'Heroes','Heroes',NULL,196,'','',0,'yes','yes',0,NULL,NULL),(50,'Ani','Года','',0,'','',0,'yes','no',0,'',NULL),(51,'2009','2009',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(52,'2005-2008','2005-2008',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(53,'2000-2004','2000-2004',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(54,'1995-1999','1995-1999',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(55,'1990-1994','1990-1994',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(56,'1980-1989','1980-1989',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(57,'1970-1979','1970-1979',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(58,'1960-1969','1960-1969',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(59,'1950-1959','1950-1959',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(60,'1930-1949','1930-1949',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(61,'1890-1929','1890-1929',NULL,50,'','',0,'yes','yes',0,NULL,NULL),(62,'Genuri de filme','Жанры фильмов','',141,'','',0,'yes','no',0,'89',NULL),(63,'Action','Боевик','',62,'','',0,'yes','yes',0,'',1),(64,'Adventure','Приключения','',62,'','',0,'yes','yes',0,'',2),(65,'Biography','Биография','',62,'','',0,'yes','yes',0,'',3),(66,'Comedy','Комедия','',62,'','',0,'yes','yes',0,'',4),(67,'Crime','Криминал','',62,'','',0,'yes','yes',0,'',5),(68,'Drama','Драма','',62,'','',0,'yes','yes',0,'',6),(69,'Family','Семейный','',62,'','',0,'yes','yes',0,'',7),(70,'Fantasy','Фантастика','',62,'','',0,'yes','yes',0,'',8),(71,'History','Исторический','',62,'','',0,'yes','yes',0,'',9),(72,'Horror','Ужасы','',62,'','',0,'yes','yes',0,'',10),(73,'Music','Музыкальный','',62,'','',0,'yes','yes',0,'',11),(74,'Romance','Романтическое','',62,'','',0,'yes','yes',0,'',12),(75,'Sci-Fi','Научная фантастика','',62,'','',0,'yes','yes',0,'',13),(76,'Sport','Спортивный','',62,'','',0,'yes','yes',0,'',14),(77,'Thriller','Триллер','',62,'','',0,'yes','yes',0,'',15),(78,'War','Военный','',62,'','',0,'yes','yes',0,'',16),(79,'Western','Вестерн','',62,'','',0,'yes','yes',0,'',17),(80,'Mystical','Мистика','',62,'','',0,'yes','yes',0,'',18),(81,'Detectiv','Детектив','',62,'','',0,'yes','yes',0,'',19),(82,'Animation','Мультфильмы','',62,'','',0,'yes','yes',0,'',20),(83,'Documentary','Документальный','',62,'','',0,'yes','yes',0,'',21),(85,'Tip de conținut','Тип содержимого','',0,'','',-1000,'yes','no',0,'',NULL),(89,'Filme','Фильмы','',85,'','',0,'yes','yes',0,'',NULL),(90,'Muzică','Музыка','',85,'','',0,'yes','yes',0,'',NULL),(92,'Software','Софт','',85,'','',0,'yes','yes',0,'',NULL),(93,'Jocuri','Игры','',85,'','',0,'yes','yes',0,'',NULL),(94,'Emisiuni TV','Телепередачи','',85,'','',0,'yes','yes',0,'',NULL),(95,'Cărţi','Книги','',85,'','',0,'yes','yes',0,'',NULL),(96,'Muzică video','Видеоклипы','',85,'','',0,'yes','yes',0,'',NULL),(97,'Anime','Аниме','',85,'','',0,'yes','yes',0,'',NULL),(98,'Filme animate','Мультфильмы','',85,'','',0,'yes','yes',0,'',NULL),(99,'Cărți audio','Аудиокниги','',85,'','',0,'yes','yes',0,'',NULL),(100,'Lecţii video','Видеоуроки','',85,'','',0,'yes','yes',0,'',NULL),(101,'Fotografii','Фотографии','',85,'','',0,'yes','yes',0,'',NULL),(102,'Calitate video','Качество видео','',0,'','',0,'yes','no',0,'89,94,96,97,98,100,313,314',NULL),(103,'DVDRip','DVDRip',NULL,102,'','',-100,'yes','yes',0,NULL,NULL),(104,'DVDscr','DVDscr',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(105,'CAM','CAM',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(106,'TELESYNC (TS)','TELESYNC (TS)',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(107,'TELECINE (TC)','TELECINE (TC)',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(108,'SCREENER (SCR)','SCREENER (SCR)',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(109,'SATRip','SATRip',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(110,'TVRip','TVRip',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(111,'HDTV','HDTV',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(114,'Workprint (WP)','Workprint (WP)',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(115,'DVD','DVD',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(116,'DVD-5','DVD-5','DVD5, DVD 5',115,'','',-10,'yes','yes',0,'',NULL),(117,'DVD-9','DVD-9','DVD9, DVD 9',115,'','',-9,'yes','yes',0,'',NULL),(118,'DVD-10','DVD-10','DVD10, DVD 10',115,'','',-8,'yes','yes',0,'',NULL),(119,'DVD-18','DVD-18','DVD18, DVD 18',115,'','',-7,'yes','yes',0,'',NULL),(120,'BDRip 720p','BDRip 720p',NULL,111,'','',-100,'yes','yes',0,NULL,NULL),(121,'BDRip 1080p','BDRip 1080p',NULL,111,'','',-99,'yes','yes',0,NULL,NULL),(122,'BDRemux','BDRemux',NULL,111,'','',0,'yes','yes',0,NULL,NULL),(123,'Blu-Ray','Blu-Ray',NULL,111,'','',0,'yes','yes',0,NULL,NULL),(124,'HDDVDRip 720p','HDDVDRip 720p',NULL,111,'','',0,'yes','yes',0,NULL,NULL),(125,'HDDVDRip 1080p','HDDVDRip 1080p',NULL,111,'','',0,'yes','yes',0,NULL,NULL),(126,'HDDVDRemux','HDDVDRemux',NULL,111,'','',0,'yes','yes',0,NULL,NULL),(127,'HDDVD','HDDVD',NULL,111,'','',0,'yes','yes',0,NULL,NULL),(128,'HDTVRip','HDTVRip',NULL,102,'','',0,'yes','yes',0,NULL,NULL),(129,'TV','Телепередачи','',45,'','',-100,'yes','yes',0,'',NULL),(131,'Comedy club','Comedy club',NULL,129,'','',0,'yes','yes',1,NULL,NULL),(132,'Genuri de documentare','Жанры документального кино','',141,'','',0,'yes','yes',0,'313',NULL),(133,'Cosmos','Космос','',132,'','',0,'yes','yes',0,'',NULL),(134,'Filme documentare','Документальные','',45,'','',0,'yes','yes',0,'',NULL),(135,'Discovery','Discovery',NULL,134,'','',0,'yes','yes',0,NULL,NULL),(136,'BBC','BBC',NULL,134,'','',0,'yes','yes',0,NULL,NULL),(137,'National Geographic','National Geographic',NULL,134,'','',0,'yes','yes',0,NULL,NULL),(138,'Genuri de muzică','Жанры Музыки','',141,'','',0,'yes','no',0,'90,96',NULL),(139,'Genuri de cărți','Жанры книг','',141,'','',0,'yes','no',0,'95,99',0),(140,'Genuri de jocuri','Жанры Игр','',141,'','',0,'yes','no',0,'93',NULL),(141,'Genuri','Жанры','',0,'','',-990,'yes','no',0,'',NULL),(142,'Platforme','Платформы','',0,'','',0,'yes','no',0,'92,93',NULL),(143,'Console de joc','Игровые приставки','',142,'','',0,'yes','yes',0,'',NULL),(144,'Sisteme de operare','Операционные системы','',142,'','',0,'yes','no',0,'',NULL),(145,'Windows','Windows',NULL,144,'','',0,'yes','yes',0,NULL,NULL),(146,'Linux/Unix','Linux/Unix',NULL,144,'','',0,'yes','yes',0,NULL,NULL),(147,'MacOS','MacOS',NULL,144,'','',0,'yes','yes',0,NULL,NULL),(148,'PlayStation Portable (PSP)','PlayStation Portable (PSP)','PSP, PlayStation',143,'','',0,'yes','yes',0,'',NULL),(149,'Nintendo DS','Nintendo DS','Nintendo',143,'','',0,'yes','yes',0,'',NULL),(150,'Telefoane mobile','Мобильные телефоны','',142,'','',0,'yes','yes',0,'',NULL),(152,'Symbian','Symbian',NULL,150,'','',0,'yes','yes',0,NULL,NULL),(153,'Calitate muzică','Качество аудио','',0,'','',0,'yes','no',0,'90',NULL),(154,'Lossy','Lossy',NULL,153,'cu pierdere de calitate','с потерями)',0,'yes','no',0,NULL,NULL),(155,'Windows Mobile','Windows Mobile',NULL,150,'','',0,'yes','yes',0,NULL,NULL),(156,'Lossless','Lossless',NULL,153,'fără pierdere de calitate','fără pierdere de calitate',0,'yes','yes',0,NULL,NULL),(157,'MP3','MP3',NULL,154,'','',0,'yes','yes',0,NULL,NULL),(158,'FLAC','FLAC',NULL,156,'','',0,'yes','yes',1,NULL,NULL),(159,'Bitrate-uri','Bitrate-uri','',154,'','',0,'no','no',0,'',NULL),(160,'128','128',NULL,159,'','',0,'yes','yes',0,NULL,NULL),(161,'160','160',NULL,159,'','',0,'yes','yes',0,NULL,NULL),(162,'320','320',NULL,159,'','',0,'yes','yes',0,NULL,NULL),(164,'Originea geografică','Географическая принадлежность','',0,'','',99,'yes','no',0,'89,94,313',NULL),(165,'Europa','Европа',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(166,'SUA','США',NULL,177,'Hollywood','Hollywood',0,'yes','yes',0,NULL,NULL),(177,'America de Nord','Северная Америка','',164,'','',0,'yes','yes',0,NULL,NULL),(178,'America Latină','Латинская Америка',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(179,'Brazilia','Бразилия',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(180,'Engleză','Английский','English,Eng',26,'','',-80,'yes','yes',1,NULL,NULL),(181,'Germană','Немецкий','German,Ger',26,'','',0,'yes','yes',0,NULL,NULL),(182,'Franceză','Французский','French,Fra',26,'','',0,'yes','yes',0,NULL,NULL),(183,'Spaniolă','Испанский','Spanish',26,'','',0,'yes','yes',0,NULL,NULL),(184,'Japoneză','Японский','Japanese,Jap,Jp',26,'','',0,'yes','yes',0,NULL,NULL),(185,'Italiană','Итальянский','Italian',26,'','',-70,'yes','yes',0,NULL,NULL),(186,'Portugheză','Португальский','Portuguese',26,'','',0,'yes','yes',0,NULL,NULL),(187,'Fără cuvinte','Немое кино','',26,'','',99,'yes','yes',0,'',NULL),(188,'Altă limbă','Другой язык','',26,'','',100,'yes','yes',0,'',NULL),(189,'2010','2010','',50,'','',0,'yes','yes',0,NULL,NULL),(190,'2011 (cele mai noi)','2011 (новинки)','',50,'','',0,'yes','yes',0,'',NULL),(192,'Turcia','Турция',NULL,212,'','',0,'yes','yes',0,NULL,NULL),(194,'Engleză','Английский','',29,'','',0,'yes','yes',0,'',NULL),(196,'Filme','Художественные','',45,'','',-90,'yes','yes',0,'',NULL),(197,'2012','2012','',50,'','',0,'yes','yes',0,'',NULL),(198,'Canada','Канада',NULL,177,'','',2,'yes','yes',0,NULL,NULL),(199,'Argentina','Аргентина',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(200,'Chile','Чили',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(201,'Columbia','Колумбия',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(202,'Cuba','Куба',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(203,'Mexica','Мексика',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(204,'Paraguai','Парагвай',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(205,'Peru','Перу',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(206,'Porto Rico','Пуэрто-Рико',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(207,'Uruguai','Уругвай',NULL,178,'','',0,'yes','yes',0,NULL,NULL),(208,'Africa','Африка',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(209,'Asia de Est','Восточная Азия',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(210,'Asia de Sud','Южная Азия',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(211,'Asia de Sud-Est','Юго-Восточная Азия',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(212,'Asia de Vest','Западная Азия',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(213,'Oceania','Океания',NULL,164,'','',0,'yes','yes',0,NULL,NULL),(214,'Burchina Faso','Буркина-Фасо','Burkina Faso',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(215,'Egipt','Египет','Egypt',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(216,'Kenia','Кения','Kenya',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(217,'Maroc','Марокко','Morocco',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(218,'Niger','Нигер','Niger',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(219,'Nigeria','Нигерия','Nigeria',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(220,'Senegal','Сенегал','Senegal',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(221,'Somalia','Сомали','Somalia',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(222,'Africa de Sud','Южная Африка','South Africa',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(223,'Tunisia','Тунис','Tunisia',208,NULL,NULL,0,'yes','yes',0,NULL,NULL),(224,'China','Китай','China',209,NULL,NULL,0,'yes','yes',0,NULL,NULL),(225,'Hong Kong','Гонконг','Hong Kong',209,NULL,NULL,0,'yes','yes',0,NULL,NULL),(226,'Japonia','Япония',NULL,209,'','',0,'yes','yes',0,NULL,NULL),(227,'Coreea','Корея','Korea',209,NULL,NULL,0,'yes','yes',0,NULL,NULL),(228,'Mongolia','Монголия','Mongolia',209,NULL,NULL,0,'yes','yes',0,NULL,NULL),(229,'Taiwan','Тайвань','Taiwan',209,NULL,NULL,0,'yes','yes',0,NULL,NULL),(230,'Afganistan','Афганистан','Afghanistan',210,NULL,NULL,0,'yes','yes',0,NULL,NULL),(231,'Bangladesh','Бангладеш','Bangladesh',210,NULL,NULL,0,'yes','yes',0,NULL,NULL),(232,'India','Индии','India',210,NULL,NULL,0,'yes','yes',0,NULL,NULL),(233,'Nepal','Непал','Nepal',210,NULL,NULL,0,'yes','yes',0,NULL,NULL),(234,'Pakistan','Пакистан','Pakistan',210,NULL,NULL,0,'yes','yes',0,NULL,NULL),(235,'Sri Lanka','Шри-Ланка','Sri Lanka',210,NULL,NULL,0,'yes','yes',0,NULL,NULL),(236,'Birmania','Бирма','Burma',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(237,'Cambodgia','Камбоджа','Cambodia',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(238,'Indonezia','Индонезия','Indonesia',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(239,'Malaezia','Малайзия','Malaysia',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(240,'Filipine','Филиппины','Philippines',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(241,'Singapore','Сингапур','Singapore',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(242,'Tailanda','Таиланд','Thailand',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(243,'Vietnam','Вьетнам','Vietnam',211,NULL,NULL,0,'yes','yes',0,NULL,NULL),(244,'Armenia','Армения','Armenia',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(245,'Azerbaidjan','Азербайджан','Azerbaijan',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(246,'Cipru','Кипр','Cyprus',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(247,'Georgia','Грузия','Georgia',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(248,'Iran','Иран','Iran',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(249,'Irak','Ирак','Iraq',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(250,'Israel','Израиль','Israel',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(251,'Iordania','Иордания','Jordan',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(252,'Liban','Ливан','Lebanon',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(253,'Palestina','Палестина','Palestine',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(254,'Arabia Saudită','Саудовская Аравия','Saudi Arabia',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(255,'Siria','Сирия','Syria',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(256,'Tadjikistan','Таджикистан','Tajikistan',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(257,'Turcia','Турция','Turkey',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(258,'Emiratele Arabe Unite','Объединенные Арабские Эмираты','United Arab Emirates',212,NULL,NULL,0,'yes','yes',0,NULL,NULL),(259,'Albania','Албания','Albania',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(260,'Austria','Австрия','Austria',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(261,'Belgia','Бельгия','Belgium',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(262,'Bosnia şi Herţegovina','Босния и Герцеговина','Bosnia and Herzegovina',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(263,'Bulgaria','Болгария','Bulgaria',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(264,'Croaţia','Хорватия','Croatia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(265,'Republica Cehă','Чешская Республика','Czech Republic',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(266,'Danemarca','Дания','Denmark',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(267,'Estonia','Эстония','Estonia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(268,'Insulele Feroe','Фарерские острова','Faroe Islands',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(269,'Finlanda','Финляндия','Finland',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(270,'Franţa','Франция','France',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(271,'Germania','Германия','Germany',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(272,'Grecia','Греция','Greece',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(273,'Ungaria','Венгрия','Hungary',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(274,'Islanda','Исландия','Iceland',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(275,'Irlanda','Ирландия','Ireland',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(276,'Italia','Италия','Italy',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(277,'Letonia','Латвия','Latvia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(278,'Lituania','Литва','Lithuania',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(279,'Luxemburg','Люксембург','Luxembourg',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(280,'Macedonia','Македония','Macedonia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(281,'Moldova','Молдова','Moldova',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(282,'Muntenegru','Черногория','Montenegro',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(283,'Olanda','Нидерланды','Netherlands',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(284,'Norvegia','Норвегия','Norway',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(285,'Polonia','Польша','Poland',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(286,'Portugalia','Португалия','Portugal',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(287,'România','Румыния','Romania',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(288,'Rusia','Россия','Russia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(289,'Imperiul Rus','Российская империя','Russian Empire',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(290,'Uniunea Sovietică','Советский Союз','Soviet Union',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(291,'Serbia','Сербия','Serbia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(292,'Slovacia','Словакия','Slovakia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(293,'Spania','Испания','Spain',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(294,'Suedia','Швеция','Sweden',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(295,'Elveţia','Швейцария','Switzerland',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(296,'Ucraina','Украина','Ukraine',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(297,'Marea Britanie','Соединенное Королевство','United Kingdom',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(298,'Iugoslavia','Югославия','Yugoslavia',165,NULL,NULL,0,'yes','yes',0,NULL,NULL),(299,'Australia','Австралия','Australia',213,NULL,NULL,0,'yes','yes',0,NULL,NULL),(300,'Fiji','Фиджи','Fiji',213,NULL,NULL,0,'yes','yes',0,NULL,NULL),(301,'Noua Zeelandă','Новой Зеландии','New Zealand',213,NULL,NULL,0,'yes','yes',0,NULL,NULL),(302,'Tipul traducerii','Тип перевода','',0,'','',0,'yes','no',0,'89,94,97,98,100,313,314',NULL),(303,'Original (fără traducere)','Оригинал (без перевода)',NULL,302,'','',-100,'yes','yes',0,NULL,NULL),(304,'Amator (o voce)','Любительский (одноголосный)','',697,'','',-99,'yes','yes',0,'',NULL),(305,'Amator (două voci)','Любительский (двухголосый)','',697,'','',-98,'yes','yes',0,'',NULL),(306,'Amator (mai multe voci)','Любительский (многоголосый)','',697,'','',-97,'yes','yes',0,'',NULL),(307,'Profesionistă (o voce)','Профессиональный (одноголосный)','',698,'','',-96,'yes','yes',0,'',NULL),(308,'Profesionistă (două voci)','Профессиональный (двухголосый)','',698,'','',-95,'yes','yes',0,'',NULL),(309,'Profesionistă (mai multe voci, voice-over)','Профессиональный (многоголосый, закадровый)','',698,'','',-94,'yes','yes',0,'',NULL),(310,'Profesionistă (dublată)','Профессиональный (полное дублирование)','',698,'','',-93,'yes','yes',0,'',NULL),(311,'Traducere sincronizată','Синхронный перевод',NULL,302,'','',0,'yes','yes',0,NULL,NULL),(312,'Altceva','Другое','',85,'','',100,'yes','yes',0,'',NULL),(313,'Documentare','Документальные','',85,'','',0,'yes','yes',0,'',NULL),(314,'Sport','Спорт','',85,'','',0,'yes','yes',0,'',NULL),(315,'Ucraineană','Украинский','Ukrainian,Uckr',26,'','',0,'yes','yes',0,NULL,NULL),(316,'Hindi','Хинди','Hindi',26,'','',0,'yes','yes',0,NULL,NULL),(317,'Genuri anime','Жанры аниме','Genuri anime',141,'','',0,'yes','no',0,'97',NULL),(318,'Live Action','Боевик','Live Action',317,'','',0,'yes','yes',0,'',NULL),(319,'Manga','Манга','Manga',317,'','',0,'yes','yes',0,'',NULL),(320,'Movie','Фильмы','Movie',317,'','',0,'yes','yes',0,'',NULL),(321,'OVA','ОВА','OVA',317,'','',0,'yes','yes',0,'',NULL),(322,'TV Anime','Аниме телепередачи','TV Anime',317,'','',0,'yes','yes',0,'',NULL),(323,'Other','Другое','Other',317,'','',0,'yes','yes',0,'',NULL),(324,'Series','Сериалы','Series',317,'','',0,'yes','yes',0,'',NULL),(325,'Cross-Platform','Cross-Platform','Cross-Platform',144,'','',0,'yes','yes',0,NULL,NULL),(326,'Alt OS','Alt OS','Alt OS',144,'','',0,'yes','yes',0,NULL,NULL),(327,'Tip de licență','Тип лицензии','Tip de licență',0,'','',0,'yes','no',0,'92,93',NULL),(328,'Shareware','Условно-бесплатно','Shareware',327,'','',0,'yes','yes',0,'',NULL),(329,'Freeware','Бесплатно','Freeware',327,'','',0,'yes','yes',0,'',NULL),(330,'Open source','Открытый исходный код','Open source',327,'','',0,'yes','yes',0,'',NULL),(331,'Alt tip de licență','Другой тип лицензии','',327,'','',0,'yes','yes',0,'',NULL),(332,'Biografie','Биография','Biography',139,'','',0,'yes','yes',0,'',NULL),(333,'Biznes și bani','Бизнес и деньги','Business & Money',139,'','',0,'yes','yes',0,'',NULL),(334,'Copii','Дети','Children',139,'','',0,'yes','yes',0,'',NULL),(335,'Computing & Internet','Компьютеры и Интернет','',139,'','',0,'yes','yes',0,'',NULL),(336,'Cooking, Food & Wine','Кулинария, Еда и Вино','',139,'','',0,'yes','yes',0,'',NULL),(337,'Diet & Health','Диеты и Здоровье','',139,'','',0,'yes','yes',0,'',NULL),(338,'Education','Образование','',139,'','',0,'yes','yes',0,'',NULL),(339,'Fiction & Literature','Художественная литература','',139,'','',0,'yes','yes',0,'',NULL),(340,'History','История','',139,'','',0,'yes','yes',0,'',NULL),(341,'Medicine','Медицина','',139,'','',0,'yes','yes',0,'',NULL),(342,'Mystery & Crime','Детектив, Криминал','',139,'','',0,'yes','yes',0,'',NULL),(343,'Reference','Справочники','',139,'','',0,'yes','yes',0,'',NULL),(344,'Religion','Религия','',139,'','',0,'yes','yes',0,'',NULL),(345,'Self-Improvement','Самосовершенствование','',139,'','',0,'yes','yes',0,'',NULL),(347,'A Capela','A Capela','A Capela',138,'','',0,'yes','yes',0,NULL,NULL),(348,'Acid','Acid','Acid',138,'','',0,'yes','yes',0,NULL,NULL),(349,'Acid Jazz','Acid Jazz','Acid Jazz',138,'','',0,'yes','yes',0,NULL,NULL),(350,'Acid Punk','Acid Punk','Acid Punk',138,'','',0,'yes','yes',0,NULL,NULL),(351,'Acoustic','Acoustic','Acoustic',138,'','',0,'yes','yes',0,NULL,NULL),(352,'Alternative','Alternative','Alternative',138,'','',0,'yes','yes',0,NULL,NULL),(353,'AlternRock','AlternRock','AlternRock',138,'','',0,'yes','yes',0,NULL,NULL),(354,'Ambient','Ambient','Ambient',138,'','',0,'yes','yes',0,NULL,NULL),(355,'Avantgarde','Avantgarde','Avantgarde',138,'','',0,'yes','yes',0,NULL,NULL),(356,'Ballad','Ballad','Ballad',138,'','',0,'yes','yes',0,NULL,NULL),(357,'Bass','Bass','Bass',138,'','',0,'yes','yes',0,NULL,NULL),(358,'Bebob','Bebob','Bebob',138,'','',0,'yes','yes',0,NULL,NULL),(359,'Big Band','Big Band','Big Band',138,'','',0,'yes','yes',0,NULL,NULL),(360,'Black Metal','Black Metal','Black Metal',138,'','',0,'yes','yes',0,NULL,NULL),(361,'Bluegrass','Bluegrass','Bluegrass',138,'','',0,'yes','yes',0,NULL,NULL),(362,'Blues','Blues','Blues',138,'','',0,'yes','yes',0,NULL,NULL),(363,'Booty Brass','Booty Brass','Booty Brass',138,'','',0,'yes','yes',0,NULL,NULL),(364,'Cabaret','Cabaret','Cabaret',138,'','',0,'yes','yes',0,NULL,NULL),(365,'Celtic','Celtic','Celtic',138,'','',0,'yes','yes',0,NULL,NULL),(366,'Chamber Music','Chamber Music','Chamber Music',138,'','',0,'yes','yes',0,NULL,NULL),(367,'Chanson','Chanson','Chanson',138,'','',0,'yes','yes',0,NULL,NULL),(368,'Chill Out','Chill Out','Chill Out',138,'','',0,'yes','yes',0,NULL,NULL),(369,'Chorus','Chorus','Chorus',138,'','',0,'yes','yes',0,NULL,NULL),(370,'Christian Rap','Christian Rap','Christian Rap',138,'','',0,'yes','yes',0,NULL,NULL),(371,'Classic Rock','Classic Rock','Classic Rock',138,'','',0,'yes','yes',0,NULL,NULL),(372,'Classical','Classical','Classical',138,'','',0,'yes','yes',0,NULL,NULL),(373,'Club','Club','Club',138,'','',0,'yes','yes',0,NULL,NULL),(374,'Comedy','Comedy','Comedy',138,'','',0,'yes','yes',0,NULL,NULL),(375,'Country','Country','Country',138,'','',0,'yes','yes',0,NULL,NULL),(376,'Cult','Cult','Cult',138,'','',0,'yes','yes',0,NULL,NULL),(377,'Dance','Dance','Dance',138,'','',0,'yes','yes',0,NULL,NULL),(378,'Dance Hall','Dance Hall','Dance Hall',138,'','',0,'yes','yes',0,NULL,NULL),(379,'Darkwave','Darkwave','Darkwave',138,'','',0,'yes','yes',0,NULL,NULL),(380,'Death Metal','Death Metal','Death Metal',138,'','',0,'yes','yes',0,NULL,NULL),(381,'Deathcore','Deathcore','Deathcore',138,'','',0,'yes','yes',0,NULL,NULL),(382,'Disco','Disco','Disco',138,'','',0,'yes','yes',0,NULL,NULL),(383,'Doom','Doom','Doom',138,'','',0,'yes','yes',0,NULL,NULL),(384,'Downtempo','Downtempo','Downtempo',138,'','',0,'yes','yes',0,NULL,NULL),(385,'Dream','Dream','Dream',138,'','',0,'yes','yes',0,NULL,NULL),(386,'Drum&Bass','Drum&Bass','Drum&Bass',138,'','',0,'yes','yes',0,NULL,NULL),(387,'Drum Solo','Drum Solo','Drum Solo',138,'','',0,'yes','yes',0,NULL,NULL),(388,'Duet','Duet','Duet',138,'','',0,'yes','yes',0,NULL,NULL),(389,'Easy Listening','Easy Listening','Easy Listening',138,'','',0,'yes','yes',0,NULL,NULL),(390,'Electronic','Electronic','Electronic',138,'','',0,'yes','yes',0,NULL,NULL),(391,'Emo','Emo','Emo',138,'','',0,'yes','yes',0,NULL,NULL),(392,'Emocore','Emocore','Emocore',138,'','',0,'yes','yes',0,NULL,NULL),(393,'Ethnic','Ethnic','Ethnic',138,'','',0,'yes','yes',0,NULL,NULL),(394,'Euro-House','Euro-House','Euro-House',138,'','',0,'yes','yes',0,NULL,NULL),(395,'Euro-Techno','Euro-Techno','Euro-Techno',138,'','',0,'yes','yes',0,NULL,NULL),(396,'Eurodance','Eurodance','Eurodance',138,'','',0,'yes','yes',0,NULL,NULL),(397,'Fast Fusion','Fast Fusion','Fast Fusion',138,'','',0,'yes','yes',0,NULL,NULL),(398,'Folk','Folk','Folk',138,'','',0,'yes','yes',0,NULL,NULL),(399,'Folk-Rock','Folk-Rock','Folk-Rock',138,'','',0,'yes','yes',0,NULL,NULL),(400,'Folklore','Folklore','Folklore',138,'','',0,'yes','yes',0,NULL,NULL),(401,'Freestyle','Freestyle','Freestyle',138,'','',0,'yes','yes',0,NULL,NULL),(402,'Funk','Funk','Funk',138,'','',0,'yes','yes',0,NULL,NULL),(403,'Fusion','Fusion','Fusion',138,'','',0,'yes','yes',0,NULL,NULL),(404,'Game','Game','Game',138,'','',0,'yes','yes',0,NULL,NULL),(405,'Gangsta','Gangsta','Gangsta',138,'','',0,'yes','yes',0,NULL,NULL),(406,'Gospel','Gospel','Gospel',138,'','',0,'yes','yes',0,NULL,NULL),(407,'Gothic','Gothic','Gothic',138,'','',0,'yes','yes',0,NULL,NULL),(408,'Gothic Rock','Gothic Rock','Gothic Rock',138,'','',0,'yes','yes',0,NULL,NULL),(409,'Grindcore','Grindcore','Grindcore',138,'','',0,'yes','yes',0,NULL,NULL),(410,'Grunge','Grunge','Grunge',138,'','',0,'yes','yes',0,NULL,NULL),(411,'Hardcore','Hardcore','Hardcore',138,'','',0,'yes','yes',0,NULL,NULL),(412,'Hard Rock','Hard Rock','Hard Rock',138,'','',0,'yes','yes',0,NULL,NULL),(413,'Hip-Hop','Hip-Hop','Hip-Hop',138,'','',0,'yes','yes',0,NULL,NULL),(414,'House','House','House',138,'','',0,'yes','yes',0,NULL,NULL),(415,'Humour','Humour','Humour',138,'','',0,'yes','yes',0,NULL,NULL),(416,'Industrial','Industrial','Industrial',138,'','',0,'yes','yes',0,NULL,NULL),(417,'Instrumental','Instrumental','Instrumental',138,'','',0,'yes','yes',0,NULL,NULL),(418,'Instrumental Pop','Instrumental Pop','Instrumental Pop',138,'','',0,'yes','yes',0,NULL,NULL),(419,'Instrumental Rock','Instrumental Rock','Instrumental Rock',138,'','',0,'yes','yes',0,NULL,NULL),(420,'Jazz','Jazz','Jazz',138,'','',0,'yes','yes',0,NULL,NULL),(421,'Jazz+Funk','Jazz+Funk','Jazz+Funk',138,'','',0,'yes','yes',0,NULL,NULL),(422,'Jungle','Jungle','Jungle',138,'','',0,'yes','yes',0,NULL,NULL),(423,'Latin','Latin','Latin',138,'','',0,'yes','yes',0,NULL,NULL),(424,'Lo-Fi','Lo-Fi','Lo-Fi',138,'','',0,'yes','yes',0,NULL,NULL),(425,'Meditative','Meditative','Meditative',138,'','',0,'yes','yes',0,NULL,NULL),(426,'Metal','Metal','Metal',138,'','',0,'yes','yes',0,NULL,NULL),(427,'Metalcore','Metalcore','Metalcore',138,'','',0,'yes','yes',0,NULL,NULL),(428,'Mathcore','Mathcore','Mathcore',138,'','',0,'yes','yes',0,NULL,NULL),(429,'Minimal','Minimal','Minimal',138,'','',0,'yes','yes',0,NULL,NULL),(430,'Musical','Musical','Musical',138,'','',0,'yes','yes',0,NULL,NULL),(431,'National Folk','National Folk','National Folk',138,'','',0,'yes','yes',0,NULL,NULL),(432,'Native American','Native American','Native American',138,'','',0,'yes','yes',0,NULL,NULL),(433,'New Age','New Age','New Age',138,'','',0,'yes','yes',0,NULL,NULL),(434,'New Wave','New Wave','New Wave',138,'','',0,'yes','yes',0,NULL,NULL),(435,'Noise','Noise','Noise',138,'','',0,'yes','yes',0,NULL,NULL),(436,'Oldies','Oldies','Oldies',138,'','',0,'yes','yes',0,NULL,NULL),(437,'Opera','Opera','Opera',138,'','',0,'yes','yes',0,NULL,NULL),(438,'Polka','Polka','Polka',138,'','',0,'yes','yes',0,NULL,NULL),(439,'Pop','Pop','Pop',138,'','',0,'yes','yes',0,NULL,NULL),(440,'Pop-Folk','Pop-Folk','Pop-Folk',138,'','',0,'yes','yes',0,NULL,NULL),(441,'Pop/Funk','Pop/Funk','Pop/Funk',138,'','',0,'yes','yes',0,NULL,NULL),(442,'Porn Groove','Porn Groove','Porn Groove',138,'','',0,'yes','yes',0,NULL,NULL),(443,'Post-Hardcore','Post-Hardcore','Post-Hardcore',138,'','',0,'yes','yes',0,NULL,NULL),(444,'Poweer Ballad','Poweer Ballad','Poweer Ballad',138,'','',0,'yes','yes',0,NULL,NULL),(445,'Powerviolence','Powerviolence','Powerviolence',138,'','',0,'yes','yes',0,NULL,NULL),(446,'Pranks','Pranks','Pranks',138,'','',0,'yes','yes',0,NULL,NULL),(447,'Primus','Primus','Primus',138,'','',0,'yes','yes',0,NULL,NULL),(448,'Progressive Rock','Progressive Rock','Progressive Rock',138,'','',0,'yes','yes',0,NULL,NULL),(449,'Psychedelic','Psychedelic','Psychedelic',138,'','',0,'yes','yes',0,NULL,NULL),(450,'Psychedelic Rock','Psychedelic Rock','Psychedelic Rock',138,'','',0,'yes','yes',0,NULL,NULL),(451,'Punk','Punk','Punk',138,'','',0,'yes','yes',0,NULL,NULL),(452,'Punk Rock','Punk Rock','Punk Rock',138,'','',0,'yes','yes',0,NULL,NULL),(453,'R&B','R&B','R&B',138,'','',0,'yes','yes',0,NULL,NULL),(454,'Rap','Rap','Rap',138,'','',0,'yes','yes',0,NULL,NULL),(455,'Rave','Rave','Rave',138,'','',0,'yes','yes',0,NULL,NULL),(456,'Reggae','Reggae','Reggae',138,'','',0,'yes','yes',0,NULL,NULL),(457,'Retro','Retro','Retro',138,'','',0,'yes','yes',0,NULL,NULL),(458,'Revival','Revival','Revival',138,'','',0,'yes','yes',0,NULL,NULL),(459,'Rhytmic Soul','Rhytmic Soul','Rhytmic Soul',138,'','',0,'yes','yes',0,NULL,NULL),(460,'Rock','Rock','Rock',138,'','',0,'yes','yes',0,NULL,NULL),(461,'Rock & Roll','Rock & Roll','Rock & Roll',138,'','',0,'yes','yes',0,NULL,NULL),(462,'Samba','Samba','Samba',138,'','',0,'yes','yes',0,NULL,NULL),(463,'Satire','Satire','Satire',138,'','',0,'yes','yes',0,NULL,NULL),(464,'Screamo','Screamo','Screamo',138,'','',0,'yes','yes',0,NULL,NULL),(465,'Showtunes','Showtunes','Showtunes',138,'','',0,'yes','yes',0,NULL,NULL),(466,'Ska','Ska','Ska',138,'','',0,'yes','yes',0,NULL,NULL),(467,'Slow Jam','Slow Jam','Slow Jam',138,'','',0,'yes','yes',0,NULL,NULL),(468,'Slow Rock','Slow Rock','Slow Rock',138,'','',0,'yes','yes',0,NULL,NULL),(469,'Sonata','Sonata','Sonata',138,'','',0,'yes','yes',0,NULL,NULL),(470,'Soul','Soul','Soul',138,'','',0,'yes','yes',0,NULL,NULL),(471,'Sound Clip','Sound Clip','Sound Clip',138,'','',0,'yes','yes',0,NULL,NULL),(472,'Soundtrack','Soundtrack','Soundtrack',138,'','',0,'yes','yes',0,NULL,NULL),(473,'Southern Rock','Southern Rock','Southern Rock',138,'','',0,'yes','yes',0,NULL,NULL),(474,'Space','Space','Space',138,'','',0,'yes','yes',0,NULL,NULL),(475,'Speech','Speech','Speech',138,'','',0,'yes','yes',0,NULL,NULL),(476,'Swing','Swing','Swing',138,'','',0,'yes','yes',0,NULL,NULL),(477,'Symphonic Rock','Symphonic Rock','Symphonic Rock',138,'','',0,'yes','yes',0,NULL,NULL),(478,'Symphony','Symphony','Symphony',138,'','',0,'yes','yes',0,NULL,NULL),(479,'Tango','Tango','Tango',138,'','',0,'yes','yes',0,NULL,NULL),(480,'Techno','Techno','Techno',138,'','',0,'yes','yes',0,NULL,NULL),(481,'Techno-Industrial','Techno-Industrial','Techno-Industrial',138,'','',0,'yes','yes',0,NULL,NULL),(482,'Thrash','Thrash','Thrash',138,'','',0,'yes','yes',0,NULL,NULL),(483,'Top 40','Top 40','Top 40',138,'','',0,'yes','yes',0,NULL,NULL),(484,'Trailer','Trailer','Trailer',138,'','',0,'yes','yes',0,NULL,NULL),(485,'Trance','Trance','Trance',138,'','',0,'yes','yes',0,NULL,NULL),(486,'Tribal','Tribal','Tribal',138,'','',0,'yes','yes',0,NULL,NULL),(487,'Trip-Hop','Trip-Hop','Trip-Hop',138,'','',0,'yes','yes',0,NULL,NULL),(488,'Vocal','Vocal','Vocal',138,'','',0,'yes','yes',0,NULL,NULL),(489,'Heavy Metal','Heavy Metal','Heavy Metal',138,'','',0,'yes','yes',0,NULL,NULL),(490,'Power Metal','Power Metal','Power Metal',138,'','',0,'yes','yes',0,NULL,NULL),(505,'Science & Technics','Наука и Техника','Science & Technics',139,'','',0,'yes','yes',0,'',NULL),(506,'Other','Другое','Other',139,'','',0,'yes','yes',0,'',NULL),(508,'OGG','OGG','OGG',154,'','',0,'yes','yes',0,NULL,NULL),(509,'WMA','WMA','WMA',154,'','',0,'yes','yes',0,NULL,NULL),(510,'AAC','AAC','AAC',154,'','',0,'yes','yes',0,NULL,NULL),(512,'MPC','MPC','MPC',154,'','',0,'yes','yes',0,NULL,NULL),(513,'APE','APE','APE',156,'','',0,'yes','yes',0,NULL,NULL),(514,'SHN','SHN','SHN',156,'','',0,'yes','yes',0,NULL,NULL),(515,'WV','WV','WV',156,'','',0,'yes','yes',0,NULL,NULL),(516,'OFR','OFR','OFR',156,'','',0,'yes','yes',0,NULL,NULL),(517,'WAV','WAV','WAV',156,'','',0,'yes','yes',0,NULL,NULL),(518,'AIFF','AIFF','AIFF',156,'','',0,'yes','yes',0,NULL,NULL),(519,'SPX','SPX','SPX',154,'','',0,'yes','yes',0,NULL,NULL),(520,'AA','AA','AA',154,'','',0,'yes','yes',0,NULL,NULL),(521,'AC3','AC3','AC3',154,'','',0,'yes','yes',0,NULL,NULL),(522,'Promovare','Продвижение','Promotion',0,'','',0,'yes','no',0,'',NULL),(523,'Arcade','Аркады','',140,'','',0,'yes','yes',0,'',NULL),(524,'Mystery','Mystery','',62,'','',0,'yes','yes',0,'',NULL),(525,'OAD','Релизы на DVD (OAD)','',317,'','',0,'yes','yes',0,'',NULL),(526,'Special','Специальные','',317,'','',0,'yes','yes',0,'',NULL),(527,'AMV','Музыкальные аниме клипы (AMV)','',317,'','',0,'yes','yes',0,'',NULL),(528,'Calipso','Calipso','',138,'','',0,'yes','yes',0,'',NULL),(529,'Vocal Jazz','Vocal Jazz','',138,'','',0,'yes','yes',0,'',NULL),(530,'Hard Trance','Hard Trance','',138,'','',0,'yes','yes',0,'',NULL),(531,'Uplifting Trance','Uplifting Trance','',138,'','',0,'yes','yes',0,'',NULL),(532,'Progressive Trance','Progressive Trance','',138,'','',0,'yes','yes',0,'',NULL),(533,'Melodic Trance','Melodic Trance','',138,'','',0,'yes','yes',0,'',NULL),(534,'Euro Trance','Euro Trance','',138,'','',0,'yes','yes',0,'',NULL),(535,'Anthem Trance','Anthem Trance','',138,'','',0,'yes','yes',0,'',NULL),(536,'Psychedelic Trance','Psychedelic Trance','',138,'','',0,'yes','yes',0,'',NULL),(537,'Dream Trance','Dream Trance','',138,'','',0,'yes','yes',0,'',NULL),(539,'Tech Trance','Tech Trance','',138,'','',0,'yes','yes',0,'',NULL),(540,'Goa Trance','Goa Trance','',138,'','',0,'yes','yes',0,'',NULL),(541,'Acid house','Acid house','',138,'','',0,'yes','yes',0,'',NULL),(542,'Deep house','Deep house','',138,'','',0,'yes','yes',0,'',NULL),(543,'Vocal house','Vocal house','',138,'','',0,'yes','yes',0,'',NULL),(544,'Progressive house','Progressive house','',138,'','',0,'yes','yes',0,'',NULL),(545,'Soulful house','Soulful house','',138,'','',0,'yes','yes',0,'',NULL),(546,'Action','Action','Шутеры',140,'','',0,'yes','yes',0,'',NULL),(547,'First-Person Shooters (FPS)','Шутеры от первого лица (FPS)','',546,'','',0,'yes','yes',0,'',NULL),(548,'Third-Person Shooters (TPS)','Шутеры от третьего лица (TPS)','',546,'','',0,'yes','yes',0,'',NULL),(549,'Other Shooters','Другие шутеры','',546,'','',0,'yes','yes',0,'',NULL),(550,'Tactical Shooters','Тактические шутеры','',546,'','',0,'yes','yes',0,'',NULL),(551,'Strategy','Стратегии','',140,'','',0,'yes','yes',0,'',NULL),(552,'Real-Time Strategy (RTS)','Стратегии в реальном времени (RTS)','',551,'','',0,'yes','yes',0,'',NULL),(553,'Turn by Turn Strategy (TBS)','Пошаговые стратегии (TBS)','',551,'','',0,'yes','yes',0,'',NULL),(554,'Economic Strategy','Экономические стратегии','',551,'','',0,'yes','yes',0,'',NULL),(555,'Simulation','Cимуляторы','',140,'','',0,'yes','yes',0,'',NULL),(556,'Sport Simulators','Спортивные симуляторы','',555,'','',0,'yes','yes',0,'',NULL),(557,'Racing','Гонки','',555,'','',0,'yes','yes',0,'',NULL),(558,'Air Simulators','Воздушные симуляторы','',555,'','',0,'yes','yes',0,'',NULL),(559,'Online Games','Онлайн Игры','',140,'','',0,'yes','yes',0,'',NULL),(560,'Adventure and Quests','Приключения и Квесты','',140,'','',0,'yes','yes',0,'',NULL),(561,'Hidden Object','Hidden Object','',140,'','',0,'yes','yes',0,'',NULL),(562,'Android','Android','',150,'','',0,'yes','yes',0,'',NULL),(563,'iOS','iOS','',150,'','',0,'yes','yes',0,'',NULL),(564,'Other','Other','',150,'','',0,'yes','yes',0,'',NULL),(565,'Genuri de sport','Виды спорта','',141,'','',0,'yes','no',0,'314',NULL),(566,'Archery','Стрельба из лука','',565,'','',0,'yes','yes',0,'',NULL),(567,'Athletics','Лёгкая атлетика','',565,'','',0,'yes','yes',0,'',NULL),(568,'Field Hockey','Хоккей на траве','',565,'','',0,'yes','yes',0,'',NULL),(569,'Judo','Дзюдо','',565,'','',0,'yes','yes',0,'',NULL),(570,'Modern Pentathlon','Современное пятиборье','',565,'','',0,'yes','yes',0,'',NULL),(571,'Taekwondo','Тхэквондо','',565,'','',0,'yes','yes',0,'',NULL),(572,'Auto Sport','Автоспорт','',565,'','',0,'yes','yes',0,'',NULL),(573,'Poker','Покер','',565,'','',0,'yes','yes',0,'',NULL),(574,'Beach Soccer','Пляжный футбол','',565,'','',0,'yes','yes',0,'',NULL),(575,'Revistă pentru bărbaţi','Журнал для мужчин','Men\'s magazine',139,'','',0,'yes','yes',0,'',NULL),(576,'Revistă pentru femei','Журнал для женщин','Women\'s magazine',139,'','',0,'yes','yes',0,'',NULL),(577,'Ziar','Газета','Newspaper',139,'','',0,'yes','yes',0,'',NULL),(578,'Psihologie','Психология','Psychology',139,'','',0,'yes','yes',0,'',NULL),(579,'Sport','Спорт','Sport',139,'','',0,'yes','yes',0,'',NULL),(580,'Lucru manual','Ручная работа','Handmade',139,'','',0,'yes','yes',0,'',NULL),(581,'Role-Playing(RPG)','Role-Playing(RPG)','Role-Playing(RPG)',140,'','',0,'yes','yes',0,'',NULL),(582,'Platformers','Platformers','Platformers',140,'','',0,'yes','yes',0,'',NULL),(583,'Fighting Games','Fighting Games','Fighting Games',140,'','',0,'yes','yes',0,'',NULL),(584,'For Kids','For Kids','For Kids',140,'','',0,'yes','yes',0,'',NULL),(585,'Other','Other','Other',140,'','',0,'yes','yes',0,'',NULL),(586,'Dubstep','Dubstep','Dubstep',138,'','',0,'yes','yes',0,'',NULL),(587,'Basketball','Basketball','Basketball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(588,'Boxing','Boxi\nng','Boxing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(589,'Fighting','Fighting','Fighting',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(590,'Martial Arts','Martial Arts','Martial Arts',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(591,'Football','Football','Football',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(592,'Formula 1','Formula 1','Formula 1',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(593,'Motorcycle Racing','Motorcycle Racing','Motorcycle Racing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(594,'Tennis','Tennis','Tennis',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(595,'Rugby','Rugby','Rugby',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(596,'Volleyball','Volleyball','Volleyball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(597,'Badminton','Badminton','Badmin\nton',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(598,'Baseball','Baseball','Baseball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(599,'Cycling','Cycling','Cycling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(600,'Biathlon','Biathlon','Biathlon',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(601,'Bi\nlliards','Billiards','Billiards',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(602,'Board Sports','Board Sports','Board Sports',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(604,'Bobsledding','Bobsledding','Bobsledding',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(605,'Boomerang','Boomerang','Boomerang',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(606,'Bowling','Bowling','Bowling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(607,'Boxball','Boxball','Boxball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(608,'Bullfighting','Bullfighting','Bullfighting',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(609,'Buzkashi','Buzkashi','Buzkashi',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(610,'Camel Racing','Camel Racing','Camel Racing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(611,'Canoe Polo','Canoe Polo','Canoe Polo',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(612,'Canoe-Kayak Racing','Canoe-Kayak Racing','Canoe-Kayak Racing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(613,'Cheerleading','Cheerleading','Cheerleading',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(614,'Cockfighting','Cockfighting','Cockfighting',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(615,'Cricket','Cricket','Cricket',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(616,'Croquet','Croquet','Croquet',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(617,'Curling','Curling','Curling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(618,'Danball','Danball','Danball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(619,'Dirtsurfing','Dirtsurfing','Dirtsurfing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(620,'Dodgeball','Dodgeball','Dodgeball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(621,'Dog Racing','Dog Racing','Dog Racing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(622,'Dogsledding','Dogsledding','Dogsledding',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(623,'Drifting','Drifting','Drifting',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(624,'Equestrian','Equestrian','Equestrian',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(625,'Extreme Sports','Extreme Sports','Extreme Sports',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(626,'Fencing','Fencing','Fencing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(627,'Fishing','Fishing','Fishing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(628,'Flying Discs','Flying Discs','Flying Discs',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(629,'Footbag','Footbag','Footbag',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(630,'Freediving','Freediving','Freediving',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(631,'Golf','Golf','Golf',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(632,'Gymnastics','Gymnastics','Gymnastics',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(633,'Handball','Handball','Handball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(634,'Hockey','Hockey','Hockey',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(635,'Horse Racing','Horse Racing','Horse Racing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(636,'Hurling','Hurling','Hurling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(637,'Jai-Alai','Jai-Alai','Jai-Alai',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(638,'Kabaddi','Kabaddi','Kabaddi',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(639,'Kickball','Kickball','Kickball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(640,'Korfball','Korfball','Korfball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(641,'K-1','K-1','K-1',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(642,'Lacrosse','Lacrosse','Lacrosse',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(643,'Le Parkour','Le Parkour','Le Parkour',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(644,'Luge','Luge','Luge',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(645,'Lumbering','Lumbering','Lumbering',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(646,'MMA','MMA','MMA',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(647,'Mountainboarding','Mountainboarding','Mountainboarding',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(648,'Muay','Muay','Muay',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(649,'Netball','Netball','Netball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(650,'Orienteering','Orienteering','Orienteering',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(651,'Paddleball','Paddleball','Paddleball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(652,'Paddling','Paddling','Paddling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(653,'Paintball','Paintball','Paintball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(654,'Pickleball','Pickleball','Pickleball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(655,'Polo','Polo','Polo',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(656,'Racewalking','Racewalking','Racewalking',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(657,'Racquetball','Racquetball','Racquetball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(658,'Ringette','Ringette','Ringette',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(659,'Rodeo','Rodeo','Rodeo',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(660,'Rowing','Rowing','Rowing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(661,'Running','Running','Running',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(662,'Sailing','Sailing','Sailing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(663,'Sandboarding','Sandboarding','Sandboarding',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(664,'Sepak Takraw','Sepak Takraw','Sepak Takraw',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(665,'Shinty','Shinty','Shinty',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(666,'Shooting','Shooting','Shooting',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(667,'Skateboarding','Skateboarding','Skateboarding',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(668,'Skating','Skating','Skating',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(669,'Skeleton','Skeleton','Skeleton',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(670,'Skiing','Skiing','Skiing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(671,'Snowboarding','Snowboarding','Snowboarding',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(672,'Snowmobiling','Snowmobiling','Snowmobiling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(673,'Soccer','Soccer','Soccer',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(674,'Softball','Softball','Softball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(675,'Sports Entertainment','Sports Entertainment','Sports Entertainment',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(676,'Squash','Squash','Squash',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(677,'Surfing','Surfing','Surfing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(678,'Swimming and Diving','Swimming and Diving','Swimming and Diving',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(679,'Table Tennis','Table Tennis','Table Tennis',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(680,'Tchoukball','Tchoukball','Tchoukball',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(681,'Track and Field','Track and Field','Track and Field',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(682,'Triathlon','Triathlon','Triathlon',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(683,'Tug-of-War','Tug-of-War','Tug-of-War',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(684,'Twirling','Twirling','Twirling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(685,'Wakeboarding','Wakeboarding','Wakeboarding',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(686,'Walking','Walking','Walking',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(687,'Water Polo','Water Polo','Water Polo',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(688,'Waterskiing','Waterskiing','Waterskiing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(689,'Weightlifting','Weightlifting','Weightlifting',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(690,'Wheelchair Racing','Wheelchair Racing','Wheelchair Racing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(691,'Windsurfing','Windsurfing','Windsurfing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(692,'Winter Sports','Winter Sports','Winter Sports',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(693,'Wrestling','Wrestling','Wrestling',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(694,'World Cup 06','World Cup 06','World Cup 06',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(695,'Other','Other','Other',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(696,'Boat Racing','Boat Racing','Boat Racing',565,NULL,NULL,0,'yes','yes',0,NULL,NULL),(697,'Amator','Любительский','Amator',302,'','',0,'yes','yes',0,'',NULL),(698,'Profesionistă','Профессиональный','Profesionistă',302,'','',0,'yes','yes',0,'',NULL),(699,'Grafică computerizată','Компьютерная графика','Computer Graphics',85,'','',0,'no','yes',0,'',NULL),(700,'Tech House','Tech House','Tech House',138,'','',0,'yes','yes',0,'',NULL);
/*!40000 ALTER TABLE `torrents_catetags` ENABLE KEYS */;
UNLOCK TABLES;



CREATE TABLE IF NOT EXISTS `torrents_catetags_index` (
  `torrent` int(9) unsigned NOT NULL,
  `catetag` mediumint(9) unsigned NOT NULL,
  PRIMARY KEY (`catetag`,`torrent`),
  KEY `torrent` (`torrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `torrents_details` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `descr_ar` text COLLATE utf8_unicode_ci NOT NULL,
  `descr_html` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `torrents_genres` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `categ` tinyint(4) unsigned DEFAULT NULL,
  `genre` smallint(6) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrentid` (`torrentid`),
  KEY `categ_genre_id` (`categ`,`genre`,`torrentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `torrents_imdb` (
  `torrent` int(9) unsigned NOT NULL,
  `imdb_tt` mediumint(9) unsigned NOT NULL,
  PRIMARY KEY (`torrent`),
  KEY `imdb_tt` (`imdb_tt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `torrents_tags` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `tagid` int(11) NOT NULL DEFAULT '0',
  `torrentid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrentid` (`torrentid`),
  KEY `tagid` (`tagid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `torrents_tags_index` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `tag_name` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_name` (`tag_name`(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `torrents_thanks` (
  `torrent` mediumint(9) unsigned DEFAULT NULL,
  `user` mediumint(9) unsigned DEFAULT NULL,
  `torrent_owner` mediumint(9) unsigned DEFAULT NULL,
  `thank_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `torrent` (`torrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `torrents_thanks_count` (
  `id` mediumint(9) unsigned DEFAULT NULL,
  `count` mediumint(9) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `torrentsmoderslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime DEFAULT NULL,
  `txt` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `old_password` varchar(40) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `passhash` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `secret` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `email` varchar(80) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `status` enum('pending','confirmed') CHARACTER SET latin1 NOT NULL DEFAULT 'pending',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `editsecret` varchar(32) DEFAULT NULL,
  `privacy` enum('strong','normal','low') CHARACTER SET latin1 NOT NULL DEFAULT 'normal',
  `info` text CHARACTER SET latin1,
  `acceptpms` enum('yes','friends','no') CHARACTER SET latin1 NOT NULL DEFAULT 'yes',
  `ip` varchar(64) DEFAULT NULL,
  `class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `avatar` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  `title` varchar(70) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `notifs` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '[pm]',
  `enabled` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'yes',
  `avatars` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'yes',
  `donor` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  `warned` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  `warneduntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torrentsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `topicsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `postsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `deletepms` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'yes',
  `savepms` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  `passkey` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `region` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `language` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `last_browse_see` int(11) unsigned DEFAULT '0',
  `have_voted` enum('yes','no') CHARACTER SET latin1 DEFAULT 'no',
  `user_opt` int(10) unsigned NOT NULL DEFAULT '0',
  `banpostinguntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `postingbanuntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `gender` enum('masc','fem','none') CHARACTER SET latin1 DEFAULT 'none',
  `clone_hash` varchar(32) CHARACTER SET latin1 DEFAULT '',
  `team` smallint(6) unsigned DEFAULT NULL,
  `thanks` int(11) DEFAULT '0',
  `browserSession` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `browserHash` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `uploadbanuntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `downloadbanuntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `inviter` int(10) unsigned NOT NULL DEFAULT '0',
  `avatar_version` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `download_no_passkey` tinyint(1) DEFAULT '0',
  `topicmd_only` enum('y','n') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'n',
  `email_is_invalid` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `passkey` (`passkey`),
  KEY `status_added` (`status`,`added`),
  KEY `ip` (`ip`),
  KEY `last_access` (`last_access`),
  KEY `enabled` (`enabled`),
  KEY `added` (`added`),
  KEY `class` (`class`),
  KEY `browserSession` (`browserSession`),
  KEY `inviter` (`inviter`),
  KEY `email` (`email`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users_additional` (
  `id` int(11) NOT NULL,
  `comments` mediumint(9) DEFAULT '0',
  `posts` mediumint(9) DEFAULT '0',
  `spankuntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_invites` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_regeneration` date NOT NULL DEFAULT '0000-00-00',
  `total_censored` int(10) DEFAULT '0',
  `author_of_reported_posts_total` int(10) DEFAULT '0',
  `author_of_reported_posts_total_censored` int(10) DEFAULT '0',
  `total_wall_posts` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users_badges` (
  `userId` int(10) unsigned NOT NULL,
  `badgeId` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users_down_up` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_access_topic` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_access_updates` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `users_hot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `last_browse_see` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `users_inbox` (
  `id` int(11) NOT NULL DEFAULT '0',
  `received` smallint(6) unsigned DEFAULT '0',
  `sended` smallint(6) unsigned DEFAULT '0',
  `unread` smallint(6) unsigned DEFAULT '0',
  `unread_notifications` smallint(6) unsigned DEFAULT '0',
  `last_read_global_notification` int(10) unsigned DEFAULT '0',
  `last_notification_email` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_notification_received` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `unread_watched_notification_maxLastSeenMsg` int(11) unsigned NOT NULL DEFAULT '0',
  `emails_sent` int(11) unsigned NOT NULL DEFAULT '0',
  `last_email_sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_global_wall_post_seen_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `users_rare` (
  `id` int(11) NOT NULL,
  `modcomment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users_stats` (
  `userid` int(10) NOT NULL,
  `total_censored` int(10) NOT NULL,
  UNIQUE KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users_username` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gender` enum('masc','fem','none') COLLATE utf8_unicode_ci DEFAULT 'none',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `watches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `type` enum('topic','torrent') COLLATE utf8_unicode_ci DEFAULT NULL,
  `thread` int(11) unsigned NOT NULL DEFAULT '0',
  `lastSeenMsg` int(11) unsigned NOT NULL DEFAULT '0',
  `lastThreadMsg` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `lastSeenMsg` (`lastSeenMsg`,`lastThreadMsg`),
  KEY `type` (`type`),
  KEY `thread` (`thread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `torrents_importer_status`(
  `domain` varchar(255),
  `last_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `torrents_importer_images_scheduled` (
  `torrent_id` int(11) NOT NULL,
  `scheduled` enum('scheduled', 'in_progress', 'error') DEFAULT 'scheduled',
  `job_started` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `url` varchar(255) NOT NULL,
  `retries` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`torrent_id`),
  KEY `scheduled` (`scheduled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
