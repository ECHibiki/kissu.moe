<?php

class Hazuki{
  private static $api_pass = 'test';
  static $send_arr = [];
  function __construct(){

  }
  static function send($background = false){
    $send = implode("&",self::$send_arr);
    if($send == ""){
      return;
    }
    self::$send_arr = [];
    if($background){
      return shell_exec("curl --data \"pass=" .  self::$api_pass . "\" \"http://localhost:4100/api?$send\" > /dev/null 2>/dev/null &");
    }else{
      return shell_exec("curl --data \"pass=" .  self::$api_pass . "\" \"http://localhost:4100/api?$send\" ");
    }
  }

  static function addQueueItem(){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"queue" ])));;
  }

  static function insertIntoArchive($id, $board){
    // insert to archive
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"archive", "Number"=>"$id", "Board"=>$board ])));
  }
  static function purgeArchive($board){
    // purge archive
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"purge-archive", "Board"=>$board ])));
  }
  static function restoreThreadFromArchive($board){
    // restore thread
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"restore-archive", "Board"=>$board ])));
  }
  static function sendAppeal($id, $message){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"add-appeal", "Number"=>"$id", "Message"=>$message ])));
  }
  static function evaluateAppeal($message, $is_spam){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"update-filter", "Spam"=>"$is_spam", "Message"=>$message ])));
  }
  static function rebuildThread($number, $board){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"build", "Number"=>"$number", "Board"=>$board, "Category"=>"thread"  ])));
  }
  static function deleteThread($number, $board){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"destroy", "Number"=>"$number", "Board"=>$board, "Category"=>"thread"  ])));
  }

  static function rebuildProperties($number , $board){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"build", "Number"=>"$number", "Board"=>$board, "Category"=>"properties"  ])));
  }

  static function rebuildOverboard(){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"build", "Category"=>"overboard"  ])));
  }
  static function rebuildCatalog($board){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"build", "Board"=>$board, "Category"=>"catalog"  ])));
  }

  static function rebuildHome(){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"build", "Category"=>"home"  ])));
  }
  static function rebuildSummary(){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"build", "Category"=>"summary"  ])));
  }

  static function decachePost($number, $board){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"decache", "Number"=>"$number", "Board"=>$board, "Category"=>"post"  ])));
  }
  static function decacheThread($number, $board){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"thread-purge", "Number"=>"$number", "Board"=>$board ])));
  }
  static function decacheHome(){
    self::$send_arr[] = "request=" . escapeshellarg(urlencode(json_encode(["Page"=>"decache", "Category"=>"home"  ])));
  }
}
