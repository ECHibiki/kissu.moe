<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<script type="text/javascript">
		var active_page = "page";
	</script>		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
		<link rel="stylesheet" media="screen" href="/stylesheets/style.css?0096"><link rel="stylesheet" type="text/css" id="stylesheet" href="/stylesheets/yotsuba-kissu-b.css?0096">		<script type="text/javascript">
			var configRoot="/";
			var inMod =false;
			var modRoot="/"+(inMod ? "mod.php?/" : "");
		</script>		<script type="text/javascript">
			var configRoot="/";
			var inMod =false;
			var modRoot="/"+(inMod ? "mod.php?/" : "");
		</script><link rel="shortcut icon" href="/static/favicon.png"><link rel="stylesheet" href="/stylesheets/font-awesome/css/font-awesome.min.css"><link rel="stylesheet" href="/static/flags/flags.css">		<style type="text/css">
			#recaptcha_area {
				float: none !important;
				padding: 0 !important;
			}
			#recaptcha_logo, #recaptcha_privacy {
				display: none;
			}
			#recaptcha_table {
				border: none !important;
			}
			#recaptcha_table tr:first-child {
				height: auto;
			}
			.recaptchatable img {
				float: none !important;
			}
			#recaptcha_response_field {
				font-size: 10pt !important;
				border: 1px solid #a9a9a9 !important;
				padding: 1px !important;
			}
			td.recaptcha_image_cell {
				background: transparent !important;
			}
			.recaptchatable, #recaptcha_area tr, #recaptcha_area td, #recaptcha_area th {
				padding: 0 !important;
			}
		</style>
	<title>Ban list</title>
</head>
<body class="8chan vichanis-not-moderator active-page" data-stylesheet="yotsuba-kissu-b.css">	<header>
		<h1>Ban list</h1>
		<div class="subtitle">		</div>
	</header><?php

	require_once('inc/bans.php');
	require_once('inc/functions.php');
	$regex_similars = array(".", "/", "[", "]");
	$regex_actuals = array("\\.", "\/", "\[", "\]");
	
	$ip = '';
	if (isset($_GET['ip']))
		$ip = trim(str_replace($regex_similars, $regex_actuals, $_GET['ip']));
	$reason = '';
	if (isset($_GET['reason']))
		$reason = trim(str_replace($regex_similars, $regex_actuals, $_GET['reason']));
	$expiration = '';
	if (isset($_GET['duration']))
		$expiration = trim(str_replace($regex_similars, $regex_actuals, $_GET['duration']));
	$post = '';
	if (isset($_GET['post']))
		$post = trim(str_replace($regex_similars, $regex_actuals, $_GET['post']));

	$bans_arr = Bans::reducedBanSearchFromJSON($ip, $reason, $expiration, $post);

	echo "<script>var longtable_json_dynamic = JSON.parse('[" . substr(str_replace(array("\\", "'"), array("\\\\", "\\'"), json_encode($bans_arr)),1,-1) . "]');</script>";
?>




<script src='main.js'></script>
<script src='js/jquery-3.4.1.min.js'></script>
<script src='js/mobile-style.js'></script>
<script src='js/strftime.min.js'></script>
<script src='js/longtable/longtable.js'></script>
<script src='js/heavy-ban-list.js'></script>
<link rel='stylesheet' href='stylesheets/longtable/longtable.css'>
<link rel='stylesheet' href='stylesheets/mod/ban-list.css'>

	<form class="banform">		<div class='banlist-opts'>
			<div class='checkboxes'>				<label><input type="checkbox" id="only_not_expired">Show only active bans</label>
			</div>
			<div class='buttons'>
				<input type="text" id="search" placeholder="Search">			</div>

			<br class='clear'>
		</div>

		<table class="mod" style="width:100%" id="banlist">
			<tbody>
			</tbody>
		</table>


		
	</form>	<script>$(function(){ banlist_init("bans.json",[], true); });</script>
	<hr><footer>
	<p class="unimportant" style="margin-top:20px;text-align:center;">- Tinyboard + 
		<a href="https://engine.vichan.net/">vichan</a>&nbsp5.1.4 + <a href="https://github.com/fallenPineapple/NPFchan">NPFchan</a> + <a href="https://github.com/ECHibiki/ViQa-Kissu/">Kissu</a>&nbsp7 - <br>Tinyboard Copyright &copy; 2010-2014 Tinyboard Development Group
	<br><a href="https://engine.vichan.net/">vichan</a> Copyright &copy; 2012-2018 vichan-devel
	<br><a href="https://github.com/fallenPineapple/NPFchan">NPFchan</a> Copyright &copy; 2017-2018 NPFchan
	<br><a href="https://github.com/ECHibiki/ViQa-Kissu/">Kissu</a>  2018-2020
	<br/> <a href="/legal.html">Legal</a>&emsp;<a href="/rules.html">Rules</a></p><p class="unimportant" style="text-align:center;">All trademarks, copyrights, comments, and images on this page are owned by and are the responsibility of their respective parties.</p><p class="unimportant" style="text-align:center;">Concerns to the gmail of ECVerniy</p>		<a style="opacity:0;position:absolute;top: -100em;" href="https://info.flagcounter.com/CexS"><img src="https://s11.flagcounter.com/count2/CexS/bg_FFFFFF/txt_000000/border_CCCCCC/columns_2/maxflags_10/viewers_0/labels_0/pageviews_0/flags_0/percent_0/" alt="Flag Counter" border="0"></a>
</footer>
</body>
</html>
