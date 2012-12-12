<form id="filter_book" name="filter_book" action="" method="post" class="filter-form">
	<input type="hidden" name="bookSortType" id="bookSortType" value="<?php echo ( isset($_SESSION['bookListFilters']) && isset($_SESSION['bookListFilters']['bookSortType']) && !empty($_SESSION['bookListFilters']['bookSortType']) ? $_SESSION['bookListFilters']['bookSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<label for="bookSearch">Recherche globale</label>
	<div class="input-append">
		<input type="search" name="bookSearch" id="bookSearch" class="span2 span2-override" value="" placeholder="dans les données textuelles" />
		<button type="submit" name="bookSearchSubmit" class="btn btn-primary search">Go</button>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<label for="bookTitleFilter">Titre</label>
	<div class="input-append">
		<input type="search" name="bookTitleFilter" id="bookTitleFilter" class="span3" value="" list="bookTitleFilterList" placeholder="titre du livre" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookTitleFilterList"></datalist>

	<label for="bookSagaFilter">Saga</label>
	<div class="input-append">
		<input type="search" name="bookSagaFilter" id="bookSagaFilter" class="span3" value="" list="bookSagaFilterList" placeholder="saga du livre" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookSagaFilterList"></datalist>

	<label for="bookAuthorFilter">Auteur</label>
	<div class="input-append">
		<input type="search" name="bookAuthorFilter" id="bookAuthorFilter" class="span3" value="" list="bookAuthorFilterList" placeholder="auteur du livre" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookAuthorFilterList"></datalist>

	<label for="bookLoanFilter">Prêt</label>
	<div class="input-append">
		<input type="search" name="bookLoanFilter" id="bookLoanFilter" class="span3" value="" list="bookLoanFilterList" placeholder="livre prêté à" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookLoanFilterList"></datalist>

	<label for="bookStorageFilter">Rangement</label>
	<div class="input-append">
		<select name="bookStorageFilter" id="bookStorageFilter" class="span3" autocomplete="off">
			<option value="">Livre rangé où ?</option>
		</select>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<div class="controls pull-right">
		<button type="submit" name="bookSearchSubmit" class="btn btn-primary search">Rechercher</button>
		<button type="reset" name="bookSearchCancel" class="btn btn-warning reset">Annuler</button>
	</div>
</form>

