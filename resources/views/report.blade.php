@extends('layouts.main')

@section('title', 'Report an Item')

@section('content')
<div class="cf-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>Report an Item</h1>
            <p>Share the key details so the campus community can help return the item quickly.</p>
        </div>
    </section>

    <section class="cf-container cf-form-shell">
        <form method="post" action="{{ route('report.store') }}" enctype="multipart/form-data" class="cf-page-form">
            @csrf
            <label>
                <span>Item Name</span>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex: Exam ticket, Card ID, blue umbrella" required>
                @error('title')<small class="cf-error">{{ $message }}</small>@enderror
            </label>

            <div class="cf-form-grid">
                <label>
                    <span>Status</span>
                    <select name="status">
                        <option value="lost" @selected(old('status', 'lost') === 'lost')>Lost</option>
                        <option value="found" @selected(old('status') === 'found')>Found</option>
                    </select>
                </label>
                <label>
                    <span>Category</span>
                    <select name="category" required>
                        @foreach(config('lostfound.categories') as $slug => $label)
                            <option value="{{ $slug }}" @selected(old('category', 'other') === $slug)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<small class="cf-error">{{ $message }}</small>@enderror
                </label>
            </div>

            <label>
                <span>Date &amp; Time</span>
                <input type="datetime-local" name="created_at" value="{{ old('created_at', now()->format('Y-m-d\TH:i')) }}" required>
            </label>

            <label>
                <span>Location</span>
                <input type="text" name="location" value="{{ old('location') }}" placeholder="Ex: Building A, 2nd floor, near Room 203" required>
                <small class="cf-field-help">Use the most specific RUPP place you remember.</small>
            </label>

            <label>
                <span>Contact Info</span>
                <input type="text" name="contact_info" value="{{ old('contact_info') }}" placeholder="Phone or Telegram" required>
            </label>

            <label>
                <span>Description</span>
                <textarea name="description" rows="4" placeholder="Color, brand, unique marks, or where it was last seen...">{{ old('description') }}</textarea>
                <small class="cf-field-help">Mention color, name, ticket detail, card type, sticker, or anything unique.</small>
            </label>

            <label>
                <span>Attach Photo</span>
                <input type="file" name="image" accept="image/*">
            </label>

            <button type="submit" class="cf-btn cf-btn-primary">Submit Report</button>
        </form>
    </section>
</div>
@endsection
