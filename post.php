<?php
/*
 *  Copyright (c) 2010-2014 Tinyboard Development Group
 */

require_once 'inc/functions.php';
require_once 'inc/anti-bot.php';
require_once 'inc/bans.php';
require_once 'inc/polling.php';
require_once 'inc/image.php';
require_once 'inc/whitelist.php';
require_once 'inc/captcha-queue.php';
require_once 'inc/mod/pages.php';

$dropped_post = false;

if (isset($_POST['boardless-delete'])) {
	// Delete
//TODO find a more secure method to do this
	global $mod;
	check_login(false);
	$is_mod = $mod && strpos($_SERVER['HTTP_REFERER'],'mod?/') || strpos($_SERVER['HTTP_REFERER'],'mod.php?/');

	if (!$_POST['pswrd'])
		error($config['error']['bot']);

	$password = &$_POST['pswrd'];

	if ($password == '' && !$is_mod)
		error($config['error']['invalidpassword']);

	$delete = array();
	foreach ($_POST as $post => $value) {
		if (preg_match('/^delete_([a-zA-Z0-9]+)_(\d+)$/', $post, $m)) {
			$delete[] = ["board" => $m[1], "no" => (int)$m[2]];
		}
	}

	checkDNSBL();

	// Check if deletion enabled
	if (!$config['allow_delete'])
		error(_('Post deletion is not allowed!'));

	if (empty($delete))
		error($config['error']['nodelete']);

	$checked_boards = [];
	foreach ($delete as &$delete_info) {
		// Check if banned
		$d_board = $delete_info["board"];
		$id = $delete_info["no"];
		if(!in_array($board, $checked_boards)){
			checkBan($d_board);
			$checked_boards[] = $d_board;
		}
		openBoard($d_board);
		$query = prepare(sprintf("SELECT `id`,`thread`,`time`,`password` FROM ``posts_%s`` WHERE `id` = :id", $d_board));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));

		if ($post = $query->fetch(PDO::FETCH_ASSOC)) {
			$thread = false;
			if (!$mod && $config['user_moderation'] && $post['thread']) {
				$thread_query = prepare(sprintf("SELECT `time`,`password` FROM ``posts_%s`` WHERE `id` = :id", $d_board));
				$thread_query->bindValue(':id', $post['thread'], PDO::PARAM_INT);
				$thread_query->execute() or error(db_error($query));

				$thread = $thread_query->fetch(PDO::FETCH_ASSOC);
			}

			if (!$is_mod && $password != '' && $post['password'] != $password && (!$thread || $thread['password'] != $password))
				error($config['error']['invalidpassword']);

			if (!$is_mod &&$post['time'] > time() - $config['delete_time'] && (!$thread || $thread['password'] != $password)) {
				error(sprintf($config['error']['delete_too_soon'], until($post['time'] + $config['delete_time'])));
			}
			if (isset($_POST['file'])) {
				// Delete just the file
				if(!$is_mod){
					deleteFile($id);
					modLog("User deleted file from his own post #$id");
				}
				else{
					mod_deletefile($d_board, $id, NULL);
					modLog("Mod deleted file from post #$id");
				}

				Hazuki::rebuildCatalog($board["uri"]);
			} else {
				// Delete entire post

				if(!$is_mod){
					 deletePost($id, true, true, false, true);
					 modLog("User deleted his own post #$id");
				}
				else {
					mod_delete($d_board, $id, false);
					modLog("Mod deleted file from post #$id");
				}

				Hazuki::rebuildProperties($id, $board["uri"]);
				Hazuki::rebuildCatalog($board["uri"]);

			}
			_syslog(LOG_INFO, 'Deleted post: ' .
				'/' . $d_board . $config['dir']['res'] . link_for($post) . ($post['thread'] ? '#' . $id : '')
			);
		}
	}
	Hazuki::rebuildOverboard();
	Hazuki::rebuildHome();
	Hazuki::rebuildSummary();
	Hazuki::decacheHome();
	buildIndex();

	$root = $is_mod ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];

	if (!isset($_POST['json_response'])) {
		header('Location: ' . $root . "/all/" . $config['file_index'], true, $config['redirect_http']);
	} else {
		header('Content-Type: text/json');
		echo json_encode(array('success' => true));
	}

				// We are already done, let's continue our heavy-lifting work in the background (if we run off FastCGI)
				// if (function_exists('fastcgi_finish_request'))
				// 				@fastcgi_finish_request();

	foreach($checked_boards as $d_board){
		rebuildThemes('post-delete', $d_board);
	}
	Hazuki::send(true);
}
else if (isset($_POST['delete'])) {
	// Delete
//TODO find a more secure method to do this
	global $mod;
	check_login(false);
	$is_mod = $mod && strpos($_SERVER['HTTP_REFERER'],'mod?/') || strpos($_SERVER['HTTP_REFERER'],'mod.php?/');

	if (!isset($_POST['board'], $_POST['pswrd']))
		error($config['error']['bot']);

	$password = &$_POST['pswrd'];

	if ($password == '' && !$is_mod)
		error($config['error']['invalidpassword']);

	$delete = array();
	foreach ($_POST as $post => $value) {
		if (preg_match('/^delete_(\d+)$/', $post, $m)) {
			$delete[] = (int)$m[1];
		}
	}

	checkDNSBL();

	// Check if board exists
	if (!openBoard($_POST['board']))
		error($config['error']['noboard']);

	if (!$is_mod && $config['board_locked']) {
    		error("Board is locked");
	}

	// Check if banned
	checkBan($board['uri']);

	// Check if deletion enabled
	if (!$config['allow_delete'])
		error(_('Post deletion is not allowed!'));

	if (empty($delete))
		error($config['error']['nodelete']);

	foreach ($delete as &$id) {
		$query = prepare(sprintf("SELECT `id`,`thread`,`time`,`password` FROM ``posts_%s`` WHERE `id` = :id", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));

		if ($post = $query->fetch(PDO::FETCH_ASSOC)) {
			$thread = false;
			if (!$mod && $config['user_moderation'] && $post['thread']) {
				$thread_query = prepare(sprintf("SELECT `time`,`password` FROM ``posts_%s`` WHERE `id` = :id", $board['uri']));
				$thread_query->bindValue(':id', $post['thread'], PDO::PARAM_INT);
				$thread_query->execute() or error(db_error($query));

				$thread = $thread_query->fetch(PDO::FETCH_ASSOC);
			}

			if (!$is_mod && $password != '' && $post['password'] != $password && (!$thread || $thread['password'] != $password))
				error($config['error']['invalidpassword']);

			if (!$is_mod &&$post['time'] > time() - $config['delete_time'] && (!$thread || $thread['password'] != $password)) {
				error(sprintf($config['error']['delete_too_soon'], until($post['time'] + $config['delete_time'])));
			}
			if (isset($_POST['file'])) {
				// Delete just the file
				if(!$is_mod){
					deleteFile($id);
					modLog("User deleted file from his own post #$id");
				}
				else{
					mod_deletefile($board['uri'], $id, NULL);
					modLog("Mod deleted file from post #$id");
				}
				Hazuki::rebuildCatalog($board["uri"]);
			} else {
				// Delete entire post
				if(!$is_mod){
					 deletePost($id, true, true, false, true);
					 modLog("User deleted his own post #$id");
				}
				else {
					mod_delete($board['uri'], $id, false);
					modLog("Mod deleted file from post #$id");
				}
				Hazuki::rebuildProperties($id, $board["uri"]);
				Hazuki::rebuildCatalog($board["uri"]);
			}
			_syslog(LOG_INFO, 'Deleted post: ' .
				'/' . $board['dir'] . $config['dir']['res'] . link_for($post) . ($post['thread'] ? '#' . $id : '')
			);
		}
	}
	Hazuki::rebuildOverboard();
	Hazuki::rebuildHome();
	Hazuki::rebuildSummary();
	Hazuki::decacheHome();
	buildIndex();

	$root = $is_mod ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];

	if (!isset($_POST['json_response'])) {
		header('Location: ' . $root . $board['dir'] . $config['file_index'], true, $config['redirect_http']);
	} else {
		header('Content-Type: text/json');
		echo json_encode(array('success' => true));
	}
        // We are already done, let's continue our heavy-lifting work in the background (if we run off FastCGI)
        // if (function_exists('fastcgi_finish_request'))
        //         @fastcgi_finish_request();

	rebuildThemes('post-delete', $board['uri']);
	Hazuki::send(true);

}
else if (isset($_POST['boardless-report'])) {
	if (!$_POST['reason'])
		error($config['error']['bot']);

	$report = array();
	foreach ($_POST as $post => $value) {
		if (preg_match('/^delete_([a-zA-Z0-9]+)_(\d+)$/', $post, $m)) {
			$report[] = ["board" => $m[1], "no" => (int)$m[2]];
		}
	}

	checkDNSBL();

	if (empty($report))
		error($config['error']['noreport']);

	if (count($report) > $config['report_limit'])
		error($config['error']['toomanyreports']);

	$checked_boards = [];
	foreach ($report as &$report_info) {
		$id = $report_info["no"];
		$d_board = $report_info["board"];
		if(!in_array($d_board, $checked_boards)){
			$checked_boards[] = $d_board;
			// Check if banned
			checkBan($d_board);
			$reason = escape_markup_modifiers($_POST['reason']);
			markup($reason);
		}
		openBoard($d_board);
		$query = prepare(sprintf("SELECT `id`, `thread` FROM ``posts_%s`` WHERE `id` = :id", $d_board));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));

		$post = $query->fetch(PDO::FETCH_ASSOC);

		if(!$post){
				error($config['error']['nonexistant']);
		}
	        $error = event('report', array('ip' => $_SERVER['REMOTE_ADDR'], 'board' => $d_board, 'post' => $post, 'reason' => $reason, 'link' => link_for($post)));

	        if ($error) {
	                error($error);
	        }

		if ($config['syslog'])
			_syslog(LOG_INFO, 'Reported post: ' .
				'/' . $d_board['dir'] . $config['dir']['res'] . link_for($post) . ($post['thread'] ? '#' . $id : '') .
				' for "' . $reason . '"'
			);
		$query = prepare("INSERT INTO ``reports`` VALUES (NULL, :time, :ip, :board, :post, :reason)");
		$query->bindValue(':time', time(), PDO::PARAM_INT);
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$query->bindValue(':board', $d_board, PDO::PARAM_STR);
		$query->bindValue(':post', $id, PDO::PARAM_INT);
		$query->bindValue(':reason', $reason, PDO::PARAM_STR);
		$query->execute() or error(db_error($query));
	}

	$is_mod = isset($_POST['mod']) && $_POST['mod'];
	$root = $is_mod ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];

	if (!isset($_POST['json_response'])) {
		$index = $root . $d_board. $config['file_index'];
		echo Element('page.html', array('config' => $config, 'body' => '<div style="text-align:center"><a href="javascript:window.close()">[ ' . _('Close window') ." ]</a> <a href='$index'>[ " . _('Return') . ' ]</a></div>', 'title' => _('Report submitted!')));
	} else {
		header('Content-Type: text/json');
		echo json_encode(array('success' => true));
	}
}
elseif (isset($_POST['report'])) {
	if (!isset($_POST['board'], $_POST['reason']))
		error($config['error']['bot']);

	$report = array();
	foreach ($_POST as $post => $value) {
		if (preg_match('/^delete_(\d+)$/', $post, $m)) {
			$report[] = (int)$m[1];
		}
	}

	checkDNSBL();

	// Check if board exists
	if (!openBoard($_POST['board']))
		error($config['error']['noboard']);

	if ((!isset($_POST['mod']) || !$_POST['mod']) && $config['board_locked']) {
   		error("Board is locked");
	}

	// Check if banned
	checkBan($board['uri']);

	if (empty($report))
		error($config['error']['noreport']);

	if (count($report) > $config['report_limit'])
		error($config['error']['toomanyreports']);

	if ($config['report_captcha'] && !isset($_POST['captcha_text'], $_POST['captcha_cookie'])) {
		error($config['error']['bot']);
	}

	if ($config['report_captcha']) {
		$resp = file_get_contents($config['captcha']['provider_check'] . "?" . http_build_query([
			'mode' => 'check',
			'text' => $_POST['captcha_text'],
			'extra' => $config['captcha']['extra'],
			'cookie' => $_POST['captcha_cookie']
		]));

		if ($resp !== '1') {
      error($config['error']['captcha']);
		}
	}

	$reason = escape_markup_modifiers($_POST['reason']);
	markup($reason);

	foreach ($report as &$id) {
		$query = prepare(sprintf("SELECT `id`, `thread` FROM ``posts_%s`` WHERE `id` = :id", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));

		$post = $query->fetch(PDO::FETCH_ASSOC);

		if(!$post){
				error($config['error']['nonexistant']);
		}
	        $error = event('report', array('ip' => $_SERVER['REMOTE_ADDR'], 'board' => $board['uri'], 'post' => $post, 'reason' => $reason, 'link' => link_for($post)));

	        if ($error) {
	                error($error);
	        }

		if ($config['syslog'])
			_syslog(LOG_INFO, 'Reported post: ' .
				'/' . $board['dir'] . $config['dir']['res'] . link_for($post) . ($post['thread'] ? '#' . $id : '') .
				' for "' . $reason . '"'
			);
		$query = prepare("INSERT INTO ``reports`` VALUES (NULL, :time, :ip, :board, :post, :reason)");
		$query->bindValue(':time', time(), PDO::PARAM_INT);
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$query->bindValue(':board', $board['uri'], PDO::PARAM_STR);
		$query->bindValue(':post', $id, PDO::PARAM_INT);
		$query->bindValue(':reason', $reason, PDO::PARAM_STR);
		$query->execute() or error(db_error($query));
	}

	$is_mod = isset($_POST['mod']) && $_POST['mod'];
	$root = $is_mod ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];

	if (!isset($_POST['json_response'])) {
		$index = $root . $board['dir'] . $config['file_index'];
		echo Element('page.html', array('config' => $config, 'body' => '<div style="text-align:center">' . "<a href='$index'>[ " . _('Return') . ' ]</a></div>', 'title' => _('Report submitted!')));
	} else {
		header('Content-Type: text/json');
		echo json_encode(array('success' => true));
	}
}
elseif (isset($_POST['post']) || $dropped_post) {

	if (!isset($_POST['body'], $_POST['board']) && !$dropped_post && !$config['error']['remove_bot_err']){
		error($config['error']['bot']);
	}
	$post = array('board' => $_POST['board'], 'files' => array());

	if(isset($_POST['captype'])){
		$post['captype'] = $_POST['captype'];
	}

	// Check if board exists
	if (!openBoard($post['board']))
		error($config['error']['noboard']);

	if($config['disable_images'] && (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name']) || ( isset($_POST['file_url']) && !empty($_POST['file_url']) ) ) ){
		error("Images are off right now");
	}

	if ((!isset($_POST['mod']) || !$_POST['mod']) && $config['board_locked']) {
    	error("Board is locked");
	}

	if (isset($_POST['fname'])){
		$post['fname'] = $_POST['fname'];
	}

	if (!isset($_POST['name']))
		$_POST['name'] = $config['anonymous'];

	if (!isset($_POST['email']))
		$_POST['email'] = '';
	if (!isset($_POST['spoiler']))
		$_POST['spoiler'] = "default";

	if (!isset($_POST['subject']))
		$_POST['subject'] = '';

	if (!isset($_POST['pswrd']))
		$_POST['pswrd'] = '';
	if (!isset($_POST['wl_token']))
		$_POST['wl_token'] = [];

	if (isset($_POST['thread'])) {
		$post['op'] = false;
		$post['thread'] = round($_POST['thread']);
	} else
		$post['op'] = true;

	// unset poll fields if not OP
	if($config['poll_board']){
		if(!$post['op']){
			foreach($_POST as $key => $value){
				if(preg_match('/^(pollopt|color)\d+/', $key)){
					unset($_POST[$key]);
				}
			}
		}
		// assign default to unset poll fields
		else{
			$num_opt = 0;
				foreach($_POST as $key => $value){
					if(preg_match('/^pollopt\d+/', $key)){
						$num_opt++;
						if ($_POST[$key] == ""){
							error("Error: Field $num_opt blank");
						}
					}
					if(preg_match('/^color\d+/', $key)){
						if($value=="#000000"){
							$_POST[$key] = '#' . dechex(mt_rand(0, 0xFFFFFF));

						}
					}
				}
			if($num_opt <= 1)
				error("Error: Not enough options");
			if (!isset($_POST['postthresh'])){
				$_POST['postthresh'] = 0;
			}
			if (!isset($_POST['lifespan'])){
				$_POST['lifespan'] = 0;
			}
			if(!isset($_POST['multisel'])){
				$_POST['multisel'] = 'false';
			}
			if(trim($_POST['postthresh']) == ""){
				$_POST['postthresh'] = '0';
			}
			if(trim($_POST['lifespan']) == ""){
				$_POST['lifespan'] = '9000';
			}
		}
	}
	$wl_token_arr = $_POST['wl_token'];
	foreach($wl_token_arr as $wl_token){
		if(!trim($wl_token)){
			$post['wl_token_valid'] = false;
		}else	if(!Whitelist::validateWLToken($wl_token)){
			$post['wl_token_valid'] = false;
			error($config['error']['wlinvalid']);
		}else{
			$post['wl_token_valid'] = true;
			$post['wl_token'] = $wl_token;
		}
	}
	if (!$dropped_post) {
		if($config['last_resort_register_ips']){
			if(!$post['wl_token_valid']){
				$ban_addr = checkIPRegistration();
			}
			checkBan($board['uri'], $post['wl_token_valid']);
			if($ban_addr){
				Bans::new_ban($ban_addr, "(Posting is Limited) -- Submit an appeal to whitelist your range for posting", 0, false, -1);
			}
		}
		else{
			// Check if banned
			checkBan($board['uri'], $post['wl_token_valid']);
		}

		// Check for CAPTCHA right after opening the board so the "return" link is in there
		if ($config['recaptcha']) {
			if (!isset($_POST['g-recaptcha-response']))
				error($config['error']['bot']);

			// Check what reCAPTCHA has to say...
			$resp = json_decode(file_get_contents(sprintf('https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s&remoteip=%s',
				$config['recaptcha_private'],
				urlencode($_POST['g-recaptcha-response']),
				$_SERVER['REMOTE_ADDR'])), true);

			if (!$resp['success']) {
				error($config['error']['captcha']);
			}
		// Same, but now with our custom captcha provider
 		if (($config['captcha']['enabled']) || (($post['op']) && ($config['new_thread_capt'])) ) {
		$resp = file_get_contents($config['captcha']['provider_check'] . "?" . http_build_query([
			'mode' => 'check',
			'text' => $_POST['captcha_text'],
			'extra' => $config['captcha']['extra'],
			'cookie' => $_POST['captcha_cookie']
		]));
		if ($resp !== '1') {
                        error($config['error']['captcha'] .
			'<script>if (actually_load_captcha !== undefined) actually_load_captcha("'.$config['captcha']['provider_get'].'", "'.$config['captcha']['extra'].'");</script>');
		}
	}
}
		if (!$config['error']['remove_bot_err'] && !(($post['op'] && $_POST['post'] == $config['button_newtopic']) ||
			(!$post['op'] && $_POST['post'] == $config['button_reply'])))
			error($config['error']['bot']);

		// Check the referrer
		if ($config['referer_match'] !== false &&
			(!isset($_SERVER['HTTP_REFERER']) || !preg_match($config['referer_match'], rawurldecode($_SERVER['HTTP_REFERER']))))
			error($config['error']['referer']);
	//DNS black list
		checkDNSBL();



		if ($post['mod'] = isset($_POST['mod']) && $_POST['mod']) {
			check_login(false);
			if (!$mod) {
				// Liar. You're not a mod.
				error($config['error']['notamod']);
			}

			$post['sticky'] = $post['op'] && isset($_POST['sticky']);
			$post['locked'] = $post['op'] && isset($_POST['lock']);
			$post['raw'] = isset($_POST['raw']);

			if ($post['sticky'] && !hasPermission($config['mod']['sticky'], $board['uri']))
				error($config['error']['noaccess']);
			if ($post['locked'] && !hasPermission($config['mod']['lock'], $board['uri']))
				error($config['error']['noaccess']);
			if ($post['raw'] && !hasPermission($config['mod']['rawhtml'], $board['uri']))
				error($config['error']['noaccess']);
		}

		if (!$post['mod']) {
			$post['antispam_hash'] = checkSpam(array($board['uri'], isset($post['thread']) ? $post['thread'] : ($config['try_smarter'] && isset($_POST['page']) ? 0 - (int)$_POST['page'] : null)));
			// if ($post['antispam_hash'] === true)
				// error($config['error']['spam']);
		}

		if ($config['robot_enable'] && $config['robot_mute']) {
			checkMute();
		}
	}
	else {
		$mod = $post['mod'] = false;
	}


	//Check if thread exists
	if (!$post['op']) {
		$query = prepare(sprintf("SELECT `sticky`,`locked`,`cycle`,`sage`,`slug` FROM ``posts_%s`` WHERE `id` = :id AND `thread` IS NULL LIMIT 1", $board['uri']));
		$query->bindValue(':id', $post['thread'], PDO::PARAM_INT);
		$query->execute() or error(db_error());

		if (!$thread = $query->fetch(PDO::FETCH_ASSOC)) {
			// Non-existant
			error($config['error']['nonexistant']);
		}
	}
	else {
		$thread = false;
	}

	// Check for an embed field
	if ($config['enable_embedding'] && isset($_POST['embed']) && !empty($_POST['embed'])) {
		// yep; validate it
		$value = $_POST['embed'];
		foreach ($config['embedding'] as &$embed) {
			if (preg_match($embed[0], $value)) {
				// Valid link
				$post['embed'] = $value;
				// This is bad, lol.
				$post['no_longer_require_an_image_for_op'] = true;
				break;
			}
		}
		if (!isset($post['embed'])) {
			error($config['error']['invalid_embed']);
		}
	}

	if (!hasPermission($config['mod']['bypass_field_disable'], $board['uri'])) {
		if ($config['field_disable_name'])
			$_POST['name'] = $config['anonymous']; // "forced anonymous"

		if ($config['field_disable_email'])
			$_POST['email'] = '';

		if ($config['field_disable_password'])
			$_POST['pswrd'] = '';

		if ($config['field_disable_subject'] || (!$post['op'] && $config['field_disable_reply_subject']))
			$_POST['subject'] = '';
	}

	if ($config['allow_upload_by_url'] && isset($_POST['file_url']) && !empty($_POST['file_url'])) {
		// yep; validate it
    $value = $_POST['file_url'];
    foreach ($config['embedding'] as &$embed) {
            if (preg_match($embed[0], $value)) {
                    // Valid link
                    $post['embed'] = $value;
                    // This is bad, lol.
                    $post['no_longer_require_an_image_for_op'] = true;
                    break;
            }
    }

    if(!isset($post['no_longer_require_an_image_for_op'])){

			$post['file_url'] = $_POST['file_url'];
			if (!preg_match('@^https?://@', $post['file_url'])){
				//error($config['error']['invalidimg']);
				$post['file_url'] = "http://" . $post['file_url'];
			}


			if (mb_strpos($post['file_url'], '?') !== false)
				$url_without_params = mb_substr($post['file_url'], 0, mb_strpos($post['file_url'], '?'));
			else
				$url_without_params = $post['file_url'];

			$post['extension'] = strtolower(mb_substr($url_without_params, mb_strrpos($url_without_params, '.') + 1));

			if ($post['op'] && $config['allowed_ext_op']) {
				if (!in_array($post['extension'], $config['allowed_ext_op']))
					error($config['error']['unknownext']);
			}
			else if (!in_array($post['extension'], $config['allowed_ext']) && !in_array($post['extension'], $config['allowed_ext_files']))
				error($config['error']['unknownext']);

			$post['file_tmp'] = tempnam($config['tmp'], 'url');
			function unlink_tmp_file($file) {
				@unlink($file);
				fatal_error_handler();
			}
			register_shutdown_function('unlink_tmp_file', $post['file_tmp']);

			$fp = fopen($post['file_tmp'], 'w');

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $post['file_url']);
			curl_setopt($curl, CURLOPT_FAILONERROR, true);
			$config['url_upload_proxy'] ? curl_setopt($curl, CURLOPT_PROXY, $config['url_upload_proxy']) : "";
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($curl, CURLOPT_USERAGENT, 'Tinyboard');
			curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($curl, CURLOPT_FILE, $fp);
			curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
			curl_setopt($curl, CURLOPT_TIMEOUT, $config['upload_by_url_timeout']);

			if (curl_exec($curl) === false)
				error($config['error']['nomove'] . '<br/>Curl says: ' . curl_error($curl));

			curl_close($curl);

			fclose($fp);

			$_FILES['file'] = array(
				'name' => basename($url_without_params),
				'tmp_name' => $post['file_tmp'],
				'file_tmp' => true,
				'error' => 0,
				'size' => filesize($post['file_tmp'])
			);
		}
	}

//  proccess post data into poll json format if  applicable. throw errors where needed and insert into body
	$post['poll_data'] = Polling::formatFields($_POST);
	$post['name'] = $_POST['name'] != '' ? $_POST['name'] : $config['anonymous'];
	$post['subject'] = $_POST['subject'];
	$post['email'] = str_replace(' ', '%20', htmlspecialchars($_POST['email']));
	$post['body'] = $_POST['body'];
	$post['password'] = $_POST['pswrd'];
	$post['has_file'] = (!isset($post['embed']) && (($post['op'] && !isset($post['no_longer_require_an_image_for_op']) &&
		$config['force_image_op']) || isset($_FILES["file"]) && $_FILES["file"]["tmp_name"] != ""));
	if (!$dropped_post) {
		if (!($post['has_file'] || isset($post['embed'])) || (($post['op'] && $config['force_body_op']) || (!$post['op'] && $config['force_body']))) {
			$stripped_whitespace = preg_replace('/[\s]/u', '', $post['body']);
			if ($stripped_whitespace == '') {
				error($config['error']['tooshort_body']);
			}
		}

		if (!$post['op']) {
			// Check if thread is locked
			// but allow mods to post
			if ($thread['locked'] && !hasPermission($config['mod']['postinlocked'], $board['uri']))
				error($config['error']['locked']);

			$numposts = numPosts($post['thread']);

			if ($config['reply_hard_limit'] != 0 && $config['reply_hard_limit'] <= $numposts['replies'])
				error($config['error']['reply_hard_limit']);

			if ($post['has_file'] && $config['image_hard_limit'] <= $numposts['images'])
				error($config['error']['image_hard_limit']);
		}

	}
	else {
		if (!$post['op']) {
			$numposts = numPosts($post['thread']);
		}
	}

	if ($post['has_file']) {
		// Determine size sanity
		$size = 0;
		if ($config['multiimage_method'] == 'split') {
			foreach ($_FILES as $key => $file) {
				$size += $file['size'];
			}
		} elseif ($config['multiimage_method'] == 'each') {
			foreach ($_FILES as $key => $file) {
				if ($file['size'] > $size) {
					$size = $file['size'];
				}
			}
		} else {
			error(_('Unrecognized file size determination method.'));
		}

		if ($size > $config['max_filesize'])
			error(sprintf3($config['error']['filesize'], array(
				'sz' => number_format($size),
				'filesz' => number_format($size),
				'maxsz' => number_format($config['max_filesize'])
			)));
		$post['filesize'] = $size;
	}


	$post['capcode'] = false;

	if ($mod && preg_match('/^((.+) )?## (.+)$/', $post['name'], $matches)) {
		$name = $matches[2] != '' ? $matches[2] : $config['anonymous'];
		$cap = $matches[3];

		if (isset($config['mod']['capcode'][$mod['type']])) {
			if (	$config['mod']['capcode'][$mod['type']] === true ||
				(is_array($config['mod']['capcode'][$mod['type']]) &&
					in_array($cap, $config['mod']['capcode'][$mod['type']])
				)) {

				$post['capcode'] = utf8tohtml($cap);
				$post['name'] = $name;
			}
		}
	}

	$trip = generate_tripcode($post['name']);
	$post['name'] = $trip[0];
	$post['trip'] = isset($trip[1]) ? $trip[1] : ''; // XX: Dropped posts and tripcodes

	$noko = false;
	if (preg_match('/noko/', strtolower($post['email']))) {
		$noko = true;
	} elseif (preg_match('/nonoko/', strtolower($post['email']))){
		$noko = false;
	} else $noko = $config['always_noko'];

	if ($post['has_file']) {
		$i = 0;
		foreach ($_FILES as $key => $file) {
			if (!in_array($file['error'], array(UPLOAD_ERR_NO_FILE, UPLOAD_ERR_OK))) {
				error(sprintf3($config['error']['phpfileserror'], array(
					'index' => $i+1,
					'code' => $file['error']
				)));
			}

			if ($file['size'] && $file['tmp_name']) {
				$file['filename'] = urldecode($file['name']);
				$file['extension'] = strtolower(mb_substr($file['filename'], mb_strrpos($file['filename'], '.') + 1));
				if(isset($post['fname'])){
					$file['name'] = str_replace("." . $file['extension'], "", urldecode($post['fname'])) . "." . $file['extension'];
				}
				$file['filename'] = urldecode($file['name']);
				if (isset($config['filename_func']))
					$file['file_id'] = $config['filename_func']($file);
				else
					$file['file_id'] = time() . substr(microtime(), 2, 3);

				if (sizeof($_FILES) > 1)
					$file['file_id'] .= "-$i";

				$file['file'] = $board['dir'] . $config['dir']['img'] . $file['file_id'] . '.' . $file['extension'];
				$file['thumb'] = $board['dir'] . $config['dir']['thumb'] . $file['file_id'] . '.' . ($config['thumb_ext'] ? $config['thumb_ext'] : $file['extension']);
				$post['files'][] = $file;
				$i++;
			}
		}
	}

	if (empty($post['files'])) $post['has_file'] = false;

	if (!$dropped_post) {
		// Check for a file
		if ($post['op'] && !isset($post['no_longer_require_an_image_for_op'])) {
			if (!$post['has_file'] && $config['force_image_op'])
				error($config['error']['noimage']);
		}

		// Check for too many files
		if (sizeof($post['files']) > $config['max_images'])
			error($config['error']['toomanyimages']);
	}

	$post['name'] = strip_combining_chars($post['name']);
	$post['email'] = strip_combining_chars($post['email']);
	$post['subject'] = strip_combining_chars($post['subject']);
	if ($config['strip_combining_chars']) {
		$post['body'] = strip_combining_chars($post['body']);
	}

	if (!$dropped_post) {
		// Check string lengths
		if (mb_strlen($post['name']) > 35)
			error(sprintf($config['error']['toolong'], 'name'));
		if (mb_strlen($post['email']) > 40)
			error(sprintf($config['error']['toolong'], 'email'));
		if (mb_strlen($post['subject']) > 100)
			error(sprintf($config['error']['toolong'], 'subject'));
		if (!$mod && mb_strlen($post['body']) > $config['max_body'])
			error($config['error']['toolong_body']);
		if (!$mod && substr_count($post['body'], PHP_EOL) > $config['max_newlines'])
			error($config['error']['toomanylines']);
		if (mb_strlen($post['password']) > 20)
			error(sprintf($config['error']['toolong'], 'password'));
	}
	wordfilters($post['body']);

	$post['body'] = escape_markup_modifiers($post['body']);


	if ($mod && isset($post['raw']) && $post['raw']) {
		$post['body'] .= "\n<tinyboard raw html>1</tinyboard>";
	}

	if (!$dropped_post)
	if (($config['country_flags'] && !$config['allow_no_country']) || ($config['country_flags'] && $config['allow_no_country'] && !isset($_POST['no_country']))) {
		require 'inc/lib/geoip/geoip.inc';
		$gi=geoip\geoip_open('inc/lib/geoip/GeoIPv6.dat', GEOIP_STANDARD);

		function ipv4to6($ip) {
			if (strpos($ip, ':') !== false) {
				if (strpos($ip, '.') > 0)
					$ip = substr($ip, strrpos($ip, ':')+1);
				else return $ip;  //native ipv6
			}
			$iparr = array_pad(explode('.', $ip), 4, 0);
			$part7 = base_convert(($iparr[0] * 256) + $iparr[1], 10, 16);
			$part8 = base_convert(($iparr[2] * 256) + $iparr[3], 10, 16);
			return '::ffff:'.$part7.':'.$part8;
		}

		if ($country_code = geoip\geoip_country_code_by_addr_v6($gi, ipv4to6($_SERVER['REMOTE_ADDR']))) {
			if (!in_array(strtolower($country_code), array('eu', 'ap', 'o1', 'a1', 'a2')))
				$post['body'] .= "\n<tinyboard flag>".strtolower($country_code)."</tinyboard>".
				"\n<tinyboard flag alt>".geoip\geoip_country_name_by_addr_v6($gi, ipv4to6($_SERVER['REMOTE_ADDR']))."</tinyboard>";
		}
	}

	if (mysql_version() >= 50503) {
		$post['body_nomarkup'] = $post['body']; // Assume we're using the utf8mb4 charset
	} else {
		// MySQL's `utf8` charset only supports up to 3-byte symbols
		// Remove anything >= 0x010000

		$chars = preg_split('//u', $post['body'], -1, PREG_SPLIT_NO_EMPTY);
		$post['body_nomarkup'] = '';
		foreach ($chars as $char) {
			$o = 0;
			$ord = ordutf8($char, $o);
			if ($ord >= 0x010000)
				continue;
			$post['body_nomarkup'] .= $char;
		}
	}
//modify body reference
	$post['tracked_cites'] = markup($post['body'], true);
	if($config['poll_board'] && !isset($post['thread']))
		$post['body'] = Polling::bodyAddablePoll($post['poll_data']) . $post['body'];

	if ($post['has_file']) {
		$md5cmd = false;
		if ($config['bsd_md5'])  $md5cmd = '/sbin/md5 -r';
		if ($config['gnu_md5'])  $md5cmd = 'md5sum';
		$allhashes = '';

		foreach ($post['files'] as $key => &$file) {
			if ($post['op'] && $config['allowed_ext_op']) {
				if (!in_array($file['extension'], $config['allowed_ext_op']))
					error($config['error']['unknownext']);
			}
			elseif (!in_array($file['extension'], $config['allowed_ext']) && !in_array($file['extension'], $config['allowed_ext_files']))
				error($config['error']['unknownext']);

			$file['is_an_image'] = !in_array($file['extension'], $config['allowed_ext_files']);

			// Truncate filename if it is too long
			$file['filename'] = mb_substr($file['filename'], 0, $config['max_filename_len']);

			$upload = $file['tmp_name'];

			if (!is_readable($upload))
				error($config['error']['nomove']);

			if ($md5cmd &&  $file['is_an_image']) {
				$output = shell_exec_error($md5cmd . " " . escapeshellarg($upload));
				$output_arr = explode(' ', $output);
				$hash = $output_arr[0];
				if($hash == "Decoded"){
					$output_arr = explode(' ', explode(PHP_EOL, $output)[2]);
					$hash = $output_arr[0];
				}
			}
			else {
				$hash = md5_file($upload);
			}
			$file['hash'] = $hash;
			$allhashes .= $hash;

			$file['spoiler'] = $_POST['spoiler'];
		}

		if (count ($post['files']) == 1) {
			$post['filehash'] = $hash;
		}
		else {
			$post['filehash'] = md5($allhashes);
		}
	}

	// don't filter whitelisters
	if($post['wl_token_valid']){ }
	else if (!hasPermission($config['mod']['bypass_filters'], $board['uri']) && !$dropped_post) {
		require_once 'inc/filters.php';
		// Captcha flood Bypass done in here
		do_filters($post);
	}
	Post_ImageProcessing::proccess($post);


	// Do filters again if OCRing
	if ($config['tesseract_ocr'] && !hasPermission($config['mod']['bypass_filters'], $board['uri']) && !$dropped_post) {
		do_filters($post);
	}


	if (!hasPermission($config['mod']['postunoriginal'], $board['uri']) && $config['robot_enable'] && checkRobot($post['body_nomarkup']) && !$dropped_post) {
		undoImage($post);
		if ($config['robot_mute']) {
			error(sprintf($config['error']['muted'], mute()));
		} else {
			error($config['error']['unoriginal']);
		}
	}

	// Remove board directories before inserting them into the database.
	if ($post['has_file']) {
		foreach ($post['files'] as $key => &$file) {
			$file['file_path'] = $file['file'];
			$file['thumb_path'] = $file['thumb'];
			$file['file'] = mb_substr($file['file'], mb_strlen($board['dir'] . $config['dir']['img']));
			if ($file['is_an_image'] && $file['thumb'] != 'spoiler')
				$file['thumb'] = mb_substr($file['thumb'], mb_strlen($board['dir'] . $config['dir']['thumb']));
			else if($file['thumb'] == 'file'){
				$file['thumb_path'] = sprintf($config['file_thumb'], isset($config['file_icons'][$file['extension']]) ?
					$config['file_icons'][$file['extension']] : $config['file_icons']['default']);
			}	else if($file['thumb'] == 'spoiler'){
				if($file['spoiler'] == "nsfw"){
					$file['thumb_path'] = $config['nsfw_image'];
				} else{
					$file['thumb_path'] = $config['spoiler_image'];
				}
			}
		}
		if (!$post['wl_token_valid'] && $file['is_an_image'] && $config['blockhash']){
			$output = shell_exec_error("blockhash " . escapeshellarg($file['thumb_path']));
			$output_arr = explode(' ', $output);
			$hash = $output_arr[0];
			if($hash == "Decoded"){
				$output_arr = explode(' ', explode(PHP_EOL, $output)[2]);
				$hash = $output_arr[0];
			}
			if($hash && !verifyUnbannedHash($hash)){
				error($config['error']['imagespam']);
			}
		}
	}

	$post = (object)$post;
	$post->files = array_map(function($a) { return (object)$a; }, $post->files);

					// ____

	$error = event('post', $post);

						// ^^^
	$post->files = array_map(function($a) { return (array)$a; }, $post->files);



	if ($error) {
		undoImage((array)$post);
		error($error);
	}
	$post = (array)$post;

	if ($post['files'])
		$post['files'] = $post['files'];
	$post['num_files'] = sizeof($post['files']);


	//use to simplify post and release
	if(!isset($_COOKIE['ui']) || $_COOKIE['ui'] == "2"){
		if(isset($numposts)){
			react_laterPost($post, $thread, $numposts, $noko, $dropped_post, $pdo);
		}
		else{
			react_laterPost($post, $thread, null, $noko, $dropped_post, $pdo);
		}
	} else{
		if(isset($numposts)){
			post_laterPost($post, $thread, $numposts, $noko, $dropped_post, $pdo);
		}
		else{
			post_laterPost($post, $thread, null, $noko, $dropped_post, $pdo);
		}
	 }
}
elseif(isset($_POST['release'])){
	//reference
	//board
	//release

	//how's the captcha...
	if($config['flood_recaptcha'] && isset($_POST['recaptcha'])){
		//https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
		$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $config['recaptcha_private'] . '&response=' . $_POST['recaptcha'] . '&remoteip=' . $_SERVER['HTTP_REFERER'];
		$data = array(
			'secret' => $config['recaptcha_private'],
			'response ' => $_POST['recaptcha'],
			'remoteip' => $_SERVER['HTTP_REFERER']
		);
		$options = array('http'=>array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST'
		));
		$context = stream_context_create($options);
		$result= file_get_contents($url);
		if($result === false){
			error('Bad URL');
		}
		else{
			$result = json_decode($result, true);
			if(isset($result['success'])){
				if($result['success']){	}
				else if(isset($result['error-codes'])){
					error('Bad captcha: ' . implode(', ', $result['error-codes']));
				}
			}
			else{
				error('Captcha API error');
			}
		}
	}
	elseif ($config['flood_captchouli'] && isset($_POST['captchouli'])){

		$kissu = curl_init($config['captchouli_addr'] . 'status?captchouli-id=' . $_POST['captchouli']);
		curl_setopt($kissu, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($kissu);

		if($result === false){
			error('Bad URL');
		}
		else{
			if ($result == "true"){	}
			else{
				if($result == "false")
					error('Bad captcha: Moldy captcha');
				else
					error('Bad captcha: ' . $result);
			}
		}
	}
	else{
		error("where is your captcha?");
	}


	global $board;
	$reference = $_POST['reference'];
	$query = prepare("SELECT * FROM `withheld` WHERE `reference`='$reference'") or error(db_error());
	$query->execute();
	$post = $query->fetchAll()[0];
	$query = prepare("DELETE FROM `withheld` WHERE `reference`='$reference'") or error(db_error());
	$query->execute();

	if(sizeof($post) == 0){
		error("Captcha expired");
	}

	openBoard($post['board']);

	$post['files'] = json_decode($post['files'], true);
	$body = str_replace(">", "&gt;", $post['body_nomarkup']);

	preg_match_all('/&gt;&gt;(\d+?)(?=[^0-9>]|$)/mi', $body, $cites, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	foreach($cites as $cite){
		$post['tracked_cites'][] = [$board['uri'], $cite[1][0]];
	}
	preg_match_all('/()?&gt;&gt;&gt;\/(' . $config['board_regex'] . 'f?)\/(\d+)?(?=[\s,.)?])?/um', $body, $cross_cites, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	foreach($cross_cites as $cite){
		$post['tracked_cites'][] = [$cite[2][0], $cite[3][0]];
	}


	if($post['num_files'] > 0) $post['has_file'] = true;
	else $post['has_file'] = false;

	$noko = false;
	if (preg_match('/noko/', strtolower($post['email']))) {
		$noko = true;
	} elseif (preg_match('/nonoko/', strtolower($post['email']))){
		$noko = false;
	} else $noko = $config['always_noko'];

	$post['op'] = true;
	if(isset($post['thread'])){
		$post['op'] = false;
	}
	$thread['sage'] = $post['sage'];
	$thread['cycle'] = $post['cycle'];
	if (!$post['op']) {
		$numposts = numPosts($post['thread']);
	}
    $post['mod'] = isset($_POST['mod']);
	if (!$post['mod']) {
		// $post['antispam_hash'] = checkSpam(array($board['uri'], isset($post['thread']) ? $post['thread'] : ($config['try_smarter'] && isset($_POST['page']) ? 0 - (int)$_POST['page'] : null)));
		// if ($post['antispam_hash'] === true)
			// error($config['error']['spam']);
	}

	if (!hasPermission($config['mod']['postunoriginal'], $board['uri']) && $config['robot_enable'] && checkRobot($post['body_nomarkup']) && !$dropped_post) {
		undoImage($post);
		if ($config['robot_mute']) {
			error(sprintf($config['error']['muted'], mute()));
		} else {
			error($config['error']['unoriginal']);
		}
	}

			// Remove board directories before inserting them into the database.
	if ($post['has_file']) {
		foreach ($post['files'] as $key => &$file) {
			$file['file_path'] = $file['file'];
			$file['thumb_path'] = $file['thumb'];
			$file['file'] = mb_substr($file['file'], mb_strlen($board['dir'] . $config['dir']['img']));
			if ($file['is_an_image'] && $file['thumb'] != 'spoiler')
				$file['thumb'] = mb_substr($file['thumb'], mb_strlen($board['dir'] . $config['dir']['thumb']));
			else if($file['thumb'] == 'file'){
				$file['thumb_path'] = sprintf($config['file_thumb'], isset($config['file_icons'][$file['extension']]) ?
					$config['file_icons'][$file['extension']] : $config['file_icons']['default']);
			}	else if($file['thumb'] == 'spoiler'){
				if($file['spoiler'] == "nsfw"){
					$file['thumb_path'] = $config['nsfw_image'];
				} else{
					$file['thumb_path'] = $config['spoiler_image'];
				}
			}
		}
		$output = shell_exec_error("blockhash " . escapeshellarg($file['thumb_path']));
		$output_arr = explode(' ', $output);
		$hash = $output_arr[0];
		if($hash == "Decoded"){
			$output_arr = explode(' ', explode(PHP_EOL, $output)[2]);
			$hash = $output_arr[0];
		}
		if($hash && !verifyUnbannedHash($hash)){
			error($config['error']['imagespam']);
		}
	}

  $post = (object)$post;
	$post->files = (array) $post->files;
	$post->files = array_map(function($a) { return (object)$a; }, $post->files);

					// ____

	$error = event('post', $post);

						// ^^^
	$post->files = array_map(function($a) { return (array)$a; }, $post->files);



	if ($error) {
		undoImage((array)$post);
		error($error);
	}

	$post = (array)$post;

	// $root = $post['mod'] ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];
	// if($config["remove_ext"]){
	// 	$redirect = preg_replace('/\\.[^.\\s]{3,4}$/', '', $root . $board['dir']);
	// }
	// else{
	// 	$redirect = $root . $board['dir'];
	// }
	//
	// if (!$post['mod']) header('X-Associated-Content: "' . $redirect . '"');
	//
	// if (!isset($_POST['json_response'])) {
	// 	header('Location: ' . $redirect, true, $config['redirect_http']);
	// } else {
	// 	header('Content-Type: text/json; charset=utf-8');
	// 	echo json_encode(array(
	// 		'redirect' => $redirect . "index",
	// 		'noko' => $noko,
	// 		'id' => "0"
	// 	));
	// }
	//
	// // We are already done, let's continue our heavy-lifting work in the background (if we run off FastCGI)
	// if (function_exists('fastcgi_finish_request')){
	// 	ignore_user_abort(true);
	// 	set_time_limit(0);
	// 	fastcgi_finish_request();
	// 	// Clean up buffers
	// 	if(ob_get_level() > 0){
	// 			ob_end_clean();
	// 			ob_flush();
	// 	}
	// 	flush();
	// }
	//
	// // Hold post in place for captcha-queue
	unset($post['time']);
	CaptchaQueue::holdForQueue();

	//use to simplify post and release
	if(!isset($_COOKIE['ui']) || $_COOKIE['ui'] == "2"){
		if(isset($numposts)){
			react_laterPost($post, $thread, $numposts, $noko, $dropped_post, $pdo);
		}
		else{
			react_laterPost($post, $thread, null, $noko, $dropped_post, $pdo);
		}
	} else{
		if(isset($numposts)){
			post_laterPost($post, $thread, $numposts, $noko, $dropped_post, $pdo);
		}
		else{
			post_laterPost($post, $thread, null, $noko, $dropped_post, $pdo);
		}
	 }

}
elseif (isset($_POST['appeal'])) {

	if (!isset($_POST['ban_id']))
		error($config['error']['bot']);

	$ban_id = (int)$_POST['ban_id'];

	$bans = Bans::find($_SERVER['REMOTE_ADDR']);
	foreach ($bans as $_ban) {
		if ($_ban['id'] == $ban_id) {
			$ban = $_ban;
			break;
		}
	}

	if (!isset($ban)) {
		error(_("That ban doesn't exist or is not for you."));
	}

	if ($ban['expires'] && $ban['expires'] - $ban['created'] <= $config['ban_appeals_min_length']) {
		error(_("You cannot appeal a ban of this length."));
	}

	$query = query("SELECT `denied` FROM ``ban_appeals`` WHERE `ban_id` = $ban_id") or error(db_error());
	$ban_appeals = $query->fetchAll(PDO::FETCH_COLUMN);

	if (count($ban_appeals) >= $config['ban_appeals_max']) {
		error(_("You cannot appeal this ban again."));
	}

	foreach ($ban_appeals as $is_denied) {
		if (!$is_denied)
			error(_("There is already a pending appeal for this ban."));
	}


	Hazuki::sendAppeal($ban_id, $_POST['appeal']);
	Hazuki::send();
	displayBan($ban);
} else {
	if (!file_exists($config['has_installed'])) {
		header('Location: install.php', true, $config['redirect_http']);
	} else {
		// They opened post.php in their browser manually.
		error($config['error']['nopost']);
	}
}
