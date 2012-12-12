<form id="filter_album" name="filter_album" action="" method="post" class="filter-form">
	<input type="hidden" name="albumSortType" id="albumSortType" value="<?php echo ( isset($_SESSION['albumListFilters']) && isset($_SESSION['albumListFilters']['albumSortType']) && !empty($_SESSION['albumListFilters']['albumSortType']) ? $_SESSION['albumListFilters']['albumSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<label for="albumSearch">Recherche globale</label>
	<div class="input-append">
		<input type="search" name="albumSearch" id="albumSearch" class="span2 span2-override" value="" placeholder="dans les données textuelles" />
		<button type="submit" name="albumSearchSubmit" class="btn btn-primary search">Go</button>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<label for="albumTitleFilter">Titre</label>
	<div class="input-append">
		<input type="search" name="albumTitleFilter" id="albumTitleFilter" class="span3" value="" list="albumTitleFilterList" placeholder="titre de l'album" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="albumTitleFilterList"></datalist>

	<label for="albumBandFilter">Groupe</label>
	<div class="input-append">
		<input type="search" name="albumBandFilter" id="albumBandFilter" class="span3" value="" list="albumBandFilterList" placeholder="groupe de l'album" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="albumBandFilterList"></datalist>

	<label for="albumLoanFilter">Prêt</label>
	<div class="input-append">
		<input type="search" name="albumLoanFilter" id="albumLoanFilter" class="span3" value="" list="albumLoanFilterList" placeholder="album prêté à" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="albumLoanFilterList"></datalist>

	<div class="controls pull-right">
		<button type="submit" name="albumSearchSubmit" class="btn btn-primary search">Rechercher</button>
		<button type="reset" name="albumSearchCancel" class="btn btn-warning reset">Annuler</button>
	</div>
</form>

