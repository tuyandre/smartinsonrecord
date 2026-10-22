@extends('layouts.app')

@section('title', 'Edit Birth Record · SMART INSEMrecord')
@section('page_title', 'Edit Birth Record')
@section('page_sub', 'Calf '.$calf['tag'].' · calf number '.$calf['no'].' of '.($mother->Name ?? $calf['mother']))
@section('actions')
  <a href="{{ route('records.calves') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
@endsection

@section('content')
  @if ($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
  @endif

  <form method="POST" action="{{ route('calves.update', [$calf['mother'], $calf['no']]) }}" novalidate>
    @csrf
    @method('PUT')
    <div class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-4">
            <label class="form-label">Calf ear tag</label>
            <input type="text" class="form-control" value="{{ $calf['tag'] }}" disabled>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Mother</label>
            <input type="text" class="form-control" value="{{ $calf['mother'] }}{{ $mother ? ' · '.$mother->Name : '' }}" disabled>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Sire (bull used)</label>
            <input type="text" class="form-control" value="{{ $calf['sire'] ?: '—' }}" disabled>
          </div>
          <div class="col-12 col-md-4">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" max="{{ date('Y-m-d') }}" class="form-control @error('dob') is-invalid @enderror" id="dob" name="dob" value="{{ old('dob', $calf['dob']) }}" required>
            @error('dob') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
          <div class="col-12 col-md-4">
            <label for="weights" class="form-label">Weight at Birth (kg)</label>
            <input type="number" min="0" class="form-control @error('weights') is-invalid @enderror" id="weights" name="weights" value="{{ old('weights', $calf['weight']) }}">
            @error('weights') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
          <div class="col-12 col-md-4">
            <label for="birth_times" class="form-label">Birth Times</label>
            <input type="number" min="0" class="form-control @error('birth_times') is-invalid @enderror" id="birth_times" name="birth_times" value="{{ old('birth_times', $calf['birth_times']) }}">
            @error('birth_times') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
          <div class="col-12">
            <label class="form-label d-block">Observation</label>
            @foreach (['Normal', 'Disability'] as $obs)
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="observation" id="obs{{ $obs }}" value="{{ $obs }}" @checked(old('observation', $calf['observation'] ?: 'Normal') === $obs)>
                <label class="form-check-label" for="obs{{ $obs }}">{{ $obs }}</label>
              </div>
            @endforeach
            @error('observation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
          <div class="col-12" id="disabilityBlock" style="display:none;">
            <label for="disabilityTextarea" class="form-label">Specify Disability</label>
            <textarea name="disability_desc" rows="3" maxlength="70" class="form-control @error('disability_desc') is-invalid @enderror" id="disabilityTextarea">{{ old('disability_desc', $calf['disability']) }}</textarea>
            @error('disability_desc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3">
      <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-1"></i> Update Information</button>
      <a href="{{ route('records.calves') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  (function () {
    function toggle() {
      var checked = document.querySelector('input[name="observation"]:checked');
      document.getElementById('disabilityBlock').style.display = (checked && checked.value === 'Disability') ? 'block' : 'none';
    }
    document.querySelectorAll('input[name="observation"]').forEach(function (el) { el.addEventListener('change', toggle); });
    toggle();
  })();
</script>
@endpush
