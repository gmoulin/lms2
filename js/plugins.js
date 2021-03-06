// Avoid `console` errors in browsers that lack a console.
(function(){
	var method,
		noop = function noop(){},
		methods = [
			'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
			'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
			'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
			'timeStamp', 'trace', 'warn'
		],
		length = methods.length,
		console = (window.console = window.console || {});

	while( length-- ){
		method = methods[length];

		// Only stub undefined methods.
		if( !console[method] ){
			console[method] = noop;
		}
	}
}());

// Place any jQuery/helper plugins in here.

/* helpers */
String.prototype.capitalize = function(){
	return this.charAt(0).toUpperCase() + this.substr(1);
};

/**
 * replace accentued characters by non accentued counterpart
 * and remove spaces
 * used in jquery template
 */
String.prototype.urlify = function(){
	var s = this,
		accent = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñŠšŸÿýŽž ',
		without = ['A','A','A','A','A','A','a','a','a','a','a','a','O','O','O','O','O','O','O','o','o','o','o','o','o','E','E','E','E','e','e','e','e','e','C','c','D','I','I','I','I','i','i','i','i','U','U','U','U','u','u','u','u','N','n','S','s','Y','y','y','Z','z',''],
		result = [];

	s = s.split('');
	len = s.length;
	for (var i = 0; i < len; i++){
		var j = accent.indexOf(s[i]);
		if( j != -1 ){
			result[i] = without[j];
		} else {
			result[i] = s[i];
		}
	}
	return result.join('');
};

/**
 * localStorage method for caching javascript objects
 */
Storage.prototype.setObject = function(key, value){
	this.setItem(key, JSON.stringify(value));
};

Storage.prototype.getObject = function(key){
	return this.getItem(key) && JSON.parse( this.getItem(key) );
};

/* templating */
// Simple JavaScript Templating
// John Resig - http://ejohn.org/ - MIT Licensed
(function(){
	var cache = {};

	this.tmpl = function tmpl(str, data){
		// Figure out if we're getting a template, or if we need to
		// load the template - and be sure to cache the result.
		var fn = !/\W/.test(str) ?
			cache[str] = cache[str] ||
				tmpl(document.getElementById(str).innerHTML) :

	// Generate a reusable function that will serve as a template
	// generator (and which will be cached).
	new Function("obj",
		"var p=[],print=function(){p.push.apply(p,arguments);};" +

		// Introduce the data as local variables using with(){}
		"with(obj){p.push('" +

		// Convert the template into pure JavaScript
		str
			.replace(/[\r\t\n]/g, " ")
			.split("<%").join("\t")
			.replace(/((^|%>)[^\t]*)'/g, "$1\r")
			.replace(/\t=(.*?)%>/g, "',$1,'")
			.split("\t").join("');")
			.split("%>").join("p.push('")
			.split("\r").join("\\'")
		+ "');}return p.join('');");

		// Provide some basic currying to the user
		return data ? fn( data ) : fn;
	};
})();


