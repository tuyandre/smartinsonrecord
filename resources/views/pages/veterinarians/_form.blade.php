{{-- Veterinarian fields (register + edit). Optional $vet when editing. --}}
@php
  $vet = $vet ?? null;
  $fieldError = fn ($f) => $errors->has($f) ? 'is-invalid' : '';
  $val = fn ($field, $default = '') => old($field, $vet ? ($field === 'date_of_birth' ? $vet->date_of_birth?->format('Y-m-d') : $vet->$field) : $default);
@endphp

<div class="card">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-12 col-md-4">
        <label for="rcvd_id" class="form-label">RCVD ID</label>
        <input type="text" maxlength="30" class="form-control {{ $fieldError('rcvd_id') }}" id="rcvd_id" name="rcvd_id" value="{{ $val('rcvd_id') }}" required>
        @error('rcvd_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>
      <div class="col-12 col-md-4">
        <label for="national_id" class="form-label">National ID</label>
        <input type="text" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" title="Exactly 16 digits" placeholder="16 digits" class="form-control {{ $fieldError('national_id') }}" id="national_id" name="national_id" value="{{ $val('national_id') }}">
        @error('national_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>
      <div class="col-12 col-md-4">
        <label for="date_of_birth" class="form-label">Date of birth</label>
        <input type="date" max="{{ date('Y-m-d') }}" class="form-control {{ $fieldError('date_of_birth') }}" id="date_of_birth" name="date_of_birth" value="{{ $val('date_of_birth') }}">
        @error('date_of_birth') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>
      <div class="col-12">
        <label for="names" class="form-label">Names</label>
        <input type="text" maxlength="120" class="form-control {{ $fieldError('names') }}" id="names" name="names" value="{{ $val('names') }}" required>
        @error('names') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>
      <div class="col-12 col-md-6">
        <label for="district" class="form-label">District</label>
        <input type="text" maxlength="60" class="form-control {{ $fieldError('district') }}" id="district" name="district" value="{{ $val('district') }}">
        @error('district') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>
      <div class="col-12 col-md-6">
        <label for="sector" class="form-label">Sector</label>
        <input type="text" maxlength="60" class="form-control {{ $fieldError('sector') }}" id="sector" name="sector" value="{{ $val('sector') }}">
        @error('sector') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>
    </div>
  </div>
</div>
