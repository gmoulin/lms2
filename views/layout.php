<?php
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
			<div class="nav-collapse collapse">
				<ul class="nav">
					<li>
						<a href="#book">
							Livres
						</a>
					</li>
					<li>
						<a href="#movie">
							Films
						</a>
					</li>
					<li>
						<a href="#album">
							Albums
						</a>
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

				<ul class="nav pull-right">
					<li>
						<!-- add buttons -->
						<button data-target="#edit_book" id="add_book" class="btn add" data-toggle="modal" data-manage="book"><i class="icon-plus-sign"></i>Ajouter un livre</button>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-sort"></i></a>
						<div class="dropdown-menu sorts">
							<!-- sort links -->
							<?php
								include('views/sorts/book.php');
							?>
						</div>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-filter"></i></a>
						<div class="dropdown-menu filters">
							<!-- filter forms -->
							<?php
								include('views/filters/book.php');
							?>
						</div>
					</li>
				</ul>
			</div><!--/.nav-collapse -->
		</div>
	</div>
</div>

<div class="container-fluid smaller-padding">
	<div class="container-list">
		<section id="list_book" class="list withCover"></section>
		<section id="list_movies" class="list withCover"></section>
		<section id="list_album" class="list withCover"></section>
		<section id="list_storage" class="list withCover"></section>
		<section id="list_saga" class="list"></section>
		<section id="list_author" class="list"></section>
		<section id="list_artist" class="list"></section>
		<section id="list_brand" class="list"></section>
	</div>
</div> <!-- /container -->

<div id="drop-overlay">
	<h1>Drop</h1>
</div>

<?php
//list templates
include('lists/book.html');

//edit forms
include('forms/book.html');

//delete confirms
include('deletes/book.html');

//details popup
include('details/book.html');

//store with saga confirms
include('stores/book.html');

//scripts and footer
include('html_footer.php');
?>
