<form id="filter_saga" name="filter_saga" action="" method="post" class="filter-form">
	<input type="hidden" name="sagaSortType" id="sagaSortType" value="<?php echo ( isset($_SESSION['sagaListFilters']) && isset($_SESSION['sagaListFilters']['sagaSortType']) && !empty($_SESSION['sagaListFilters']['sagaSortType']) ? $_SESSION['sagaListFilters']['sagaSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />

	<label for="sagaTitleFilter">Titre</label>
	<div class="input-append">
		<input type="search" name="sagaTitleFilter" id="sagaTitleFilter" class="span3" value="" list="sagaTitleFilterList" placeholder="titre de la saga" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="sagaTitleFilterList"></datalist>

	<div class="controls pull-right">
		<button type="submit" name="sagaSearchSubmit" class="btn btn-primary search">Rechercher</button>
		<button type="reset" name="sagaSearchCancel" class="btn btn-warning reset">Annuler</button>
	</div>
</form>

