@extends('layouts.main')

@section('title', 'My Contact - Details')

@section('content')
<div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header card-title">
            <strong>Contact Details</strong>
          </div>           
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="mb-2 row">
                  <label class="col-md-3 col-form-label">Full Name</label>
                  <div class="col-md-9">
                    <p class="form-control-plaintext text-muted">{{ $data['name'] }}</p>
                  </div>
                </div>

                <div class="mb-2 row">
                  <label class="col-md-3 col-form-label">Gender</label>
                  <div class="col-md-9">
                    <p class="form-control-plaintext text-muted">{{ $data['gender'] }}</p>
                  </div>
                </div>

                <div class="mb-2 row">
                  <label class="col-md-3 col-form-label">Email Address</label>
                  <div class="col-md-9">
                    <p class="form-control-plaintext text-muted">{{ $data['email'] }}</p>
                  </div>
                </div>

                <div class="mb-2 row">
                  <label class="col-md-3 col-form-label">Phone Number</label>
                  <div class="col-md-9">
                    <p class="form-control-plaintext text-muted">{{ $data['phone'] }}</p>
                  </div>
                </div>

                <div class="mb-2 row">
                  <label class="col-md-3 col-form-label">Address</label>
                  <div class="col-md-9">
                    <p class="form-control-plaintext text-muted">{{ $data['address'] ?: 'N/A' }}</p>
                  </div>
                </div>
                <div class="mb-2 row">
                  <label class="col-md-3 col-form-label">Company</label>
                  <div class="col-md-9">
                    <p class="form-control-plaintext text-muted">{{ $data['company'] }}</p>
                  </div>
                </div>
                <hr>
                <div class="mb-2 row">
                  <div class="col-md-9 offset-md-3">
                      <a href="{{ route('contacts.edit', $data['id']) }}" class="btn btn-info text-white">Edit</a>
                      <form action="{{ route('contacts.destroy', $data['id']) }}" method="POST" style="display:inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                      </form>
                      <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection
