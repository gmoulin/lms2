<?php
try {
	require_once('inc/conf.ini.php');

	//metadata
	$metadata['description'] = 'Librairy Content Manager - gestionnaire de bibliothèque, vidéothèque et musicothèque';
	$metadata['motscles'] = 'librairie, contenu, gestion, gestionnaire, bibliothèque, livre, roman, auteur, vidéothèque, film, acteur, musique, musicothèque, album, groupe';
	$lang = 'fr';

	//only for dev to assure last version
	if( file_exists(LMS_PATH.'/css/lms2.css') ) $cssTS = filemtime( LMS_PATH.'/css/lms2.css' );
	if( file_exists(LMS_PATH.'/js/script.js') ) $scriptTS = filemtime( LMS_PATH.'/js/script.js' );
	if( file_exists(LMS_PATH.'/js/plugins.js') ) $pluginTS = filemtime( LMS_PATH.'/js/plugins.js' );

} catch (Exception $e) {
	echo $e->getMessage();
	die;
}

include('views/layout.php');
?>