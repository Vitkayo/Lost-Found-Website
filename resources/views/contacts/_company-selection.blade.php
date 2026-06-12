<select name="company_id" id="company_id" class="form-select">
    <option value="" selected>All Companies</option>
    @foreach($companies_data as $key => $value)
        <option value="{{ $key }}" @selected((string) old('company_id') === (string) $key)>{{ $value['name'] }}</option>
    @endforeach
</select>
