<?php

require_once "./inc/functions.php";

echo $_SERVER['HTTP_USER_AGENT'] . "<br/>";
echo $_SERVER['REMOTE_ADDR']. "<br/>";
echo gethostbyaddr($_SERVER['REMOTE_ADDR']) . "<br/>";
echo isset($_GET['ip']) ? gethostbyaddr($_GET['ip']) . "<br/>" : "";
echo isset($_GET['ip']) ? preg_match("/(?i)(server|cloud|host|vps|ovh|vpn)/", gethostbyaddr($_GET['ip'])) . "<br/>" : "";
