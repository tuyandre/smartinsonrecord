@extends('layouts.app')

@section('title', 'Dashboard · SMART INSEMrecord')
@section('page_title', 'Dashboard')
@section('page_sub')<span id="clock" class="fw-medium"></span> · {{ date('l, j F Y') }}@endsection
@section('actions')
  <a href="{{ route('registration.index') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Register Cow</a>
  <a href="{{ route('insemination.index') }}" class="btn btn-outline-primary"><i class="bi bi-heart-pulse me-1"></i>New Insemination</a>
@endsection

@push('head')
  <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
@endpush

@section('content')
  <div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon"><i class="bi bi-heart-pulse"></i></div>
        <div>
          <div class="value">{{ $insemTotal }}</div>
          <div class="label fw-medium">Inseminated Cows</div>
          <div class="hint">Entire number</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon green"><i class="bi bi-person-badge"></i></div>
        <div>
          <div class="value">{{ $vetTotal }}</div>
          <div class="label fw-medium">Veternarians</div>
          <div class="hint">Registered users</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon amber"><i class="bi bi-droplet-half"></i></div>
        <div class="overflow-hidden">
          <div class="value">{{ $bullCount }}</div>
          <div class="label fw-medium">Semens</div>
          <div class="hint text-truncate" title="{{ implode(', ', $bullNames) }}">{{ $bullNames ? 'From '.implode(', ', $bullNames) : 'No data available' }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon violet"><i class="bi bi-gem"></i></div>
        <div class="overflow-hidden">
          <div class="value">{{ $bullCount }}</div>
          <div class="label fw-medium">Bulls</div>
          <div class="hint text-truncate" title="{{ implode(', ', $bullNames) }}">{{ $bullNames ? 'Includes '.implode(', ', $bullNames) : 'No data available' }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-12 col-lg-7">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6 mb-0">Total Inseminated Cows Observations</h2>
          <p class="text-muted small">Number of observations conducted per insemination</p>
          <div style="position:relative;height:300px"><canvas id="observationsChart"></canvas></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-5">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6 mb-0">Inseminated Cows Per Month</h2>
          <p class="text-muted small">Started collecting data from February 2025</p>
          <div style="position:relative;height:300px"><canvas id="inseminationChart"></canvas></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Latest Inseminations</span>
      <a href="{{ route('records.inseminations') }}" class="btn btn-sm btn-outline-primary">View all</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle sir-table" data-title="Latest Inseminations" data-paging="false" data-buttons="false" data-order='[[3,"desc"]]'>
        <thead>
          <tr><th>Ear Tag</th><th>Cow</th><th>Bull</th><th>Insemination Date</th><th>Veterinary</th></tr>
        </thead>
        <tbody>
          @foreach ($latest as $row)
            <tr>
              <td>{{ $row->Eartagno }}</td>
              <td>{{ $row->cow_name ?? '—' }}</td>
              <td>{{ $row->Bull_Used_1 ?: '—' }}</td>
              <td>{{ $row->Insemination_Date_1 ?: '—' }}</td>
              <td>{{ $row->Veternary_Name1 ?: '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  @if ($searchResults !== null)
    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cow Details · “{{ request('pattern') }}”</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            @if ($searchResults->isEmpty())
              <div class="empty-state"><i class="bi bi-search"></i>Cow Eartagno Information Not Found</div>
            @else
              @php
                $cowFields = [
                  'Name' => 'Name', 'Eartagno' => 'Ear Tag', 'Gender' => 'Gender', 'Breed' => 'Breed',
                  'Cross_breed1' => 'Cross Breed 1', 'Cross_breed2' => 'Cross Breed 2', 'Breed_Percentage' => 'Breed %',
                  'Dob' => 'DOB', 'Number_Calving' => 'Calvings', 'Owner_names' => 'Owner',
                  'National_Id' => 'National ID', 'Phone' => 'Phone', 'District' => 'District', 'Sector' => 'Sector',
                  'Cell' => 'Cell', 'Village' => 'Village',
                ];
              @endphp
              @foreach ($searchResults as $cow)
                <div class="mb-4">
                  <h6 class="text-primary">{{ $cow->Name }} · {{ $cow->Eartagno }}</h6>
                  <div class="row g-2">
                    @foreach ($cowFields as $field => $label)
                      <div class="col-6 col-md-4 col-lg-3">
                        <div class="small text-muted">{{ $label }}</div>
                        <div class="fw-medium">{{ $cow->$field !== '' && $cow->$field !== null ? $cow->$field : '—' }}</div>
                      </div>
                    @endforeach
                  </div>
                  @foreach ([1, 2, 3] as $n)
                    @php $tag = 'Calf_Eartagno_'.$n; @endphp
                    @if (! empty($cow->$tag))
                      <div class="border rounded p-2 mt-2 small">
                        <strong>Calf {{ $n }}:</strong> Tag {{ $cow->$tag }} ·
                        {{ $cow->{'Breed_'.$n} }} · DOB {{ $cow->{'Dob_'.$n} }} · {{ $cow->{'Weight_at_Birth_'.$n} }} kg ·
                        {{ $cow->{'Observation_'.$n} }} {{ $cow->{'Disability_'.$n} }}
                      </div>
                    @endif
                  @endforeach
                </div>
              @endforeach
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
  <script>
    (function tick() {
      var n = new Date(), p = function (v) { return v < 10 ? '0' + v : v; };
      document.getElementById('clock').textContent = p(n.getHours()) + ':' + p(n.getMinutes()) + ':' + p(n.getSeconds());
      setTimeout(tick, 1000);
    })();

    var obsCtx = document.getElementById('observationsChart').getContext('2d');
    var gradient = obsCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, '#1d6fdc');
    gradient.addColorStop(1, '#7c4dff');

    new Chart(obsCtx, {
      type: 'bar',
      data: {
        labels: @json($observationLabels),
        datasets: [{ label: 'Number of Observations', data: @json($observationCounts), backgroundColor: gradient, borderRadius: 8 }]
      },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    new Chart(document.getElementById('inseminationChart'), {
      type: 'bar',
      data: {
        labels: @json($months),
        datasets: [{ label: 'Inseminated Cows', data: @json($monthCounts), backgroundColor: 'rgba(20,198,113,.65)', borderRadius: 6 }]
      },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    @if ($searchResults !== null)
      new bootstrap.Modal(document.getElementById('searchModal')).show();
    @endif
  </script>
@endpush
