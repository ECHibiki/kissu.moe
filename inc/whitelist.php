<?php

class Whitelist{
  static public function validateWLToken($test_token){
    global $config;
    $query = prepare("SELECT token FROM ``whitelist_tokens``") or error(db_error());
  	$query->execute();
  	$token_list = $query->fetchAll(PDO::FETCH_COLUMN);
    foreach ($token_list as $token) {
      if($token == $test_token){
        return true;
      }
    }
    return false;
  }
}

?>
