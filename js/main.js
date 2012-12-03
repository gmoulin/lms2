var $nav,
	$navLinks,
	$body,
	$win,
	activeTab,
	target,
	updating = 0,
	scrolling = false,
	end = false;

var subDomains = ['s1', 's2', 's3'],
	useSubDomains = false;

$(document).ready(function(){
	$win = $(window);
	$body = $('body');
	$nav = $('.nav');
	$navLinks = $nav.find('a');

	//improve image loading using subdomains
		var i;
		if( window.location.host == 'lms.dev' || window.location.host == 'lms' ){
			useSubDomains = true;
			for( i = 0; i < subDomains.length; i++ ){
				subDomains[ i ] = window.location.protocol + '//' + subDomains[ i ] + '.' + window.location.host + '/';
			}
		} else if( window.location.host == 'lms.kapok.fr' ){
			useSubDomains = true;
			for( i = 0; i < subDomains.length; i++ ){
				subDomains[ i ] = window.location.protocol + '//' + subDomains[ i ] + '.kapok.fr/';
			}
		}

	//tab change via menu and url#hash
		window.addEventListener("hashchange", tabSwitch, false);

	//menu link
		$navLinks.click(function(e){
			//refresh current tab if already active (url#hash will not change)
			target = $(this).attr('href').substr(1);
			if( target == activeTab ){
				e.preventDefault();
				getList(0);
			}
		});

	//infinite scroll
		$win.scroll(function(){
			if( end || scrolling ){
				return true;
			} else {
				scrolling = true;
			}

			if( document.body.scrollHeight - $win.height() - $doc.scrollTop() < 300 ){
				getList(3);
			} else {
				scrolling = false;
			}
		});

	//initial load
		tabSwitch();

	//prevent dropdown hide on inside input click
		$('.dropdown').find('input, label').click(function(e){
			e.stopPropagation();
		});
});

/**
 * change the current tab
 */
var tabSwitch = function(){
	target = window.location.hash.substr(1) || $navLinks.eq(0).attr('href').substr(1);

	$navLinks.parents().removeClass('active');
	$navLinks.filter('[href$="'+ target +'"]').parent().addClass('active');

	$('.list, .filter-form').hide();
	$('#list_'+ target +', #filter_'+ target).show();

	activeTab = target;

	if( $('#list_'+ target).length > 0 ){ // Argument is a valid tab name
		window.location.hash = '#' + target; //security if hash empty

		getList(0);
	}
};

var getList = function( type ){
	if( !$nav.length ) return;

	//multiple call protection
	if( updating !== 1 ){
		updating = 1;

		var tab = activeTab;
		if( tab === undefined ) return;

		var $list = $('#list_' + tab);

		$body.css('cursor', 'progress');

		$.ajax({
			url: 'ajax/manage'+ target.capitalize() +'.php',
			data: 'action=' + ( type == 3 ? 'more' : 'list&type=' + type ),
			type: 'POST',
			dataType: 'json',
			complete: function(){
				updating = 0;
				$body.css('cursor', '');
			},
			success: function(data){
				if( type === 0 ){
					$win.scrollTop(0);
					$list.empty();
				}

				end = (data.nb == data.total);

				$list.append( tmpl('list_'+ target +'_tmpl', data) );
			}
		});
	}
};

/**
 * test if sub domains are available
 * yes : return the full url
 * no : return relative url
 * used in jquery template
 */
var getFullUrl = function(partialUrl, id){
	if( useSubDomains ){
		return subDomains[ id % subDomains.length ] + partialUrl + id;
	}

	return partialUrl + id;
};

/* helpers */
String.prototype.capitalize = function(){
	return this.charAt(0).toUpperCase() + this.substr(1);
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


