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
							Books
						</a>
					</li>
					<li>
						<a href="#movie">
							Movies
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
							<li><a href="#storage">Storages</a></li>
							<li class="divider"></li>
							<li><a href="#saga">Sagas</a></li>
							<li class="divider"></li>
							<li><a href="#author">Author</a></li>
							<li><a href="#artist">Artist</a></li>
							<li><a href="#band">Bands</a></li>
						</ul>
					</li>
				</ul>

				<ul class="nav pull-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-filter icon-large"></i></a>
						<div class="dropdown-menu filters">
							<!-- filter forms -->
							<?php
								include('views/filters/book.php');
							?>
						</div>
				</ul>
			</div><!--/.nav-collapse -->
		</div>
	</div>
</div>

<div class="container-list">
	<section id="list_book" class="list withCover"></section>
	<section id="list_movies" class="list withCover"></section>
	<section id="list_album" class="list withCover"></section>
	<section id="list_storage" class="list withCover"></section>
	<section id="list_saga" class="list"></section>
	<section id="list_author" class="list"></section>
	<section id="list_artist" class="list"></section>
	<section id="list_brand" class="list"></section>
</div> <!-- /container -->

<?php
//list templates
include('lists/book.html');

//scripts and footer
include('html_footer.php');
?>
