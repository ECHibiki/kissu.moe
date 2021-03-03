<?php

  require_once 'inc/hazuki-coms.php';
  require_once 'inc/functions.php';

class CaptchaQueue{

  static function holdForQueue(){
    Hazuki::addQueueItem();
    $ms_queue_time = Hazuki::send();
    if(@intval($ms_queue_time)){
      $us_queue_time = intval($ms_queue_time) * 1000;
    } else{
      error($ms_queue_time . " - CaptchaQueue insert failed");
    }
    $us_current_arr = explode(" ", microtime());
    $us_current = ($us_current_arr[0] + $us_current_arr[1])*1000000;

    if($us_queue_time - $us_current > 0){
      usleep($us_queue_time - $us_current);
    }
  }
}

?>
