<?php
include('html_header.php');
?>
<!-- This code is taken from http://twitter.github.com/bootstrap/examples/hero.html -->
<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href="#">LMS2</a>
			<div class="nav-collapse collapse">
				<ul class="nav">
					<li class="active"><a href="#book">Books</a></li>
					<li><a href="#movie">Movies</a></li>
					<li><a href="#album">Albums</a></li>
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
			</div><!--/.nav-collapse -->
		</div>
	</div>
</div>

<div class="container">

	<!-- Main hero unit for a primary marketing message or call to action -->
	<div class="hero-unit">
		<h1>Hello, world!</h1>
		<p>This is a template for a simple marketing or informational website. It includes a large callout called the hero unit and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
		<p><a class="btn btn-primary btn-large">Learn more &raquo;</a></p>
	</div>

	<!-- Example row of columns -->
	<div class="row">
		<div class="span4">
			<h2>Heading</h2>
			<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
			<p><a class="btn" href="#">View details &raquo;</a></p>
		</div>
		<div class="span4">
			<h2>Heading</h2>
			<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
			<p><a class="btn" href="#">View details &raquo;</a></p>
	   </div>
		<div class="span4">
			<h2>Heading</h2>
			<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
			<p><a class="btn" href="#">View details &raquo;</a></p>
		</div>
	</div>

	<hr>

	<footer>
		<p>&copy; Company 2012</p>
	</footer>

</div> <!-- /container -->
<?php
include('html_footer.php');
?>
