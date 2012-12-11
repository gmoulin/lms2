<?php
$parts = array('book', 'movie', 'album', 'storage', 'loan', 'author', 'artist', 'band', 'saga');
include('html_header.php');
?>
<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>

			<a class="brand" href="#">LMS2</a>

			<ul class="nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-filter"></i></a>
					<div class="dropdown-menu filters">
						<!-- filter forms -->
						<?php
							foreach( $parts as $p ){
								if( file_exists('views/filters/'.$p.'.php') ){
									include('views/filters/'.$p.'.php');
								}
							}
						?>
					</div>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-sort"></i></a>
					<div class="dropdown-menu sorts">
						<!-- sort links -->
						<?php
							foreach( $parts as $p ){
								if( file_exists('views/sorts/'.$p.'.php') ){
									include('views/sorts/'.$p.'.php');
								}
							}
						?>

						<div class="pagination">
							<span class="nb"></span> sur <span class="total"></span>
						</div>
					</div>
				</li>
				<li class="hidden-phone hidden-tablet">
					<!-- add buttons -->
					<button data-target="#edit_book" id="add_book" class="btn add" data-toggle="modal" data-manage="book"><i class="icon-plus-sign"></i><span>Ajouter un livre</span></button>
					<button data-target="#edit_movie" id="add_movie" class="btn add" data-toggle="modal" data-manage="movie"><i class="icon-plus-sign"></i><span>Ajouter un film</span></button>
					<button data-target="#edit_album" id="add_album" class="btn add" data-toggle="modal" data-manage="album"><i class="icon-plus-sign"></i><span>Ajouter un album</span></button>
					<button data-target="#edit_storage" id="add_storage" class="btn add" data-toggle="modal" data-manage="storage"><i class="icon-plus-sign"></i><span>Ajouter un rangement</span></button>
					<button data-target="#edit_saga" id="add_saga" class="btn add" data-toggle="modal" data-manage="saga"><i class="icon-plus-sign"></i><span>Ajouter une saga</span></button>
					<button data-target="#edit_author" id="add_author" class="btn add" data-toggle="modal" data-manage="author"><i class="icon-plus-sign"></i><span>Ajouter un auteur</span></button>
					<button data-target="#edit_artist" id="add_artist" class="btn add" data-toggle="modal" data-manage="artist"><i class="icon-plus-sign"></i><span>Ajouter un artiste</span></button>
					<button data-target="#edit_band" id="add_band" class="btn add" data-toggle="modal" data-manage="band"><i class="icon-plus-sign"></i><span>Ajouter un groupe</span></button>
				</li>
			</ul>

			<div class="nav-collapse">
				<ul class="nav">
					<li>
						<a href="#book">Livres</a>
					</li>
					<li>
						<a href="#movie">Films</a>
					</li>
					<li>
						<a href="#album">Albums</a>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="#storage">Rangements</a></li>
							<li class="divider"></li>
							<li><a href="#saga">Sagas</a></li>
							<li class="divider"></li>
							<li><a href="#author">Auteurs</a></li>
							<li><a href="#artist">Artistes</a></li>
							<li><a href="#band">Groupes</a></li>
						</ul>
					</li>
				</ul>
			</div><!--/.nav-collapse -->
		</div>
	</div>
</div>

<div class="container-fluid override">
	<div class="container-list">
		<section id="list_book" class="list withCover"></section>
		<section id="list_movie" class="list withCover"></section>
		<section id="list_album" class="list withCover"></section>
		<section id="list_storage" class="list"></section>
		<section id="list_saga" class="list"></section>
		<section id="list_author" class="list"></section>
		<section id="list_artist" class="list"></section>
		<section id="list_band" class="list"></section>
	</div>
</div> <!-- /container -->

<div id="drop-overlay">
	<h1>Drop</h1>
</div>

<?php
//list templates
foreach( $parts as $p ){
	if( file_exists('views/lists/'.$p.'.html') ){
		include('views/lists/'.$p.'.html');
	}
}

//edit forms
foreach( $parts as $p ){
	if( file_exists('views/forms/'.$p.'.html') ){
		include('views/forms/'.$p.'.html');
	}
}

//delete confirms
foreach( $parts as $p ){
	if( file_exists('views/deletes/'.$p.'.html') ){
		include('views/deletes/'.$p.'.html');
	}
}

//details popup
foreach( $parts as $p ){
	if( file_exists('views/details/'.$p.'.html') ){
		include('views/details/'.$p.'.html');
	}
}

//store with saga confirms
foreach( $parts as $p ){
	if( file_exists('views/stores/'.$p.'.html') ){
		include('views/stores/'.$p.'.html');
	}
}

//scripts and footer
include('html_footer.php');
?>
