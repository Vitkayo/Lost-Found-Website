@extends('layouts.main')

@section('title', 'My Contact')

@section('content')
<div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
            <div class="card-header card-title m-0">
              <div class="d-flex justify-content-between">
                <h4 class="m-0">All Contacts</h4>
                <div class="m-0">
                  <a href="{{ route('contacts.create') }}" class="btn btn-success"><i class="bi bi-plus-square"></i> Add New</a>
                </div>
              </div>
            </div>
          <div class="card-body">
            
            @include('contacts._company-filter')

            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Company</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {{-- Option 2: use @each with subview for contact items and empty view --}}
                  @each('contacts._contact', $data, 'value', 'contacts._empty')
                </tbody>
              </table> 
            </div>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                  <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                  </li>
                  <li class="page-item active"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                  </li>
                </ul>
              </nav>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection
