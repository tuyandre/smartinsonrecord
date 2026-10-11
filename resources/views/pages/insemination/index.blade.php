@extends('layouts.app')

@section('title', 'Cow Insemination · SMART INSEMrecord')
@section('page_title', 'Cow Insemination')
@section('page_sub', 'Record a cow or heifer insemination')

@section('actions')
  <form method="GET" action="{{ route('insemination.index') }}" class="d-flex gap-2 flex-grow-1" style="min-width:0;">
    <div class="input-group">
      <input type="text" class="form-control" placeholder="Cow Eartag Number..." name="pattern" value="{{ $pattern }}" aria-label="Cow Eartag Number">
      <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Search</button>
    </div>
  </form>
@endsection

@push('head')
<style>
  .insem-sticky { position: sticky; top: 1rem; }
  .obs-item { padding: .5rem .75rem .5rem 2.25rem; border: 1px solid var(--bs-border-color); border-radius: .5rem; }
  .obs-item:has(input:checked) { background: var(--bs-primary-bg-subtle); border-color: var(--bs-primary); }
  .obs-item .form-check-label { width: 100%; cursor: pointer; }
  .report-section-title { background: var(--bs-light); border: 1px solid var(--bs-border-color); border-radius: .375rem; padding: .4rem .75rem; font-weight: 600; margin-bottom: .5rem; }
  @media print {
    body * { visibility: hidden; }
    #reportModal, #reportModal * { visibility: visible; }
    #reportModal { position: absolute; left: 0; top: 0; width: 100%; overflow: visible !important; }
    #reportModal .modal-dialog { margin: 0; max-width: 100%; }
    #reportModal .modal-content { border: 0; }
    #reportModal .no-print { display: none !important; }
  }
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-gender-female"></i></div>
      <div>
        <div class="value">{{ $totalInseminated }}</div>
        <div class="label">Inseminated Cows</div>
        <div class="hint">Entire Number</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon green"><i class="bi bi-person-badge"></i></div>
      <div>
        <div class="value">{{ $totalVets }}</div>
        <div class="label">Veternarians</div>
        <div class="hint">+1(New)</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon amber"><i class="bi bi-droplet"></i></div>
      <div>
        <div class="value">{{ $bullCount }}</div>
        <div class="label">Semens</div>
        <div class="hint text-truncate">From {{ implode(', ', $bullNames) }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="icon violet"><i class="bi bi-gender-male"></i></div>
      <div>
        <div class="value">{{ $bullCount }}</div>
        <div class="label">Bulls</div>
        <div class="hint text-truncate">Includes {{ implode(', ', $bullNames) }}</div>
      </div>
    </div>
  </div>
</div>

<form method="POST" action="{{ route('insemination.store') }}">
  @csrf
  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-1"><i class="bi bi-clipboard2-pulse text-primary me-2"></i>Cow Insemination Information</h5>
          <p class="text-muted small mb-3">Provide below information related to the cow or Heifer insemination</p>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="eartag" class="form-label">EarTag Number</label>
              <input type="number" class="form-control @error('eartag') is-invalid @enderror" name="eartag" id="eartag" value="{{ old('eartag') }}" placeholder="EarTag Number/Iherena" required />
              @error('eartag') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6" id="pre_calv_wrap">
              <label for="pre_calv" class="form-label" id="pre_calv_label">Date of previous calving</label>
              <input type="date" class="form-control @error('pre_calv') is-invalid @enderror" name="pre_calv" id="pre_calv" value="{{ old('pre_calv') }}" placeholder="Previous Calv/Igihe iherukira kubyariraho" />
              @error('pre_calv') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="heat_obs" class="form-label">Date of heat Observation</label>
              <input type="date" class="form-control @error('heat_obs') is-invalid @enderror" name="heat_obs" id="heat_obs" value="{{ old('heat_obs') }}" placeholder="Heat Observation" required />
              @error('heat_obs') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="hours_heat" class="form-label">Hours heat Observation</label>
              <input type="time" class="form-control @error('hours_heat') is-invalid @enderror" name="hours_heat" id="hours_heat" value="{{ old('hours_heat') }}" placeholder="Heat Observation" required />
              @error('hours_heat') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="insem_date" class="form-label">Insemination Date</label>
              <input type="text" class="form-control" id="insem_date" value="{{ now()->format('Y-m-d h:i:s') }}" placeholder="Insemination Date" readonly />
            </div>
            <div class="col-12 col-md-6">
              <label for="mating" class="form-label">AI Service Times</label>
              <select class="form-select @error('mating') is-invalid @enderror" name="mating" id="mating">
                @foreach ([1, 2, 3] as $n)
                  <option value="{{ $n }}" @selected((string) old('mating', 1) === (string) $n)>{{ $n }}</option>
                @endforeach
              </select>
              @error('mating') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="semen" class="form-label">Bull used</label>
              <select class="form-select @error('bull_used') is-invalid @enderror" name="bull_used" id="semen">
                <option value="">------Select Bull----------</option>
                @foreach ($bulls as $bull)
                  <option value="{{ $bull['Eartag'] ?? '' }}">{{ ($bull['Eartag'] ?? '').' ('.($bull['Name'] ?? '').')' }}</option>
                @endforeach
              </select>
              @error('bull_used') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="available_semens" class="form-label">Semen Info</label>
              <select class="form-select @error('semen') is-invalid @enderror" name="semen" id="available_semens" required>
                <option value="">------Available Semen----------</option>
              </select>
              @error('semen') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-1"><i class="bi bi-list-check text-primary me-2"></i>Observation</h5>
          <p class="text-muted small mb-3">Provide observed information</p>
          <div class="row g-2">
            @foreach ($observations as $observation)
              <div class="col-12 col-md-6">
                <div class="form-check obs-item">
                  <input class="form-check-input" type="checkbox" name="obsr[]" id="obsr_{{ $loop->index }}" value="{{ $observation }}" @checked(in_array($observation, old('obsr', []), true))>
                  <label class="form-check-label" for="obsr_{{ $loop->index }}">{{ $observation }}{{ in_array($observation, ['Easy Cervix Penetration', 'Difficult Cervix Penetration']) ? '?' : '' }}</label>
                </div>
              </div>
            @endforeach
          </div>
          @error('obsr') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

          <div class="d-grid d-sm-flex justify-content-sm-end mt-4">
            <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-check2-circle me-1"></i>Save Information</button>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card insem-sticky">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="bi bi-person-vcard text-primary me-2"></i>Inseminator Information</h5>
          <div class="mb-3">
            <label class="form-label">Veterinary Name</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly />
          </div>
          <div class="mb-3">
            <label class="form-label">Veterinary RCVD ID</label>
            <input type="text" class="form-control" value="{{ auth()->user()->rcvd_id }}" readonly />
          </div>
          <div>
            <label class="form-label">Veterinary Email</label>
            <input type="text" class="form-control" value="{{ auth()->user()->email }}" readonly />
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

@if ($report)
  <div class="modal fade print-container" id="reportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header no-print">
          <h5 class="modal-title" id="reportModalLabel"><i class="bi bi-file-earmark-text me-2"></i>Report</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center">
            <img src="{{ asset('admin-assets/images/Coat.png') }}" alt="Logo" width="30" height="30">
            <img src="{{ asset('admin-assets/images/RAB_Logo.png') }}" alt="Logo" height="30">
          </div>
          <hr>
          <h2 class="text-center h3">Insemination Report Form</h2>
          <p class="text-center">on:&nbsp;&nbsp;{{ date('d F Y') }}</p>

          <div class="report-section-title">Cow or Heiffer Information</div>
          <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Ear Tag Number:</strong>&nbsp;{{ $report['eartag'] }}</li>
            <li class="list-group-item"><strong>Heat Observation Date:</strong>&nbsp;{{ $report['heat_obs'] }}</li>
            <li class="list-group-item"><strong>Hours of Heat Observation:</strong>&nbsp;{{ $report['heat_hours'] }}</li>
            <li class="list-group-item"><strong>Insemination Date:</strong>&nbsp;{{ $report['insem_date'] }}</li>
          </ul>
          <div class="report-section-title">Heat Observation Information</div>
          <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Bull Used(Semen):</strong>&nbsp;{{ $report['bull_used'] }}</li>
            <li class="list-group-item"><strong>Mating Time:</strong>&nbsp;{{ $report['mating'] }}</li>
            <li class="list-group-item"><strong>Observation:</strong>&nbsp;{{ $report['observation'] }}</li>
          </ul>
          <div class="report-section-title">Veternary Information</div>
          <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Veterinary Name:</strong>&nbsp;{{ $report['vet_name'] }}</li>
            <li class="list-group-item"><strong>Veterinary RCVD ID:</strong>&nbsp;{{ $report['vet_rcvd'] }}</li>
            <li class="list-group-item"><strong>Veterinary Email:</strong>&nbsp;{{ $report['vet_email'] }}</li>
          </ul>
          <p class="mb-1">Done at byumba_hospital</p>
          <p class="mb-3">On {{ date('Y-m-d') }}</p>
          <p class="mb-4">_________________________</p>
        </div>
        <div class="modal-footer no-print">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="window.print();"><i class="bi bi-printer me-1"></i>Print Report</button>
        </div>
      </div>
    </div>
  </div>
@endif

@if ($searchResults !== null && $searchResults->isNotEmpty())
  <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="searchModalLabel"><i class="bi bi-search me-2"></i>Cow Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @php
            $columns = [
              'Name' => 'Name', 'Eartagno' => 'Ear Tag', 'Gender' => 'Gender', 'Breed' => 'Breed',
              'Cross_breed1' => 'Cross Breed 1', 'Cross_breed2' => 'Cross Breed 2',
              'Breed_Percentage' => 'Breed Percentage', 'Dob' => 'DOB', 'Number_Calving' => 'Number of Calving',
              'Owner_names' => 'Owner Names', 'National_Id' => 'National ID', 'Phone' => 'Phone',
              'District' => 'District', 'Sector' => 'Sector', 'Cell' => 'Cell', 'Village' => 'Village',
            ];
            foreach ([1, 2, 3] as $n) {
              $columns['Calf_Eartagno_'.$n] = 'Calf Ear Tag '.$n;
              $columns['Breed_'.$n] = 'Breed '.$n;
              $columns['Dob_'.$n] = 'DOB '.$n;
              $columns['Weight_at_Birth_'.$n] = 'Weight at Birth '.$n;
              $columns['Birth_Times_'.$n] = 'Birth Times '.$n;
              $columns['Observation_'.$n] = 'Observation '.$n;
              $columns['Disability_'.$n] = 'Disability '.$n;
            }
          @endphp
          <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle text-nowrap sir-table" data-title="Cow Details" data-buttons="false">
              <thead class="table-light">
                <tr>
                  @foreach ($columns as $label)
                    <th>{{ $label }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach ($searchResults as $row)
                  <tr>
                    @foreach ($columns as $key => $label)
                      <td>{{ $row->{$key} }}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endif
@endsection

@push('scripts')
<script>
  (function () {
    var bulls = @json($bulls);

    function checkMatingTime() {
      var hide = ['2', '3'].indexOf($('#mating').val()) !== -1;
      $('#pre_calv_wrap').toggleClass('d-none', hide);
    }
    $('#mating').on('change', checkMatingTime);
    checkMatingTime();

    $('#semen').on('change', function () {
      var eartag = $(this).val();
      var $semens = $('#available_semens');
      $semens.empty().append('<option value="">------Available Semen----------</option>');
      var bull = bulls.find(function (b) { return String(b.Eartag) === String(eartag); });
      if (!bull) { return; }
      for (var i = 1; i <= 5; i++) {
        var code = bull['Semen_code_' + i] || bull['semen_code_' + i];
        if (code) {
          $semens.append($('<option></option>').val(code).text('Semen Code ' + code));
        }
      }
    });

    @if ($report)
      new bootstrap.Modal(document.getElementById('reportModal')).show();
    @endif
    @if ($searchResults !== null && $searchResults->isNotEmpty())
      new bootstrap.Modal(document.getElementById('searchModal')).show();
    @elseif ($searchResults !== null)
      swal({ title: 'Error!', text: 'Cow Eartagno Information Not Found', icon: 'warning' });
    @endif
  })();
</script>
@endpush
