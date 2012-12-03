<form id="filter_book" name="filter_book" action="" method="post" class="filter-form">
	<input type="hidden" name="bookSortType" id="bookSortType" value="<?php echo ( isset($_SESSION['bookListFilters']) && isset($_SESSION['bookListFilters']['bookSortType']) && !empty($_SESSION['bookListFilters']['bookSortType']) ? $_SESSION['bookListFilters']['bookSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<label for="bookSearch">Search</label>
	<div class="input-append controls-row">
		<input type="search" name="bookSearch" id="bookSearch" class="span2" value="" placeholder="in textual data" />
		<button type="submit" name="bookSearchSubmit" class="btn btn-primary span1">Search</button>
	</div>

	<label for="bookTitleFilter">Title</label>
	<input type="search" name="bookTitleFilter" id="bookTitleFilter" class="span3" value="" list="bookTitleFilterList" placeholder="book title" />
	<datalist id="bookTitleFilterList"></datalist>

	<label for="bookSagaFilter">Saga</label>
	<input type="search" name="bookSagaFilter" id="bookSagaFilter" class="span3" value="" list="bookSagaFilterList" placeholder="book saga" />
	<datalist id="bookSagaFilterList"></datalist>

	<label for="bookAuthorFilter">Author</label>
	<input type="search" name="bookAuthorFilter" id="bookAuthorFilter" class="span3" value="" list="bookAuthorFilterList" placeholder="book author" />
	<datalist id="bookAuthorFilterList"></datalist>

	<label for="bookLoanFilter">Loan</label>
	<input type="search" name="bookLoanFilter" id="bookLoanFilter" class="span3" value="" list="bookLoanFilterList" placeholder="book loaned to" />
	<datalist id="bookLoanFilterList"></datalist>

	<label for="bookStorageFilter">Storage</label>
	<select name="bookStorageFilter" id="bookStorageFilter" class="span3">
		<option value="">Book stored where</option>
	</select>

	<div class="controls pull-right">
		<button type="submit" name="bookSearchSubmit" class="btn btn-primary">Search</button>
		<button type="reset" name="bookSearchCancel" class="btn">Reset</button>
	</div>
</form>

