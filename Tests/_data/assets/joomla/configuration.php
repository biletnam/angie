<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

class JConfig {
	public $MetaAuthor = '1';
	public $MetaDesc = 'Integration test';
	public $MetaKeys = '';
	public $MetaRights = '';
	public $MetaTitle = '1';
	public $MetaVersion = '0';
	public $access = '1';
	public $cache_handler = 'file';
	public $cachetime = '30';
	public $caching = '0';
	public $captcha = '0';
	public $cookie_domain = '';
	public $cookie_path = '';
	public $db = 'integration';
	public $dbprefix = 'test_';
	public $dbtype = 'pdomysql';
	public $debug = '1';
	public $debug_lang = '0';
	public $display_offline_message = '1';
	public $editor = 'tinymce';
	public $error_reporting = 'development';
	public $feed_email = 'author';
	public $feed_limit = '10';
	public $force_ssl = '0';
	public $fromname = 'Integration test';
	public $ftp_enable = '0';
	public $ftp_host = '';
	public $ftp_pass = '';
	public $ftp_port = '21';
	public $ftp_root = '';
	public $ftp_user = '';
	public $gzip = '0';
	public $helpurl = 'http://help.joomla.org/proxy/index.php?option=com_help&keyref=Help{major}{minor}:{keyref}';
	public $host = '##DBHOST##';
	public $lifetime = '1000';
	public $list_limit = '20';
	public $live_site = '##LIVESITEURL##';
	public $log_path = '##SITEROOT##/log';
	public $mailer = 'sendmail';
	public $mailfrom = '';
	public $memcache_compress = '1';
	public $memcache_persist = '1';
	public $memcache_server_host = 'localhost';
	public $memcache_server_port = '11211';
	public $offline = '0';
	public $offline_image = '';
	public $offline_message = 'This site is down for maintenance.<br /> Please check back again soon.';
	public $offset = 'UTC';
	public $offset_user = 'UTC';
	public $password = 'integration';
	public $robots = '';
	public $secret = 'IAMINTEGRATIONTEST';
	public $sef = '1';
	public $sef_rewrite = '1';
	public $sef_suffix = '0';
	public $sendmail = '/usr/sbin/sendmail';
	public $session_handler = 'database';
	public $sitename = 'Integration Test';
	public $sitename_pagetitles = '0';
	public $smtpauth = '0';
	public $smtphost = '';
	public $smtppass = '';
	public $smtpport = '25';
	public $smtpsecure = 'none';
	public $smtpuser = '';
	public $tmp_path = '##SITEROOT##/tmp';
	public $unicodeslugs = '1';
	public $user = 'integration';
	public $mailonline = '1';
	public $frontediting = '1';
	public $asset_id = '1';
	public $memcached_persist = '1';
	public $memcached_compress = '0';
	public $memcached_server_host = 'localhost';
	public $memcached_server_port = '11211';
	public $proxy_enable = '0';
	public $proxy_host = '';
	public $proxy_port = '';
	public $proxy_user = '';
	public $proxy_pass = '';
	public $session_memcache_server_host = 'localhost';
	public $session_memcache_server_port = '11211';
	public $session_memcached_server_host = 'localhost';
	public $session_memcached_server_port = '11211';
	public $redis_persist = '1';
	public $redis_server_host = 'localhost';
	public $redis_server_port = '6379';
	public $redis_server_auth = '';
	public $redis_server_db = '0';
}
