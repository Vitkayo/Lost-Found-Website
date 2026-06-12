@extends('layouts.main')

@section('title', 'My Contact - Create')

@section('content')
<div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header card-title">
            <strong>Add New Contact</strong>
          </div>           
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <form method="POST" action="{{ route('contacts.store') }}">
                    @csrf
                    <div class="mb-3 row">
                      <label for="name" class="col-md-3 col-form-label">First Name</label>
                      <div class="col-md-9">
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                    </div>
                    <div class="mb-3 row">
                      <label for="gender" class="col-md-3 col-form-label">Gender</label>
                      <div class="col-md-9">
                        <select name="gender" id="gender" class="form-control form-select">
                          <option value="">Select Gender</option>
                          <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                          <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                        </select>
                        @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                    </div>
                    <div class="mb-3 row">
                      <label for="email" class="col-md-3 col-form-label">Email</label>
                      <div class="col-md-9">
                        <input type="text" name="email" id="email" class="form-control" value="{{ old('email') }}">
                        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                    </div>
                    <div class="mb-3 row">
                      <label for="phone" class="col-md-3 col-form-label">Phone</label>
                      <div class="col-md-9">
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                    </div>
                    <div class="mb-3 row">
                      <label for="address" class="col-md-3 col-form-label">Address</label>
                      <div class="col-md-9">
                        <textarea name="address" id="address" rows="3" class="form-control">{{ old('address') }}</textarea>
                        @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                    </div>
                    
                    <div class="mb-3 row">
                      <label for="company_id" class="col-md-3 col-form-label">Company</label>
                      <div class="col-md-9">
                         @include('contacts._company-selection')
                         @error('company_id')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                    </div>
                    
                    <hr>
                    <div class="mb-3 row">
                      <div class="col-md-9 offset-md-3">
                          <button type="submit" class="btn btn-primary">Save</button>
                          <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                      </div>
                    </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection
