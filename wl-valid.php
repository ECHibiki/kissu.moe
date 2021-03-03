<?php
require_once 'inc/functions.php';
require_once 'inc/whitelist.php';
//require "inc/functions.php";
	$post['wl_token'] = $_GET['wl_token'];
	if(!trim($post['wl_token'])){
		$post['wl_token_valid'] = false;
	echo "insert WL";

	}else	if(!Whitelist::validateWLToken($post['wl_token'])){
		$post['wl_token_valid'] = false;
//		error($config['error']['wlinvalid']);
	}else{
		$post['wl_token_valid'] = true;
	echo "WL-Valid";
	
}
