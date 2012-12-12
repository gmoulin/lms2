'use strict';

var $nav,
	$navLinks,
	$navDropdowns,
	$body,
	$win,
	$doc,
	$parts,
	$dropOverlay,
	$listContainer,
	$detailItem,
	$pagiNb,
	$pagiTotal,
	activeTab,
	target,
	updating = 0,
	dropTimeout,
	scrolling = false,
	end = false,
	$help,
	centeringDone = true;

var subDomains = ['s1', 's2', 's3'],
	useSubDomains = false;

$(document).ready(function(){
	$win = $(window);
	$body = $('body');
	$doc = $(document);
	$nav = $('.nav-collapse');
	$navLinks = $nav.find('a');
	$navDropdowns = $('.navbar').find('.container-fluid').children('.nav').find('.dropdown');
	$parts = $('.list, .filter-form, .sort-links, .add');
	$dropOverlay = $('#drop-overlay');
	$help = $('<span class="help-block"></span>');
	$listContainer = $('.container-list');

	var $pagi = $('.pagination');
	$pagiNb = $pagi.find('.nb');
	$pagiTotal = $pagi.find('.total');
	$pagi = null;

	$win.resize(function(){
		responseToResize();
	});

	/** _____________________________________________ IMG SUBDOMAINS **/
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

	//initial load
		tabSwitch();

	/** _____________________________________________ NAV **/
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

	/** _____________________________________________ LIST INFINITE SCROLL **/
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

	/** _____________________________________________ MODALS **/
		$('.modal').on('hide', function(){
			if( $(this).find('.cover-status').length > 0 ){
				$('html, .modal-backdrop')
					.unbind('dragenter')
					.unbind('dragover')
					.unbind('dragleave');

				$('html')[0].removeEventListener("drop", dropCover, true);
			}
		});

	/** _____________________________________________ EDIT FORM **/
		//quick links for title in form
		var $quickLink = $('<a class="btn btn-info quicklink" target="_blank"><i class="icon-link"></i></a>');
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
			})
			.on('submit', function(e){
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
			})
			.on('change', '.title', function(){
				var $this = $(this),
					rel = $this.closest('.edit-form').attr('data-manage');
				if( $this.val() === '' ){
					$this.parent().removeClass('input-append')
						.find('.quicklink').remove();
				}

				if( $this.siblings('.quicklink').length === 0 ){
					$this.parent().addClass('input-append');
					if( rel == 'book' ){
						$quickLink.clone().attr('title', 'Rechercher dans Google Image').appendTo( $this.parent() );
						$quickLink.clone().attr('title', 'Rechercher dans Fantastic Fiction').appendTo( $this.parent() );
					} else if( rel == 'movie' ){
						$quickLink.clone().attr('title', 'Rechercher dans Google Image').appendTo( $this.parent() );
						$quickLink.clone().attr('title', 'Rechercher dans IMDB').appendTo( $this.parent() );
					} else if( rel == 'album' ){
						$quickLink.clone().attr('title', 'Rechercher dans Google Image').appendTo( $this.parent() );
					} else if( rel == 'band' ){
						$quickLink.clone().attr('title', 'Rechercher sur Wikipedia').appendTo( $this.parent() );
					}
				}

				if( rel == 'book' ){
					$this.siblings('.quicklink')
						.first().attr('href', 'http://www.google.com/images?q=' + $this.val() + ' book').end()
						.last().attr('href', 'http://www.fantasticfiction.co.uk/search/?searchfor=book&keywords='+ $this.val());
				} else if( rel == 'movie' ){
					$this.siblings('.quicklink')
						.first().attr('href', 'http://www.google.com/images?q=' + $this.val() + ' movie').end()
						.last().attr('href', 'http://www.imdb.com/find?s=all&q='+ $this.val());
				} else if( rel == 'album' ){
					$this.siblings('.quicklink').attr('href', 'http://www.google.com/images?q=' + $this.val() + ' movie').end()
				} else if( rel == 'band' ){
					$this.siblings('.quicklink').attr('href', 'http://en.wikipedia.org/w/index.php?search=' + $this.val());
				}
			})
			.each(function(){
				//add event listener for dynamic form validation
				this.addEventListener("invalid", checkField, true);
				this.addEventListener("blur", checkField, true);
				this.addEventListener("input", checkField, true);
			});


		//saga title in form
		$('#bookSagaTitle, #movieSagaTitle').change(function(){
			var $this = $(this),
				$form = $this.closest('.edit-form'),
				rel = $form.attr('data-manage'),
				$dl = $this.siblings('datalist'),
				decoder = $('<textarea>'),
				$addAnother = $form.find('.add-another');

			$this.parent().removeClass('input-append')
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

	/** _____________________________________________ DETAIL **/
		$body.on('click', '.detail', function(){
			var $this = $(this),
				rel = $this.attr('data-manage'),
				$modal = $( $this.attr('data-target') ).attr('data-manage', rel);

			$detailItem = $this.closest('.item');
			fillDetailModal( $modal );
		});

	/** _____________________________________________ STORE WITH SAGA ACTION **/
		$body.on('click', '.store', function(){
			var $this = $(this),
				sagaId = $this.attr('data-sagaId'),
				itemId = $this.attr('data-itemId'),
				rel = $this.attr('data-manage'),
				$modal = $( $this.attr('data-target') ),
				$inputs = $modal.find('input');

			$inputs.filter('[name="id"]').val( itemId );
			$modal.find('.store-form').attr('data-manage', rel);

			//hide detail if visible
			$('.detail-modal').modal('hide');

			$.post('ajax/manage'+ rel.capitalize() +'.php', 'action=getSagaStorage&sagaId='+ sagaId, function(data){
				if( data.storageID && data.storageID > 0 ){
					$inputs.filter('[name="storageId"]').val( data.storageID );

					var $decoder = $('<textarea/>');

					$modal.find('p').show()
						.filter('.none-found').hide().end()
						.end()
						.find('.btn-primary').show;

					$modal.find('.storage-description').html( $decoder.html(data.storageRoom +' '+ data.storageType +' - '+ data.storageColumn + data.storageLine).val() );
				} else {
					$modal.find('p').hide()
						.filter('.none-found').show().end()
						.end()
						.find('.btn-primary').hide();
				}
			});
		});

		$('.store-form').submit(function(e){
			e.preventDefault();
			var $form = $(this),
				rel = $form.attr('data-manage');

			$.post('ajax/manage'+ rel.capitalize() +'.php', $form.serialize(), function(data){
				if( data == 'ok' ){
					$form.closest('.modal').modal('hide');
					getList(2);
				}
			});
		});

	/** _____________________________________________ ADD / EDIT ACTIONS **/
		$body.on('click', '.add, .edit', function(e){
			e.preventDefault();

			var $this = $(this),
				rel = $this.attr('data-manage'),
				$form = $('#edit_' + rel),
				$another = $form.find('.another');

			//hide detail if visible
			$('.detail-modal').modal('hide');

			$another.filter(':gt(0)').remove();

			$another.first()
				.find('input')
					.attr('id', function(i, id){ return id.replace(/_[0-9]+/, '_1'); })
					.attr('name', function(i, name){ return name.replace(/_[0-9]+/, '_1'); })
				.end()
				.find('label')
					.attr('for', function(i, attr){ return attr.replace(/_[0-9]+/, '_1'); });

			//reseting form
			$form
				.data('save_clicked', 0)
				.find(':input').val('').end()
				.find('.help-inline').remove().end()
				.find('.control-group').attr('class', 'control-group').end()
				.find('.quicklink-append').removeClass('input-append')
				.find('.quicklink').remove();

			if( $form.find('.cover-status').length > 0 ){
				$('html, #drop-overlay')
					.bind('dragenter', dragEnter)
					.bind('dragover', dragOver)
					.bind('dragleave', dragLeave);

				$('html')[0].addEventListener("drop", dropCover, true);

				$form.find('.cover-preview').empty();
			}

			$form.find('datalist, select').loadList();

			if( $this.is('.edit') ){
				//set action
				$('#' + rel + 'Action').val('update');

				var decoder = $('<textarea>'),
					indice = 1;
				//load the data and set the form fields with it
				$.post('ajax/manage'+ rel.capitalize() +'.php', 'action=get&id=' + $this.attr('data-itemId'), function(data){
					switch( rel ){
						case 'book':
								$('#bookID').val( data.bookID );
								$('#bookTitle').val( decoder.html(data.bookTitle).val() ).change();
								$('#bookSize').val( data.bookSize );
								$('#bookCover').val( data.bookCover );
								$form.find('.cover-preview').html( $('<img>', { src: 'image.php?cover=book&id='+ data.bookID }) );
								$('#bookSagaTitle').val( decoder.html(data.sagaTitle).val() ).change();
								$('#bookSagaPosition').val( data.bookSagaPosition );

								//options for this select are reseted by loadList()
								//at this point the list can be empty
								$('#bookStorage').data('selectedId', data.storageID);

								$.each(data.authors, function(i, author){
									if( indice > 1 ) $another.click();
									$('#bookAuthor_'+ indice).val( decoder.html(author.authorFirstName+' '+author.authorLastName).val() );
									indice++;
								});
							break;
						case 'movie':
								$('#movieID').val( data.movieID );
								$('#movieTitle').val( decoder.html(data.movieTitle).val() ).change();
								$('#movieGenre').val( decoder.html(data.movieGenre).val() );
								$('#movieMediaType').val( decoder.html(data.movieMediaType ).val());
								$('#movieLength').val( data.movieLength );
								$('#movieCover').val( data.movieCover );
								$form.find('.cover-preview').html( $('<img>', { src: 'image.php?cover=movie&id='+ data.movieID }) );
								$('#movieSagaTitle').val( decoder.html(data.sagaTitle).val() ).change();
								$('#movieSagaPosition').val( data.movieSagaPosition );

								//options for this select are reseted by initMovieFormList()
								//at this point the list can be empty
								$('#movieStorage').data('selectedId', data.storageID);

								$.each(data.artists, function(i, artist){
									if( indice > 1 ) $another.click();
									$('#movieArtist_'+ indice).val( decoder.html(artist.artistFirstName+' '+artist.artistLastName).val() );
									indice++;
								});
							break;
						case 'album':
								$('#albumID').val( data.albumID );
								$('#albumTitle').val( decoder.html(data.albumTitle).val() ).change();
								$('#albumType').val( data.albumType );
								$('#albumCover').val( data.albumCover );
								$form.find('.cover-preview').html( $('<img>', { src: 'image.php?cover=album&id='+ data.albumID }) );

								//options for this select are reseted by initAlbumFormList()
								//at this point the list can be empty
								$('#albumStorage').data('selectedId', data.storageID);

								$.each(data.bands, function(i, band){
									if( indice > 1 ) $another.click();
									$('#albumBand_'+ indice).val( decoder.html(band.bandName).val() );
									indice++;
								});
							break;
						case 'alcohol':
								$('#alcoholID').val( data.alcoholID );
								$('#alcoholName').val( decoder.html(data.alcoholName).val() ).change();
								$('#alcoholType').val( data.alcoholType );
								$('#alcoholYear').val( data.alcoholYear );
								$('#alcoholCover').val( data.alcoholCover );
								$('#alcoholRating_'+ data.alcoholRating).prop('checked', true);
								$form.find('.cover-preview').html( $('<img>', { src: 'image.php?cover=alcohol&id='+ data.alcoholID }) );

								//options for this select are reseted by initalcoholFormList()
								//at this point the list can be empty
								$('#alcoholStorage').data('selectedId', data.storageID);

								$.each(data.makers, function(i, maker){
									if( indice > 1 ) $another.click();
									$('#alcoholMaker_'+ indice).val( decoder.html(maker.makerName).val() );
									indice++;
								});
							break;
						case 'author':
								$('#authorID').val( data.authorID );
								$('#authorFirstName').val( decoder.html(data.authorFirstName).val() );
								$('#authorLastName').val( decoder.html(data.authorLastName).val() );
								$('#authorWebSite').val( data.authorWebSite );
								$('#authorSearchURL').val( data.authorSearchURL );
							break;
						case 'band':
								$('#bandID').val( data.bandID);
								$('#bandName').val( decoder.html(data.bandName).val() ).change();
								$('#bandGenre').val( decoder.html(data.bandGenre).val() );
								$('#bandWebSite').val( data.bandWebSite );
							break;
						case 'artist':
								$('#artistID').val( data.artistID );
								$('#artistFirstName').val( decoder.html(data.artistFirstName).val() );
								$('#artistLastName').val( decoder.html(data.artistLastName).val() );
							break;
						case 'saga':
								$('#sagaID').val( data.sagaID );
								$('#sagaTitle').val( decoder.html(data.sagaTitle).val() );
								$('#sagaSearchURL').val( data.sagaSearchURL );
								$('#sagaRating_'+ data.sagaRating).prop('checked', true);
							break;
						case 'storage':
								$('#storageID').val( data.storageID );
								$('#storageRoom').val( data.storageRoom );
								$('#storageType').val( data.storageType );
								$('#storageColumn').val( data.storageColumn );
								$('#storageLine').val( data.storageLine );
								var url = getFullUrl('storage/'+ data.storageRoom.urlify() +'_'+ data.storageType.urlify() + (data.storageColumn !== null || data.storageLine !== null ? '_'+ data.storageColumn + data.storageLine : '' ) +'.png', '');
								$form.find('.cover-preview').html( $('<img>', { src: url }) );
							break;
						default:
							break;
					}
				});
			} else {
				//set action
				$('#'+ rel +'Action').val('add');

				if( rel == 'loan' ){
					$form
						.find('#loanFor').val( $this.attr('data-relation') ).end()
						.find('#itemID').val( $this.attr('data-itemId') );
				} else if( rel == 'album' ){
					$form.find('#albumStorage').data('selectedId', 56); //Miro
				}
			}

			window.setTimeout(function(){
				//remove validation classes and focus the first field
				$form
					.find('select').blur().end()
					.find('input').filter('[type="text"]').first().focus().end()
					.find('.control-group').attr('class', 'control-group');
			}, 300);
		});

	/** _____________________________________________ DELETE ACTIONS **/
		$body.on('click', '.delete', function(e){
			var $this = $(this),
				$modal = $( $this.attr('data-target') ),
				$form = $modal.find('.delete-form'),
				rel = $this.attr('data-manage'),
				r = rel.capitalize(),
				itemId = $this.attr('data-itemId');

			//modify modal according to rel
			$form.attr('data-manage', rel)
				.find('input')
					.filter('[name="action"]').attr('id', rel +'Action').end()
					.filter('[name="id"]').attr('id', rel +'ID');

			if( rel == 'storage' || rel == 'author' || rel == 'artist' || rel == 'band' || rel == 'maker' || rel == 'saga' ){
				$.ajax({
					url: 'ajax/manage'+ r +'.php',
					type: 'POST',
					data: 'action=impact&id='+ itemId,
					dataType: 'html',
					async: false,
					success: function(data){
						if( $.trim(data) !== '' ){
							$form
								.find('.impact').html(data).show().end()
								.find('.lead').hide();
							$modal.find('.modal-footer').find('.btn-primary').prop('disabled', true);
							$('#impact'+ r +'List').loadList();
						} else {
							$form
								.find('.impact').hide().end()
								.find('.lead').show();
							$modal.find('.modal-footer').find('.btn-primary').removeProp('disabled');
						}
					}
				});
			} else {
				$form
					.find('.impact').hide().end()
					.find('.lead').show();
			}

			$form.data('save_clicked', 0)
				.find('#'+ rel +'ID').val( itemId );
		});

		$('.delete-form').submit(function(e){
			e.preventDefault();
			var $this = $(this),
				$modal = $this.closest('.modal'),
				rel = $this.attr('data-manage');

			//multiple call protection
			if( $this.data('save_clicked') != 1 ){
				$this.data('save_clicked', 1);

				//send delete
				$.post('ajax/manage'+ rel.capitalize() +'.php', $this.serialize(), function(data){
					if( data == 'ok' ){
						//refresh list
						getList(2);
						//modal close
						$modal.modal('hide');
					} else {
						//form errors display
						formErrors(data);
					}
				});
			}
		});

		$body.on('submit', '.impact-form', function(e){
			e.preventDefault();

			if( $('#impactStorageList').val() === '' ){
				formErrors([['impactStorageList', 'Le nouveau rangement est requis.', 'required']]);
			} else {
				var $form = $(this);
				$.post('ajax/manageStorage.php', 'action=relocate&'+ $.param( $form.find('input:checked, select'), true ), function(data){
					if( data == 'ok' ){
						//clean the relocated items
						$impactStorage.find('input').filter(':checked').parent().remove();

						//activate the confirm button when all items have been relocated
						if( $form.find('input').length === 0 ){
							$form.closest('.detele-modal')
								.find('.modal-footer').find('.btn-primary').removeProp("disabled").end()
								.find('.lead').show();

							$form.parent()
								.find('.modal-impact').remove().end()
								.hide();
						}
					}
				});
			}
		});

		$body.on('click', '.impact-form h3', function(e){
			var $this = $(this),
				$sagaToggle = $this.find('input'),
				$ul = $this.next('ul');

			if( !$(e.target).is('input') ){
				$sagaToggle.prop('checked', !$sagaToggle.prop('checked'));
			}

			$ul.find('input').prop('checked', $sagaToggle.prop('checked'));
		});

	/** _____________________________________________ SEARCH & FILTERS BUTTONS **/
		$body.on('click', '.filter', function(e){
			e.preventDefault();
			var $this = $(this),
				filter = $this.attr('data-filter').capitalize();

			//hide all popup
			$('.modal').filter(':visible').modal('hide');

			//set filter value in filter form
			$('#'+ activeTab + filter +'Filter').val( $this.attr('data-value') );

			getList(1);

			//open filter dropdown
			window.setTimeout(function(){
				$('.navbar').find('.filters').dropdown('toggle').closest('.dropdown').addClass('open');
			}, 300);
		});

		$('.filter-form')
			.on('click', '.search', function(e){
				e.preventDefault();
				getList(1);
			})
			.on('click', '.reset', function(e){
				e.preventDefault();
				$(this).closest('.filter-form').find('input[type="search"], select').val('');
				getList(1);
			})
			.on('click', '.clear', function(e){
				e.preventDefault();
				$(this).siblings('input, select').val('');
				getList(1);
			})
			.submit(function(e){
				e.preventDefault();
			});

		$('.filter-form, .sort-links')
			//prevent dropdown hide on inside element click
			.on('click', 'input, select, label, button', function(e){
				e.stopPropagation();
			});
});

/**
 * change the current tab
 */
var tabSwitch = function(){
	var onLoad = target === undefined;

	target = window.location.hash.substr(1) || $navLinks.eq(0).attr('href').substr(1);

	$navLinks.parents().removeClass('active');
	$navLinks.filter('[href$="'+ target +'"]').parent().addClass('active');

	$parts.hide()
		.filter('[id$="_'+ target +'"]').show();

	$navDropdowns.toggle( target != 'storage' && target != 'author' && target != 'artist' && target != 'band' && target != 'maker' );

	if( target != activeTab && $parts.filter('[id="list_'+ target +'"]').hasClass('withCover') && $listContainer.width() > 767 ) centeringDone = false;

	activeTab = target;

	if( $('#list_'+ target).length > 0 ){ // Argument is a valid tab name
		window.location.hash = '#' + target; //security if hash empty

		if( onLoad ){
			getList(0);
		} else {
			responseToResize();
		}
	}
};

/**
 * load the list
 * @param integer type
 * 		0: new list
 * 		1: search or sort
 * 		2: list refresh after item modification
 * 		3: new page on current list (infinite scroll)
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

			$pagiNb.text( data.nb );
			$pagiTotal.text( data.total );

			$list.append( tmpl('list_'+ target +'_tmpl', data) );

			if( !centeringDone ){
				var containerWidth = $listContainer.width(),
					itemWidth = $listContainer.find('#list_'+ activeTab).find('.item').first().outerWidth(true);

				$listContainer.width( Math.floor(containerWidth / itemWidth) * itemWidth );

				centeringDone = true;
			} else {
				$listContainer.css('width', 'auto');
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

			if( $this.data('selectedId') ){
				$this.val( $this.data('selectedId') );
				$this.removeData('selectedId');
			}
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
				.find('.help-block').remove().end()
				//add error message
				.append( $help.clone().text(error[1]) );
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

	dropTimeout = window.setTimeout(function(){ $dropOverlay.hide(); }, 10000);
}

/**
 * manage the drag over event for drag and drop
 * @param object event
 */
function dragOver(event){
	event.preventDefault();
	if( $dropOverlay.is(':hidden') ) $dropOverlay.show();

	clearTimeout(dropTimeout);
	dropTimeout = window.setTimeout(function(){ $dropOverlay.hide(); }, 10000);
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
		.siblings('.help-block').remove();
	$controlGroup.removeClass('error success warning info upload');

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
			$controlGroup.removeClass('upload error success warning info upload');
		})
		.done(function(result){
			$controlGroup.addClass('success')
				.find('.cover-preview').html( $('<img>', { src: 'covers/' + file.name + '?' + (new Date().getTime()) }) );
			$('#'+ rel +'Cover').val( result );
		})
		.fail(function(XMLHttpRequest, textStatus, errorThrown){
			$coverStatus.parent().append( $help.clone().text(errorThrown) );
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
							$coverStatus.siblings('.help-block').remove();
							$controlGroup.addClass('success')
								.find('.cover-preview').html( $('<img>', { src: 'covers/' + file.name + '?' + (new Date().getTime()) }) );
							$('#'+ rel +'Cover').val( file.name );
						} else {
							$coverStatus.siblings('.help-block').remove();
							if( xhr.status == 500 || xhr.responseText === '' ){
								$coverStatus.parent().append( $help.clone().text(xhr.statusText) );
							} else {
								$coverStatus.parent().append( $help.clone().text(xhr.responseText) );
							}
							$controlGroup.addClass('error');
						}
					}
				};
			}, true);

			reader.addEventListener('error', function(event){
				$coverStatus.siblings('.help-block').remove();
				switch(event.target.error.code){
					case event.target.error.NOT_FOUND_ERR:
							$coverStatus.parent().append( $help.clone().text('File not found !') );
							$controlGroup.addClass('error').removeClass('upload');
						break;
					case event.target.error.NOT_READABLE_ERR:
							$coverStatus.parent().append( $help.clone().text('File not readable !') );
							$controlGroup.addClass('error').removeClass('upload');
						break;
					case event.target.error.ABORT_ERR:
						break;
					default:
							$coverStatus.parent().append( $help.clone().text('Reading error !') );
							$controlGroup.addClass('error').removeClass('upload');
						break;
				}
			}, true);

			reader.addEventListener('progress', function(event){
				if( event.lengthComputable ){
					$coverStatus.siblings('.help-block').remove();
					$coverStatus.parent().append( $help.clone().text('Loading: '+ Math.round((event.loaded * 100) / event.total) +'%') );
				}
			}, true);

			reader.addEventListener('loadProgress', function(event){
				if( event.lengthComputable ){
					$coverStatus.siblings('.help-block').remove();
					$coverStatus.parent().append( $help.clone().text('Loading: '+ Math.round((event.loaded * 100) / event.total) +'%') );
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
							$coverStatus.siblings('.help-block').remove();
							$controlGroup.addClass('success')
								.find('.cover-preview').html( $('<img>', { src: 'covers/' + file.name + '?' + (new Date().getTime()) }) );
							$('#'+ rel +'Cover').val( file.name );
						} else {
							$coverStatus.parent().append( $help.clone().text(xhr.responseText) );
							$controlGroup.addClass('error');
						}
					}
				};

				xhr.onerror = function(){
					console.log('webkit error');
				};
			};
		}

		// The function that starts reading the file as a binary string
		reader.readAsBinaryString( file );
	} else {
		$controlGroup.removeClass('upload').addClass('error');
		$coverStatus.parent().append( $help.clone().text('Upload functionnality is not supported') );
	}
}

/**
 * fill detail modal parts from item code in list
 */
var fillDetailModal = function( $modal ){
	var $clone = $detailItem.clone();
	$detailItem = null;

	$modal.find('.modal-body')
		.find('.cover').html( $clone.find('img') ).end()
		.find('.data').html( $clone.find('dl') )
		.find('.detail').remove();
};

/**
 * called when window resize
 */
var responseToResize = function(){
	if( matchMedia('screen and (max-width: 480px)').matches ){
		$('.sorts').find('.btn-group').addClass('responseTo').removeClass('btn-group');
	}

	getList(1);
};
