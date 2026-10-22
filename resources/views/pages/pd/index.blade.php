@extends('layouts.app')

@section('title', 'PD Info · SMART INSEMrecord')
@section('page_title', 'PD Info')
@section('page_sub')<span id="clock" class="fw-medium"></span> · Pregnancy Diagnosis Test @endsection

@push('head')
  <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
@endpush

@section('content')
  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6 mb-0"><i class="bi bi-clipboard2-pulse text-primary me-1"></i>Cow PD Test</h2>
          <p class="text-muted small">Test once cow Insemination Succeed</p>

          <form method="POST" action="{{ route('pd.lookup') }}" class="mb-3">
            @csrf
            <label for="eartagno" class="form-label">Provide Cow Ear Tag Number</label>
            <div class="input-group">
              <input type="text" class="form-control @error('pd_eartagno') is-invalid @enderror" id="eartagno" name="pd_eartagno" value="{{ old('pd_eartagno') }}" placeholder="Any eartag number /Shaka Ukoresheje Iherena..." />
              <button type="submit" name="search" class="btn btn-primary" value="Test"><i class="bi bi-search me-1"></i>Test</button>
            </div>
            @error('pd_eartagno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </form>

          @if ($candidate)
            <hr>
            <form method="POST" action="{{ route('pd.store') }}">
              @csrf
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label for="insemination_date" class="form-label">Latest Insemination Date</label>
                  <input type="text" class="form-control" id="insemination_date" value="{{ $candidate['insemination_date'] }}" readonly />
                </div>
                <div class="col-12 col-md-6">
                  <label for="pd_eartagno_confirm" class="form-label">Ear Tag Number</label>
                  <input type="text" class="form-control" id="pd_eartagno_confirm" name="eartagno" value="{{ $candidate['eartagno'] }}" readonly />
                  @error('eartagno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                  <label for="status" class="form-label">Choose Status</label>
                  <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    <option value="Conception">Conception</option>
                    <option value="Not Pregnant">Not Pregnant</option>
                  </select>
                  @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 d-grid d-sm-block">
                  <button type="submit" name="update_status" class="btn btn-primary" value="Update Status"><i class="bi bi-check2-circle me-1"></i>Update Status</button>
                </div>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-5">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6 mb-0">Anual Insemination Survey</h2>
          <p class="text-muted small">Started collecting data from February 2019</p>
          <div style="position:relative;height:300px"><canvas id="inseminationChart"></canvas></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function tick() {
      var n = new Date(), p = function (v) { return v < 10 ? '0' + v : v; };
      document.getElementById('clock').textContent = p(n.getHours()) + ':' + p(n.getMinutes()) + ':' + p(n.getSeconds());
      setTimeout(tick, 1000);
    })();

    new Chart(document.getElementById('inseminationChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: @json($months),
        datasets: [{ label: 'Number of Inseminated Cows', data: @json($counts), backgroundColor: 'rgba(20,198,113,.65)', borderRadius: 6 }]
      },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
  </script>
@endpush
