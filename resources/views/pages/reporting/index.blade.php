@extends('layouts.app')

@section('title', 'Reporting · SMART INSEMrecord')
@section('page_title', 'Reporting')
@section('page_sub', 'Search records and print official reports')

@push('head')
  <style>
    .report-sheet { background: #fff; border: 1px solid var(--bs-border-color); border-radius: .75rem; padding: 1.25rem; }
    .report-sheet table { font-size: .85rem; }
    .report-sheet th { white-space: nowrap; }
    @media print {
      .page-head { display: none !important; }
      .report-sheet { border: 0; padding: 0; }
      .report-sheet .table-responsive { overflow: visible !important; }
      .report-sheet table { font-size: 8pt; }
      .report-sheet th { white-space: normal; }
      .report-sheet .dt-search, .report-sheet .dt-length, .report-sheet .dt-info, .report-sheet .dt-paging,
      .report-sheet .dt-buttons, .report-sheet .sir-filters { display: none !important; }
      .report-sheet table.dataTable thead > tr > th::before,
      .report-sheet table.dataTable thead > tr > th::after,
      .report-sheet table.dataTable thead .dt-column-order { display: none !important; }
      .report-sheet .dt-container, .report-sheet .dt-layout-table, .report-sheet .dt-layout-cell { overflow: visible !important; width: 100% !important; }
    }
  </style>
@endpush

@section('actions')
  <form method="GET" action="{{ route('reporting.index') }}" class="no-print">
    <button type="submit" name="check_semen" value="Semen Info" class="btn btn-primary">
      <i class="bi bi-droplet-half me-1"></i>Semen Info
    </button>
  </form>
@endsection

@section('content')
<div class="row g-3 mb-4 no-print">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card stat-card h-100">
      <div class="icon"><i class="bi bi-heart-pulse"></i></div>
      <div>
        <div class="value">{{ $insemTotal }}</div>
        <div class="label fw-medium">Inseminated Cows</div>
        <div class="hint">Entire Number</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card stat-card h-100">
      <div class="icon green"><i class="bi bi-person-badge"></i></div>
      <div>
        <div class="value">{{ $vetTotal }}</div>
        <div class="label fw-medium">Veternarians</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card stat-card h-100">
      <div class="icon amber"><i class="bi bi-droplet"></i></div>
      <div>
        <div class="value">{{ $bullCount }}</div>
        <div class="label fw-medium">Semens</div>
        <div class="hint">From {{ implode(', ', $bullNames) }}</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card stat-card h-100">
      <div class="icon violet"><i class="bi bi-gender-male"></i></div>
      <div>
        <div class="value">{{ $bullCount }}</div>
        <div class="label fw-medium">Bulls</div>
        <div class="hint">Includes {{ implode(', ', $bullNames) }}</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4 no-print">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-3"><i class="bi bi-search me-2 text-primary"></i>Search Semen</h5>
        <form method="GET" action="{{ route('reporting.index') }}">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search Semen..." name="semen_pattern" value="{{ request('semen_pattern') }}">
            <button type="submit" name="search_semen" value="Search" class="btn btn-primary">
              <i class="bi bi-search me-1"></i>Search
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-1"><i class="bi bi-calendar2-range me-2 text-primary"></i>Find Report Timeline</h5>
        <p class="text-muted small mb-3">Register new born since birth</p>
        <form action="{{ route('reporting.index') }}" method="GET">
          <div class="row g-2">
            <div class="col-12 col-md-5">
              <label for="searchBy" class="form-label">Search by</label>
              <select class="form-select" id="searchBy" name="searchBy">
                @foreach ($searchOptions as $value => $label)
                  <option value="{{ $value }}" @selected($searchBy === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-7">
              <label for="pattern" class="form-label">Search term</label>
              <div class="input-group">
                <input type="text" class="form-control" id="pattern" name="pattern" value="{{ request('pattern') }}" placeholder="Enter search term" required>
                <button type="submit" name="search" value="Find" class="btn btn-primary">
                  <i class="bi bi-funnel me-1"></i>Find
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@if ($semenSearched)
  <div class="print-container mb-4">
    @if (! empty($semen))
      @include('pages.reporting._semen')
    @else
      <div class="empty-state no-print">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>No information found
      </div>
    @endif
  </div>
@endif

@if ($report)
  <div class="print-container mb-4">
    @if ($report['rows']->isNotEmpty())
      @include('pages.reporting._table')
    @elseif ($report['notFound'])
      @if ($searchBy === 'ins_status')
        <div class="alert alert-warning text-center no-print">{{ $report['notFound'] }}</div>
      @else
        <div class="empty-state no-print">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>{{ $report['notFound'] }}
        </div>
      @endif
    @endif
  </div>
@endif
@endsection
