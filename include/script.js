function submit_expert(form) {
	form.action = form.elements['command'].value + '.' + form.format.value;
}

function fade(elm) {
	if (ie) {
		elm.style.filter = 'alpha(opacity=' + op + ')';
	}
	else {
		elm.style.opacity = op;
	}
  
	if (op < destOp) {
		op += delta;
		setTimeout(function() {
			fade(elm);
		}, 50);
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
			if (elm) {
				fade(elm);
			}
		}
	}
}
