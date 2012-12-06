<form id="filter_book" name="filter_book" action="" method="post" class="filter-form">
	<input type="hidden" name="bookSortType" id="bookSortType" value="<?php echo ( isset($_SESSION['bookListFilters']) && isset($_SESSION['bookListFilters']['bookSortType']) && !empty($_SESSION['bookListFilters']['bookSortType']) ? $_SESSION['bookListFilters']['bookSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<label for="bookSearch">Search</label>
	<div class="input-append">
		<input type="search" name="bookSearch" id="bookSearch" class="span2" value="" placeholder="in textual data" />
		<button type="submit" name="bookSearchSubmit" class="btn btn-primary search">Search</button>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<label for="bookTitleFilter">Title</label>
	<div class="input-append">
		<input type="search" name="bookTitleFilter" id="bookTitleFilter" class="span3" value="" list="bookTitleFilterList" placeholder="book title" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookTitleFilterList"></datalist>

	<label for="bookSagaFilter">Saga</label>
	<div class="input-append">
		<input type="search" name="bookSagaFilter" id="bookSagaFilter" class="span3" value="" list="bookSagaFilterList" placeholder="book saga" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookSagaFilterList"></datalist>

	<label for="bookAuthorFilter">Author</label>
	<div class="input-append">
		<input type="search" name="bookAuthorFilter" id="bookAuthorFilter" class="span3" value="" list="bookAuthorFilterList" placeholder="book author" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookAuthorFilterList"></datalist>

	<label for="bookLoanFilter">Loan</label>
	<div class="input-append">
		<input type="search" name="bookLoanFilter" id="bookLoanFilter" class="span3" value="" list="bookLoanFilterList" placeholder="book loaned to" autocomplete="off">
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>
	<datalist id="bookLoanFilterList"></datalist>

	<label for="bookStorageFilter">Storage</label>
	<div class="input-append">
		<select name="bookStorageFilter" id="bookStorageFilter" class="span3" autocomplete="off">
			<option value="">Book stored where</option>
		</select>
		<button class="btn btn-warning clear"><i class="icon-remove-sign"></i></button>
	</div>

	<div class="controls pull-right">
		<button type="submit" name="bookSearchSubmit" class="btn btn-primary search">Search</button>
		<button type="reset" name="bookSearchCancel" class="btn btn-warning reset">Reset</button>
	</div>
</form>

