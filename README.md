branch submodules
	sub/bootstrap
	sub/fontawesome
	sub/h5b-build-script
	sub/html5boilerplate
	sub/less

sub/boilerplates-merge
	contains merged sources for html5boilerplate, bootstrap, fontawesome and h5b-build-script

sub/boilerplates-merge/less_src
	contains less sources (bootstrap with fontawesome)
	contains site less files
 	contains modified Makefile for compiling css and js files, then copying them in "root" img, css and js folders

sub/boilerplates-merge/sass_src
	contains sass sources (bootstrap-sass with fontawesome)
	contains site sass files
	contains modified Rakefile for compiling or watching scss files
 	contains modified Makefile for compiling js files, then copying files in "root" img and js folders
