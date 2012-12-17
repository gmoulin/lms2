<form id="filter_band" name="filter_band" action="" method="post" class="filter-form">
	<input type="hidden" name="bandSortType" id="bandSortType" value="<?php echo ( isset($_SESSION['bandListFilters']) && isset($_SESSION['bandListFilters']['bandSortType']) && !empty($_SESSION['bandListFilters']['bandSortType']) ? $_SESSION['bandListFilters']['bandSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />

	<label for="bandNameFilter">Nom</label>
	<div class="input-append">
		<input type="search" name="bandNameFilter" id="bandNameFilter" class="span3" value="" list="bandNameFilterList" placeholder="nom du groupe" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bandNameFilterList"></datalist>

	<div class="controls pull-right">
		<button type="submit" name="bandSearchSubmit" class="btn btn-primary search">Rechercher</button>
		<button type="reset" name="bandSearchCancel" class="btn btn-warning reset">Annuler</button>
	</div>
</form>

