<form id="filter_alcohol" name="filter_alcohol" action="" method="post" class="filter-form">
	<input type="hidden" name="alcoholSortType" id="alcoholSortType" value="<?php echo ( isset($_SESSION['alcoholListFilters']) && isset($_SESSION['alcoholListFilters']['alcoholSortType']) && !empty($_SESSION['alcoholListFilters']['alcoholSortType']) ? $_SESSION['alcoholListFilters']['alcoholSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<label for="alcoholSearch">Recherche globale</label>
	<div class="input-append">
		<input type="search" name="alcoholSearch" id="alcoholSearch" class="span2 span2-override" value="" placeholder="dans les données textuelles" />
		<button type="submit" name="alcoholSearchSubmit" class="btn btn-primary search">Go</button>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<label for="alcoholNameFilter">Nom</label>
	<div class="input-append">
		<input type="search" name="alcoholNameFilter" id="alcoholNameFilter" class="span3" value="" list="alcoholNameFilterList" placeholder="nom de l'alcool" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="alcoholNameFilterList"></datalist>

	<label for="alcoholMakerFilter">Producteur</label>
	<div class="input-append">
		<input type="search" name="alcoholMakerFilter" id="alcoholMakerFilter" class="span3" value="" list="alcoholMakerFilterList" placeholder="nom du producteur" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="alcoholMakerFilterList"></datalist>

	<label for="alcoholOfferedByFilter">Offert par</label>
	<div class="input-append">
		<input type="search" name="alcoholOfferedByFilter" id="alcoholOfferedByFilter" class="span3" value="" list="alcoholOfferedByFilterList" placeholder="nom" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="alcoholOfferedByFilterList"></datalist>

	<div class="controls pull-right">
		<button type="submit" name="alcoholSearchSubmit" class="btn btn-primary search">Rechercher</button>
		<button type="reset" name="alcoholSearchCancel" class="btn btn-warning reset">Annuler</button>
	</div>
</form>

