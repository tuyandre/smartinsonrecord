@extends('layouts.app')

@section('title', 'New Born · SMART INSEMrecord')
@section('page_title', 'New Born Calf')
@section('page_sub', 'Register new born since birth')

@push('head')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<style>
  .chart-box { position: relative; height: 280px; }
  #exampleModal .report-list .list-group-item { display: flex; flex-wrap: wrap; gap: .25rem .5rem; }
  @media print {
    body * { visibility: hidden !important; }
    #exampleModal, #exampleModal * { visibility: visible !important; }
    #exampleModal { position: absolute !important; left: 0; top: 0; width: 100%; display: block !important; overflow: visible !important; }
    #exampleModal .modal-dialog { max-width: 100% !important; margin: 0 !important; }
    #exampleModal .modal-content { border: 0 !important; box-shadow: none !important; }
    #exampleModal .modal-body { overflow: visible !important; }
    #exampleModal .no-print { display: none !important; }
    .modal-backdrop { display: none !important; }
  }
</style>
@endpush

@section('content')
<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link" href="{{ route('registration.index') }}"><i class="bi bi-clipboard2-pulse me-1"></i>Inserminated</a></li>
  <li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ route('newborn.index') }}"><i class="bi bi-heart-pulse me-1"></i>New Born</a></li>
</ul>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
      <div><div class="value">{{ $totalInseminated }}</div><div class="label">Inseminated Cows</div><div class="hint">Entire Number</div></div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon green"><i class="bi bi-person-badge"></i></div>
      <div><div class="value">{{ $totalVets }}</div><div class="label">Veternarians</div><div class="hint">+1(New)</div></div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon amber"><i class="bi bi-droplet"></i></div>
      <div><div class="value">{{ $bullCount }}</div><div class="label">Semens</div><div class="hint">From {{ implode(', ', $bullNames) }}</div></div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon violet"><i class="bi bi-gender-male"></i></div>
      <div><div class="value">{{ $bullCount }}</div><div class="label">Bulls</div><div class="hint">Includes {{ implode(', ', $bullNames) }}</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-8">
    <form method="POST" action="{{ route('newborn.store') }}">
      @csrf
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-1"><i class="bi bi-heart-pulse text-primary me-2"></i>New Born Registration Form</h5>
          <p class="text-muted small mb-3">Register new born since birth</p>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="tagno" class="form-label">Tag Number</label>
              <input type="number" class="form-control @error('tagno') is-invalid @enderror" id="tagno" name="tagno" value="{{ old('tagno') }}" placeholder="New Born EarTag number" required>
              @error('tagno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="M_tagno" class="form-label">Mother Tag Number</label>
              <input type="number" class="form-control @error('M_tagno') is-invalid @enderror" name="M_tagno" id="M_tagno" value="{{ old('M_tagno') }}" placeholder="Mother EarTag number" required>
              @error('M_tagno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              <div class="form-text">Breed and birth time fill in automatically.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Birth details</h5>
          <div id="optionalFields" class="row g-3">
            <div class="col-12 col-md-6">
              <label for="breeds" class="form-label">Bull Used EarTag Number</label>
              <select class="form-select" name="breeds" id="breeds" readonly>
                <option value="">Select Breed</option>
              </select>
              @error('breeds') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="matingTimes" class="form-label">Birth Times</label>
              <select class="form-select" name="birth_times" id="matingTimes" readonly>
                <option value="0">Choose Birth Time</option>
              </select>
              @error('birth_times') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-4">
              <label for="dob" class="form-label">DOB</label>
              <input type="date" class="form-control @error('dob') is-invalid @enderror" id="dob" name="dob" value="{{ old('dob') }}">
              @error('dob') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-4">
              <label for="weights" class="form-label">Weight at Birth</label>
              <input type="number" class="form-control @error('weights') is-invalid @enderror" id="weights" name="weights" value="{{ old('weights') }}" placeholder="Kgs...">
              @error('weights') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-4">
              <label for="calf_number" class="form-label">Calf Number</label>
              <select class="form-select" name="calf_number" id="calf_number">
                <option value="">----Number of calf-----</option>
                @foreach ([1, 2, 3] as $n)
                  <option value="{{ $n }}" @selected(old('calf_number') == $n)>{{ $n }}</option>
                @endforeach
              </select>
              @error('calf_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="bi bi-eye text-primary me-2"></i>Observation</h5>
          <div class="d-flex flex-wrap gap-4 mb-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="observation" id="obsNormal" value="Normal" @checked(old('observation') === 'Normal')>
              <label class="form-check-label" for="obsNormal">Normal</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="observation" id="obsDisability" value="Disability" @checked(old('observation') === 'Disability')>
              <label class="form-check-label" for="obsDisability">Disability</label>
            </div>
          </div>
          @error('observation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          <div id="disabilityBlock" class="mt-3" style="display:none;">
            <label for="disabilityTextarea" class="form-label" id="disabilityLabel">Specify Disability</label>
            <textarea name="disability_desc" rows="3" placeholder="Describe disability" class="form-control @error('disability_desc') is-invalid @enderror" id="disabilityTextarea">{{ old('disability_desc') }}</textarea>
            @error('disability_desc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      <div class="d-grid d-sm-flex justify-content-sm-end">
        <button type="submit" class="btn btn-primary px-4" name="register" value="Register"><i class="bi bi-check2-circle me-1"></i>Register</button>
      </div>
    </form>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-1"><i class="bi bi-bar-chart text-primary me-2"></i>Anual Insemination Details</h5>
        <p class="text-muted small">Started collecting data from February 2019</p>
        <div class="chart-box"><canvas id="inseminationChart"></canvas></div>
      </div>
    </div>
  </div>
</div>

@if ($report)
  <div class="modal fade print-container" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Report</h5>
          <button type="button" class="btn-close no-print" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('admin-assets/images/Coat.png') }}" alt="Logo" width="30" height="30">
            <img src="{{ asset('admin-assets/images/RAB_Logo.png') }}" alt="Logo" height="30">
          </div>
          <hr>
          <div class="text-center my-4">
            <h2 class="h3 fw-bold">Insemination Report Form</h2>
            <div>on:&nbsp;&nbsp; {{ date('d F Y') }}</div>
          </div>

          <div class="fw-bold bg-light border rounded-top px-3 py-2">Newborn Information</div>
          <ul class="list-group list-group-flush report-list border border-top-0 mb-3">
            <li class="list-group-item"><strong>Ear Tag Number:</strong> {{ $report['calf_tagno'] }}</li>
            <li class="list-group-item"><strong>Mother Ear Tag Number:</strong> {{ $report['mother_tagno'] }}</li>
            <li class="list-group-item"><strong>Breed(Semen) used:</strong> {{ $report['breed'] }}</li>
            <li class="list-group-item"><strong>Date of Birth:</strong> {{ $report['dob'] }}</li>
          </ul>

          <div class="fw-bold bg-light border rounded-top px-3 py-2">Other information</div>
          <ul class="list-group list-group-flush report-list border border-top-0">
            <li class="list-group-item"><strong>Born as calf number:</strong> {{ $report['calf_number'] }}</li>
            <li class="list-group-item"><strong>Weights at birth:</strong> {{ $report['weights'] }}</li>
            <li class="list-group-item"><strong>Born at Time of:</strong> {{ $report['birth_time'] }}</li>
            <li class="list-group-item"><strong>Observation:</strong> {{ $report['observation'] }}</li>
            <li class="list-group-item"><strong>Disability Description if any:</strong> {{ $report['disability'] }}</li>
          </ul>

          <div class="mt-5">
            Done at Huye<br>
            <br>On {{ date('d F Y') }}<br>
            _________________________
          </div>
          <button class="btn btn-primary mt-4 no-print" onclick="window.print();"><i class="bi bi-printer me-1"></i>Print Report</button>
        </div>
      </div>
    </div>
  </div>
@endif
@endsection

@push('scripts')
<script>
  function toggleDisabilityField() {
    var checked = document.querySelector('input[name="observation"]:checked');
    var show = checked && checked.value === 'Disability';
    document.getElementById('disabilityBlock').style.display = show ? 'block' : 'none';
  }
  document.querySelectorAll('input[name="observation"]').forEach(function (el) {
    el.addEventListener('change', toggleDisabilityField);
  });
  toggleDisabilityField();

  function setOption(select, value, fallbackValue, fallbackLabel) {
    select.innerHTML = '';
    var option = document.createElement('option');
    if (value) {
      option.value = value;
      option.textContent = value;
    } else {
      option.value = fallbackValue;
      option.textContent = fallbackLabel;
    }
    select.appendChild(option);
  }

  document.getElementById('M_tagno').addEventListener('input', function () {
    var motherTag = this.value;
    if (!motherTag) {
      return;
    }
    fetch(@json(route('newborn.insemination')) + '?mother_tag=' + encodeURIComponent(motherTag), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        setOption(document.getElementById('breeds'), data.breed, '', 'Select Breed');
        setOption(document.getElementById('matingTimes'), data.mating_time, '0', 'Choose Birth Time');
      });
  });

  document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('inseminationChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: @json($months),
        datasets: [{
          label: 'Number of Inseminated Cows',
          data: @json($counts),
          backgroundColor: 'rgba(13, 110, 253, 0.6)',
          borderColor: 'rgba(13, 110, 253, 1)',
          borderWidth: 1,
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
      }
    });
    @if ($report)
      new bootstrap.Modal(document.getElementById('exampleModal')).show();
    @endif
  });
</script>
@endpush
