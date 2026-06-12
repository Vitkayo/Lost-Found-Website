<div class="row">
    <div class="col-md-6"></div>
    <div class="col-md-6">
        <div class="row">
            <div class="col">
                @includeUnless(empty($companies_data), 'contacts._company-selection')
            </div>
            <div class="col">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search..." aria-label="Search...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search-heart"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>