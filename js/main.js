var $nav,
	$navLinks,
	$body,
	$win,
	$doc,
	$parts,
	$dropOverlay,
	activeTab,
	target,
	updating = 0,
	dropTimeout,
	scrolling = false,
	end = false;

var subDomains = ['s1', 's2', 's3'],
	useSubDomains = false;

$(document).ready(function(){
	$win = $(window);
	$body = $('body');
	$doc = $(document);
	$nav = $('.nav').filter(':not(.pull-right)');
	$navLinks = $nav.find('a');
	$parts = $('.list, .filter-form, .sort-links, .add');
	$dropOverlay = $('#drop-overlay');

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

	//sort links
		$('.sorts')
			.find('.sort-links').each(function(){ //sort init if present
				var $this = $(this),
					id = $this.attr('id'),
					$sortTypeField = $('#'+ id.replace(/sort/, 'filter')).find('.sortTypeField');

				if( $sortTypeField.length > 0 ){
					var activeSort = $sortTypeField.val();

					//set if empty
					if( activeSort === '' ){
						activeSort = 0;
						$sortTypeField.val(0);
					}

					//each activeSort value is linked to a "ORDER BY" sentence in the php database tables classes
					//each sort button has 2 arrows
					//class "both" -> no arrow highlighted
					//class "asc" -> top arrow highlighted
					//class "desc" -> bottom arrow highlighted
					if( activeSort % 2 === 0 ){ //even -> asc sort
						$this.find('.sort').filter('[data-sort="'+ activeSort +'"]').find('i').removeClass('icon-sort icon-sort-up').addClass('icon-sort-down');
					} else { //odd -> desc sort
						$this.find('.sort').filter('[data-sort="'+ activeSort +'"]').find('i').removeClass('icon-sort icon-sort-down').addClass('icon-sort-up');
					}
				}
			})
			.on('click', '.sort', function(e){
				e.preventDefault();
				var $this = $(this),
					$icon = $this.find('i'),
					$sortLinks = $this.closest('.sort-links'),
					id = $sortLinks.attr('id'),
					$sortTypeField = $('#'+ id.replace(/sort/, 'filter')).find('.sortTypeField');

				//save clicked link current icon
				var c = $icon.attr('class'),
					h = parseInt($this.attr('data-sort'), 10);

				//reset all links icons
				$sortLinks.find('i').removeClass('icon-sort-down').removeClass('icon-sort-up').addClass('icon-sort');

				//set back clicked link icon
				$icon.attr('class', c);

				//default state -> asc
				if( $icon.hasClass('icon-sort') ){
					$icon.toggleClass('icon-sort icon-sort-down');

				//asc state -> desc
				} else if( $icon.hasClass('icon-sort-down') ){
					$icon.toggleClass('icon-sort-down icon-sort-up');
					h++;

				//desc state -> default
				} else if( $icon.hasClass('icon-sort-up') ){
					$icon.toggleClass('icon-sort-up icon-sort');

					//reseting sort type to 0
					$sortLinks.find('.sort').eq(0).trigger('click');
					return;
				}

				$sortTypeField.val( h );

				getList(1);
			});


	//author, band and artist inputs in forms
		$('.edit-form')
			.on('click', '.add-another', function(e){
				e.preventDefault();

				var $list = $(this).closest('.edit-form').find('.another'),
					$anotherBlock = $list.last().clone(true),
					$input = $anotherBlock.find('input'),
					tmp = $input.attr('id').split('_'),
					indice = parseInt(tmp[1], 10);

				$input
					.attr('id', function(index, attr){ return attr.replace(new RegExp(indice), indice + 1); })
					.attr('name', function(index, attr){ return attr.replace(new RegExp(indice), indice + 1); })
					.val(''); //reseting the value
				$anotherBlock.find('.control-label').attr('for', $input.attr('id'));

				$anotherBlock.insertAfter( $list.last() );
			})
			.on('click', '.delete-another', function(e){
				e.preventDefault();
				var $this = $(this),
					$list = $this.closest('.edit-form').find('.another');

				//only remove if not the only one
				if( $list.length > 1 ){
					$this.closest('.another').remove();
				} else { //reset the input
					$this.siblings('input').val('');
				}
			});

	//add, update, delete, relocate, move, addLoan
		$('.add').click(function(e){
			e.preventDefault();

			var $this = $(this),
				rel = $this.attr('data-manage'),
				$form = $('#edit_' + rel),
				$another = $form.find('.another');

			$another.filter(':gt(0)').remove();

			$another.first()
				.find('input')
					.attr('id', function(i, id){ return id.replace(/_[0-9]+/, '_1'); })
					.attr('name', function(i, name){ return name.replace(/_[0-9]+/, '_1'); })
				.end()
				.find('label')
					.attr('for', function(i, attr){ return attr.replace(/_[0-9]+/, '_1'); });

			$form
				.data('save_clicked', 0)
				.find(':input').val('').end()
				.find('.quicklink-append').removeClass('input-append')
				.find('.quicklink').remove();

			//setting action
			$form.find('#'+ rel +'Action').val('add');

			if( $form.find('.cover-status').length > 0 ){
				$('html, .modal-backdrop')
					.bind('dragenter', dragEnter)
					.bind('dragover', dragOver)
					.bind('dragleave', dragLeave);

				$('html')[0].addEventListener("drop", dropCover, true);
			}

			$form.find('datalist, select').loadList();
		});


		$('.modal').on('hide', function(){
			if( $(this).find('.cover-status').length > 0 ){
				$('html, .modal-backdrop')
					.unbind('dragenter')
					.unbind('dragover')
					.unbind('dragleave');

				$('html')[0].removeEventListener("drop", dropCover, true);
			}
		});

	//forms actions
		$('.edit-form')
			.each(function(){
				//add event listener for dynamic form validation
				this.addEventListener("invalid", checkField, true);
				this.addEventListener("blur", checkField, true);
				this.addEventListener("input", checkField, true);
			});

		$body
			.on('submit', '.edit-form', function(e){
				var $this = $(this),
					rel = $this.attr('data-manage');

				//multiple call protection
				if( $this.data('save_clicked') !== 1 ){
					$this.data('save_clicked', 1);

					$.ajax({
						url: 'ajax/manage'+ rel.capitalize() +'.php',
						data: $this.serialize(),
						type: 'POST',
						dataType: 'json'
					})
					.always(function(){
						$this.data('save_clicked', 0);
					})
					.done(function(data){
						if( data == 'ok' ){
							//modal close
							$('#edit_'+ rel).modal('hide');

							//inform user

							getList(2);

						} else {
							//form errors display
							formErrors(data);
						}
					});
				} else {
					e.preventDefault();
				}
			});

	//quick links for title in form
		var $quickLink = $('<a class="btn btn-small quicklink" target="_blank"><i class="icon-link"></i></a>');

		$('#bookTitle')[0].addEventListener('input', function(){
			var $this = $(this);
			if( $this.val() === '' ){
				$this
					.parent().removeClass('input-append')
					.find('.quicklink').remove();
			}

			if( $this.siblings('.quicklink').length === 0 ){
				$this.parent().addClass('input-append');
				$quickLink.clone().attr('title', 'Rechercher sur Google Image').appendTo( $this.parent() );
				$quickLink.clone().attr('title', 'Rechercher sur Fantastic Fiction').appendTo( $this.parent() );
			}

			$this.siblings('.quicklink')
					.first().attr('href', 'http://www.google.com/images?q=' + $this.val() + ' movie').end()
					.last().attr('href', 'http://www.fantasticfiction.co.uk/search/?searchfor=book&keywords='+ $this.val() +' movie');
		}, false);


	//saga title in form
		$('#bookSagaTitle, #movieSagaTitle').change(function(){
			var $this = $(this),
				$form = $this.closest('.edit-form'),
				rel = $form.attr('data-manage'),
				$dl = $this.siblings('datalist'),
				decoder = $('<textarea>'),
				$addAnother = $form.find('.add-another');

			$this
				.parent().removeClass('input-append')
				.siblings('.quickLink').remove();

			//is the saga present in the database
			if( $this.val() !== '' && $dl.find('option').filter('[value="'+ $this.val() +'"]').length > 0 ){
				$.post('ajax/manageSaga.php', 'action=getByTitleFor'+ rel.capitalize() +'&title='+ $this.val(), function(saga){
					if( !$.isEmptyObject(saga) ){
						if( saga.sagaSearchURL !== '' && saga.sagaSearchURL !== null ){
							$this.parent().addClass('input-append');
							$quickLink
								.clone()
								.attr('title', 'Détail de cette saga sur internet')
								.attr('href', decoder.html(saga.sagaSearchURL).val())
								.insertAfter( $this );
						}

						//setting fields only if in add mode
						if( $('#'+ rel +'Action').val() == 'add' ){
							$('#'+ rel +'SagaPosition').val( saga.position ).trigger('blur');

							$('#'+ rel +'Storage').val( saga.storageID ).trigger('blur');

							$form.find('.another').filter(':gt(0)').remove();
							var indice = 1;
							if( rel == 'book' ){
								$.each( saga.authors, function(i, a){
									if( indice > 1 ) $addAnother.click();
									$('#bookAuthor_'+ indice).val( decoder.html(a).val() ).trigger('blur');
									indice++;
								});
							} else if( rel == 'movie' ){
								$.each( saga.artists, function(i, a){
									if( indice > 1 ) $addAnother.click();
									$('#movieArtist_'+indice).val( decoder.html(a).val() ).trigger('blur');
									indice++;
								});
							}
						}

						$('#'+ rel +'SagaPosition').focus();
					}
				});
			}
		});
});

/**
 * change the current tab
 */
var tabSwitch = function(){
	target = window.location.hash.substr(1) || $navLinks.eq(0).attr('href').substr(1);

	$navLinks.parents().removeClass('active');
	$navLinks.filter('[href$="'+ target +'"]').parent().addClass('active');

	$parts.hide()
		.filter('[id$="_'+ target +'"]').show();

	activeTab = target;

	if( $('#list_'+ target).length > 0 ){ // Argument is a valid tab name
		window.location.hash = '#' + target; //security if hash empty

		getList(0);
	}
};

/**
 * load the list
 */
var getList = function( type ){
	if( !$nav.length ) return;

	//multiple call protection
	if( updating !== 1 ){
		updating = 1;

		var tab = activeTab;
		if( tab === undefined ) return;

		var $list = $('#list_'+ tab),
			$filter = $('#filter_'+ tab);

		$body.css('cursor', 'progress');

		$.ajax({
			url: 'ajax/manage'+ target.capitalize() +'.php',
			data: 'action=' + ( type == 3 ? 'more' : 'list&type=' + type + '&' + $filter.serialize() ),
			type: 'POST',
			dataType: 'json'
		})
		.always(function(){
			updating = 0;
			scrolling = false;
			$body.css('cursor', '');
		})
		.done(function(data){
			if( type === 0 ){
				$win.scrollTop(0);
			}

			if( type != 3 ){
				$list.empty();
				$filter.find('datalist, select').loadList();
			}

			end = (data.nb == data.total);

			$list.append( tmpl('list_'+ target +'_tmpl', data) );
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

/**
 * ajax load <datalist> and <select> content
 */
$.fn.loadList = function(){
	return this.each(function(){
		var $this = $(this),
			key = $this.attr('id'),
			decoder = $('<textarea>'),
			cachedData,
			lastModified = 0;

		try {
			cachedData = localStorage.getObject(key);
			if( cachedData ){
				lastModified = cachedData.lastModified;
			}
		} catch( e ){
			alert(e);
		}

		//ask the list values to the server and create the <option>s with it
		$.ajax({
			url: 'ajax/loadList.php',
			data: 'field=' + $this.attr('id'),
			dataType: 'json',
			headers: {
				'If-Modified-Since': lastModified
			}
		})
		.done(function(data, textStatus, jqXHR){
			//server will send a 304 status if the list has not changed
			if( jqXHR.status == 200 ){
				try {
					lastModified = jqXHR.getResponseHeader('Last-Modified');

					localStorage.setObject(key, {'lastModified': lastModified, 'data': data});
				} catch( e ){
					alert(e);
				}

			} else { //304
				data = cachedData.data;

				if( $this.find('option:gt(0)').length ){
					//options already present, no need to fill the field
					return;
				}
			}

			var savedVal;

			if( $this.is('datalist') ){
				$this.empty();
			} else {
				var isFilter = false;
				if( $this.attr('id').search(/Filter/) != -1 && $this.val() !== '' ){
					savedVal = $this.val();
					isFilter = true;
				}
				$this.find('option:gt(0)').remove(); //keep the first option aka "placeholder"
			}

			$.each(data, function(i, obj){
				obj.value = decoder.html(obj.value).val();
				$('<option>', { "value": ( obj.id ? obj.id : obj.value ), text: obj.value }).appendTo( $this );
			});

			if( isFilter ) $this.val( savedVal );
		});
	});
};

/**
 * dynamic form fields validation using HTML5 form validation API
 * called through javascript events listeners
 * set classes for css form validation rules
 * @param object event
 */
var checkField = function( event ){
	var $el = $(event.target),
		$controlGroup = $el.closest('.control-group');

	$controlGroup.attr('class', 'control-group');

	if( $el[0].validity ){
		if( $el[0].validity.valid ){
			if( $el.val() !== '' ){
				$controlGroup.addClass('success');
			}
		} else if( $el[0].validity.valueMissing ){
			$controlGroup.addClass('warning');
		} else {
			$controlGroup.addClass('error');
		}
	}
};

/**
 * display the form errors
 * use ".class + .validation-icon" css rules
 * use ".class ~ .tip" css rules
 * @param array [[field id, message, error type]]
 */
var formErrors = function( data ){
	$.each(data, function(index, error){
		$('#'+ error[0])
			//add error class
			.closest('.control-group').addClass( error[2] == 'required' ? 'warning' : error[2] )
			.find('.controls')
				//remove previous error message if present
				.find('.help-inline').remove().end()
				//add error message
				.append( $('<span>', { 'class': 'help-inline', 'text': error[1] }) );
	});
};

/**
 * manage the drag enter event for drag and drop
 * @param object event
 */
function dragEnter(event){
	event.preventDefault();
	$dropOverlay.show();
	if( event.dataTransfer ){
		event.dataTransfer.effectAllowed = "copy";
		event.dataTransfer.dropEffect = "copy";
	} else if( event.originalEvent.dataTransfer ){
		event.originalEvent.dataTransfer.effectAllowed = "copy";
		event.originalEvent.dataTransfer.dropEffect = "copy";
	}

	dropTimeout = window.setTimeout(function(){ $dropOverlay.hide(); }, 3000);
}

/**
 * manage the drag over event for drag and drop
 * @param object event
 */
function dragOver(event){
	event.preventDefault();
	if( $dropOverlay.is(':hidden') ) $dropOverlay.show();

	clearTimeout(dropTimeout);
	dropTimeout = window.setTimeout(function(){ $dropOverlay.hide(); }, 3000);
}

/**
 * manage the drag leave event for drag and drop
 * @param object event
 */
function dragLeave(event){
	event.stopPropagation();
}

/**
 * manage the drop event for the cover
 * validate the dropped file or url
 * if image, upload it
 * if url, send it for curling
 * @param object event
 */
function dropCover(event){
	event.preventDefault();

	$dropOverlay.hide();

	var $form = $('.modal').filter(':visible').find('.edit-form'),
		rel = $form.attr('data-manage'),
		$coverStatus = $form.find('.cover-status'),
		$controlGroup = $coverStatus.closest('.control-group');

	//reset validation visual infos and error tip
	$coverStatus
		.siblings('.help-block, .help-inline').remove();
	$controlGroup.removeClass('warning error success info upload');

	var dt = event.dataTransfer,
		files = dt.files;

	// manage remote image
	if( files.length === 0 && dt.types.contains("application/x-moz-file-promise-url") ){
		url = dt.getData("application/x-moz-file-promise-url");

		$controlGroup.addClass('upload');

		$.ajax({
			url: 'ajax/manageCover.php?rel='+ rel,
			data: 'url='+ dt.getData("application/x-moz-file-promise-url")
		})
		.always(function(){
			$controlGroup.removeClass('upload');
		})
		.done(function(result){
			//var timestamp = new Date().getTime();
			//$('#editPreview').empty().append( $('<img>', { src: 'covers/' + result + '?' + timestamp }) );
			$('#'+ rel +'Cover').val( result );
			$controlGroup.addClass('success');
		})
		.fail(function(XMLHttpRequest, textStatus, errorThrown){
			$coverStatus.parent().append('<div class="help-block">'+ errorThrown +'</div>');
			$controlGroup.addClass('error');
		});
		return;
	}

	//only one cover for each cover
	if( files.length > 1 ){
		formErrors([[rel +'CoverStatus', 'Only one image is permitted, taking into account the first one.', 'warning']]);
	}

	//if it's not a remote image
	var file = files[0];
	if(file.type.match(/image.(jpe?g|png|gif)/)) {
		upload(file, rel, $coverStatus, $controlGroup);
	} else {
		formErrors([[rel +'CoverStatus', 'Only .jpg, .jpeg, .png or .gif images are permitted.', 'error']]);
	}
}

/*
 * Upload files to the server using HTML5 File API and sendAsBinary method
 * @param object file
 */
function upload(file, rel, $coverStatus, $controlGroup){
	if( window.FileReader ){
		var reader = new FileReader();
		if( typeof(reader.addEventListener) === "function" ){
			reader.addEventListener('loadend', function(){
				var xhr = new XMLHttpRequest();
				xhr.open("POST", 'ajax/manageCover.php?up=true&rel='+ rel, true);
				xhr.setRequestHeader('UP-NAME', 'upload');
				xhr.setRequestHeader('UP-FILENAME', file.name);
				xhr.setRequestHeader('UP-SIZE', file.size);
				xhr.setRequestHeader('UP-TYPE', file.type);
				xhr.send( window.btoa(reader.result) );

				$controlGroup.addClass('upload');

				xhr.onreadystatechange = function(){
					if( xhr.readyState == 4 ){
						$controlGroup.removeClass('upload');
						if( xhr.status == 200 ){
							$coverStatus.siblings('.help-inline, .help-block').remove();
							$controlGroup.addClass('valid');
							//var timestamp = new Date().getTime();
							//$('#editPreview').empty().append( $('<img>', { src: 'covers/' + file.name + '?' + timestamp }) );
							$('#'+ rel +'Cover').val( file.name );
						} else {
							$coverStatus.parent().append('<div class="help-block">'+ xhr.responseText +'</div>');
							$controlGroup.addClass('error');
						}
					}
				};
			}, true);

			reader.addEventListener('error', function(event){
				switch(event.target.error.code){
					case event.target.error.NOT_FOUND_ERR:
							$coverStatus.parent().append('<div class="help-block">File not found !</div>');
							$controlGroup.addClass('error').removeClass('upload');
						break;
					case event.target.error.NOT_READABLE_ERR:
							$coverStatus.parent().append('<div class="help-block">File not readable !</div>');
							$controlGroup.addClass('error').removeClass('upload');
						break;
					case event.target.error.ABORT_ERR:
						break;
					default:
							$coverStatus.parent().append('<div class="help-block">Reading error !</div>');
							$controlGroup.addClass('error').removeClass('upload');
						break;
				}
			}, true);

			reader.addEventListener('progress', function(event){
				if( event.lengthComputable ){
					$coverStatus.parent().append('<div class="help-block">Chargement : '+ Math.round((event.loaded * 100) / event.total) +'%</div>');
				}
			}, true);

			reader.addEventListener('loadProgress', function(event){
				if( event.lengthComputable ){
					$coverStatus.parent().append('<div class="help-block">Chargement : '+ Math.round((event.loaded * 100) / event.total) +'%</div>');
				}
			}, true);

		} else {
			//webkit
			reader.onload = function(){
				var xhr = new XMLHttpRequest();
				xhr.open("POST", 'ajax/manageCover.php?up=true&rel='+ rel, true);
				xhr.setRequestHeader('UP-NAME', 'upload');
				xhr.setRequestHeader('UP-FILENAME', file.name);
				xhr.setRequestHeader('UP-SIZE', file.size);
				xhr.setRequestHeader('UP-TYPE', file.type);
				xhr.send(window.btoa(reader.result));

				$controlGroup.addClass('upload');

				xhr.onload = function(){
					if( xhr.readyState == 4 ){
						$controlGroup.removeClass('upload');
						if( xhr.status == 200 ){
							$coverStatus.siblings('.help-inline, .help-block').remove();
							$controlGroup.addClass('valid');
							//var timestamp = new Date().getTime();
							//$('#editPreview').empty().append( $('<img>', { src: 'covers/' + file.name + '?' + timestamp }) );
							$('#'+ rel +'Cover').val( file.name );
						} else {
							$coverStatus.parent().append('<div class="help-block">'+ xhr.responseText +'</div>');
							$controlGroup.addClass('error');
						}
					}
				};
			};
		}

		// The function that starts reading the file as a binary string
		reader.readAsBinaryString( file );
	} else {
		$controlGroup.removeClass('upload').addClass('error');
		$coverStatus.parent().append('<div class="help-block">Upload functionnality is not supported</div>');
	}
}

/* helpers */
String.prototype.capitalize = function(){
	return this.charAt(0).toUpperCase() + this.substr(1);
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


