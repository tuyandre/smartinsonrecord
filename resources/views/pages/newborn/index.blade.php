@extends('layouts.app')

@section('title', 'New Born · SMART INSEMrecord')
@section('page_title', 'New Born Calf')
@section('page_sub', 'Register a calf at birth. It is added to the cows or bulls list automatically.')
@section('actions')
  @can('calves.view')
    <a href="{{ route('records.calves') }}" class="btn btn-outline-primary"><i class="bi bi-list-ul me-1"></i> Calf List</a>
  @endcan
@endsection

@push('head')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<style>
  .chart-box { position: relative; height: 260px; }
  .reg-card-title { font-weight: 700; display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
  .reg-card-title i { color: var(--bs-primary); }
  .sire-box { background: #f8faff; border: 1px dashed var(--bs-border-color); border-radius: .6rem; padding: .6rem .8rem; }
  #exampleModal .report-list .list-group-item { display: flex; flex-wrap: wrap; gap: .25rem .5rem; }
  @media print {
    @page { size: A4 portrait; margin: 12mm; }
    html, body { height: auto !important; overflow: visible !important; background: #fff !important; }
    .sir-sidebar, .sir-topbar, .sir-footer, .modal-backdrop { display: none !important; }
    .sir-main { margin: 0 !important; min-height: 0 !important; }
    main.sir-content { padding: 0 !important; }
    main.sir-content > *:not(#exampleModal) { display: none !important; }
    #exampleModal { position: static !important; display: block !important; opacity: 1 !important; overflow: visible !important; }
    #exampleModal .modal-dialog { margin: 0 !important; max-width: 100% !important; max-height: none !important; transform: none !important; }
    #exampleModal .modal-content { border: 0 !important; box-shadow: none !important; max-height: none !important; overflow: visible !important; }
    #exampleModal .modal-body { overflow: visible !important; padding: 0 !important; font-size: 10.5pt; }
    #exampleModal .no-print { display: none !important; }
    #exampleModal h2 { font-size: 15pt; }
    #exampleModal .my-4, #exampleModal .mt-5 { margin-top: .5rem !important; margin-bottom: .5rem !important; }
    #exampleModal .list-group-item { padding: .2rem .6rem; }
    #exampleModal .modal-body > * { break-inside: avoid; }
  }
</style>
@endpush

@section('content')
@if ($errors->any())
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
@endif

<form method="POST" action="{{ route('newborn.store') }}" novalidate>
  @csrf
  <div class="row g-3">
    {{-- Mother and sire --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-heart-pulse"></i> Mother &amp; Sire</div>
          <p class="text-muted small">Search the inseminated cow. Sire, birth times and free calf numbers fill in automatically.</p>
          <div class="row g-3">
            <div class="col-12">
              <label for="M_tagno" class="form-label">Mother (inseminated cow)</label>
              <select class="form-select @error('M_tagno') is-invalid @enderror" name="M_tagno" id="M_tagno" data-no-select2 data-url="{{ route('newborn.mothers') }}" required>
                @if ($selectedMother)
                  <option value="{{ $selectedMother->Eartagno }}" selected>{{ $selectedMother->Eartagno }} · {{ $selectedMother->Name }}</option>
                @endif
              </select>
              @error('M_tagno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
              <div class="sire-box">
                <div class="small text-muted">Sire (bull used in the last insemination)</div>
                <div class="fw-semibold" id="sireText">—</div>
                <input type="hidden" name="breeds" id="breeds" value="{{ old('breeds') }}">
              </div>
            </div>
            <div class="col-12 d-none" id="relationAlert"><div class="alert alert-danger mb-0" role="alert"></div></div>
            <div class="col-12 col-md-6">
              <label for="matingTimes" class="form-label">Birth Times</label>
              <input type="number" class="form-control" name="birth_times" id="matingTimes" value="{{ old('birth_times', 0) }}" readonly>
            </div>
            <div class="col-12 col-md-6">
              <label for="calf_number" class="form-label">Calf Number</label>
              <select class="form-select @error('calf_number') is-invalid @enderror" name="calf_number" id="calf_number" required>
                @foreach ([1, 2, 3] as $n)
                  <option value="{{ $n }}" @selected((string) old('calf_number', 1) === (string) $n)>{{ $n }}</option>
                @endforeach
              </select>
              @error('calf_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Calf --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-info-circle"></i> Calf Information</div>
          <p class="text-muted small">The calf is registered with its mother's owner and location.</p>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="tagno" class="form-label">EarTag Number</label>
              <input type="text" maxlength="30" pattern="[A-Za-z0-9]+" title="Letters and digits only" class="form-control @error('tagno') is-invalid @enderror" id="tagno" name="tagno" value="{{ old('tagno') }}" placeholder="New Born EarTag number" required>
              @error('tagno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="calf_name" class="form-label">Name <span class="text-muted small">(optional)</span></label>
              <input type="text" maxlength="20" class="form-control @error('calf_name') is-invalid @enderror" id="calf_name" name="calf_name" value="{{ old('calf_name') }}">
              @error('calf_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label d-block">Sex</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sex" id="sexFemale" value="Female" @checked(old('sex', 'Female') === 'Female')>
                <label class="form-check-label" for="sexFemale">Female (cow)</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sex" id="sexMale" value="Male" @checked(old('sex') === 'Male')>
                <label class="form-check-label" for="sexMale">Male (bull)</label>
              </div>
              @error('sex') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="dob" class="form-label">Date of Birth</label>
              <input type="date" max="{{ date('Y-m-d') }}" class="form-control @error('dob') is-invalid @enderror" id="dob" name="dob" value="{{ old('dob') }}" required>
              @error('dob') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="weights" class="form-label">Weight at Birth</label>
              <div class="input-group">
                <input type="number" min="0" class="form-control @error('weights') is-invalid @enderror" id="weights" name="weights" value="{{ old('weights') }}" placeholder="...">
                <span class="input-group-text">kg</span>
              </div>
              @error('weights') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Observation --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-eye"></i> Observation</div>
          <div class="d-flex flex-wrap gap-4 my-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="observation" id="obsNormal" value="Normal" @checked(old('observation', 'Normal') === 'Normal')>
              <label class="form-check-label" for="obsNormal">Normal</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="observation" id="obsDisability" value="Disability" @checked(old('observation') === 'Disability')>
              <label class="form-check-label" for="obsDisability">Disability</label>
            </div>
          </div>
          @error('observation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          <div id="disabilityBlock" class="mt-2" style="display:none;">
            <label for="disabilityTextarea" class="form-label">Specify Disability</label>
            <textarea name="disability_desc" rows="3" maxlength="70" placeholder="Describe disability" class="form-control @error('disability_desc') is-invalid @enderror" id="disabilityTextarea">{{ old('disability_desc') }}</textarea>
            @error('disability_desc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-bar-chart"></i> Annual Insemination Details</div>
          <div class="chart-box"><canvas id="inseminationChart"></canvas></div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <button type="submit" id="saveBtn" class="btn btn-primary btn-lg w-100 w-md-auto"><i class="bi bi-check2-circle me-1"></i>Register Calf</button>
    </div>
  </div>
</form>

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
            <h2 class="h3 fw-bold">New Born Report Form</h2>
            <div>on:&nbsp;&nbsp; {{ date('d F Y') }}</div>
          </div>

          <div class="fw-bold bg-light border rounded-top px-3 py-2">Newborn Information</div>
          <ul class="list-group list-group-flush report-list border border-top-0 mb-3">
            <li class="list-group-item"><strong>Ear Tag Number:</strong> {{ $report['calf_tagno'] }}</li>
            <li class="list-group-item"><strong>Sex:</strong> {{ $report['sex'] }}</li>
            <li class="list-group-item"><strong>Mother Ear Tag Number:</strong> {{ $report['mother_tagno'] }}</li>
            <li class="list-group-item"><strong>Sire (bull used):</strong> {{ $report['breed'] ?: '—' }}</li>
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
          <button type="button" class="btn btn-primary mt-4 no-print" id="printReport"><i class="bi bi-printer me-1"></i>Print Report</button>
        </div>
      </div>
    </div>
  </div>
@endif
@endsection

@push('scripts')
<script>
  (function () {
    function toggleDisabilityField() {
      var checked = document.querySelector('input[name="observation"]:checked');
      document.getElementById('disabilityBlock').style.display = (checked && checked.value === 'Disability') ? 'block' : 'none';
    }
    document.querySelectorAll('input[name="observation"]').forEach(function (el) { el.addEventListener('change', toggleDisabilityField); });
    toggleDisabilityField();

    // Mother: search select over the inseminated cows.
    $('#M_tagno').select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: 'Search by ear tag, name or owner',
      allowClear: true,
      ajax: {
        url: $('#M_tagno').data('url'),
        dataType: 'json',
        delay: 250,
        data: function (params) { return { q: params.term || '' }; },
        processResults: function (data) { return data; }
      }
    });

    function loadMother() {
      var tag = $('#M_tagno').val();
      var alertBox = $('#relationAlert');
      alertBox.addClass('d-none');
      $('#saveBtn').prop('disabled', false);
      if (!tag) {
        $('#sireText').text('—');
        $('#breeds').val('');
        $('#matingTimes').val(0);
        return;
      }
      $.getJSON(@json(route('newborn.insemination')), { mother_tag: tag }, function (d) {
        $('#sireText').text(d.bull ? d.bull + (d.bull_name ? ' (' + d.bull_name + ')' : '') : 'No bull recorded');
        $('#breeds').val(d.bull || '');
        $('#matingTimes').val(d.mating_time || 0);

        // Only free calf numbers can be chosen; pick the first one.
        var select = $('#calf_number');
        select.find('option').each(function () { $(this).prop('disabled', d.free_numbers.indexOf(parseInt(this.value, 10)) === -1); });
        if (d.free_numbers.length) { select.val(String(d.free_numbers[0])); }

        if (!d.free_numbers.length) {
          alertBox.removeClass('d-none').find('.alert').text('All three calf numbers of this cow are already recorded.');
          $('#saveBtn').prop('disabled', true);
        } else if (d.related) {
          alertBox.removeClass('d-none').find('.alert').html('<i class="bi bi-exclamation-octagon me-1"></i><strong>Not allowed.</strong> ' + $('<div>').text(d.message).html());
          $('#saveBtn').prop('disabled', true);
        }
      });
    }
    $('#M_tagno').on('change', loadMother);
    if ($('#M_tagno').val()) { loadMother(); }

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
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    @if ($report)
      var reportModal = document.getElementById('exampleModal');
      var listUrl = @json(auth()->user()->can('calves.view') ? route('records.calves') : route('newborn.index'));
      var leaving = false;
      function leave() { if (!leaving) { leaving = true; window.location.href = listUrl; } }

      $('#printReport').on('click', function () { window.print(); });
      window.addEventListener('afterprint', leave);           // printed or cancelled: report is done
      reportModal.addEventListener('hidden.bs.modal', leave);  // closed without printing
      new bootstrap.Modal(reportModal).show();
    @endif
  })();
</script>
@endpush
