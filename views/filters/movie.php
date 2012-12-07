<form id="filter_movie" name="filter_movie" action="" method="post" class="filter-form">
	<input type="hidden" name="movieSortType" id="movieSortType" value="<?php echo ( isset($_SESSION['movieListFilters']) && isset($_SESSION['movieListFilters']['movieSortType']) && !empty($_SESSION['movieListFilters']['movieSortType']) ? $_SESSION['movieListFilters']['movieSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<label for="movieSearch">Recherche globale</label>
	<div class="input-append">
		<input type="search" name="movieSearch" id="movieSearch" class="span2" value="" placeholder="dans les données textuelles" />
		<button type="submit" name="movieSearchSubmit" class="btn btn-primary search">Go</button>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<label for="movieTitleFilter">Titre</label>
	<div class="input-append">
		<input type="search" name="movieTitleFilter" id="movieTitleFilter" class="span3" value="" list="movieTitleFilterList" placeholder="titre du film" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="movieTitleFilterList"></datalist>

	<label for="movieSagaFilter">Saga</label>
	<div class="input-append">
		<input type="search" name="movieSagaFilter" id="movieSagaFilter" class="span3" value="" list="movieSagaFilterList" placeholder="saga du film" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="movieSagaFilterList"></datalist>

	<label for="movieArtistFilter">Artiste</label>
	<div class="input-append">
		<input type="search" name="movieArtistFilter" id="movieArtistFilter" class="span3" value="" list="movieArtistFilterList" placeholder="artiste du film" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="movieArtistFilterList"></datalist>

	<label for="movieLoanFilter">Prêt</label>
	<div class="input-append">
		<input type="search" name="movieLoanFilter" id="movieLoanFilter" class="span3" value="" list="movieLoanFilterList" placeholder="film prêté à" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="movieLoanFilterList"></datalist>

	<label for="movieStorageFilter">Rangement</label>
	<div class="input-append">
		<select name="movieStorageFilter" id="movieStorageFilter" class="span3" autocomplete="off">
			<option value="">Film rangé où ?</option>
		</select>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<div class="controls pull-right">
		<button type="submit" name="movieSearchSubmit" class="btn btn-primary search">Rechercher</button>
		<button type="reset" name="movieSearchCancel" class="btn btn-warning reset">Annuler</button>
	</div>
</form>

