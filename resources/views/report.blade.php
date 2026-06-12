@extends('layouts.main')

@section('title', 'Report an Item')

@section('content')
@php
    $editing = isset($editItem);
    $value = fn (string $field, $default = '') => old($field, $editing ? ($editItem->{$field} ?? $default) : $default);
@endphp
<div class="cf-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>{{ $editing ? 'Edit Report' : 'Report an Item' }}</h1>
            <p>{{ $editing ? 'Update the report details while keeping private verification information secure.' : 'Share the key details so the campus community can help return the item quickly.' }}</p>
        </div>
    </section>

    <section class="cf-container cf-form-shell">
        <form method="post" action="{{ $editing ? route('report.update', $editItem) : route('report.store') }}" enctype="multipart/form-data" class="cf-page-form">
            @csrf
            @if($editing) @method('PUT') @endif
            <label>
                <span>Item Name</span>
                <input type="text" name="title" value="{{ $value('title') }}" placeholder="Ex: Exam ticket, Card ID, blue umbrella" required>
                @error('title')<small class="cf-error">{{ $message }}</small>@enderror
            </label>

            <div class="cf-form-grid">
                <label>
                    <span>Status</span>
                    <select name="status">
                        <option value="lost" @selected($value('status', 'lost') === 'lost')>Lost</option>
                        <option value="found" @selected($value('status') === 'found')>Found</option>
                    </select>
                </label>
                <label>
                    <span>Category</span>
                    <select name="category" required>
                        @foreach(config('lostfound.categories') as $slug => $label)
                            <option value="{{ $slug }}" @selected($value('category', 'other') === $slug)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<small class="cf-error">{{ $message }}</small>@enderror
                </label>
            </div>

            <label>
                <span>Date &amp; Time</span>
                <input type="datetime-local" name="created_at" value="{{ old('created_at', $editing ? $editItem->reported_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
            </label>

            <label>
                <span>Location</span>
                <input type="text" name="location" value="{{ $value('location') }}" placeholder="Ex: Building A, 2nd floor, near Room 203" required>
                <small class="cf-field-help">Use the most specific campus location you remember.</small>
            </label>

            <label>
                <span>Contact Info</span>
                <input type="text" name="contact_info" value="{{ $value('contact_info', auth()->user()->phone ?? '') }}" placeholder="Phone or Telegram" required>
            </label>

            <label>
                <span>Description</span>
                <textarea name="description" rows="4" placeholder="Color, brand, unique marks, or where it was last seen...">{{ $value('description') }}</textarea>
                <small class="cf-field-help">Mention color, name, ticket detail, card type, sticker, or anything unique.</small>
            </label>

            <div class="cf-verification-fields">
                <label>
                    <span>Ownership Verification Question</span>
                    <input type="text" name="verification_question" value="{{ $value('verification_question') }}" placeholder="Example: What image is on the lock screen?">
                    <small class="cf-field-help">Required for found items. Ask something only the owner should know.</small>
                    @error('verification_question')<small class="cf-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>Private Answer {{ $editing ? '(leave blank to keep current answer)' : '' }}</span>
                    <input type="text" name="verification_answer" value="" placeholder="This answer is stored securely">
                </label>
                <label>
                    <span>Hidden Identifying Details <small>optional</small></span>
                    <textarea name="hidden_details" rows="3" placeholder="Details not shown publicly, used when reviewing claims">{{ $value('hidden_details') }}</textarea>
                </label>
            </div>

            <label>
                <span>Attach Photo</span>
                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <small class="cf-field-help">Images are automatically resized and compressed.</small>
            </label>

            <button type="submit" class="cf-btn cf-btn-primary">{{ $editing ? 'Save Changes' : 'Submit Report' }}</button>
        </form>
    </section>
</div>
@endsection
