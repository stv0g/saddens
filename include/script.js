function submit_expert(form) {
	form.action = form.elements["command"].value + "." + form.format.value;
}

function submit_simple(form) {
	var matches = form.elements["rdata"].value.match(/^((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/);
	
	if (matches)
		form.elements["type"].value = "A";
	else
		form.elements["type"].value = "URL";
}

function fade(elm) {

	ie ? elm.style.filter = "alpha(opacity=" + op + ")" : elm.style.opacity = op;
  
	if (op < destOp) {
		op += delta;
		setTimeout(function() { fade(elm) }, 50);
	}
}

function slide_iframe(elm, currheight, maxheight, addheight) {
  newheight = currheight + addheight;
  
  elm.style.height = newheight + "px";
  
  if (newheight < maxheight)
  {
    window.setTimeout(function() { slide_iframe(elm, newheight, maxheight, addheight); }, 30);
  }
}

function installSearchEngine(openSearchXml) {
	if (window.external && ("AddSearchProvider" in window.external)) {
		window.external.AddSearchProvider(openSearchXml);
	}
	else {
		alert('Sorry your browser doesnt support the OpenSearch format!');
	}
}

var mousemoved = 0;
var op = 0;
var destOp = 100;
var delta = 5; // Schritt bei der Opacity-Aenderung

var ie = (navigator.appName.indexOf("Explorer") != -1) ? true : false;
if (!ie) {
	destOp = destOp / 100;
	delta = delta / 100;
}

document.onmousemove = function() {
	if (op == 0) {
		mousemoved++;
		if (mousemoved >= 5) {
			elm = document.getElementsByTagName('footer')[0];

			if (elm)
				fade(elm);
		}
	}
}

window.onload = function () {
	window.focus();
	msgs = parent.frames.ifr.document.getElementById("messages");
	
	if (msgs) {
		elm = parent.document.getElementById("ifr");
		slide_iframe(elm, 0, msgs.clientHeight, 5)
	}
	else
		document.formular.host.focus();
}