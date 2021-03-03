
onready(function(){

	var giveMessage = function(motd){

		alert(
			"<h1>Welcome to kissu.moe!</h1><br/>\
			<h2>Message Of the Day</h2>\
			<p><strong>" + motd + "</strong></p>\
			<h2>Boards</h2>\
			<ul style='text-align: left;'>\
			<li><a href='/all/'>/all/ - Overboard</a></li>\
			<hr/>\
			<li><a href='/qa/'>/qa/ - Quality Anime</a></li>\
			<li><a href='/jp/'>/jp/ - 2D/Random</a></li>\
			<li><a href='/f/'>/f/ - Files and Flash</a></li>\
			<li><a href='/ec/'>/ec/ - エッチ/Cute</a></li>\
			<li><a href='/win/'>/win/ - Seasonal Blogging</a></li>\
			<hr/>\
			<li><a href='/b/'>/b/ - Site Developement</a></li>\
			<li><a href='/poll/'>/poll/ - Community Polling/Meta</a></li></ul>\
			<h2>Select Default Theme</h2>\
			Other options are selectable later in options<br/><br/>\
			<label>Default Theme: <select onchange='$(\"#style-select-\" + $(this).val()).click();'><option value='1' selected='selected' >Light</option><option value='2'>Dark</option><option value='3'>Special</option></select></label><br/>\
			<h2>Rules</h2>\
			<p><a href='/rules.html'>Rules</a></p>");

	}
	setTimeout(function(){
		var request = new XMLHttpRequest();
		var motd = "";
		request.open("GET", '/motd.txt');
			request.onreadystatechange = function() {
			if (this.readyState === 4 && this.status === 200 && JSON.stringify(window.navigator) != "{}") {
				motd = this.responseText;
				var request = new XMLHttpRequest();
				if(typeof localStorage.firstLoad == "undefined" || localStorage.firstLoad != 3){
					localStorage.firstLoad = 3;
					giveMessage(motd);
				}
			};

		}
		request.send();

		document.getElementById("clickerimg").onclick = function(){
				var request = new XMLHttpRequest();
				var motd = "";
				request.open("GET", '/motd.txt?' + Date.now());
					request.onreadystatechange = function() {
						if (this.readyState === 4 && this.status === 200) {
							   motd = this.responseText;
							  var request = new XMLHttpRequest();
							   localStorage.firstLoad = 3;
					giveMessage(motd);
						 };

				}
				request.send();
		}

	},2500)
});
