<?php

  setcookie("ui", "2");
  echo("New UI toggled<br/>");
  echo("Returning in <span id='t'>5</span> seconds...<hr/>");
  echo("<strong>Refresh after return</strong><br/>");
  echo("<script>
  var sec = 5
  setInterval(
    function(){
      sec--;
      if(sec == 0){
        window.history.back();
      } else if(sec >= 0){
        document.getElementById('t').textContent = sec;
      }
    }, 1000
    )</script>");
    echo("<button type='button' onclick='javascript:history.back()'>Back</button>");


?>
