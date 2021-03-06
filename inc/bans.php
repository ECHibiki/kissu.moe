<?php

require 'inc/lib/IP/Lifo/IP/IP.php';
require 'inc/lib/IP/Lifo/IP/BC.php';
require 'inc/lib/IP/Lifo/IP/CIDR.php';

use Lifo\IP\CIDR;

// clean ipv4 for operations
function make_parsable(&$ip){
	$ip = explode(".", $ip);
	foreach ($ip as &$component){
		$end_pt = 0;
		for($i = 0 ; $i < strlen($component); $i++){
			if($component[$i] == "0")
				$end_pt = $i + 1;
			else{
				if($end_pt == strlen($component)){
					$component = "0";
				}
				else{
					$component = substr($component, $i);
				}
				break;
			}
		}
	};
	$ip = implode(".", $ip);
	return $ip;
}

class Bans {
	static public function range_to_string($mask) {
		list($ipstart, $ipend) = $mask;

		if (!isset($ipend) || $ipend === false) {
			// Not a range. Single IP address.
			$ipstr = inet_ntop($ipstart);
			return $ipstr;
		}

		if (strlen($ipstart) != strlen($ipend))
			return '???'; // What the fuck are you doing, son?

		$range = CIDR::range_to_cidr(inet_ntop($ipstart), inet_ntop($ipend));
		if ($range !== false)
			return $range;

		return '???';
	}

	private static function calc_cidr($mask) {
		$cidr = new CIDR($mask);
		$range = $cidr->getRange();

		return array(inet_pton($range[0]), inet_pton($range[1]));
	}

	public static function parse_time($str) {
		if (empty($str))
			return false;

		if (($time = @strtotime($str)) !== false)
			return $time;

		if (!preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $str, $matches))
			return false;

		$expire = 0;

		if (isset($matches[2])) {
			// Years
			$expire += (int)$matches[2]*60*60*24*365;
		}
		if (isset($matches[4])) {
			// Months
			$expire += (int)$matches[4]*60*60*24*30;
		}
		if (isset($matches[6])) {
			// Weeks
			$expire += (int)$matches[6]*60*60*24*7;
		}
		if (isset($matches[8])) {
			// Days
			$expire += (int)$matches[8]*60*60*24;
		}
		if (isset($matches[10])) {
			// Hours
			$expire += (int)$matches[10]*60*60;
		}
		if (isset($matches[12])) {
			// Minutes
			$expire += (int)$matches[12]*60;
		}
		if (isset($matches[14])) {
			// Seconds
			$expire += (int)$matches[14];
		}

		return time() + $expire;
	}

	static public function parse_range($mask) {
		$ipstart = false;
		$ipend = false;

		if (preg_match('@^(\d{1,3}\.){1,3}([\d*]{1,3})?$@', $mask) && substr_count($mask, '*') == 1) {
			// IPv4 wildcard mask
			$parts = explode('.', $mask);
			$ipv4 = '';
			foreach ($parts as $part) {
				if ($part == '*') {
					$ipstart = inet_pton($ipv4 . '0' . str_repeat('.0', 3 - substr_count($ipv4, '.')));
					$ipend = inet_pton($ipv4 . '255' . str_repeat('.255', 3 - substr_count($ipv4, '.')));
					break;
				} elseif(($wc = strpos($part, '*')) !== false) {
					$ipstart = inet_pton($ipv4 . substr($part, 0, $wc) . '0' . str_repeat('.0', 3 - substr_count($ipv4, '.')));
					$ipend = inet_pton($ipv4 . substr($part, 0, $wc) . '9' . str_repeat('.255', 3 - substr_count($ipv4, '.')));
					break;
				}
				$ipv4 .= "$part.";
			}
		} elseif (preg_match('@^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/\d+$@', $mask)) {
			list($ipv4, $bits) = explode('/', $mask);
			if ($bits > 32)
				return false;

			list($ipstart, $ipend) = self::calc_cidr($mask);
		} elseif (preg_match('@^[:a-z\d]+/\d+$@i', $mask)) {
			list($ipv6, $bits) = explode('/', $mask);
			if ($bits > 128)
				return false;

			list($ipstart, $ipend) = self::calc_cidr($mask);
		} else {
			if (($ipstart = @inet_pton($mask)) === false)
				return false;
		}

		return array($ipstart, $ipend);
	}

	static public function find($ip, $board = false, $get_mod_info = false) {
		global $config;
		make_parsable($ip);
		$query = prepare('SELECT ``bans``.*' . ($get_mod_info ? ', `username`' : '') . ' FROM ``bans``
		' . ($get_mod_info ? 'LEFT JOIN ``mods`` ON ``mods``.`id` = `creator`' : '') . '
		WHERE
			(' . ($board !== false ? '(`board` IS NULL OR `board` = :board) AND' : '') . '
			(`ipstart` = :ip OR (:ip >= `ipstart` AND :ip <= `ipend`)))
		ORDER BY `expires` IS NULL, `expires` DESC');

		if ($board !== false)
			$query->bindValue(':board', $board, PDO::PARAM_STR);

		$query->bindValue(':ip', inet_pton($ip));
		$query->execute() or error(db_error($query));

		$ban_list = array();
		while ($ban = $query->fetch(PDO::FETCH_ASSOC)) {
			if ($ban['expires'] && ($ban['seen'] || !$config['require_ban_view']) && $ban['expires'] < time()) {
				if(!$config['last_resort_register_ips']){
					self::delete($ban['id']);
				}
			} else {
				if ($ban['post'])
					$ban['post'] = json_decode($ban['post'], true);
				$ban['mask'] = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
				$ban_list[] = $ban;
			}
		}

		return $ban_list;
	}
	static public function findID($id, $board = false, $get_mod_info = false){
		global $config;
		$query = prepare('SELECT ``bans``.*' . ($get_mod_info ? ', `username`' : '') . ' FROM ``bans``
		' . ($get_mod_info ? 'LEFT JOIN ``mods`` ON ``mods``.`id` = `creator`' : '') . '
		WHERE
			(' . ($board !== false ? '(`board` IS NULL OR `board` = :board) AND' : '') . '
			(``bans``.`id` = :id )
			)
		ORDER BY `expires` IS NULL, `expires` DESC');

		if ($board !== false)
			$query->bindValue(':board', $board, PDO::PARAM_STR);

		$query->bindValue(':id', $id);
		$query->execute() or error(db_error($query));

		$ban = $query->fetch(PDO::FETCH_ASSOC);
		if($ban === false){
			return false;
		} else if ($ban['expires'] && ($ban['seen'] || !$config['require_ban_view']) && $ban['expires'] < time()) {
			if(!$config['last_resort_register_ips']){
				self::delete($ban['id']);
			}
		} else {
			if ($ban['post'])
				$ban['post'] = json_decode($ban['post'], true);
			$ban['mask'] = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
		}
		return $ban;
	}
	static public function findInWhitelist($ip, $narrow_only=false, $board = false, $get_mod_info = false) {
		global $config;
		make_parsable($ip);
		$query = prepare('SELECT ``whitelist``.*' . ($get_mod_info ? ', `username`' : '') . ' FROM ``whitelist``
		' . ($get_mod_info ? 'LEFT JOIN ``mods`` ON ``mods``.`id` = `creator`' : '') . '
		WHERE
			(' . ($board !== false ? '(`board` IS NULL OR `board` = :board) AND' : '') . '
			(`ipstart` = :ip ' . (!$narrow_only ? ' OR (:ip >= `ipstart` AND :ip <= `ipend`)' : 'AND ipend IS NULL') . '))');

		if ($board !== false)
			$query->bindValue(':board', $board, PDO::PARAM_STR);

		$query->bindValue(':ip', inet_pton($ip));
		$query->execute() or error(db_error($query));

		$white_list = array();
		while ($white = $query->fetch(PDO::FETCH_ASSOC)) {
				$white['mask'] = self::range_to_string(array($white['ipstart'], $white['ipend']));
				$white_list[] = $white;
		}

		return $white_list;
	}

	static public function findPastOffences($ip, $board = false, $get_mod_info = false) {
		global $config;
		make_parsable($ip);
		$query = prepare('SELECT ``bans``.*' . ($get_mod_info ? ', `username`' : '') . ' FROM ``bans``
		' . ($get_mod_info ? 'LEFT JOIN ``mods`` ON ``mods``.`id` = `creator`' : '') . '
		WHERE
			(' . ($board !== false ? '(`board` IS NULL OR `board` = :board) AND' : '') . '
			(`ipstart` = :ip OR (:ip >= `ipstart` AND :ip <= `ipend`)))
		ORDER BY `expires` IS NULL, `expires` DESC');

		if ($board !== false)
			$query->bindValue(':board', $board, PDO::PARAM_STR);

		$query->bindValue(':ip', inet_pton($ip));
		$query->execute() or error(db_error($query));

		$ban_list = array();

		while ($ban = $query->fetch(PDO::FETCH_ASSOC)) {
			if ($ban['expires'] && $ban['expires'] < time()) {
				if ($ban['post'])
					$ban['post'] = json_decode($ban['post'], true);
				$ban['mask'] = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
				$ban_list[] = $ban;
			}
		}
		return $ban_list;
	}

	static public function stream_json($out = false, $filter_ips = false, $filter_staff = false, $board_access = false, $full_list=false) {
		$query = query("SELECT ``bans``.*, `username` FROM ``bans``
			LEFT JOIN ``mods`` ON ``mods``.`id` = `creator` " . ($full_list ? "" : " WHERE bans.`reason` NOT LIKE 'VPN/Proxy/Tor%' or bans.`reason` IS NULL" )  .
 			" ORDER BY `created` DESC") or error(db_error());
      $bans = $query->fetchAll(PDO::FETCH_ASSOC);

		if ($board_access && $board_access[0] == '*') $board_access = false;

		$out ? fputs($out, "[") : print("[");

		$end = end($bans);

                foreach ($bans as &$ban) {
                        $ban['mask'] = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
			if(!$ban['reason'])
				$ban['reason'] = 'N/A';
			if ($ban['post']) {
				$post = json_decode($ban['post']);
				$ban['message'] = isset($post->body) ? $post->body : 0;
			}
			$ban['message'] = !isset($ban['message']) ? '' : $ban['message'];

			unset($ban['ipstart'], $ban['ipend'], $ban['post'], $ban['creator']);

			if ($board_access === false || in_array ($ban['board'], $board_access)) {
				$ban['access'] = true;
			}

			if (filter_var($ban['mask'], FILTER_VALIDATE_IP) !== false) {
				$ban['single_addr'] = true;
			}
			if ($filter_staff || ($board_access !== false && !in_array($ban['board'], $board_access))) {
				$ban['username'] = '?';
			}
			if ($filter_ips || ($board_access !== false && !in_array($ban['board'], $board_access))) {
				@list($ban['mask'], $subnet) = explode("/", $ban['mask']);
				$ban['mask'] = preg_split("/[\.:]/", $ban['mask']);
				$ban['mask'] = array_slice($ban['mask'], 0, 1);
				$ban['mask'] = implode(".", $ban['mask']);
				$ban['mask'] .= ".x.x.x";
				if (isset ($subnet)) {
					$ban['mask'] .= "/$subnet";
				}
				$ban['masked'] = true;
			}

			// modify URLs incase offensive
			$ban['message'] = preg_replace("/(http|https):\/\/.+(\..*)/i", "$1://***$2", preg_replace("/<a.*?>(.*?)<\/a>/i","$1", $ban['message']));

			$json = json_encode($ban);
			$out ? fputs($out, $json) : print($json);

			if ($ban['id'] != $end['id']) {
				$out ? fputs($out, ",") : print(",");
			}
		}

                $out ? fputs($out, "]") : print("]");

	}
	static public function stream_whitelist_json($out = false, $filter_ips = false, $filter_staff = false, $board_access = false) {
		$query = query("SELECT ``whitelist``.*, `username` FROM ``whitelist``
			LEFT JOIN ``mods`` ON ``mods``.`id` = `creator`
 			ORDER BY `created` DESC") or error(db_error());
      $filters = $query->fetchAll(PDO::FETCH_ASSOC);

		if ($board_access && $board_access[0] == '*') $board_access = false;

		$out ? fputs($out, "[") : print("[");

		$end = end($filters);
  	foreach ($filters as &$filter) {
      $filter['mask'] = self::range_to_string(array($filter['ipstart'], $filter['ipend']));
			if(!$filter['exemption_regex'])
				$filter['exemption_regex'] = 'N/A';

			unset($filter['ipstart'], $filter['ipend'],  $filter['creator']);

			if ($board_access === false || in_array ($filter['board'], $board_access)) {
				$filter['access'] = true;
			}

			if (filter_var($filter['mask'], FILTER_VALIDATE_IP) !== false) {
				$filter['single_addr'] = true;
			}
			if ($filter_staff || ($board_access !== false && !in_array($filter['board'], $board_access))) {
				$filter['username'] = '?';
			}
			if ($filter_ips || ($board_access !== false && !in_array($filter['board'], $board_access))) {
				@list($filter['mask'], $subnet) = explode("/", $filter['mask']);
				$filter['mask'] = preg_split("/[\.:]/", $filter['mask']);
				$filter['mask'] = array_slice($filter['mask'], 0, 1);
				$filter['mask'] = implode(".", $filter['mask']);
				$filter['mask'] .= ".x.x.x";
				if (isset ($subnet)) {
					$filter['mask'] .= "/$subnet";
				}
				$filter['masked'] = true;
			}

			$json = json_encode($filter);
			$out ? fputs($out, $json) : print($json);

			if ($filter['id'] != $end['id']) {
				$out ? fputs($out, ",") : print(",");
			}
		}

    $out ? fputs($out, "]") : print("]");

	}

	static public function reducedBanSearchFromJSON($ip, $reason, $expiration, $post){
		if(trim($ip) == "" && trim($reason) == "" && trim($expiration) == "" && trim($post) == ""){
			header('Location: /bans-all.html');
			die;
		}
		$negative_search_ip = $ip != "" && $ip[0] == '-';
		$ip = $negative_search_ip ? substr($ip, 1) : $ip;

		$negative_search_reason = $reason != "" && $reason[0] == '-';
		$reason = $negative_search_reason ? substr($reason, 1) : $reason;

		$negative_search_expiration = $expiration != "" && $expiration[0] == '-';
		$expiration = $negative_search_expiration ? substr($expiration, 1) : $expiration;

		$negative_search_post = $post != "" && $post[0] == '-';
		$post = $negative_search_post ? substr($post, 1) : $post;

		$ban_json =  json_decode(file_get_contents('bans.json'), true);
		foreach($ban_json as $key=>$entry){
			// placment in json requires modifications of ban-list js file
			$entry['expires'] = !isset($entry['expires']) ? 'never' : $entry['expires'];
			//boolean mathematics(negation)
			$ip_bool = ($negative_search_ip + preg_match("/$ip/i", $entry['mask']));
			$reason_bool = ($negative_search_reason + preg_match("/$reason/i", $entry['reason'])) % 2;
			$expiration_bool = ($negative_search_expiration + preg_match("/$expiration/i", $entry['expires'])) % 2;
			$post_bool = ($negative_search_post + preg_match("/$post/i", $entry['message'])) % 2;
			if(!($ip_bool && $reason_bool && $expiration_bool && $post_bool)){
				unset($ban_json[$key]);
			}
		}
		$formatted_bans = array();
		foreach($ban_json as $key=>$entry){
			array_push($formatted_bans, $entry);
		}
		return $formatted_bans;
	}

	static public function seen($ban_id) {
		$query = query("UPDATE ``bans`` SET `seen` = 1 WHERE `id` = " . (int)$ban_id) or error(db_error());
                rebuildThemes('bans');
	}

	static public function purge() {
		$query = query("DELETE FROM ``bans`` WHERE `expires` IS NOT NULL AND `expires` < " . time() . " AND `seen` = 1") or error(db_error());
		rebuildThemes('bans');
	}

	static public function delete($ban_id, $modlog = false, $boards = false, $dont_rebuild = false) {
		global $config;

		if ($boards && $boards[0] == '*') $boards = false;

		if ($modlog) {
			$query = query("SELECT `ipstart`, `ipend`, `board` FROM ``bans`` WHERE `id` = " . (int)$ban_id) or error(db_error());
			if (!$ban = $query->fetch(PDO::FETCH_ASSOC)) {
				// Ban doesn't exist
				return false;
			}

			if ($boards !== false && !in_array($ban['board'], $boards))
		                error($config['error']['noaccess']);

			$mask = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
			$range = self::parse_range($mask);
			modLog("Removed ban #{$ban_id} for " .
				(filter_var($mask, FILTER_VALIDATE_IP) !== false ? "<a href=\"?/IP/$mask\">$mask</a>" : $mask));
		}

		query("DELETE FROM ``bans`` WHERE `id` = " . (int)$ban_id) or error(db_error());

		if (!$dont_rebuild) rebuildThemes('bans');

		return true;
	}
	static public function unwhitelist($whitelist_id, $modlog = false, $boards = false, $dont_rebuild = false) {
		global $config;

		if ($boards && $boards[0] == '*') $boards = false;

		if ($modlog) {
			$query = query("SELECT `ipstart`, `ipend`, `board` FROM ``whitelist`` WHERE `id` = " . (int)$whitelist_id) or error(db_error());
			if (!$white = $query->fetch(PDO::FETCH_ASSOC)) {
				// Ban doesn't exist
				return false;
			}

			if ($boards !== false && !in_array($white['board'], $boards))
		                error($config['error']['noaccess']);

			$mask = self::range_to_string(array($white['ipstart'], $white['ipend']));
			$range = self::parse_range($mask);
			modLog("Removed whitelist #{$whitelist_id} for " .
				(filter_var($mask, FILTER_VALIDATE_IP) !== false ? "<a href=\"?/IP/$mask\">$mask</a>" : $mask));
		}

		query("DELETE FROM ``whitelist`` WHERE `id` = " . (int)$whitelist_id) or error(db_error());

		if (!$dont_rebuild) rebuildThemes('bans');

		return true;
	}

	static public function new_ban($mask, $reason, $length = false, $ban_board = false, $mod_id = false, $post = false) {
		global $mod, $pdo, $board;

		if ($mod_id === false) {
			$mod_id = isset($mod['id']) ? $mod['id'] : -1;
		}

		$range = self::parse_range($mask);
		$mask = self::range_to_string($range);
		$query = prepare("INSERT INTO ``bans`` VALUES (NULL, :ipstart, :ipend, :time, :expires, :board, :mod, :reason, 0, :post)");

		$query->bindValue(':ipstart', $range[0]);
		if ($range[1] !== false && $range[1] != $range[0])
			$query->bindValue(':ipend', $range[1]);
		else
			$query->bindValue(':ipend', null, PDO::PARAM_NULL);

		$query->bindValue(':mod', $mod_id);
		$query->bindValue(':time', time());

		if ($reason !== '') {
			$reason = escape_markup_modifiers($reason);
			markup($reason);
			$query->bindValue(':reason', $reason);
		} else
			$query->bindValue(':reason', null, PDO::PARAM_NULL);

		if ($length) {
			if (is_int($length) || ctype_digit($length)) {
				$length = time() + $length;
			} else {
				$length = self::parse_time($length);
			}
			$query->bindValue(':expires', $length);
		} else {
			$query->bindValue(':expires', null, PDO::PARAM_NULL);
		}

		if ($ban_board)
			$query->bindValue(':board', $ban_board);
		else
			$query->bindValue(':board', null, PDO::PARAM_NULL);

		if ($post) {
			$post['board'] = $board['uri'];
			$query->bindValue(':post', json_encode($post));
		} else
			$query->bindValue(':post', null, PDO::PARAM_NULL);

		$query->execute() or error(db_error($query));

		if (isset($mod['id']) && $mod['id'] == $mod_id) {
			modLog('Created a new ' .
				($length > 0 ? preg_replace('/^(\d+) (\w+?)s?$/', '$1-$2', until($length)) : 'permanent') .
				' ban on ' .
				($ban_board ? '/' . $ban_board . '/' : 'all boards') .
				' for ' .
				(filter_var($mask, FILTER_VALIDATE_IP) !== false ? "<a href=\"?/IP/$mask\">$mask</a>" : $mask) .
				' (<small>#' . $pdo->lastInsertId() . '</small>)' .
				' with ' . ($reason ? 'reason: ' . utf8tohtml($reason) . '' : 'no reason'));
		}

		rebuildThemes('bans');

		return $pdo->lastInsertId();
	}
	static public function whitelist($mask, $wl_pattern, $ban_board = false, $mod_id = false) {
		global $mod, $pdo, $board;

		if ($mod_id === false) {
			$mod_id = isset($mod['id']) ? $mod['id'] : -1;
		}

		$range = self::parse_range($mask);
		$mask = self::range_to_string($range);
		$query = prepare("INSERT INTO ``whitelist`` VALUES (NULL, :ipstart, :ipend, :time, :board, :mod, :exemption)");

		$query->bindValue(':ipstart', $range[0]);
		if ($range[1] !== false && $range[1] != $range[0])
			$query->bindValue(':ipend', $range[1]);
		else
			$query->bindValue(':ipend', null, PDO::PARAM_NULL);

		$query->bindValue(':mod', $mod_id);
		$query->bindValue(':time', time());
		$query->bindValue(':exemption', $wl_pattern);

		if ($ban_board)
			$query->bindValue(':board', $ban_board);
		else
			$query->bindValue(':board', null, PDO::PARAM_NULL);

		$query->execute() or error(db_error($query));

		if (isset($mod['id']) && $mod['id'] == $mod_id) {
			modLog('Created a whitelist ' .
				' on ' .
				($ban_board ? '/' . $ban_board . '/' : 'all boards') .
				' for ' .
				(filter_var($mask, FILTER_VALIDATE_IP) !== false ? "<a href=\"?/IP/$mask\">$mask</a>" : $mask) .
				' (<small>#' . $pdo->lastInsertId() . '</small>)'
			);
		}

		rebuildThemes('bans');

		return $pdo->lastInsertId();
	}

	static public function new_ban_multiple($masks, $reason, $length = false, $ban_board = false, $mod_id = false, $post = false) {
		global $mod, $pdo, $board;

		foreach($masks as $mask){
			if ($mod_id === false) {
				$mod_id = isset($mod['id']) ? $mod['id'] : -1;
			}

			$range = self::parse_range($mask);
			$mask = self::range_to_string($range);

			$query = prepare("INSERT INTO ``bans`` VALUES (NULL, :ipstart, :ipend, :time, :expires, :board, :mod, :reason, 0, :post)");

			$query->bindValue(':ipstart', $range[0]);
			if ($range[1] !== false && $range[1] != $range[0])
				$query->bindValue(':ipend', $range[1]);
			else
				$query->bindValue(':ipend', null, PDO::PARAM_NULL);

			$query->bindValue(':mod', $mod_id);
			$query->bindValue(':time', time());

			if ($reason !== '') {
				$reason = escape_markup_modifiers($reason);
				markup($reason);
				$query->bindValue(':reason', $reason);
			} else
				$query->bindValue(':reason', null, PDO::PARAM_NULL);

			if ($length) {
				if (is_int($length) || ctype_digit($length)) {
					$length = time() + $length;
				} else {
					$length = self::parse_time($length);
				}
				$query->bindValue(':expires', $length);
			} else {
				$query->bindValue(':expires', null, PDO::PARAM_NULL);
			}

			if ($ban_board)
				$query->bindValue(':board', $ban_board);
			else
				$query->bindValue(':board', null, PDO::PARAM_NULL);

			if ($post) {
				$post['board'] = $board['uri'];
				$query->bindValue(':post', json_encode($post));
			} else
				$query->bindValue(':post', null, PDO::PARAM_NULL);

			$query->execute() or error(db_error($query));

			if (isset($mod['id']) && $mod['id'] == $mod_id) {
				modLog('Created a new ' .
					($length > 0 ? preg_replace('/^(\d+) (\w+?)s?$/', '$1-$2', until($length)) : 'permanent') .
					' ban on ' .
					($ban_board ? '/' . $ban_board . '/' : 'all boards') .
					' for ' .
					(filter_var($mask, FILTER_VALIDATE_IP) !== false ? "<a href=\"?/IP/$mask\">$mask</a>" : $mask) .
					' (<small>#' . $pdo->lastInsertId() . '</small>)' .
					' with ' . ($reason ? 'reason: ' . utf8tohtml($reason) . '' : 'no reason'));
			}
		}
		rebuildThemes('bans');

		return $pdo->lastInsertId();
	}
}
