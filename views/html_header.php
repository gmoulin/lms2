<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" class="no-js"  manifest="site.manifest">
<head>
	<meta charset="utf-8">
	<title>Gestionnaire de Médiathèque</title>
	<meta name="viewport" content="width=device-width">
	<meta name="identifier-url" content="http://<?php echo $_SERVER['SERVER_NAME']; ?>" />
	<meta name="Description" content="<?php echo strip_tags( $metadata['description'] ); ?>" />
	<meta name="Keywords" content="<?php echo strip_tags( $metadata['motscles'] ); ?>" />
	<meta name="robots" content="index, follow, noarchive" />
	<meta name="author" content="Guillaume Moulin" />

	<link rel="stylesheet" href="css/vendor/bootstrap.min.css">
	<style>
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}
	</style>
	<link rel="stylesheet" href="css/vendor/bootstrap-responsive.min.css">
	<link rel="stylesheet" href="css/lms2.css?v=<?php echo $cssTS; ?>">

	<script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
</head>
<body>
