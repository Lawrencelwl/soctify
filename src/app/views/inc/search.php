<!-- Modal -->
<div class="modal fade create-post-modal" id="searchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <!-- Header -->
            <div class="modal-header py-3 border-0 pb-0">
                <header>Search</header>
                <!-- close button -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <input type="search" id="search" class="form-control" placeholder="Search for users" />
                <span id="seacrhLoadingSpinner" class="spinner-border p-4 my-3 mx-auto" role="status"
                    aria-hidden="true"></span>

                <div id="searchResult" class="d-flex flex-column gap-3 mt-3">
                </div>
            </div>
        </div>
    </div>
</div>