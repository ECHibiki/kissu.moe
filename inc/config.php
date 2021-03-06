<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 *
 *  WARNING: This is a project-wide configuration file and is overwritten when upgrading to a newer
 *  version of Tinyboard. Please leave this file unchanged, or it will be a lot harder for you to upgrade.
 *  If you would like to make instance-specific changes to your own setup, please use instance-config.php.
 *
 *  This is the default configuration. You can copy values from here and use them in
 *  your instance-config.php
 *
 *  You can also create per-board configuration files. Once a board is created, locate its directory and
 *  create a new file named config.php (eg. b/config.php). Like instance-config.php, you can copy values
 *  from here and use them in your per-board configuration files.
 *
 *  Some directives are commented out. This is either because they are optional and examples, or because
 *  they are "optionally configurable", and given their default values by Tinyboard's code later if unset.
 *
 *  More information: https://web.archive.org/web/20121003095922/http://tinyboard.org/docs/?p=Config
 *
 *  Tinyboard documentation: https://web.archive.org/web/20121003095807/http://tinyboard.org/docs/?p=Main_Page
 *

/* AHEM */
 // config.php should be left in tact
 // make your modifications in a new file called instance-config.php and it will be used to replace most of the values here. Filters are an exception.


	defined('TINYBOARD') or exit;

/*
 * =======================
 *  General/misc settings
 * =======================
 */

	// Global announcement -- the very simple version.
	// This used to be wrongly named $config['blotter'] (still exists as an alias).
	// $config['global_message'] = 'This is an important announcement!';
	$config['blotter'] = &$config['global_message'];

	// remote server things
	$config['max_image_transfer_size'] = 1048576;
	$config["storage_server_addr"] = "";
	$config["storage_server_user"] = "";
	$config["storage_server_port"] = 0;
	$config["storage_server_root_directory"] = "/";
	// SFTP or FTPS
	$config["storage_server_connection"] = "FTPS";

	$config['site_name'] = "kissu.moe";

        // flag for react to replace standard html template behaviour with dynamic ones.
	$config['js_ui'] = false;

	// Automatically check if a newer version of Tinyboard is available when an administrator logs in.
	$config['check_updates'] = false;
	// How often to check for updates
	$config['check_updates_time'] = 43200; // 12 hours

	// Shows some extra information at the bottom of pages. Good for development/debugging.
	$config['debug'] = false;
	// For development purposes. Displays (and "dies" on) all errors and warnings. Turn on with the above.
	$config['verbose_errors'] = true;
	// EXPLAIN all SQL queries (when in debug mode).
	$config['debug_explain'] = false;

	// Directory where temporary files will be created.
	$config['tmp'] = sys_get_temp_dir();

	// The HTTP status code to use when redirecting. http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	// Can be either 303 "See Other" or 302 "Found". (303 is more correct but both should work.)
	// There is really no reason for you to ever need to change this.
	$config['redirect_http'] = 303;

	// A tiny text file in the main directory indicating that the script has been ran and the board(s) have
	// been generated. This keeps the script from querying the database and causing strain when not needed.
	$config['has_installed'] = '.installed';

	// Use syslog() for logging all error messages and unauthorized login attempts.
	$config['syslog'] = false;
	$config["max_login_attempts_refresh_time"] = 60 * 60; // 1 hour
	$config["true_login_refresh_time"] = 60 * 60 * 24 * 14; // 14 days
	$config["max_login_attempts"] = 100;
	$config["error"]["max_logins_reached"] = "You have reached the maximum number of login attempts.";

	// Use `host` via shell_exec() to lookup hostnames, avoiding query timeouts. May not work on your system.
	// Requires safe_mode to be disabled.
	$config['dns_system'] = false;

	// Check validity of the reverse DNS of IP addresses. Highly recommended.
	$config['fcrdns'] = true;

	// When executing most command-line tools (such as `convert` for ImageMagick image processing), add this
	// to the environment path (seperated by :).
	$config['shell_path'] = '/usr/local/bin';



/*
 * ====================
 *  Database settings
 * ====================
 */

	// Database driver (http://www.php.net/manual/en/pdo.drivers.php)
	// Only MySQL is supported by Tinyboard at the moment, sorry.
	$config['db']['type'] = 'mysql';
	// Hostname, IP address or Unix socket (prefixed with ":")
	$config['db']['server'] = 'localhost';
	// Example: Unix socket
	// $config['db']['server'] = ':/tmp/mysql.sock';
	// Login
	$config['db']['user'] = '';
	$config['db']['password'] = '';
	// Tinyboard database
	$config['db']['database'] = '';
	// Table prefix (optional)
	$config['db']['prefix'] = '';
	// Use a persistent database connection when possible
	$config['db']['persistent'] = false;
	// Anything more to add to the DSN string (eg. port=xxx;foo=bar)
	$config['db']['dsn'] = '';
	// Connection timeout duration in seconds
	$config['db']['timeout'] = 30;

/*
 * ====================
 *  Cache, lock and queue settings
 * ====================
 */

	/*
	 * On top of the static file caching system, you can enable the additional caching system which is
	 * designed to minimize SQL queries and can significantly increase speed when posting or using the
	 * moderator interface. APC is the recommended method of caching.
	 *
	 * https://web.archive.org/web/20121003095626/http://tinyboard.org/docs/?p=Config/Cache
	 */

	$config['cache']['enabled'] = 'php';
	// $config['cache']['enabled'] = 'xcache';
	// $config['cache']['enabled'] = 'apc';
	// $config['cache']['enabled'] = 'memcached';
	// $config['cache']['enabled'] = 'redis';
	// $config['cache']['enabled'] = 'fs';

	// Timeout for cached objects such as posts and HTML.
	$config['cache']['timeout'] = 60 * 60 * 48; // 48 hours

	// Optional prefix if you're running multiple Tinyboard instances on the same machine.
	$config['cache']['prefix'] = '';

	// Memcached servers to use. Read more: http://www.php.net/manual/en/memcached.addservers.php
	$config['cache']['memcached'] = array(
		array('localhost', 11211)
	);

	// Redis server to use. Location, port, password, database id.
	// Note that Tinyboard may clear the database at times, so you may want to pick a database id just for
	// Tinyboard to use.
	$config['cache']['redis'] = array('localhost', 6379, '', 1);

	// EXPERIMENTAL: Should we cache configs? Warning: this changes board behaviour, i'd say, a lot.
	// If you have any lambdas/includes present in your config, you should move them to instance-functions.php
	// (this file will be explicitly loaded during cache hit, but not during cache miss).
	$config['cache_config'] = false;

	// Define a lock driver.
	$config['lock']['enabled'] = 'fs';

	// Define a queue driver.
	$config['queue']['enabled'] = 'fs'; // xD

/*
 * ====================
 *  Cookie settings
 * ====================
 */

	// Used for moderation login.
	$config['cookies']['mod'] = 'mod';

	// Used for communicating with Javascript; telling it when posts were successful.
	$config['cookies']['js'] = 'serv';

	// Cookies path. Defaults to $config['root']. If $config['root'] is a URL, you need to set this. Should
	// be '/' or '/board/', depending on your installation.
	// $config['cookies']['path'] = '/';
	// Where to set the 'path' parameter to $config['cookies']['path'] when creating cookies. Recommended.
	$config['cookies']['jail'] = true;

	// How long should the cookies last (in seconds). Defines how long should moderators should remain logged
	// in (0 = browser session).
	$config['cookies']['expire'] = 60 * 60 * 24 * 30 * 6; // ~6 months

	// Make this something long and random for security.
	$config['cookies']['salt'] = 'abcdefghijklmnopqrstuvwxyz09123456789!@#$%^&*()';

	// Whether or not you can access the mod cookie in JavaScript. Most users should not need to change this.
	$config['cookies']['httponly'] = true;

	// Used to salt secure tripcodes ("##trip") and poster IDs (if enabled).
	$config['secure_trip_salt'] = ')(*&^%$#@!98765432190zyxwvutsrqponmlkjihgfedcba';

/*
 * ====================
 *  Flood/spam settings
 * ====================
 */

	/*
	 * To further prevent spam and abuse, you can use DNS blacklists (DNSBL). A DNSBL is a list of IP
	 * addresses published through the Internet Domain Name Service (DNS) either as a zone file that can be
	 * used by DNS server software, or as a live DNS zone that can be queried in real-time.
	 *
	 * Read more: https://web.archive.org/web/20121003095945/http://tinyboard.org/docs/?p=Config/DNSBL
	 */

	// Prevents most Tor exit nodes from making posts. Recommended, as a lot of abuse comes from Tor because
	// of the strong anonymity associated with it.
	// Example: $config['dnsbl'][] = 'another.blacklist.net'; //
	// $config['dnsbl'][] = array('tor.dnsbl.sectoor.de', 1); //sectoor.de site is dead. the number stands for (an) ip adress(es) I guess.

	// Replacement for sectoor.de
	//$config['dnsbl'][] = array('rbl.efnet.org', 4);

	// block banned from viewing site
	$config['ban_block'] = false;

	// http://www.sorbs.net/using.shtml
	// $config['dnsbl'][] = array('dnsbl.sorbs.net', array(2, 3, 4, 5, 6, 7, 8, 9));

	// http://www.projecthoneypot.org/httpbl.php
	// $config['dnsbl'][] = array('<your access key>.%.dnsbl.httpbl.org', function($ip) {
	//	$octets = explode('.', $ip);
	//
	//	// days since last activity
	//	if ($octets[1] > 14)
	//		return false;
	//
	//	// "threat score" (http://www.projecthoneypot.org/threat_info.php)
	//	if ($octets[2] < 5)
	//		return false;
	//
	//	return true;
	// }, 'dnsbl.httpbl.org'); // hide our access key

	// Skip checking certain IP addresses against blacklists (for troubleshooting or whatever)
	$config['dnsbl_exceptions'][] = '127.0.0.1';

	/*
	 * Introduction to Tinyboard's spam filter:
	 *
	 * In simple terms, whenever a posting form on a page is generated (which happens whenever a
	 * post is made), Tinyboard will add a random amount of hidden, obscure fields to it to
	 * confuse bots and upset hackers. These fields and their respective obscure values are
	 * validated upon posting with a 160-bit "hash". That hash can only be used as many times
	 * as you specify; otherwise, flooding bots could just keep reusing the same hash.
	 * Once a new set of inputs (and the hash) are generated, old hashes for the same thread
	 * and board are set to expire. Because you have to reload the page to get the new set
	 * of inputs and hash, if they expire too quickly and more than one person is viewing the
	 * page at a given time, Tinyboard would return false positives (depending on how long the
	 * user sits on the page before posting). If your imageboard is quite fast/popular, set
	 * $config['spam']['hidden_inputs_max_pass'] and $config['spam']['hidden_inputs_expire'] to
	 * something higher to avoid false positives.
	 *
	 * See also: https://web.archive.org/web/20121003095610/http://tinyboard.org/docs/?p=Your_request_looks_automated
	 *
	 */

	// Number of hidden fields to generate.
	$config['spam']['hidden_inputs_min'] = 4;
	$config['spam']['hidden_inputs_max'] = 12;

	// How many times can a "hash" be used to post?
	$config['spam']['hidden_inputs_max_pass'] = 12;

	// How soon after regeneration do hashes expire (in seconds)?
	$config['spam']['hidden_inputs_expire'] = 60 * 60 * 3; // three hours

	// Whether to use Unicode characters in hidden input names and values.
	$config['spam']['unicode'] = true;

	// These are fields used to confuse the bots. Make sure they aren't actually used by Tinyboard, or it won't work.
	$config['spam']['hidden_input_names'] = array(
		'user',
		'username',
		'login',
		'search',
		'q',
		'url',
		'firstname',
		'lastname',
		'text',
		'message'
	);

	// Always update this when adding new valid fields to the post form, or EVERYTHING WILL BE DETECTED AS SPAM!
	$config['spam']['valid_inputs'] = array(
		'captype',
		'hash',
		'board',
		'thread',
		'mod',
		'name',
		'email',
		'subject',
		'post',
		'body',
		'pswrd',
		'sticky',
		'lock',
		'raw',
		'embed',
		'g-recaptcha-response',
		'captcha_cookie',
		'captcha_text',
		'spoiler',
		'page',
		'file_url',
		'json_response',
		'user_flag',
		'no_country',
		'tag'
	);

	// Enable reCaptcha to make spam even harder. Rarely necessary.
	$config['recaptcha'] = false;
	// - currently not set up - Enable captchouli to make spam even harder.
	$config['captchouli'] = false;

	// ReCaptcha flood bypass.
	// Use to set a captcha for floods
	// If both true defaults to recaptcha
	$config['flood_recaptcha'] = false;
	// Captchouli flood bypass.
	// Use to set a captcha for floods
	// If both true defaults to recaptcha
	$config['flood_captchouli'] = false;

	//where to request captcha
	$config['captchouli_addr'] = '127.0.0.1:8512/captcha';

	// Public and private key pair from https://www.google.com/recaptcha/admin/create
	$config['recaptcha_public'] = '6LcXTcUSAAAAAKBxyFWIt2SO8jwx4W7wcSMRoN3f';
	$config['recaptcha_private'] = '6LcXTcUSAAAAAOGVbVdhmEM1_SyRF4xTKe8jbzf_';

	// Enable Custom Captcha you need to change a couple of settings
	// Read more at: /captcha/instructions.md
	 $config['captcha'] = array();

	// Enable custom captcha provider
	$config['captcha']['enabled'] = false;

	// New thread captcha
 	// Require solving a captcha to post a thread.
 	// Default off.
 	 $config['new_thread_capt'] = false;

	// Custom captcha get provider path (if not working get the absolute path aka your url.)
	$config['captcha']['provider_get'] = '../inc/captcha/entrypoint.php';
	// Custom captcha check provider path
	$config['captcha']['provider_check'] = '../inc/captcha/entrypoint.php';

	// Custom captcha extra field (eg. charset)
	 $config['captcha']['extra'] = 'abcdefghijklmnopqrstuvwxyz';

	// Ability to lock a board for normal users and still allow mods to post.  Could also be useful for making an archive board
	// Do on a per board entry
	$config['board_locked'] = false;

	// If poster's proxy supplies X-Forwarded-For header, check if poster's real IP is banned.
	$config['proxy_check'] = false;

	// If poster's proxy supplies X-Forwarded-For header, save it for further inspection and/or filtering.
	$config['proxy_save'] = false;

	/*
	 * Custom filters detect certain posts and reject/ban accordingly. They are made up of a condition and an
	 * action (for when ALL conditions are met). As every single post has to be put through each filter,
	 * having hundreds probably isn't ideal as it could slow things down.
	 *
	 * By default, the custom filters array is populated with basic flood prevention conditions. This
	 * includes forcing users to wait at least 5 seconds between posts. To disable (or amend) these flood
	 * prevention settings, you will need to empty the $config['filters'] array first. You can do so by
	 * adding "$config['filters'] = array();" to inc/instance-config.php. Basic flood prevention used to be
	 * controlled solely by config variables such as $config['flood_time'] and $config['flood_time_ip'], and
	 * it still is, as long as you leave the relevant $config['filters'] intact. These old config variables
	 * still exist for backwards-compatability and general convenience.
	 *
	 * Read more: https://web.archive.org/web/20121003095807/http://tinyboard.org/docs/?p=Config/Flood_filters
	 * handled in: inc/filters.php
	 */

	 $config['last_resort_register_ips'] = false;

	// Minimum time between posts by the same IP address (all boards).
	$config['flood_time'] = 20;
	// Minimum time between posts by the same IP address with the same text.
	$config['flood_time_ip'] = 80;
	// Minimum time between posts with the same text. (Same content, but not always the same IP address.)
	$config['flood_time_same'] = 120;
	// Minimum time between each post on the board
	$config['flood_board_time'] = 10;
	$config['flood_board_active'] = false;

	// Time until posts held for captcha verification expire
	$config['captcha_flood_hold_time'] = 121;

	// Decide the result of a flood detection
	// possible: 'reject', 'ban' or 'captcha'
	// Decide the result of a flood detection
	// possible: 'reject', 'ban' or 'captcha'
	$config['flood_reject'] = 'reject';
	$config['flood_ban'] = 'ban'; // note: bans require a reason and a time. Search below for examples
	$config['flood_captcha'] = 'captcha';

$config['filters'][] = array(
	'condition' => array(
		'agent' => array(
			"Mozilla/5.0 (Linux; Android 8.0.0; BND-L24) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Mobile Safari/537.36",
			"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:56.0) Gecko/20100101 Firefox/79.0",
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:56.0) Gecko/20100101 Firefox/56.0"
		)
	),
	'action' => 'ban',
	'add_note' => true,
	'all_boards' => true,
	'expires' =>  0,
	'reason' => 'VPN/Proxy/Tor (Auto)'
);
$config['filters'][] = array(
	'condition' => array(
		'rdns' => "/(?i)(server|cloud|tor\-exit|vps|ovh|vpn)/"
	),
	'action' => 'ban',
	'add_note' => true,
	'all_boards' => true,
	'expires' =>  0,
	'reason' => 'VPN/Proxy/Tor (RDNS)'
);


	// link shortener match
	// $config['filters'][] = array(
	//         'condition' => array(
	// 			'strpos-list-match-body' =>
	// 			[
	//
	// 			]
	// 			),
	// 		  'action' => 'reject',
	// 		  'message' => &$config['error']['spam']
	// 		);

//🎲 Rolled 1F3B2
	$config['filters'][] = array(
	        'condition' => array(
				'raw-body' =>	"/^&#127922; Rolled .+?= [0-9]+$/u"
				),
			  'action' => 'reject',
			  'message' => &$config['error']['dice']
			);
	// link shortener match
	$config['filters'][] = array(
	        'condition' => array(
				'list-match-body' =>
				[
					"/\b0rz\.tw\b/i",
					"/\b1-url\.net\b/i",
					"/\b126\.am\b/i",
					"/\b1link\.in\b/i",
					"/\b1tk\.us\b/i",
					"/\b1un\.fr\b/i",
					"/\b1url\.com\b/i",
					"/\b1url\.cz\b/i",
					"/\b1wb2\.net\b/i",
					"/\b23o\.net\b/i",
					"/\b2ad\.in\b/i",
					"/\b2big\.at\b/i",
					"/\b2doc\.net\b/i",
					"/\b2fear\.com\b/i",
					"/\b2pl\.us\b/i",
					"/\b2tu\.us\b/i",
					"/\b2ty\.in\b/i",
					"/\b2ya\.com\b/i",
					"/\b3ra\.be\b/i",
					"/\b3x\.si\b/i",
					"/\b4i\.ae\b/i",
					"/\b4url\.cc\b/i",
					"/\b4view\.me\b/i",
					"/\b5em\.cz\b/i",
					"/\b5url\.net\b/i",
					"/\b5z8\.info\b/i",
					"/\b6fr\.ru\b/i",
					"/\b6g6\.eu\b/i",
					"/\b6url\.com\b/i",
					"/\b76\.gd\b/i",
					"/\b77\.ai\b/i",
					"/\b7fth\.cc\b/i",
					"/\b7li\.in\b/i",
					"/\b7vd\.cn\b/i",
					"/\b8u\.cz\b/i",
					"/\b944\.la\b/i",
					"/\b98\.to\b/i",
					"/\b9qr\.de\b/i",
					"/\bAltURL\.com\b/i",
					"/\bBudURL\.com\b/i",
					"/\bBuff\.ly\b/i",
					"/\bBurnURL\.com\b/i",
					"/\bbiturl\.top\b/i",
					"/\bC-O\.IN\b/i",
					"/\bClickMeter\.com\b/i",
					"/\bDecentURL\.com\b/i",
					"/\bDigBig\.com\b/i",
					"/\bDigg\.com\b/i",
					"/\bDwarfURL\.com\b/i",
					"/\bEasyURI\.com\b/i",
					"/\bEasyURL\.net\b/i",
					"/\bEsyURL\.com\b/i",
					"/\bFhurl\.com\b/i",
					"/\bFly2\.ws\b/i",
					"/\bGoWat\.ch\b/i",
					"/\bHurl\.it\b/i",
					"/\bIsCool\.net\b/i",
					"/\bJust\.as\b/i",
					"/\bL9\.fr\b/i",
					"/\bLvvk\.com\b/i",
					"/\bMyURL\.in\b/i",
					"/\bPiURL\.com\b/i",
					"/\bProfile\.to\b/i",
					"/\bQLNK\.net\b/i",
					"/\bQuip-Art\.com\b/i",
					"/\bRedirX\.com\b/i",
					"/\bSharein\.com\b/i",
					"/\bShortLinks.co.uk\b/i",
					"/\bShrinkify\.com\b/i",
					"/\bSimURL\.com\b/i",
					"/\bStartURL\.com\b/i",
					"/\bTightURL\.com\b/i",
					"/\bTnij\.org\b/i",
					"/\bTo8\.cc\b/i",
					"/\bTraceURL\.com\b/i",
					"/\bURL\.ie\b/i",
					"/\bURLHawk\.com\b/i",
					"/\bWapURL.co.uk\b/i",
					"/\bXeeURL\.com\b/i",
					"/\bYep\.it\b/i",
					"/\ba0\.fr\b/i",
					"/\ba2a\.me\b/i",
					"/\babbr\.sk\b/i",
					"/\babbrr\.com\b/i",
					"/\bad-med\.cz\b/i",
					"/\bad5\.eu\b/i",
					"/\bad7\.biz\b/i",
					"/\badb\.ug\b/i",
					"/\badf\.ly\b/i",
					"/\badfa\.st\b/i",
					"/\badfly\.fr\b/i",
					"/\badfoc\.us\b/i",
					"/\badjix\.com\b/i",
					"/\badli\.pw\b/i",
					"/\badmy\.link\b/i",
					"/\badv\.li\b/i",
					"/\bajn\.me\b/i",
					"/\baka\.gr\b/i",
					"/\bal\.ly\b/i",
					"/\balil\.in\b/i",
					"/\bany\.gs\b/i",
					"/\baqva\.pl\b/i",
					"/\bares\.tl\b/i",
					"/\basso\.in\b/i",
					"/\batu\.ca\b/i",
					"/\bau\.ms\b/i",
					"/\bayt\.fr\b/i",
					"/\bazali\.fr\b/i",
					"/\bb00\.fr\b/i",
					"/\bb23\.ru\b/i",
					"/\bb54\.in\b/i",
					"/\bbacn\.me\b/i",
					"/\bbaid\.us\b/i",
					"/\bbc\.vc\b/i",
					"/\bbee4\.biz\b/i",
					"/\bbim\.im\b/i",
					"/\bbit\.do\b/i",
					"/\bbit\.ly\b/i",
					"/\bbitw\.in\b/i",
					"/\bbkite\.com\b/i",
					"/\bblap\.net\b/i",
					"/\bble\.pl\b/i",
					"/\bblip\.tv\b/i",
					"/\bbloat\.me\b/i",
					"/\bboi\.re\b/i",
					"/\bbote\.me\b/i",
					"/\bbougn\.at\b/i",
					"/\bbr4\.in\b/i",
					"/\bbrk\.to\b/i",
					"/\bbrzu\.net\b/i",
					"/\bbudurl\.com\b/i",
					"/\bbuk\.me\b/i",
					"/\bbul\.lu\b/i",
					"/\bbxl\.me\b/i",
					"/\bbzh\.me\b/i",
					"/\bcachor\.ro\b/i",
					"/\bcaptur\.in\b/i",
					"/\bcatchylink\.com\b/i",
					"/\bcbs\.so\b/i",
					"/\bcbug\.cc\b/i",
					"/\bcc\.cc\b/i",
					"/\bccj\.im\b/i",
					"/\bcf\.ly\b/i",
					"/\bcf2\.me\b/i",
					"/\bcf6\.co\b/i",
					"/\bchilp\.it\b/i",
					"/\bcjb\.net\b/i",
					"/\bclck\.ru\b/i",
					"/\bcli\.gs\b/i",
					"/\bclikk\.in\b/i",
					"/\bcn86\.org\b/i",
					"/\bcoinurl\.com\b/i",
					"/\bcort\.as\b/i",
					"/\bcouic\.fr\b/i",
					"/\bcr\.tl\b/i",
					"/\bcudder\.it\b/i",
					"/\bcur\.lv\b/i",
					"/\bcurl\.im\b/i",
					"/\bcut\.pe\b/i",
					"/\bcut\.sk\b/i",
					"/\bcutt\.eu\b/i",
					"/\bcutt\.us\b/i",
					"/\bcutu\.me\b/i",
					"/\bcuturl\.com\b/i",
					"/\bcybr\.fr\b/i",
					"/\bcyonix\.to\b/i",
					"/\bd75\.eu\b/i",
					"/\bdaa\.pl\b/i",
					"/\bdai\.ly\b/i",
					"/\bdd\.ma\b/i",
					"/\bddp\.net\b/i",
					"/\bdecenturl\.com\b/i",
					"/\bdfl8\.me\b/i",
					"/\bdft\.ba\b/i",
					"/\bdoiop\.com\b/i",
					"/\bdolp\.cc\b/i",
					"/\bdopice\.sk\b/i",
					"/\bdroid\.ws\b/i",
					"/\bdv\.gd\b/i",
					"/\bdy\.fi\b/i",
					"/\bdyo\.gs\b/i",
					"/\be37\.eu\b/i",
					"/\becra\.se\b/i",
					"/\beepurl\.com\b/i",
					"/\bely\.re\b/i",
					"/\berax\.cz\b/i",
					"/\berw\.cz\b/i",
					"/\bewerl\.com\b/i",
					"/\bex9\.co\b/i",
					"/\bezurl\.cc\b/i",
					"/\bff\.im\b/i",
					"/\bfff\.re\b/i",
					"/\bfff\.to\b/i",
					"/\bfff\.wf\b/i",
					"/\bfilz\.fr\b/i",
					"/\bfire\.to\b/i",
					"/\bfirsturl\.de\b/i",
					"/\bflic\.kr\b/i",
					"/\bfly2\.ws\b/i",
					"/\bfnk\.es\b/i",
					"/\bfoe\.hn\b/i",
					"/\bfolu\.me\b/i",
					"/\bfon\.gs\b/i",
					"/\bfreze\.it\b/i",
					"/\bfur\.ly\b/i",
					"/\bfwd4\.me\b/i",
					"/\bg00\.me\b/i",
					"/\bgg\.gg\b/i",
					"/\bgit\.io\b/i",
					"/\bgl\.am\b/i",
					"/\bgo2\.me\b/i",
					"/\bgo2cut\.com\b/i",
					"/\bgoo\.gl\b/i",
					"/\bgoo\.lu\b/i",
					"/\bgood\.ly\b/i",
					"/\bgoshrink\.com\b/i",
					"/\bgrem\.io\b/i",
					"/\bgri\.ms\b/i",
					"/\bguiama\.is\b/i",
					"/\bgurl\.es\b/i",
					"/\bhadej\.co\b/i",
					"/\bhec\.su\b/i",
					"/\bhellotxt\.com\b/i",
					"/\bhex\.io\b/i",
					"/\bhide\.my\b/i",
					"/\bhjkl\.fr\b/i",
					"/\bhops\.me\b/i",
					"/\bhover\.com\b/i",
					"/\bhref\.in\b/i",
					"/\bhref\.li\b/i",
					"/\bht\.ly\b/i",
					"/\bhtxt\.it\b/i",
					"/\bhugeurl\.com\b/i",
					"/\bhurl\.me\b/i",
					"/\bhurl\.ws\b/i",
					"/\bi-2\.co\b/i",
					"/\bi99\.cz\b/i",
					"/\bicanhaz\.com\b/i",
					"/\bicit\.fr\b/i",
					"/\bick\.li\b/i",
					"/\bicks\.ro\b/i",
					"/\bidek\.net\b/i",
					"/\biiiii\.in\b/i",
					"/\biky\.fr\b/i",
					"/\bilix\.in\b/i",
					"/\binfo\.ms\b/i",
					"/\binreply\.to\b/i",
					"/\bis\.gd\b/i",
					"/\bisra\.li\b/i",
					"/\biterasi\.net\b/i",
					"/\bitm\.im\b/i",
					"/\bity\.im\b/i",
					"/\bix\.sk\b/i",
					"/\bjdem\.cz\b/i",
					"/\bjieb\.be\b/i",
					"/\bjijr\.com\b/i",
					"/\bjmp2\.net\b/i",
					"/\bjp22\.net\b/i",
					"/\bjqw\.de\b/i",
					"/\bkask\.us\b/i",
					"/\bkd2\.org\b/i",
					"/\bkfd\.pl\b/i",
					"/\bkissa\.be\b/i",
					"/\bkl\.am\b/i",
					"/\bklck\.me\b/i",
					"/\bkorta\.nu\b/i",
					"/\bkr3w\.de\b/i",
					"/\bkrat\.si\b/i",
					"/\bkratsi\.cz\b/i",
					"/\bkrod\.cz\b/i",
					"/\bkrunchd\.com\b/i",
					"/\bkuc\.cz\b/i",
					"/\bkxb\.me\b/i",
					"/\bl-k\.be\b/i",
					"/\blc-s\.co\b/i",
					"/\blc\.cx\b/i",
					"/\blcut\.in\b/i",
					"/\blibero\.it\b/i",
					"/\blick\.my\b/i",
					"/\blien\.li\b/i",
					"/\blien\.pl\b/i",
					"/\bliip\.to\b/i",
					"/\bliltext\.com\b/i",
					"/\blin\.cr\b/i",
					"/\blin\.io\b/i",
					"/\blinkbee\.com\b/i",
					"/\blinkbun\.ch\b/i",
					"/\blinkn\.co\b/i",
					"/\bliurl\.cn\b/i",
					"/\bllu\.ch\b/i",
					"/\bln-s\.net\b/i",
					"/\bln-s\.ru\b/i",
					"/\blnk\.co\b/i",
					"/\blnk\.gd\b/i",
					"/\blnk\.in\b/i",
					"/\blnk\.ly\b/i",
					"/\blnk\.sk\b/i",
					"/\blnked\.in\b/i",
					"/\blnks\.fr\b/i",
					"/\blnky\.fr\b/i",
					"/\blnp\.sn\b/i",
					"/\bloopt\.us\b/i",
					"/\blp25\.fr\b/i",
					"/\blru\.jp\b/i",
					"/\blt\.tl\b/i",
					"/\blurl\.no\b/i",
					"/\blynk\.my\b/i",
					"/\bm1p\.fr\b/i",
					"/\bm3mi\.com\b/i",
					"/\bmake\.my\b/i",
					"/\bmcaf\.ee\b/i",
					"/\bmdl29\.net\b/i",
					"/\bmetamark\.net\b/i",
					"/\bmic\.fr\b/i",
					"/\bmigre\.me\b/i",
					"/\bminilien\.com\b/i",
					"/\bminiurl\.com\b/i",
					"/\bminu\.me\b/i",
					"/\bminurl\.fr\b/i",
					"/\bmoourl\.com\b/i",
					"/\bmore\.sh\b/i",
					"/\bmut\.lu\b/i",
					"/\bmyurl\.in\b/i",
					"/\bne1\.net\b/i",
					"/\bnet\.ms\b/i",
					"/\bnet46\.net\b/i",
					"/\bnicou\.ch\b/i",
					"/\bnig\.gr\b/i",
					"/\bniny\.io\b/i",
					"/\bnjx\.me\b/i",
					"/\bnn\.nf\b/i",
					"/\bnotlong\.com\b/i",
					"/\bnov\.io\b/i",
					"/\bnq\.st\b/i",
					"/\bnsfw\.in\b/i",
					"/\bnxy\.in\b/i",
					"/\bo-x\.fr\b/i",
					"/\bokok\.fr\b/i",
					"/\bom\.ly\b/i",
					"/\bou\.af\b/i",
					"/\bou\.gd\b/i",
					"/\boua\.be\b/i",
					"/\bouo\.io\b/i",
					"/\bow\.ly\b/i",
					"/\bpara\.pt\b/i",
					"/\bparky\.tv\b/i",
					"/\bpast\.is\b/i",
					"/\bpd\.am\b/i",
					"/\bpdh\.co\b/i",
					"/\bph\.dog\b/i",
					"/\bph\.ly\b/i",
					"/\bpic\.gd\b/i",
					"/\bpich\.in\b/i",
					"/\bpin\.st\b/i",
					"/\bping\.fm\b/i",
					"/\bplots\.fr\b/i",
					"/\bpm\.wu\.cz\b/i",
					"/\bpnt\.me\b/i",
					"/\bpo\.st\b/i",
					"/\bpoprl\.com\b/i",
					"/\bpost\.ly\b/i",
					"/\bposted\.at\b/i",
					"/\bppfr\.it\b/i",
					"/\bppst\.me\b/i",
					"/\bppt\.cc\b/i",
					"/\bppt\.li\b/i",
					"/\bprejit\.cz\b/i",
					"/\bptab\.it\b/i",
					"/\bptm\.ro\b/i",
					"/\bpw2\.ro\b/i",
					"/\bpy6\.ru\b/i",
					"/\bqbn\.ru\b/i",
					"/\bqicute\.com\b/i",
					"/\bqqc\.co\b/i",
					"/\bqr\.net\b/i",
					"/\bqrtag\.fr\b/i",
					"/\bqxp\.cz\b/i",
					"/\bqxp\.sk\b/i",
					"/\brb6\.co\b/i",
					"/\brb6\.me\b/i",
					"/\brcknr\.io\b/i",
					"/\brdz\.me\b/i",
					"/\bredir\.ec\b/i",
					"/\bredir\.fr\b/i",
					"/\bredu\.it\b/i",
					"/\bref\.so\b/i",
					"/\breise\.lc\b/i",
					"/\brelink\.fr\b/i",
					"/\bri\.ms\b/i",
					"/\brickroll\.it\b/i",
					"/\briz\.cz\b/i",
					"/\briz\.gd\b/i",
					"/\brod\.gs\b/i",
					"/\broflc\.at\b/i",
					"/\brsmonkey\.com\b/i",
					"/\brt\.se\b/i",
					"/\brt\.tc\b/i",
					"/\bru\.ly\b/i",
					"/\brubyurl\.com\b/i",
					"/\bs-url\.fr\b/i",
					"/\bs7y\.us\b/i",
					"/\bsafe\.mn\b/i",
					"/\bsagyap\.tk\b/i",
					"/\bsdu\.sk\b/i",
					"/\bseeme\.at\b/i",
					"/\bsegue\.se\b/i",
					"/\bsh\.st\b/i",
					"/\bshar\.as\b/i",
					"/\bsharetabs\.com\b/i",
					"/\bshorl\.com\b/i",
					"/\bshort\.cc\b/i",
					"/\bshort\.ie\b/i",
					"/\bshort\.nr\b/i",
					"/\bshort\.pk\b/i",
					"/\bshort\.to\b/i",
					"/\bshorte\.st\b/i",
					"/\bshortna\.me\b/i",
					"/\bshoturl\.us\b/i",
					"/\bshrinkee\.com\b/i",
					"/\bshrinkster\.com\b/i",
					"/\bshrinkurl\.in\b/i",
					"/\bshrt\.in\b/i",
					"/\bshrt\.st\b/i",
					"/\bshrten\.com\b/i",
					"/\bshrunkin\.com\b/i",
					"/\bshw\.me\b/i",
					"/\bshy\.si\b/i",
					"/\bsicax\.net\b/i",
					"/\bsina\.lt\b/i",
					"/\bsk\.gy\b/i",
					"/\bskr\.sk\b/i",
					"/\bskroc\.pl\b/i",
					"/\bsmll\.co\b/i",
					"/\bsn\.im\b/i",
					"/\bsn\.vc\b/i",
					"/\bsnipr\.com\b/i",
					"/\bsnipurl\.com\b/i",
					"/\bsnsw\.us\b/i",
					"/\bsnurl\.com\b/i",
					"/\bsoo\.gd\b/i",
					"/\bsp2\.ro\b/i",
					"/\bspedr\.com\b/i",
					"/\bspn\.sr\b/i",
					"/\bsptfy\.com\b/i",
					"/\bsq6\.ru\b/i",
					"/\bsqrl\.it\b/i",
					"/\bssl\.gs\b/i",
					"/\bsturly\.com\b/i",
					"/\bsu\.pr\b/i",
					"/\bsurl\.me\b/i",
					"/\bsux\.cz\b/i",
					"/\bsy\.pe\b/i",
					"/\bta\.gd\b/i",
					"/\btabzi\.com\b/i",
					"/\btau\.pe\b/i",
					"/\btcrn\.ch\b/i",
					"/\btdjt\.cz\b/i",
					"/\bthesa\.us\b/i",
					"/\bthinfi\.com\b/i",
					"/\bthrdl\.es\b/i",
					"/\btin\.li\b/i",
					"/\btini\.cc\b/i",
					"/\btiny\.cc\b/i",
					"/\btiny\.lt\b/i",
					"/\btiny\.ms\b/i",
					"/\btiny\.pl\b/i",
					"/\btiny123\.com\b/i",
					"/\btinyarro\.ws\b/i",
					"/\btinytw\.it\b/i",
					"/\btinyuri\.ca\b/i",
					"/\btinyurl\.[a-z]+\b/i",
					"/\btinyvid\.io\b/i",
					"/\btixsu\.com\b/i",
					"/\btldr\.sk\b/i",
					"/\btldrify\.com\b/i",
					"/\btllg\.net\b/i",
					"/\btnij\.org\b/i",
					"/\btny\.cz\b/i",
					"/\btny\.im\b/i",
					"/\bto\.ly\b/i",
					"/\btogoto\.us\b/i",
					"/\btohle\.de\b/i",
					"/\btpmr\.com\b/i",
					"/\btr\.im\b/i",
					"/\btr\.my\b/i",
					"/\btr5\.in\b/i",
					"/\btrck\.me\b/i",
					"/\btrick\.ly\b/i",
					"/\btrkr\.ws\b/i",
					"/\btrunc\.it\b/i",
					"/\bturo\.us\b/i",
					"/\btweetburner\.com\b/i",
					"/\btwet\.fr\b/i",
					"/\btwi\.im\b/i",
					"/\btwirl\.at\b/i",
					"/\btwit\.ac\b/i",
					"/\btwitterpan\.com\b/i",
					"/\btwitthis\.com\b/i",
					"/\btwiturl\.de\b/i",
					"/\btwlr\.me\b/i",
					"/\btwurl\.cc\b/i",
					"/\btwurl\.nl\b/i",
					"/\bu6e\.de\b/i",
					"/\bub0\.cc\b/i",
					"/\buby\.es\b/i",
					"/\bucam\.me\b/i",
					"/\bug\.cz\b/i",
					"/\bulmt\.in\b/i",
					"/\bunlc\.us\b/i",
					"/\bupdating\.me\b/i",
					"/\bupzat\.com\b/i",
					"/\bur1\.ca\b/i",
					"/\burl.co.uk\b/i",
					"/\burl2\.fr\b/i",
					"/\burl4\.eu\b/i",
					"/\burl5\.org\b/i",
					"/\burlao\.com\b/i",
					"/\burlbrief\.com\b/i",
					"/\burlcover\.com\b/i",
					"/\burlcut\.com\b/i",
					"/\burlenco\.de\b/i",
					"/\burlin\.it\b/i",
					"/\burlkiss\.com\b/i",
					"/\burlkr\.com\b/i",
					"/\burlot\.com\b/i",
					"/\burlpire\.com\b/i",
					"/\burls\.fr\b/i",
					"/\burlx\.ie\b/i",
					"/\burlx\.org\b/i",
					"/\burlz\.fr\b/i",
					"/\burlzs\.com\b/i",
					"/\burlzen\.com\b/i",
					"/\burub\.us\b/i",
					"/\butfg\.sk\b/i",
					"/\bv5\.gd\b/i",
					"/\bvaaa\.fr\b/i",
					"/\bvalv\.im\b/i",
					"/\bvaza\.me\b/i",
					"/\bvbly\.us\b/i",
					"/\bvd55\.com\b/i",
					"/\bverd\.in\b/i",
					"/\bvgn\.me\b/i",
					"/\bvirl\.com\b/i",
					"/\bvl\.am\b/i",
					"/\bvov\.li\b/i",
					"/\bvsll\.eu\b/i",
					"/\bvt802\.us\b/i",
					"/\bvur\.me\b/i",
					"/\bvv\.vg\b/i",
					"/\bw1p\.fr\b/i",
					"/\bw3t\.org\b/i",
					"/\bwaa\.ai\b/i",
					"/\bwb1\.eu\b/i",
					"/\bweb99\.eu\b/i",
					"/\bwed\.li\b/i",
					"/\bwideo\.fr\b/i",
					"/\bwipi\.es\b/i",
					"/\bwp\.me\b/i",
					"/\bwtc\.la\b/i",
					"/\bwu\.cz\b/i",
					"/\bww7\.fr\b/i",
					"/\bwwy\.me\b/i",
					"/\bx10\.mx\b/i",
					"/\bx2c\.eu\b/i",
					"/\bx2c\.eumx\b/i",
					"/\bxaddr\.com\b/i",
					"/\bxav\.cc\b/i",
					"/\bxgd\.in\b/i",
					"/\bxib\.me\b/i",
					"/\bxl8\.eu\b/i",
					"/\bxoe\.cz\b/i",
					"/\bxr\.com\b/i",
					"/\bxrl\.in\b/i",
					"/\bxrl\.us\b/i",
					"/\bxt3\.me\b/i",
					"/\bxua\.me\b/i",
					"/\bxub\.me\b/i",
					"/\bxurl\.jp\b/i",
					"/\bxurls\.co\b/i",
					"/\bxzb\.cc\b/i",
					"/\by2u\.be\b/i",
					"/\byagoa\.fr\b/i",
					"/\byagoa\.me\b/i",
					"/\byau\.sh\b/i",
					"/\byeca\.eu\b/i",
					"/\byect\.com\b/i",
					"/\byep\.it\b/i",
					"/\byfrog\.com\b/i",
					"/\byogh\.me\b/i",
					"/\byon\.ir\b/i",
					"/\byoufap\.me\b/i",
					"/\bysear\.ch\b/i",
					"/\byweb\.com\b/i",
					"/\byyv\.co\b/i",
					"/\bz9\.fr\b/i",
					"/\bzSMS\.net\b/i",
					"/\bzapit\.nu\b/i",
					"/\bzeek\.ir\b/i",
					"/\bzi\.ma\b/i",
					"/\bzi\.pe\b/i",
					"/\bzipmyurl\.com\b/i",
					"/\bzkr\.cz\b/i",
					"/\bzkrat\.me\b/i",
					"/\bzkrt\.cz\b/i",
					"/\bzoodl\.com\b/i",
					"/\bzpag\.es\b/i",
					"/\bzti\.me\b/i",
					"/\bzxq\.net\b/i",
					"/\bzyva\.org\b/i",
					"/\bzz\.gd\b/i",
					"/\bzzb.bz/i",

					"/\b2\.gp\b/i",
					"/\b2\.ht\b/i",
					"/\b7\.ly\b/i",
					"/\ba\.co\b/i",
					"/\ba\.gg\b/i",
					"/\ba\.nf\b/i",
					"/\bfa\.b\b/i",
					"/\bj\.gs\b/i",
					"/\bj\.mp\b/i",
					"/\bl\.gg\b/i",
					"/\bp\.pw\b/i",
					"/\bq\.gs\b/i",
					"/\bs\.id\b/i",
					"/\bt\.cn\b/i",
					"/\bt\.co\b/i",
					"/\bu\.nu\b/i",
					"/\bu\.to\b/i",
					"/\bv\.gd\b/i",
					"/\bv\.ht\b/i",
					"/\bx\.co\b/i",
					"/\bx\.nu\b/i",
					"/\bx\.se\b/i",
					"/(^|[^a-zA-Z0-9\-\._~])zip\.net\b/i",

					'/\bapg\.de\b/i',
					'/\bpgg\.link\b/i',
					'/\bcutt\.ly\b/i',
					'/\bgg\.gg\b/i',
					'/\blinkr\.uk\b/i',

					"/\brb\.gy\b/i",
					"/\bbitlylink\.com\b/i",
					"/\brocketlink\.io\b/i",
					"/\bt2mio\.com\b/i",
					"/\bdelivr\.com\b/i",
					"/\blnnkin\.com\b/i",
					"/\bshorten\.rest\b/i",
					"/\boe\.cd\b/i",
					"/\bshorturl\.[a-z]+\b/i",
					"/\byt\.vu\b/i",
					"/\bshlink\.io\b/i",
					"/\bpixelme\.me\b/i",
					"/\bbitly\.[a-z]+\b/i",
					"/\bqr-creator\.com\b/i",
					"/\bprettylinks\.com\b/i",
					"/\bgolinks\.io\b/i",
					"/\bai6\.net\b/i",
					"/\bumuly\.com\b/i",
					"/\bshortlink\.net\b/i",
					"/\goo\.su\b/i"
				]
				),
			  'action' => 'reject',
			  'message' => &$config['error']['spam']
			);



			// link shortener match
			$config['filters'][] = array(
			        'condition' => array(
						'strpos-list-match-body' =>
						[
							"tranny"
						]
						),
					  'action' => 'reject',
					  'message' => &$config['error']['spam']
					);

$config['filters'][] = array(
        'condition' => array(
			'body' => '/ロリータキッズ/',
			),
		  'action' => 'reject',
		  'message' => &$config['error']['spam']
		);

$config['filters'][] = array(
        'condition' => array(
			'email' => '/Lolita/',
			),
		  'action' => 'reject',
		  'message' => &$config['error']['spam']
		);

// $config['filters'][] = array(
// 	'condition' => array(
// 		'flood-match' => array('ip'),
// 		'flood-time' => 60 * 60, // 1 hour
// 		'flood-count' => 30, // 31 Threads
// 		'OP' => true
// 	),
// 	'action' => 'reject',
// 	'message' => 'Maximum number of threads for the hour (8).'
// );

/* Captcha cases bellow here*/

	// Minimum time between posts by the same IP address (all boards).
	$config['filters'][] = array(
		'condition' => array(
			'flood-match' => array('ip'), // Only match IP address
			'flood-time' => &$config['flood_time']
		),
		'action' => $config['flood_captcha'],
		'message' => &$config['error']['flood']['ip']
	);

	// Minimum time between posts by the same IP address with the same text.
	$config['filters'][] = array(
		'condition' => array(
			'flood-match' => array('ip', 'body'), // Match IP address and post body
			'flood-time' => &$config['flood_time_ip'],
			'!body' => '/^$/', // Post body is NOT empty
		),
		'action' => $config['flood_captcha'],
		'message' => &$config['error']['flood']['repeat']
	);

	// Minimum time between posts with the same text. (Same content, but not always the same IP address.)
	$config['filters'][] = array(
		'condition' => array(
			'flood-match' => array('body'), // Match only post body
			'flood-time' => &$config['flood_time_same']
		),
		'action' => $config['flood_captcha'],
		'message' => &$config['error']['flood']['repeat']
	);

	// Minimum time between each post on the board
	$config['filters'][] = array(
		'condition' => array(
			'flood-match' => array('board'), // Match anything
			'flood-time' => &$config['flood_board_time']
		),
		'action' => $config['flood_captcha'],
		'message' => &$config['error']['flood']['fast']
	);

	// Example: Minimum time between posts with the same file hash.
	// $config['filters'][] = array(
	// 	'condition' => array(
	// 		'flood-match' => array('file'), // Match file hash
	// 		'flood-time' => 60 * 2 // 2 minutes minimum
	// 	),
	// 	'action' => 'reject',
	// 	'message' => &$config['error']['flood']
	// );

	// Example: Use the "flood-count" condition to only match if the user has made at least two posts with
	// the same content and IP address in the past 2 minutes.
	// $config['filters'][] = array(
	// 	'condition' => array(
	// 		'flood-match' => array('ip', 'body'), // Match IP address and post body
	// 		'flood-time' => 60 * 2, // 2 minutes
	// 		'flood-count' => 2 // At least two recent posts
	// 	),
	// 	'!body' => '/^$/',
	// 	'action' => 'reject',
	// 	'message' => &$config['error']['flood']
	// );

	// Example: Blocking an imaginary known spammer, who keeps posting a reply with the name "surgeon",
	// ending his posts with "regards, the surgeon" or similar.
	// $config['filters'][] = array(
	// 	'condition' => array(
	// 		'name' => '/^surgeon$/',
	// 		'body' => '/regards,\s+(the )?surgeon$/i',
	// 		'OP' => false
	// 	),
	// 	'action' => 'reject',
	// 	'message' => 'Go away, spammer.'
	// );

	// Example: Same as above, but issuing a 3-hour ban instead of just reject the post and
	// add an IP note with the message body
	// $config['filters'][] = array(
	// 	'condition' => array(
	// 		'name' => '/^surgeon$/',
	// 		'body' => '/regards,\s+(the )?surgeon$/i',
	// 		'OP' => false
	// 	),
	// 	'action' => 'ban',
	//	'add_note' => true,
	// 	'expires' => 60 * 60 * 3, // 3 hours
	// 	'reason' => 'Go away, spammer.'
	// );

	// Example: PHP 5.3+ (anonymous functions)
	// There is also a "custom" condition, making the possibilities of this feature pretty much endless.
	// This is a bad example, because there is already a "name" condition built-in.
	// $config['filters'][] = array(
	// 	'condition' => array(
	// 		'body' => '/h$/i',
	// 		'OP' => false,
	// 		'custom' => function($post) {
	// 			if($post['name'] == 'Anonymous')
	// 				return true;
	// 			else
	// 				return false;
	// 		}
	// 	),
	// 	'action' => 'reject'
	// );

	// Filter flood prevention conditions ("flood-match") depend on a table which contains a cache of recent
	// posts across all boards. This table is automatically purged of older posts, determining the maximum
	// "age" by looking at each filter. However, when determining the maximum age, Tinyboard does not look
	// outside the current board. This means that if you have a special flood condition for a specific board
	// (contained in a board configuration file) which has a flood-time greater than any of those in the
	// global configuration, you need to set the following variable to the maximum flood-time condition value.
	// $config['flood_cache'] = 60 * 60 * 24; // 24 hours

/*
 * ====================
 *  Post settings
 * ====================
 */

	// Do you need a body for your reply posts?
	$config['force_body'] = false;
	// Do you need a body for new threads?
	$config['force_body_op'] = true;
	// Require an image for threads?
	$config['force_image_op'] = true;
	// Strip superfluous new lines at the end of a post.
	$config['strip_superfluous_returns'] = true;
	// Strip combining characters from Unicode strings (eg. "Zalgo").
	$config['strip_combining_chars'] = true;

	// A regex pattern that referers must match.
	// Prevents. posts originating from fishy locations.
	$config['referer_match'] = false;

	// Maximum post body length.
	$config['max_body'] = 1800;
	// Maximum number of post body lines to show on the index page.
	$config['body_truncate'] = 15;
	// Maximum number of characters to show on the index page.
	$config['body_truncate_char'] = 2500;

	// Typically spambots try to post many links. Refuse a post with X links?
	$config['max_links'] = 20;
	// Maximum number of cites per post (prevents abuse, as more citations mean more database queries).
	$config['max_cites'] = 45;
	$config['max_newlines'] = 50;
	// Maximum number of cross-board links/citations per post.
	$config['max_cross'] = $config['max_cites'];

	// Track post citations (>>XX). Rebuilds posts after a cited post is deleted, removing broken links.
	// Puts a little more load on the database.
	$config['track_cites'] = true;

	// Maximum filename length (will be truncated).
	$config['max_filename_len'] = 255;
	// Maximum filename length to display (the rest can be viewed upon mouseover).
	$config['max_filename_display'] = 30;
	// Swap file and unix location
	$config['file_location_swap'] = true;

	// Allow users to delete their own posts?
	$config['allow_delete'] = true;
	$config['embed_delete_url'] = "https://www.youtube.com/watch?v=G5s4-Kak49o";
	// How long after posting should you have to wait before being able to delete that post? (In seconds.)
	$config['delete_time'] = 10;
	// Reply limit (stops bumping thread when this is reached).
	$config['reply_limit'] = 151;
	// At this point cycled threads will delete older posts as new ones come in.
	$config['cycle_limit'] = 149;
	// Image hard limit (stops allowing new image replies when this is reached if not zero).
	$config['image_hard_limit'] = 150;
	// Reply hard limit (stops allowing new replies when this is reached if not zero).
	$config['reply_hard_limit'] = 0;
	$config['disable_images'] = false;


	$config['robot_enable'] = false;
	// Strip repeating characters when making hashes.
	$config['robot_strip_repeating'] = true;
	// Enabled mutes? Tinyboard uses ROBOT9000's original 2^x implementation where x is the number of times
	// you have been muted in the past.
	$config['robot_mute'] = true;
	// How long before Tinyboard forgets about a mute?
	$config['robot_mute_hour'] = 336; // 2 weeks
	// If you want to alter the algorithm a bit. Default value is 2.
	$config['robot_mute_multiplier'] = 2; // (n^x where x is the number of previous mutes)
	$config['robot_mute_descritpion'] = _('You have been muted for unoriginal content.');

	// Automatically convert things like "..." to Unicode characters ("…").
	$config['auto_unicode'] = true;
	// Whether to turn URLs into functional links.
	$config['markup_urls'] = true;

	// Optional URL prefix for links (eg. "http://anonym.to/?").
	$config['link_prefix'] = '';
	// leave alias
	$config['url_ads'] = &$config['link_prefix'];

	// Allow "uploading" images via URL as well. Users can enter the URL of the image and then Tinyboard will
	// download it. Not usually recommended.
	$config['allow_upload_by_url'] = false;
	// The timeout for the above, in seconds.
	$config['upload_by_url_timeout'] = 15;
	$config['url_upload_proxy'] = false;

	// Enable early 404? With default settings, a thread would 404 if it was to leave page 3, if it had less
	// than 3 replies.
	$config['early_404'] = false;

	$config['early_404_page'] = 3;
	$config['early_404_replies'] = 5;
	$config['early_404_staged'] = false;

	// A wordfilter (sometimes referred to as just a "filter" or "censor") automatically scans users’ posts
	// as they are submitted and changes or censors particular words or phrases.

	// For a normal string replacement:
	// $config['wordfilters'][] = array('cat', 'dog');
	// Advanced raplcement (regular expressions):
	// $config['wordfilters'][] = array('/ca[rt]/', 'dog', true); // 'true' means it's a regular expression

	// Expandable and retractable post form for more compact appearance
	$config['advanced_post_form'] = false;

	// Always act as if the user had typed "noko" into the email field.
	$config['always_noko'] = false;

	// Example: Custom tripcodes. The below example makes a tripcode of "#test123" evaluate to "!HelloWorld".
	// $config['custom_tripcode']['#test123'] = '!HelloWorld';
	// Example: Custom secure tripcode.
	// $config['custom_tripcode']['##securetrip'] = '!!somethingelse';

	// enable tripcodes from phonetic dictionary
	$config["phonetic_tripcodes"] = false;

	// Allow users to mark their image as a "spoiler" when posting. The thumbnail will be replaced with a
	// static spoiler image instead (see $config['spoiler_image']).
	$config['spoiler_images'] = false;

	// With the following, you can disable certain superfluous fields or enable "forced anonymous".

	// When true, all names will be set to $config['anonymous'].
	$config['field_disable_name'] = false;
	// When true, there will be no email field.
	$config['field_disable_email'] = false;
	// When true, there will be no subject field.
	$config['field_disable_subject'] = false;
	// When true, there will be no subject field for replies.
	$config['field_disable_reply_subject'] = false;
	// When true, a blank password will be used for files (not usable for deletion).
	$config['field_disable_password'] = false;
	// Hide password from users
	$config['field_hide_password'] = false;
	// When true, users are instead presented a selectbox for email. Contains, blank, noko and sage.
	$config['field_email_selectbox'] = false;

	// When true, the sage won't be displayed
	$config['hide_sage'] = false;

	// Don't display user's email when it's not "sage"
	$config['hide_email'] = false;


	// Attach country flags to posts.
	$config['country_flags'] = false;

	// Allow the user to decide whether or not he wants to display his country
	$config['allow_no_country'] = false;

	// Load all country flags from one file
	$config['country_flags_condensed'] = true;
	$config['country_flags_condensed_css'] = 'static/flags/flags.css';

	// Allow the user choose a /pol/-like user_flag that will be shown in the post. For the user flags, please be aware
	// that you will have to disable BOTH country_flags and contry_flags_condensed optimization (at least on a board
	// where they are enabled).
	$config['user_flag'] = false;

	// List of user_flag the user can choose. Flags must be placed in the directory set by $config['uri_flags']
	$config['user_flags'] = array();
	/* example: 
	$config['user_flags'] = array (
		'nz' => 'Nazi',
		'cm' => 'Communist',
		'eu' => 'Europe'
	);
	*/

	// Allow dice rolling: an email field of the form "dice XdY+/-Z" will result in X Y-sided dice rolled and summed,
	// with the modifier Z added, with the result displayed at the top of the post body.
	$config['allow_roll'] = false;

	// Use semantic URLs for threads, like /b/res/12345/daily-programming-thread.html
	$config['slugify'] = false;

	// Max size for slugs
	$config['slug_max_size'] = 80;

/*
* ====================
*  Ban settings
* ====================
*/

	//JSON compress ban list(Breaks chrome sometimes)
	$config['JSON_ban_compression'] = false;
	// Require users to see the ban page at least once for a ban even if it has since expired.
	$config['require_ban_view'] = true;

	// Show the post the user was banned for on the "You are banned" page.
	$config['ban_show_post'] = false;

	// Optional HTML to append to "You are banned" pages. For example, you could include instructions and/or
	// a link to an email address or IRC chat room to appeal the ban.
	$config['ban_page_extra'] = '';

	// Allow users to appeal bans through Tinyboard.
	$config['ban_appeals'] = false;

	$config['ban_evasion_cookie_time'] = 60*60*1; // 1 hour

	// Do not allow users to appeal bans that are shorter than this length (in seconds).
	$config['ban_appeals_min_length'] = 60 * 60 * 6; // 6 hours

	// How many ban appeals can be made for a single ban?
	$config['ban_appeals_max'] = 1;

	// Show moderator name on ban page.
	$config['show_modname'] = false;

/*
 * ====================
 *  Markup settings
 * ====================
 */

	// "Wiki" markup syntax ($config['wiki_markup'] in pervious versions):
	$config['markup'][] = array("/'''(.+?)'''/ims", "<strong>\$1</strong>");
	$config['markup'][] = array("/\[b\](.+?)\[\/b\]/is", "<strong>\$1</strong>");
	$config['markup'][] = array("/''(.+?)''/is", "<em>\$1</em>");
	$config['markup'][] = array("/\[e\](.+?)\[\/e\]/is", "<em>\$1</em>");
	$config['markup'][] = array("/\*\*(.+?)\*\*/is", "<span class=\"spoiler\">\$1</span>");
	$config['markup'][] = array("/\[ul\](.+?)\[\/ul\]/is", "<u>\$1</u>");
	$config['markup'][] = array("/\[spoiler\](.+?)\[\/spoiler\]/is", "<span class=\"spoiler\">\$1</span>");
	$config['markup'][] = array("/\[spoilers\](.+?)\[\/spoilers\]/is", "<span class=\"spoiler\">\$1</span>");
	$config['markup'][] = array("/==(.+?)==/is", "<span class=\"heading\">\$1</span>");
	$config['markup'][] = array("/\[header\](.+?)\[\/header\]/is", "<span class=\"heading\">\$1</span>");

	// Markup from Nen
	$config['markup'][] = array("/\[pink\](.+?)\[\/pink\]/is", "<span class=\"glowpink\">\$1</span>");
	$config['markup'][] = array("/\[blue\](.+?)\[\/blue\]/is", "<span class=\"glowblue\">\$1</span>");
	$config['markup'][] = array("/\[gold\](.+?)\[\/gold\]/is", "<span class=\"glowgold\">\$1</span>");
	$config['markup'][] = array("/\[s glowpink\](.+?)\[\/s\]/is", "<span class=\"glowpink\">\$1</span>");
	$config['markup'][] = array("/\[s glowblue\](.+?)\[\/s\]/is", "<span class=\"glowblue\">\$1</span>");
	$config['markup'][] = array("/\[s glowgold\](.+?)\[\/s\]/is", "<span class=\"glowgold\">\$1</span>");

	//kissu markup
	$config['markup'][] = array("/~~(.+?)~~/is", "<strike>\$1</strike>");
	$config['markup'][] = array("/\[sjis\](.+?)\[\/sjis\]/is", "<span class=\"sjis\">\$1</span>");
	$config['markup'][] = array("/\[s sjis\](.+?)\[\/s\]/is", "<span class=\"sjis\">\$1</span>");
	$config['markup'][] = array("/\[code\](.+?)\[\/code\]/is", "<span class=\"code\">\$1</span>");

	// Code markup. This should be set to a regular expression, using tags you want to use. Examples:
	// "/\[code\](.*?)\[\/code\]/is"
	// "/```([a-z0-9-]{0,20})\n(.*?)\n?```\n?/s"
	$config['markup_code'] = false;

	// Repair markup with HTML Tidy. This may be slower, but it solves nesting mistakes. Tinyboad, at the
	// time of writing this, can not prevent out-of-order markup tags (eg. "**''test**'') without help from
	// HTML Tidy.
	$config['markup_repair_tidy'] = false;

	// Always regenerate markup. This isn't recommended and should only be used for debugging; by default,
	// Tinyboard only parses post markup when it needs to, and keeps post-markup HTML in the database. This
	// will significantly impact performance when enabled.
	// briefly enable this to apply markup changes to older posts.
	$config['always_regenerate_markup'] = false;

/*
 * ====================
 *  Image settings
 * ====================
 */
	// Maximum number of images allowed. Increasing this number enabled multi image.
	// If you make it more than 1, make sure to enable the below script for the post form to change.
	// $config['additional_javascript'][] = 'js/multi-image.js';
	$config['max_images'] = 1;

	// Method to use for determing the max filesize.
	// "split" means that your max filesize is split between the images. For example, if your max filesize
	// is 2MB, the filesizes of all files must add up to 2MB for it to work.
	// "each" means that each file can be 2MB, so if your max_images is 3, each post could contain 6MB of
	// images. "split" is recommended.
	$config['multiimage_method'] = 'split';

	// For resizing, maximum thumbnail dimensions.
	$config['thumb_width'] = 255;
	$config['thumb_height'] = 255;
	// Maximum thumbnail dimensions for thread (OP) images.
	$config['thumb_op_width'] = 255;
	$config['thumb_op_height'] = 255;

	// Thumbnail extension ("png" recommended). Leave this empty if you want the extension to be inherited
	// from the uploaded file.
	$config['thumb_ext'] = 'png';

	// Maximum amount of animated GIF frames to resize (more frames can mean more processing power). A value
	// of "1" means thumbnails will not be animated. Requires $config['thumb_ext'] to be 'gif' (or blank) and
	//  $config['thumb_method'] to be 'imagick', 'convert', or 'convert+gifsicle'. This value is not
	// respected by 'convert'; will just resize all frames if this is > 1.
	$config['thumb_keep_animation_frames'] = 1;

	/*
	 * Thumbnailing method:
	 *
	 *   'gd'		   PHP GD (default). Only handles the most basic image formats (GIF, JPEG, PNG).
	 *				  GD is a prerequisite for Tinyboard no matter what method you choose.
	 *
	 *   'imagick'	  PHP's ImageMagick bindings. Fast and efficient, supporting many image formats.
	 *				  A few minor bugs. http://pecl.php.net/package/imagick
	 *
	 *   'convert'	  The command line version of ImageMagick (`convert`). Fixes most of the bugs in
	 *				  PHP Imagick. `convert` produces the best still thumbnails and is highly recommended.
	 *
	 *   'gm'		   GraphicsMagick (`gm`) is a fork of ImageMagick with many improvements. It is more
	 *				  efficient and gets thumbnailing done using fewer resources.
	 *
	 *   'convert+gifscale'
	 *	OR  'gm+gifsicle'  Same as above, with the exception of using `gifsicle` (command line application)
	 *					   instead of `convert` for resizing GIFs. It's faster and resulting animated
	 *					   thumbnails have less artifacts than if resized with ImageMagick.
	 */
	$config['thumb_method'] = 'gd';
	// $config['thumb_method'] = 'convert';

	// Command-line options passed to ImageMagick when using `convert` for thumbnailing. Don't touch the
	// placement of "%s" and "%d".
	$config['convert_args'] = '-size %dx%d %s -thumbnail %dx%d -auto-orient +profile "*" %s';

	// Strip EXIF metadata from JPEG files.
	$config['strip_exif'] = false;
	// Use the command-line `exiftool` tool to strip EXIF metadata without decompressing/recompressing JPEGs.
	// Ignored when $config['redraw_image'] is true. This is also used to adjust the Orientation tag when
	//  $config['strip_exif'] is false and $config['convert_manual_orient'] is true.
	$config['use_exiftool'] = false;

	// Redraw the image to strip any excess data (commonly ZIP archives) WARNING: This might strip the
	// animation of GIFs, depending on the chosen thumbnailing method. It also requires recompressing
	// the image, so more processing power is required.
	$config['redraw_image'] = false;

	// Automatically correct the orientation of JPEG files using -auto-orient in `convert`. This only works
	// when `convert` or `gm` is selected for thumbnailing. Again, requires more processing power because
	// this basically does the same thing as $config['redraw_image']. (If $config['redraw_image'] is enabled,
	// this value doesn't matter as $config['redraw_image'] attempts to correct orientation too.)
	$config['convert_auto_orient'] = false;

	// Is your version of ImageMagick or GraphicsMagick old? Older versions may not include the -auto-orient
	// switch. This is a manual replacement for that switch. This is independent from the above switch;
	// -auto-orrient is applied when thumbnailing too.
	$config['convert_manual_orient'] = false;

	// Regular expression to check for an XSS exploit with IE 6 and 7. To disable, set to false.
	// Details: https://github.com/savetheinternet/Tinyboard/issues/20
	$config['ie_mime_type_detection'] = '/<(?:body|head|html|img|plaintext|pre|script|table|title|a href|channel|scriptlet)/i';

	// Allowed image file extensions.
	$config['allowed_ext'][] = 'jpg';
	$config['allowed_ext'][] = 'jpeg';
	$config['allowed_ext'][] = 'bmp';
	$config['allowed_ext'][] = 'gif';
	$config['allowed_ext'][] = 'png';

	// $config['allowed_ext'][] = 'svg';

	// Allowed extensions for OP. Inherits from the above setting if set to false. Otherwise, it overrides both allowed_ext and
	// allowed_ext_files (filetypes for downloadable files should be set in allowed_ext_files as well). This setting is useful
	// for creating fileboards.
	$config['allowed_ext_op'] = false;

	// Allowed additional file extensions (not images; downloadable files).
	// $config['allowed_ext_files'][] = 'txt';
	// $config['allowed_ext_files'][] = 'zip';

	// An alternative function for generating image filenames, instead of the default UNIX timestamp.
	// $config['filename_func'] = function($post) {
	//	  return sprintf("%s", time() . substr(microtime(), 2, 3));
	// };

	// Thumbnail to use for the non-image file uploads.
	$config['file_icons']['default'] = 'file.png';
	$config['file_icons']['zip'] = 'zip.png';
	$config['file_icons']['webm'] = 'video.png';
	$config['file_icons']['mp4'] = 'video.png';
	// Example: Custom thumbnail for certain file extension.
	// $config['file_icons']['extension'] = 'some_file.png';

	// Location of above images.
	$config['file_thumb'] = 'static/%s';
	// Location of thumbnail to use for spoiler images.
	$config['spoiler_image'] = 'static/spoiler.png';
	$config['nsfw_image'] = 'static/kissu-nsfw.png';
	// Location of thumbnail to use for deleted images.
	$config['image_deleted'] = 'static/deleted.png';

	// When a thumbnailed image is going to be the same (in dimension), just copy the entire file and use
	// that as a thumbnail instead of resizing/redrawing.
	$config['minimum_copy_resize'] = false;

	// Maximum image upload size in bytes.
	$config['max_filesize'] = 40 * 1024 * 1024; // 10MB
	// Maximum image dimensions.
	$config['max_width'] = 10000;
	$config['max_height'] = $config['max_width'];
	// Reject duplicate image uploads.
	$config['image_reject_repost'] = true;
	// Reject duplicate image uploads within the same thread. Doesn't change anything if
	//  $config['image_reject_repost'] is true.
	$config['image_reject_repost_in_thread'] = false;

	// Display the aspect ratio of uploaded files.
	$config['show_ratio'] = false;
	// Display the file's original filename.
	$config['show_filename'] = true;

	// WebM Settings
	$config['webm']['use_ffmpeg'] = false;
	$config['webm']['expected_format'] = array('webm' => 'webm', 'mp4' => 'mp4');
	$config['webm']['video_codecs'] = array('vp8', 'vp9', 'h264', 'av1');
	$config['webm']['allow_audio'] = false;
	$config['webm']['max_length'] = 120;
	$config['webm']['ffmpeg_path'] = 'ffmpeg';
	$config['webm']['ffprobe_path'] = 'ffprobe';

	// Display image identification links for ImgOps, regex.info/exif, Google Images and iqdb.
	$config['image_identification'] = false;
	// Which of the identification links to display. Only works if $config['image_identification'] is true.
	$config['image_identification_imgops'] = true;
	$config['image_identification_exif'] = true;
	$config['image_identification_google'] = true;
	// Anime/manga search engine.
	$config['image_identification_iqdb'] = false;

	// Set this to true if you're using a BSD
	$config['bsd_md5'] = false;

	// Set this to true if you're using Linux and you can execute `md5sum` binary.
	$config['gnu_md5'] = false;

	$config['blockhash'] = true;
	$config['blockhash_nearness_threshold'] = 340;

	// Use Tesseract OCR to retrieve text from images, so you can use it as a spamfilter.
	$config['tesseract_ocr'] = false;

	// Tesseract parameters
	$config['tesseract_params'] = '';

	// Tesseract preprocess command
	$config['tesseract_preprocess_command'] = 'convert -monochrome %s -';

	// Number of posts in a "View Last X Posts" page
	$config['noko50_count'] = 50;
	// Number of posts a thread needs before it gets a "View Last X Posts" page.
	// Set to an arbitrarily large value to disable.
	$config['noko50_min'] = 100;
/*
 * ====================
 *  Board settings
 * ====================
 */

	// Maximum amount of threads to display per page.
	$config['threads_per_page'] = 10;
	// Maximum number of pages. Content past the last page is automatically purged.
	$config['max_pages'] = 10;
	// Replies to show per thread on the board index page.
	$config['threads_preview'] = 5;
	// Same as above, but for stickied threads.
	$config['threads_preview_sticky'] = 1;

	// How to display the URI of boards. Usually '/%s/' (/b/, /mu/, etc). This doesn't change the URL. Find
	//  $config['board_path'] if you wish to change the URL.
	$config['board_abbreviation'] = '/%s/';

	// The default name (ie. Anonymous). Can be an array - in that case it's picked randomly from the array.
	// Example: $config['anonymous'] = array('Bernd', 'Senpai', 'Jonne', 'ChanPro');
	$config['anonymous'] = 'Anonymous';

	// Number of reports you can create at once.
	$config['report_limit'] = 3;

	// Allow unfiltered HTML in board subtitle. This is useful for placing icons and links.
	$config['allow_subtitle_html'] = false;

/*
 * ====================
 *  Display settings
 * ====================
 */

	// Tinyboard has been translated into a few langauges. See inc/locale for available translations.
	$config['locale'] = 'en'; // (en, ru_RU.UTF-8, fi_FI.UTF-8, pl_PL.UTF-8)

	// Timezone to use for displaying dates/times.
	$config['timezone'] = 'America/Los_Angeles';
	// The format string passed to strftime() for displaying dates.
	// http://www.php.net/manual/en/function.strftime.php
	$config['post_date'] = '%m/%d/%y (%a) %H:%M:%S';
	// Same as above, but used for "you are banned' pages.
	$config['ban_date'] = '%A %e %B, %Y';

	// The names on the post buttons. (On most imageboards, these are both just "Post").
	$config['button_newtopic'] = _('New Topic');
	$config['button_reply'] = _('New Reply');

	// Assign each poster in a thread a unique ID, shown by "ID: xxxxx" before the post number.
	$config['poster_ids'] = false;
	// Number of characters in the poster ID (maximum is 40).
	$config['poster_id_length'] = 5;

	// Show thread subject in page title.
	$config['thread_subject_in_title'] = false;

	// Additional lines added to the footer of all pages.
	$config['footer'][] = _('All trademarks, copyrights, comments, and images on this page are owned by and are the responsibility of their respective parties.');

	// Characters used to generate a random password (with Javascript).
	$config['genpassword_chars'] = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';

        // List of banners sepperated by `,`
        $config['url_banner_list'] = "https://impatientprogrammer.net/wp-content/uploads/2017/12/BlogBanner_Cpp_Basics-1035x270.jpg,http://www.iucedu.com/images/c-c-plus-training-institute-in-chennai.jpg";

        // Exists for legacy purposes
	// Banners should be set by 'url_banner_list' instead
	//$config['url_banner'] = array_rand($config['url_banner_list']);

	// Banner dimensions are also optional. As the banner loads after the rest of the page, everything may be
	// shifted down a few pixels when it does. Making the banner a fixed size will prevent this.
	// $config['banner_width'] = 300;
	// $config['banner_height'] = 100;

	// Custom stylesheets available for the user to choose. See the "stylesheets/" folder for a list of
	// available stylesheets (or create your own).
	//$config['stylesheets']['Yotsuba B'] = ''; // Default; there is no additional/custom stylesheet for this.
	//$config['stylesheets']['Yotsuba'] = 'yotsuba.css';
	// $config['stylesheets']['Futaba'] = 'futaba.css';
	// $config['stylesheets']['Dark'] = 'dark.css';

	// The prefix for each stylesheet URI. Defaults to $config['root']/stylesheets/
	// $config['uri_stylesheets'] = 'http://static.example.org/stylesheets/';

	// The default stylesheet to use.
//	$config['default_stylesheet'] = array('Yotsuba B', $config['stylesheets']['Yotsuba B']);

	// Make stylesheet selections board-specific.
	$config['stylesheets_board'] = false;

	// Use Font-Awesome for displaying lock and pin icons, instead of the images in static/.
	// http://fortawesome.github.io/Font-Awesome/icon/pushpin/
	// http://fortawesome.github.io/Font-Awesome/icon/lock/
	$config['font_awesome'] = true;
	$config['font_awesome_css'] = 'stylesheets/font-awesome/css/font-awesome.min.css';

	/*
	 * For lack of a better name, “boardlinks” are those sets of navigational links that appear at the top
	 * and bottom of board pages. They can be a list of links to boards and/or other pages such as status
	 * blogs and social network profiles/pages.
	 *
	 * "Groups" in the boardlinks are marked with square brackets. Tinyboard allows for infinite recursion
	 * with groups. Each array() in $config['boards'] represents a new square bracket group.
	 */

	$config['boards'] = array(
	 	array('b')//,
	// 	array('c', 'd', 'e', 'f', 'g'),
	// 	array('h', 'i', 'j'),
	// 	array('k', array('l', 'm')),
	// 	array('status' => 'http://status.example.org/')
	);

	// Whether or not to put brackets around the whole board list
	$config['boardlist_wrap_bracket'] = false;

	// Show page navigation links at the top as well.
	$config['page_nav_top'] = false;

	// Show "Catalog" link in page navigation. Use with the Catalog theme. Set to false to disable.
	$config['catalog_link'] = 'catalog';

	// Board categories. Only used in the "Categories" theme.
	// $config['categories'] = array(
	// 	'Group Name' => array('a', 'b', 'c'),
	// 	'Another Group' => array('d')
	// );
	// Optional for the Categories theme. This is an array of name => (title, url) groups for categories
	// with non-board links.
	// $config['custom_categories'] = array(
	// 	'Links' => array(
	// 		'Github' => 'https://github.com/my/project',
	// 		'Donate' => 'donate.html'
	// 	)
	// );

	// Automatically remove unnecessary whitespace when compiling HTML files from templates.
	$config['minify_html'] = true;

	/*
	 * Advertisement HTML to appear at the top and bottom of board pages.
	 */

	// $config['ad'] = array(
	//	'top' => '',
	//	'bottom' => '',
	// );

	// Display flags (when available). This config option has no effect unless poster flags are enabled (see
	// $config['country_flags']). Disable this if you want all previously-assigned flags to be hidden.
	$config['display_flags'] = true;

	// Location of post flags/icons (where "%s" is the flag name). Defaults to static/flags/%s.png.
	// $config['uri_flags'] = 'http://static.example.org/flags/%s.png';

	// Width and height (and more?) of post flags. Can be overridden with the Tinyboard post modifier:
	// <tinyboard flag style>.
	$config['flag_style'] = 'width:16px;height:11px;';

/*
 * ====================
 *  Javascript/
 * ====================
 */

	// Additional Javascript files to include on board index and thread pages. See js/ for available scripts.
	// $config['additional_javascript'][] = 'js/jquery.min.js';
	// $config['additional_javascript'][] = 'js/inline-expanding.js';
	// $config['additional_javascript'][] = 'js/local-time.js';

	// Some scripts require jQuery. Check the comments in script files to see what's needed. When enabling
	// jQuery, you should first empty the array so that "js/query.min.js" can be the first, and then re-add
	// "js/inline-expanding.js" or else the inline-expanding script might not interact properly with other
	// scripts.
	$config['additional_javascript'] = array();
	$config['additional_javascript'][] = 'js/jquery.min.js';
	// $config['additional_javascript'][] = 'js/inline-expanding.js';
	// $config['additional_javascript'][] = 'js/auto-reload.js';
	// $config['additional_javascript'][] = 'js/post-hover.js';
	// $config['additional_javascript'][] = 'js/style-select.js';
	// $config['additional_javascript'][] = 'js/captcha.js';

	// Where these script files are located on the web. Defaults to $config['root'].
	// $config['additional_javascript_url'] = 'http://static.example.org/tinyboard-javascript-stuff/';

	// Compile all additional scripts into one file ($config['file_script']) instead of including them seperately.
	$config['additional_javascript_compile'] = true;

	// Minify Javascript using http://code.google.com/p/minify/.
	$config['minify_js'] = false;

	// Dispatch thumbnail loading and image configuration with JavaScript. It will need a certain javascript
	// code to work.
	$config['javascript_image_dispatch'] = false;

/*
 * ====================
 *  Video embedding
 * ====================
 */

	// Enable embedding (see below).
	$config['enable_embedding'] = true;

	// Custom embedding (YouTube, vimeo, etc.)
	// It's very important that you match the entire input (with ^ and $) or things will not work correctly.
		$config['embedding'] = array(
			array(
				'/^https?:\/\/(\w+\.)?youtube\.com\/watch\?v=([a-zA-Z0-9\-_]{10,11}).*?(t=([0-9]+))?$/i',
				'<iframe style="margin: 10px 20px;" width="%%tb_width%%" height="%%tb_height%%" frameborder="0" id="ytplayer" src="https://www.youtube.com/embed/$2?start=$5" allowfullscreen></iframe>'
			),
			array(
				'/^https?:\/\/(\w+\.)?youtu\.be\/([a-zA-Z0-9\-_]{10,11})((?!t=[0-9]).)*(t=([0-9]+))?((?!t=[0-9]).)*$/i',
				'<iframe style="margin: 10px 20px;" width="%%tb_width%%" height="%%tb_height%%" frameborder="0" id="ytplayer" src="https://www.youtube.com/embed/$2?start=$5" allowfullscreen></iframe>'
			),
			array(
				'/^https?:\/\/(\w+\.)?nicovideo\.jp\/watch\/sm([a-zA-Z0-9\-_]+)\??$/i',
				'<iframe style="float: left;margin: 10px 20px;"  width="%%tb_width%%" height="%%tb_height%%" frameborder="0" id="nnplayer" src="https://embed.nicovideo.jp/watch/sm$2" scrolling="no" style="border:solid 1px #ccc;" frameborder="0" allowfullscreen><a href="https://www.nicovideo.jp/watch/sm$2"></a></iframe>'
			),

			array(
				'/^https?:\/\/(\w+\.)?vimeo\.com\/(\d{2,10})(\?.+)?$/i',
				'<iframe style="float: left;margin: 10px 20px;" width="%%tb_width%%" height="%%tb_height%%" src="https://player.vimeo.com/video/$2" frameborder="0"></iframe>'
			),
			array(
				'/^https?:\/\/(\w+\.)?dailymotion\.com\/video\/([a-zA-Z0-9]{2,10})(_.+)?$/i',
				'<object style="float: left;margin: 10px 20px;" width="%%tb_width%%" height="%%tb_height%%"><param name="movie" value="https://www.dailymotion.com/swf/video/$2"><param name="allowFullScreen" value="true"><param name="allowScriptAccess" value="always"><param name="wmode" value="transparent"><embed type="application/x-shockwave-flash" src="https://www.dailymotion.com/swf/video/$2" width="%%tb_width%%" height="%%tb_height%%" wmode="transparent" allowfullscreen="true" allowscriptaccess="always"></object>'
			),
			array(
				'/^https?:\/\/(\w+\.)?metacafe\.com\/watch\/(\d+)\/([a-zA-Z0-9_\-.]+)\/(\?[^\'"<>]+)?$/i',
				'<div style="float:left;margin:10px 20px;width:%%tb_width%%px;height:%%tb_height%%px"><embed flashVars="playerVars=showStats=no|autoPlay=no" src="https://www.metacafe.com/fplayer/$2/$3.swf" width="%%tb_width%%" height="%%tb_height%%" wmode="transparent" allowFullScreen="true" allowScriptAccess="always" name="Metacafe_$2" pluginspage="https://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></div>'
			),
			array(
				'/^https?:\/\/video\.google\.com\/videoplay\?docid=(\d+)([&#](.+)?)?$/i',
				'<embed src="https://video.google.com/googleplayer.swf?docid=$1&hl=en&fs=true" style="width:%%tb_width%%px;height:%%tb_height%%px;float:left;margin:10px 20px" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>'
			),
			array(
				'/^https?:\/\/(\w+\.)?vocaroo\.com\/i\/([a-zA-Z0-9]{2,15})$/i',
				'<object style="float: left;margin: 10px 20px;" width="148" height="44"><param name="movie" value="https://vocaroo.com/player.swf?playMediaID=$2&autoplay=0"><param name="wmode" value="transparent"><embed src="https://vocaroo.com/player.swf?playMediaID=$2&autoplay=0" width="148" height="44" wmode="transparent" type="application/x-shockwave-flash"></object>'
			)
		);


	// Embedding width and height.
	$config['embed_width'] = 300;
	$config['embed_height'] = 246;

/*
 * ====================
 *  Error messages
 * ====================
 */

 // Error messages
 $config['error']['bot']			= _('You look like a bot.');
 $config['error']['remove_bot_err'] = true;
 $config['error']['referer']		= _('Your browser sent an invalid or no HTTP referer.');
 $config['error']['toolong']		= _('The %s field was too long.');
 $config['error']['toolong_body']	= _('The body was too long.');
 $config['error']['tooshort_body']	= _('The body was too short or empty.');
 $config['error']['noimage']		= _('You must upload an image.');
 $config['error']['toomanyimages'] = _('You have attempted to upload too many images!');
 $config['error']['nomove']		= _('The server failed to handle your upload.');
 $config['error']['fileext']		= _('Unsupported image format.');
 $config['error']['noboard']		= _('Invalid board!');
 $config['error']['nonexistant']		= _('Thread specified does not exist.');
 $config['error']['locked']		= _('Thread locked. You may not reply at this time.');
 $config['error']['reply_hard_limit']	= _('Thread has reached its maximum reply limit.');
 $config['error']['faux_text_board_limit']	= _('Faux-TextBoard -> Only OPs may post images.');
 //$config['error']['image_hard_limit']	= _('Thread has reached its maximum image limit.');
 $config['error']['image_hard_limit']	= _('Thread has reached its maximum image limit.');
 $config['error']['nopost']		= _('You didn\'t make a post.');
 $config['error']['flood']['ip']		= _('Flood detected; Same IP posting too fast.');
 $config['error']['flood']['repeat']		= _('Flood detected; Same message.');
 $config['error']['flood']['fast']		= _('Flood detected; Board is too fast.');
 $config['error']['spam']		= _('Your request looks like spam.');
 $config['error']['dice']		= _('🎲 It looks like you\'re trying to cheat !');
 $config['error']['imagespam']		= _('Your image looks like spam.');
 $config['error']['wlinvalid']		= _('Your whitelist token is invalid.');
 $config['error']['unoriginal']		= _('Unoriginal content!');
 $config['error']['muted']		= _('Unoriginal content! You have been muted for %d seconds.');
 $config['error']['youaremuted']		= _('You are muted! Expires in %d seconds.');
 $config['error']['dnsbl']		= _('Your IP address is listed in %s.');
 $config['error']['toomanylinks']	= _('Too many links; flood detected.');
 $config['error']['toomanycites']	= _('Too many cites; post discarded.');
 $config['error']['toomanylines']	= _('Too many lines; post discarded.');
 $config['error']['toomanycross']	= _('Too many cross-board links; post discarded.');
 $config['error']['nodelete']		= _('You didn\'t select anything to delete.');
 $config['error']['noreport']		= _('You didn\'t select anything to report.');
 $config['error']['invalidreport']	= _('The reason was too long.');
 $config['error']['toomanyreports']	= _('You can\'t report that many posts at once.');
 $config['error']['invalidpassword']	= _('Wrong password…');
 $config['error']['invalidimg']		= _('Invalid image.');
 $config['error']['phpfileserror']	= _('Upload failure (file #%index%): Error code %code%. Refer to <a href="http://php.net/manual/en/features.file-upload.errors.php">http://php.net/manual/en/features.file-upload.errors.php</a>; post discarded.');
 $config['error']['unknownext']		= _('Unknown file extension.');
 $config['error']['filesize']		= _('Maximum file size: %maxsz% bytes<br>Your file\'s size: %filesz% bytes');
 $config['error']['maxsize']		= _('The file was too big.');
 $config['error']['genwebmerror']	= _('There was a problem processing your webm .1.');
 $config['error']['webmerror'] 		= _('There was a problem processing your webm .2.');//Is this error used anywhere ?
 $config['error']['invalidwebm'] 	= _('Invalid webm uploaded.');
 $config['error']['webmhasaudio'] 	= _('The uploaded webm contains an audio or another type of additional stream.');
 $config['error']['webmtoolong'] 	= _('The uploaded webm is longer than ' . $config['webm']['max_length'] . ' seconds.');
 $config['error']['fileexists']		= _('That file <a href="%s">already exists</a>!');
 $config['error']['fileexistsinthread']	= _('That file <a href="%s">already exists</a> in this thread!');
 $config['error']['delete_too_soon']	= _('You\'ll have to wait another %s before deleting that.');
 $config['error']['mime_exploit']	= _('MIME type detection XSS exploit (IE) detected; post discarded.');
 $config['error']['invalid_embed']	= _('Couldn\'t make sense of the URL of the video you tried to embed.');
 $config['error']['captcha']		= _('You seem to have mistyped the verification.');


 // Moderator errors
 $config['error']['toomanyunban']	= _('You are only allowed to unban %s users at a time. You tried to unban %u users.');
 $config['error']['nowhitelistpattern']	= _('Whitelist requires a valid regex pattern.');
 $config['error']['invalid']		= _('Invalid username and/or password.');
 $config['error']['notamod']		= _('You are not a mod…');
 $config['error']['invalidafter']	= _('Invalid username and/or password. Your user may have been deleted or changed.');
 $config['error']['malformed']		= _('Invalid/malformed cookies.');
 $config['error']['missedafield']	= _('Your browser didn\'t submit an input when it should have.');
 $config['error']['required']		= _('The %s field is required.');
 $config['error']['invalidfield']	= _('The %s field was invalid.');
 $config['error']['boardexists']		= _('There is already a %s board.');
 $config['error']['noaccess']		= _('You don\'t have permission to do that.');
 $config['error']['invalidpost']		= _('That post doesn\'t exist…');
 $config['error']['404']			= _('Page not found.');
 $config['error']['modexists']		= _('That mod <a href="?/users/%d">already exists</a>!');
 $config['error']['invalidtheme']	= _('That theme doesn\'t exist!');
 $config['error']['csrf']		= _('Invalid security token! Please go back and try again.');
 $config['error']['badsyntax']		= _('Your code contained PHP syntax errors. Please go back and correct them. PHP says: ');

/*
 * =========================
 *  Directory/file settings
 * =========================
 */

	// The root directory, including the trailing slash, for Tinyboard.
	// Examples: '/', 'http://boards.chan.org/', '/chan/'.
	if (isset($_SERVER['REQUEST_URI'])) {
		$request_uri = $_SERVER['REQUEST_URI'];
		if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '')
			$request_uri = substr($request_uri, 0, - 1 - strlen($_SERVER['QUERY_STRING']));
		$config['root']	 = str_replace('\\', '/', dirname($request_uri)) == '/'
			? '/' : str_replace('\\', '/', dirname($request_uri)) . '/';
		unset($request_uri);
	} else
		$config['root'] = '/'; // CLI mode

	// The scheme and domain. This is used to get the site's absolute URL (eg. for image identification links).
	// If you use the CLI tools, it would be wise to override this setting.
	$config['domain'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
	$config['domain'] .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

	// If for some reason the folders and static HTML index files aren't in the current working direcotry,
	// enter the directory path here. Otherwise, keep it false.
	$config['root_file'] = false;

	// Location of files.
	$config['file_index'] = 'index.html';
        $config['file_index_no_ext'] = 'index';
	$config['file_page'] = '%d.html'; // NB: page is both an index page and a thread
	$config['remove_ext'] = false;
	$config['file_page_no_ext'] = '%d';
        $config['file_page50'] = '%d+50.html';
	$config['file_page_slug'] = '%d-%s.html';
	$config['file_page50_slug'] = '%d-%s+50.html';
	$config['file_mod'] = 'mod.php';
	$config['file_post'] = 'post.php';
	$config['file_script'] = 'main.js';

	// Board directory, followed by a forward-slash (/).
	$config['board_path'] = '%s/';
	// Misc directories.
	$config['dir']['img'] = 'src/';
	$config['dir']['thumb'] = 'thumb/';
	$config['dir']['res'] = 'res/';


	// Directory for archived threads
	$config['dir']['archive'] = 'archive/';
	// Directory for "Featured Threads" (threads makred for permanent storage)
	$config['dir']['featured'] = 'featured/';



/*
* ====================
*  Archive settings
* ====================
*/

    	// Indicate if threads should be archived
    	$config['archive']['threads'] = true;
	// Indicate if it is possible to mark threads as featured (stored forever)
	$config['feature']['threads'] = true;
	// Indicate if link to featured archive should be shown on post and thread page
	$config['feature']['link_post_page'] = false;
   	// Indicate if it is possible to mark threads as nostalgic (stored forever but will only be accessable to mods)
    	$config['mod_archive']['threads'] = true;
	// Days to keep archived threads before deletion (if set to false all archived threads are kept forever) threads are kept forever
	$config['archive']['lifetime'] = "3";
   	// Number of chars in snippet
	$config['archive']['snippet_len'] = 400;
    	// If any is set to run in crom both will be run in cron regardless
    	// Archiving is run in cron job
    	$config['archive']['cron_job']['archiving'] = false;
    	// Purging of archive is run in cron job
	$config['archive']['cron_job']['purge'] = false;

    	// Automatically send threads with thiese trips to Featured Archive
    	// $config['archive']['auto_feature_trips'] = array("!!securetrip", "!trip");
    	$config['archive']['auto_feature_trips'] = array();


	// For load balancing, having a seperate server (and domain/subdomain) for serving static content is
	// possible. This can either be a directory or a URL. Defaults to $config['root'] . 'static/'.
	// $config['dir']['static'] = 'http://static.example.org/';

	// Where to store the .html templates. This folder and the template files must exist.
	$config['dir']['template'] = getcwd() . '/templates';
	// Location of Tinyboard "themes".
	$config['dir']['themes'] = getcwd() . '/templates/themes';
	// Same as above, but a URI (accessable by web interface).
	$config['dir']['themes_uri'] = 'templates/themes';
	// Home directory. Used by themes.
	$config['dir']['home'] = '';

	// Location of a blank 1x1 gif file. Only used when country_flags_condensed is enabled
	// $config['image_blank'] = 'static/blank.gif';

	// Static images. These can be URLs OR base64 (data URI scheme). These are only used if
	// $config['font_awesome'] is false (default).
	// $config['image_sticky']	= 'static/sticky.png';
	// $config['image_locked']	= 'static/locked.gif';
	// $config['image_bumplocked']	= 'static/sage.png'.

	// If you want to put images and other dynamic-static stuff on another (preferably cookieless) domain.
	// This will override $config['root'] and $config['dir']['...'] directives. "%s" will get replaced with
	//  $board['dir'], which includes a trailing slash.
	// $config['uri_thumb'] = 'http://images.example.org/%sthumb/';
	// $config['uri_img'] = 'http://images.example.org/%ssrc/';

	// Set custom locations for stylesheets and the main script file. This can be used for load balancing
	// across multiple servers or hostnames.
	// $config['url_stylesheet'] = 'http://static.example.org/style.css'; // main/base stylesheet
	// $config['url_javascript'] = 'http://static.example.org/main.js';

	// Website favicon.
	// $config['url_favicon'] = '/favicon.gif';

	// Try not to build pages when we shouldn't have to.
	$config['try_smarter'] = true;

/*
 * ====================
 *  Advanced build
 * ====================
 */

	// Strategies for file generation. Also known as an "advanced build". If you don't have performance
	// issues, you can safely ignore that part, because it's hard to configure and won't even work on
	// your free webhosting.
	//
	// A strategy is a function, that given the PHP environment and ($fun, $array) variable pair, returns
	// an $action array or false.
	//
	// $fun - a controller function name, see inc/controller.php. This is named after functions, so that
	//        we can generate the files in daemon.
	//
	// $array - arguments to be passed
	//
	// $action - action to be taken. It's an array, and the first element of it is one of the following:
	//   * "immediate" - generate the page immediately
	//   * "defer" - defer page generation to a moment a worker daemon gets to build it (serving a stale
	//               page in the meantime). The remaining arguments are daemon-specific. Daemon isn't
	//               implemented yet :DDDD inb4 while(true) { generate(Queue::Get()) }; (which is probably it).
	//   * "build_on_load" - defer page generation to a moment, when the user actually accesses the page.
	//                       This is a smart_build behaviour. You shouldn't use this one too much, if you
	//                       use it for active boards, the server may choke due to a possible race condition.
	//                       See my blog post: https://engine.vichan.net/blog/res/2.html
	//
	// So, let's assume we want to build a thread 1324 on board /b/, because a new post appeared there.
	// We try the first strategy, giving it arguments: 'sb_thread', array('b', 1324). The strategy will
	// now return a value $action, denoting an action to do. If $action is false, we try another strategy.
	//
	// As I said, configuration isn't easy.
	//
	// 1. chmod 0777 directories: tmp/locks/ and tmp/queue/.
	// 2. serve 403 and 404 requests to go thru smart_build.php
	//    for nginx, this blog post contains config snippets: https://engine.vichan.net/blog/res/2.html
	// 3. disable indexes in your webserver
	// 4. launch any number of daemons (eg. twice your number of threads?) using the command:
	//    $ tools/worker.php
	//    You don't need to do that step if you are not going to use the "defer" option.
	// 5. enable smart_build_helper (see below)
	// 6. edit the strategies (see inc/functions.php for the builtin ones). You can use lambdas. I will test
	//    various ones and include one that works best for me.
	$config['generation_strategies'] = array();
	// Add a sane strategy. It forces to immediately generate a page user is about to land on. Otherwise,
	// it has no opinion, so it needs a fallback strategy.
	$config['generation_strategies'][] = 'strategy_sane';
	// Add an immediate catch-all strategy. This is the default function of imageboards: generate all pages
	// on post time.
	$config['generation_strategies'][] = 'strategy_immediate';
	// NOT RECOMMENDED: Instead of an all-"immediate" strategy, you can use an all-"build_on_load" one (used
	// to be initialized using $config['smart_build']; ) for all pages instead of those to be build
	// immediately. A rebuild done in this mode should remove all your static files
	// $config['generation_strategies'][1] = 'strategy_smart_build';

	// Deprecated. Leave it false. See above.
	$config['smart_build'] = false;

	// Use smart_build.php for dispatching missing requests. It may be useful without smart_build or advanced
	// build, for example it will regenerate the missing files.
	$config['smart_build_helper'] = true;

	// smart_build.php: when a file doesn't exist, where should we redirect?
	$config['page_404'] = '/404.html';

	// Extra controller entrypoints. Controller is used only by smart_build and advanced build.
	$config['controller_entrypoints'] = array();

/*
 * ====================
 *  Mod settings
 * ====================
 */

 	// Limit how many bans can be removed via the ban list. Set to false (or zero) for no limit.
 	$config['mod']['unban_limit'] = false;

 	// Whether or not to lock moderator sessions to IP addresses. This makes cookie theft ineffective.
 	$config['mod']['lock_ip'] = true;

 	// The page that is first shown when a moderator logs in. Defaults to the dashboard (?/).
 	$config['mod']['default'] = '/';

 	// Mod links (full HTML).
 	$config['mod']['link_delete'] = '[D]';
 	$config['mod']['link_delete_keeporder'] = '[Dk]';
 	$config['mod']['link_ban'] = '[B]';
 	$config['mod']['link_hash'] = '[H]';
 	$config['mod']['link_bandelete'] = '[B&amp;D]';
 	$config['mod']['link_bandeletehash'] = '[B&amp;D&amp;H]';
 	$config['mod']['link_deletefile'] = '[F]';
 	$config['mod']['link_spoilerimage'] = '[S]';
 	$config['mod']['link_nsfwimage'] = '[N]';
 	$config['mod']['link_deletebyip'] = '[D+]';
 	$config['mod']['link_deletebyip_global'] = '[D++]';
 	$config['mod']['link_archive'] = '[Archive]';
 	$config['mod']['link_sticky'] = '[Sticky]';
 	$config['mod']['link_desticky'] = '[-Sticky]';
 	$config['mod']['link_lock'] = '[Lock]';
 	$config['mod']['link_unlock'] = '[-Lock]';
 	$config['mod']['link_bumplock'] = '[Sage]';
 	$config['mod']['link_bumpunlock'] = '[-Sage]';
 	$config['mod']['link_editpost'] = '[Edit]';
 	$config['mod']['link_move'] = '[Move]';
 	$config['mod']['link_cycle'] = '[Cycle]';
 	$config['mod']['link_uncycle'] = '[-Cycle]';

 	// Moderator capcodes.
 	$config['capcode'] = ' <span class="capcode">## %s</span>';

 	// "## Custom" becomes lightgreen, italic and bold:
 	$config['custom_capcode']['Custom'] ='<span class="capcode" style="color:lightgreen;font-style:italic;font-weight:bold"> ## %s</span>';

 	// "## Mod" makes everything purple, including the name and tripcode:
 	$config['custom_capcode']['Mod'] = array(
 		'<span class="capcode" style="color:purple"> ## %s</span>',
 		'color:purple', // Change name style; optional
 		'color:purple' // Change tripcode style; optional
 	);

 	// "## Admin" makes everything red and bold, including the name and tripcode:
 	$config['custom_capcode']['Admin'] = array(
 		'<span class="capcode" style="color:red;font-weight:bold"> ## %s</span>',
 		'color:red;font-weight:bold', // Change name style; optional
 		'color:red;font-weight:bold' // Change tripcode style; optional
 	);

         // "## Admin" makes everything red and bold, including the name and tripcode:
         $config['custom_capcode']['Something'] = array(
                 '<span class="capcode" style="color:red;font-weight:bold"> ## %s</span>',
                 'color:red;font-weight:bold', // Change name style; optional
                 'color:red;font-weight:bold' // Change tripcode style; optional
         );


 	// Enable the moving of single replies
 	//--$config['move_replies'] = false;

 	// How often (minimum) to purge the ban list of expired bans (which have been seen). Only works when
 	//  $config['cache'] is enabled and working.
 	$config['purge_bans'] = 60 * 60 * 12; // 12 hours

 	// Do DNS lookups on IP addresses to get their hostname for the moderator IP pages (?/IP/x.x.x.x).
 	$config['mod']['dns_lookup'] = true;
 	// How many recent posts, per board, to show in ?/IP/x.x.x.x.
 	$config['mod']['ip_recentposts'] = 5;

 	// Number of posts to display on the reports page.
 	$config['mod']['recent_reports'] = 10;
 	// Number of actions to show per page in the moderation log.
 	$config['mod']['modlog_page'] = 350;
 	// Number of bans to show per page in the ban list.
 	$config['mod']['banlist_page'] = 350;
 	// Number of news entries to display per page.
 	$config['mod']['news_page'] = 40;
 	// Number of results to display per page.
 	$config['mod']['search_page'] = 200;
 	// Number of entries to show per page in the moderator noticeboard.
 	$config['mod']['noticeboard_page'] = 50;
 	// Number of entries to summarize and display on the dashboard.
 	$config['mod']['noticeboard_dashboard'] = 5;

 	// Check public ban message by default.
 	$config['mod']['check_ban_message'] = false;
 	// Default public ban message. In public ban messages, %length% is replaced with "for x days" or
 	// "permanently" (with %LENGTH% being the uppercase equivalent).
 	$config['mod']['default_ban_message'] = _('USER WAS BANNED FOR THIS POST');
 	// $config['mod']['default_ban_message'] = 'USER WAS BANNED %LENGTH% FOR THIS POST';
 	// HTML to append to post bodies for public bans messages (where "%s" is the message).
 	$config['mod']['ban_message'] = '<span class="public_ban">(%s)</span>';

 	// When moving a thread to another board and choosing to keep a "shadow thread", an automated post (with
 	// a capcode) will be made, linking to the new location for the thread. "%s" will be replaced with a
 	// standard cross-board post citation (>>>/board/xxx)
 	$config['mod']['shadow_mesage'] = _('Moved to %s.');
 	// Capcode to use when posting the above message.
 	$config['mod']['shadow_capcode'] = 'Mod';
 	// Name to use when posting the above message. If false, $config['anonymous'] will be used.
 	$config['mod']['shadow_name'] = false;

 	// PHP time limit for ?/rebuild. A value of 0 should cause PHP to wait indefinitely.
 	$config['mod']['rebuild_timelimit'] = 0;

 	// PM snippet (for ?/inbox) length in characters.
 	$config['mod']['snippet_length'] = 75;

 	// Edit raw HTML in posts by default.
 	$config['mod']['raw_html_default'] = false;

 	// Automatically dismiss all reports regarding a thread when it is locked.
 	$config['mod']['dismiss_reports_on_lock'] = true;

 	// Use ?/config with a simple text editor for editing inc/instance-config.php.
 	$config['mod']['config_editor_php'] = false;
 	// Use  ?/config with a reduced editor for editing inc/instance-config.php.
 	$config['mod']['simplified_editor_php'] = true;

/*
 * ====================
 *  Mod permissions
 * ====================
 */

	// Probably best not to change this unless you are smart enough to figure out what you're doing. If you
	// decide to change it, remember that it is impossible to redefinite/overwrite groups; you may only add
	// new ones.
$config['nerf_mods'] = false;
$config['nerf_mods_board'] = 'trans';
$config['nerf_mods_max_level_number'] = 30;

$config['mod']['groups'] = array(
  5   => 'Bot',
  10	=> 'Janitor',
  20	=> 'Mod',
  30	=> 'Admin',
  // 98	=> 'God',
  99	=> 'Disabled'
);

	// If you add stuff to the above, you'll need to call this function immediately after.
	define_groups();

	// Example: Adding a new permissions group.
	// $config['mod']['groups'][0] = 'NearlyPowerless';
	// define_groups();

	// Capcode permissions.
$config['mod']['capcode'] = array(
  BOT   => array('Bot'),
  JANITOR		=> array('Janitor'),
  MOD		=> array('Mod'),
  ADMIN		=> true
);

	// Example: Allow mods to post with "## Moderator" as well
	// $config['mod']['capcode'][MOD][] = 'Moderator';
	// Example: Allow janitors to post with any capcode
	// $config['mod']['capcode'][JANITOR] = true;

	// Set any of the below to "DISABLED" to make them unavailable for everyone.

	// Don't worry about per-board moderators. Let all mods moderate any board.
	$config['mod']['skip_per_board'] = false;


	/* Post Controls */
	// View IP addresses
	$config['mod']['show_ip'] = MOD;
	// Delete a post
	$config['mod']['delete'] = JANITOR;
	// Ban a user for a post
	$config['mod']['ban'] = MOD;
	// Ban and delete (one click; instant)
	$config['mod']['bandelete'] = MOD;
	// Ban and delete and filter hash (one click; instant)
	$config['mod']['bandeletehash'] = MOD;
	// Remove bans
	$config['mod']['unban'] = MOD;
	// Remove whitelist
	$config['mod']['remove_whitelist'] = MOD;
	// New whitelist token
	$config['mod']['whitelist_tokens'] = ADMIN;
	// Spoiler image
	$config['mod']['spoilerimage'] = JANITOR;
	// Delete file (and keep post)
	$config['mod']['deletefile'] = JANITOR;
	// Delete all posts by IP
	$config['mod']['deletebyip'] = MOD;
	// Delete all posts by IP across all boards
	$config['mod']['deletebyip_global'] = ADMIN;
	// Sticky a thread
	$config['mod']['sticky'] = MOD;
	// Cycle a thread
	$config['mod']['cycle'] = MOD;
	// Lock a thread
	$config['mod']['lock'] = MOD;
	// Post in a locked thread
	$config['mod']['postinlocked'] = MOD;
	// Prevent a thread from being bumped
	$config['mod']['bumplock'] = MOD;
	// View whether a thread has been bumplocked ("-1" to allow non-mods to see too)
	$config['mod']['view_bumplock'] = MOD;
	// Edit posts
	$config['mod']['editpost'] = MOD;
	// "Move" a thread to another board (EXPERIMENTAL; has some known bugs)
	$config['mod']['move'] = MOD;
	// Bypass "field_disable_*" (forced anonymity, etc.)
	$config['mod']['bypass_field_disable'] = MOD;
	// Post bypass unoriginal content check on robot-enabled boards
	$config['mod']['postunoriginal'] = ADMIN;
	// Bypass flood check
	$config['mod']['bypass_filters'] = ADMIN;
	$config['mod']['flood'] = &$config['mod']['bypass_filters'];
	// Raw HTML posting
	$config['mod']['rawhtml'] = ADMIN;

	/* Administration */
	// View the report queue
	$config['mod']['reports'] = JANITOR;
	// Dismiss an abuse report
	$config['mod']['report_dismiss'] = JANITOR;
	// Dismiss all abuse reports by an IP
	$config['mod']['report_dismiss_ip'] = JANITOR;

	// View list of bans
	$config['mod']['view_banlist'] = MOD;
	// View the username of the mod who made a ban
	$config['mod']['view_whitelist'] = MOD;
	// View the username of the mod who made a ban
	$config['mod']['view_banstaff'] = MOD;
	// If the moderator doesn't fit the $config['mod']['view_banstaff''] (previous) permission, show him just
	// a "?" instead. Otherwise, it will be "Mod" or "Admin".
	$config['mod']['view_banquestionmark'] = false;
	// Show expired bans in the ban list (they are kept in cache until the culprit returns)
	$config['mod']['view_banexpired'] = true;
	// View ban for IP address
	$config['mod']['view_ban'] = $config['mod']['view_banlist'];
	// View IP address notes
	$config['mod']['view_notes'] = JANITOR;
	// Create notes
	$config['mod']['create_notes'] = $config['mod']['view_notes'];
	// Remote notes
	$config['mod']['remove_notes'] = ADMIN;
	// Create a new board
	$config['mod']['newboard'] = ADMIN;
	// Manage existing boards (change title, etc)
	$config['mod']['manageboards'] = ADMIN;
	// Delete a board
	$config['mod']['deleteboard'] = ADMIN;
	// List/manage users
	$config['mod']['manageusers'] = MOD;
	// Promote/demote users
	$config['mod']['promoteusers'] = ADMIN;
	// Edit any users' login information
	$config['mod']['editusers'] = ADMIN;
	// Change user's own password
	$config['mod']['change_password'] = JANITOR;
	// Delete a user
	$config['mod']['deleteusers'] = ADMIN;
	// Create a user
	$config['mod']['createusers'] = ADMIN;
	// View the moderation log
	$config['mod']['modlog'] = ADMIN;
	// View IP addresses of other mods in ?/log
	$config['mod']['show_ip_modlog'] = ADMIN;
	// View relevant moderation log entries on IP address pages (ie. ban history, etc.) Warning: Can be
	// pretty resource intensive if your mod logs are huge.
	$config['mod']['modlog_ip'] = MOD;
	// Create a PM (viewing mod usernames)
	$config['mod']['create_pm'] = JANITOR;
	// Read any PM, sent to or from anybody
	$config['mod']['master_pm'] = ADMIN;
	// Rebuild everything
	$config['mod']['rebuild'] = BOT;
	// Search through posts, IP address notes and bans
	$config['mod']['search'] = JANITOR;
	// Allow searching posts (can be used with board configuration file to disallow searching through a
	// certain board)
	$config['mod']['search_posts'] = JANITOR;
	// Read the moderator noticeboard
	$config['mod']['noticeboard'] = JANITOR;
	// Post to the moderator noticeboard
	$config['mod']['noticeboard_post'] = MOD;
	// Delete entries from the noticeboard
	$config['mod']['noticeboard_delete'] = ADMIN;
	// Public ban messages; attached to posts
	$config['mod']['public_ban'] = MOD;
	// Manage and install themes for homepage
	$config['mod']['themes'] = ADMIN;
	// Post news entries
	$config['mod']['news'] = ADMIN;
	// Custom name when posting news
	$config['mod']['news_custom'] = ADMIN;
	// Delete news entries
	$config['mod']['news_delete'] = ADMIN;
	// Execute un-filtered SQL queries on the database (?/debug/sql)
	$config['mod']['debug_sql'] = DISABLED;
	// Look through all cache values for debugging when APC is enabled (?/debug/apc)
	$config['mod']['debug_apc'] = ADMIN;
	// Edit the current configuration (via web interface)
	$config['mod']['edit_config'] = ADMIN;
	// View ban appeals
	$config['mod']['view_ban_appeals'] = MOD;
	// Accept and deny ban appeals
	$config['mod']['ban_appeals'] = MOD;
	// View the recent posts page
	$config['mod']['recent'] = MOD;
	// Create pages
	$config['mod']['edit_pages'] = MOD;
	$config['pages_max'] = 10;

	// Config editor permissions
	$config['mod']['config'] = array();


	// Disable the following configuration variables from being changed via ?/config. The following default
	// banned variables are considered somewhat dangerous.
	$config['mod']['config'][DISABLED] = array(
		'mod>config',
		'mod>config_editor_php',
		'mod>groups',
		'convert_args',
		'db>password',
	);

	$config['mod']['config'][JANITOR] = array(
		'!', // Allow editing ONLY the variables listed (in this case, nothing).
	);

	$config['mod']['config'][MOD] = array(
		'!', // Allow editing ONLY the variables listed (plus that in $config['mod']['config'][JANITOR]).
		'global_message',
	);

	// Example: Disallow ADMIN from editing (and viewing) $config['db']['password'].
	// $config['mod']['config'][ADMIN] = array(
	// 	'db>password',
	// );

	// Example: Allow ADMIN to edit anything other than $config['db']
	// (and $config['mod']['config'][DISABLED]).
	// $config['mod']['config'][ADMIN] = array(
	// 	'db',
	// );

	// Allow OP to remove arbitrary posts in his thread
	$config['user_moderation'] = false;

	// File board. Like 4chan /f/
	$config['file_board'] = false;

	// NSFW board. Load a disclaimer for this board
	$config['nsfw_board'] = false;

	// Poll board
	$config['poll_board'] = false;
	// Thread tags. Set to false to disable
	// Example: array('A' => 'Chinese cartoons', 'M' => 'Music', 'P' => 'Pornography');
	$config['allowed_tags'] = false;

		// Score boards
	$config['score_board'] = false;

/*
 * ====================
 *  Public pages
 * ====================
 */

	// Public post search settings
	$config['search'] = array();

	// Enable the search form
	$config['search']['enable'] = false;

	// Enable search in the board index.
	$config['board_search'] = false;

	// Maximal number of queries per IP address per minutes
	$config['search']['queries_per_minutes'] = Array(15, 2);

	// Global maximal number of queries per minutes
	$config['search']['queries_per_minutes_all'] = Array(50, 2);

	// Limit of search results
	$config['search']['search_limit'] = 100;

	// Boards for searching
	//$config['search']['boards'] = array('a', 'b', 'c', 'd', 'e');

	// Enable public logs? 0: NO, 1: YES, 2: YES, but drop names
	$config['public_logs'] = 0;

/*
 * ====================
 *  Events (PHP 5.3.0+)
 * ====================
 */

	// https://web.archive.org/web/20121003095551/http://tinyboard.org/docs/?p=Events

	// event_handler('post', function($post) {
	// 	// do something
	// });

	// event_handler('post', function($post) {
	// 	// do something else
	//
	// 	// return an error (reject post)
	// 	return 'Sorry, you cannot post that!';
	// });

/*
 * =============
 *  API settings
 * =============
 */

	// Whether or not to enable the 4chan-compatible API, disabled by default. See
	// https://github.com/4chan/4chan-API for API specification.
	$config['api']['enabled'] = true;

	// Extra fields in to be shown in the array that are not in the 4chan-API. You can get these by taking a
	// look at the schema for posts_ tables. The array should be formatted as $db_column => $translated_name.
	// Example: Adding the pre-markup post body to the API as "com_nomarkup".
	// $config['api']['extra_fields'] = array('body_nomarkup' => 'com_nomarkup');

/*
 * ==================
 *  NNTPChan settings
 * ==================
 */

/*
 * Please keep in mind that NNTPChan support in vichan isn't finished yet / is in an experimental
 * state. Please join #nntpchan on Rizon in order to peer with someone.
 */

	$config['nntpchan'] = array();

	// Enable NNTPChan integration
	$config['nntpchan']['enabled'] = false;

	// NNTP server
	$config['nntpchan']['server'] = "localhost:1119";

	// Global dispatch array. Add your boards to it to enable them. Please make
	// sure that this setting is set in a global context.
	$config['nntpchan']['dispatch'] = array(); // 'overchan.test' => 'test'

	// Trusted peer - an IP address of your NNTPChan instance. This peer will have
	// increased capabilities, eg.: will evade spamfilter.
	$config['nntpchan']['trusted_peer'] = '127.0.0.1';

	// Salt for message ID generation. Keep it long and secure.
	$config['nntpchan']['salt'] = 'change_me+please';

	// A local message ID domain. Make sure to change it.
	$config['nntpchan']['domain'] = 'example.vichan.net';

	// An NNTPChan group name.
	// Please set this setting in your board/config.php, not globally.
	$config['nntpchan']['group'] = false; // eg. 'overchan.test'



/*
 * ====================
 *  Other/uncategorized
 * ====================
 */

	// Meta keywords. It's probably best to include these in per-board configurations.
	// $config['meta_keywords'] = 'chan,anonymous discussion,imageboard,tinyboard';

	// Link imageboard to your Google Analytics account to track users and provide traffic insights.
	// $config['google_analytics'] = 'UA-xxxxxxx-yy';
	// Keep the Google Analytics cookies to one domain -- ga._setDomainName()
	// $config['google_analytics_domain'] = 'www.example.org';

	// Link imageboard to your Statcounter.com account to track users and provide traffic insights without the Google botnet.
	// Extract these values from Statcounter's JS tracking code:
	// $config['statcounter_project'] = '1234567';
	// $config['statcounter_security'] = 'acbd1234';

	// If you use Varnish, Squid, or any similar caching reverse-proxy in front of Tinyboard, you can
	// configure Tinyboard to PURGE files when they're written to.
	// $config['purge'] = array(
	// 	array('127.0.0.1', 80)
	// 	array('127.0.0.1', 80, 'example.org')
	// );

	// Connection timeout for $config['purge'], in seconds.
	$config['purge_timeout'] = 3;

	// Additional mod.php?/ pages. Look in inc/mod/pages.php for help.
	// $config['mod']['custom_pages']['/something/(\d+)'] = function($id) {
	// 	global $config;
	// 	if (!hasPermission($config['mod']['something']))
	// 		error($config['error']['noaccess']);
	// 	// ...
	// };

	// You can also enable themes (like ukko) in mod panel like this:
	// require_once("templates/themes/ukko/theme.php");
	//
	// $config['mod']['custom_pages']['/\*/'] = function() {
	//        global $mod;
	//
	//        $ukko = new ukko();
	//        $ukko->settings = array();
	//        $ukko->settings['uri'] = '*';
	//        $ukko->settings['title'] = 'derp';
	//        $ukko->settings['subtitle'] = 'derpity';
	//        $ukko->settings['thread_limit'] = 15;
	//        $ukko->settings['exclude'] = '';
	//
	//        echo $ukko->build($mod);
	// };

	// Example: Add links to dashboard (will all be in a new "Other" category).
	// $config['mod']['dashboard_links']['Something'] = '?/something';

	// Remote servers. I'm not even sure if this code works anymore. It might. Haven't tried it in a while.
	// $config['remote']['static'] = array(
	// 	'host' => 'static.example.org',
	// 	'auth' => array(
	// 		'method' => 'plain',
	// 		'username' => 'username',
	// 		'password' => 'password!123'
	// 	),
	// 	'type' => 'scp'
	// );

	// Create gzipped static files along with ungzipped.
	// This is useful with nginx with gzip_static on.
	$config['gzip_static'] = false;

	// Regex for board URIs. Don't add "`" character or any Unicode that MySQL can't handle. 58 characters
	// is the absolute maximum, because MySQL cannot handle table names greater than 64 characters.
	$config['board_regex'] = '[0-9a-zA-Z$_\x{0080}-\x{FFFF}]{1,58}';

	// Youtube.js embed HTML code
	$config['youtube_js_html'] = '<div class="video-container" data-video="$2" data-start="$5"><a href="https://youtu.be/$2?start=$5" target="_blank" class="file"><img style="width:300px;height:225px;" src="//img.youtube.com/vi/$2/0.jpg" class="post-image"/></a></div>';

	// Password hashing function
	//
	// $5$ <- SHA256
	// $6$ <- SHA512
	//
	// 25000 rounds make for ~0.05s on my 2015 Core i3 computer.
	//
	// https://secure.php.net/manual/en/function.crypt.php
	$config['password_crypt'] = '$6$rounds=25000$';

	// Password hashing method version
	// If set to 0, it won't upgrade hashes using old password encryption schema, only create new.
	// You can set it to a higher value, to further migrate to other password hashing function.
	$config['password_crypt_version'] = 1;

	// Use CAPTCHA for reports?
	$config['report_captcha'] = false;

	// Allowed HTML tags in ?/edit_pages.
	$config['allowed_html'] = 'a[href|title],p,br,li,ol,ul,strong,em,u,h2,b,i,tt,div,img[src|alt|title],hr';


// Json File Scrambler
// Indicate if json filenames should be scrambled
$config['json_scrambler']['scramble'] = false;
// Salt for hashing json filenames
$config['json_scrambler']['salt'] = '0123456789012345678901';

// banners.
$config['banner_ads'] = false;

$config['preload_banner'] = false;
$config['iframe_banner'] = false;
$config['banner_src'] = "https://art.kissu.moe/";
$config['banner_api_route'] = "api/banner?size=wide";

$config['banner_w'] = 500;
$config['banner_h'] = 100;
$config['webscraper_path'] = '/var/www/html/inc/Regex-Webscraper/py-cmd/regexscraper.py';
