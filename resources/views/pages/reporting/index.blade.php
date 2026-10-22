@extends('layouts.app')

@section('title', 'Reporting · SMART INSEMrecord')
@section('page_title', 'Reporting')
@section('page_sub', 'Choose a report, set the filters and print or export it')

@push('head')
  <script>
    window.SIR_LETTERHEAD = {
      lines: ['Huye District', 'Smart InsemRecord', 'Ngoma'],
      logoLeft: @json(asset('admin-assets/images/Coat.png')),
      logoRight: @json(asset('admin-assets/images/RAB_Logo.png'))
    };
  </script>
  <style>
    .report-sheet { background: #fff; border: 1px solid var(--bs-border-color); border-radius: .75rem; padding: 1.25rem; }
    .report-sheet table { font-size: .85rem; }
    .report-sheet th { white-space: nowrap; }
    .report-types .nav-link { display: flex; align-items: center; gap: .4rem; padding: .4rem .85rem; font-size: .9rem; }
    .report-filters .form-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--bs-secondary-color); margin-bottom: .2rem; }
    @media print {
      @page { size: A4 landscape; margin: 10mm; }
      .page-head { display: none !important; }
      .report-sheet { border: 0; padding: 0; }
      .report-sheet .table-responsive, .report-sheet .dt-layout-cell { overflow: visible !important; }
      .report-sheet table { font-size: 7pt; }
      .report-sheet th, .report-sheet td { padding: .15rem .25rem !important; white-space: normal !important; }
      .report-sheet .dt-search, .report-sheet .dt-length, .report-sheet .dt-info, .report-sheet .dt-paging,
      .report-sheet .dt-buttons, .report-sheet .sir-filters { display: none !important; }
      .report-sheet table.dataTable thead > tr > th::before,
      .report-sheet table.dataTable thead > tr > th::after,
      .report-sheet table.dataTable thead .dt-column-order { display: none !important; }
    }
  </style>
@endpush

@section('content')
@php
  $filterBox = fn ($name) => 'data-filter="'.$name.'"';
@endphp

<form method="GET" action="{{ route('reporting.index') }}" id="reportForm" class="card mb-3 no-print">
  <input type="hidden" name="run" value="1">
  <input type="hidden" name="type" id="reportType" value="{{ $type }}">
  <div class="card-body">
    <ul class="nav nav-pills report-types gap-1 mb-3">
      @foreach ($types as $key => [$label, $icon])
        <li class="nav-item">
          <a href="#" class="nav-link {{ $key === $type ? 'active' : '' }}" data-type="{{ $key }}"><i class="bi {{ $icon }}"></i>{{ $label }}</a>
        </li>
      @endforeach
    </ul>

    <div class="row g-2 report-filters">
      <div class="col-6 col-md-3 col-xl-2" data-for="date">
        <label class="form-label" for="from">From</label>
        <input type="date" class="form-control form-control-sm" id="from" name="from" value="{{ $f['from'] }}">
      </div>
      <div class="col-6 col-md-3 col-xl-2" data-for="date">
        <label class="form-label" for="to">To</label>
        <input type="date" class="form-control form-control-sm" id="to" name="to" value="{{ $f['to'] }}">
      </div>
      <div class="col-6 col-md-3 col-xl-2" data-for="district">
        <label class="form-label" for="district">District</label>
        <select class="form-select form-select-sm" id="district" name="district">
          <option value="">All</option>
          @foreach ($districts as $d) <option value="{{ $d }}" @selected($f['district'] === $d)>{{ $d }}</option> @endforeach
        </select>
      </div>
      <div class="col-6 col-md-3 col-xl-2" data-for="sector">
        <label class="form-label" for="sector">Sector</label>
        <select class="form-select form-select-sm" id="sector" name="sector">
          <option value="">All</option>
          @foreach ($sectors as $s) <option value="{{ $s }}" @selected($f['sector'] === $s)>{{ $s }}</option> @endforeach
        </select>
      </div>
      <div class="col-6 col-md-3 col-xl-2" data-for="vet">
        <label class="form-label" for="vet">Veterinary</label>
        <select class="form-select form-select-sm" id="vet" name="vet">
          <option value="">All</option>
          @foreach ($vets as $v) <option value="{{ $v }}" @selected($f['vet'] === $v)>{{ $v }}</option> @endforeach
        </select>
      </div>
      <div class="col-6 col-md-3 col-xl-2" data-for="status">
        <label class="form-label" for="status">PD result</label>
        <select class="form-select form-select-sm" id="status" name="status">
          <option value="">All</option>
          @foreach ($statuses as $s) <option value="{{ strtolower($s) }}" @selected($f['status'] === strtolower($s))>{{ $s }}</option> @endforeach
        </select>
      </div>
      <div class="col-12 col-md-6 col-xl-3" data-for="cow">
        <label class="form-label" for="cow">Cow</label>
        <select class="form-select form-select-sm" id="cow" name="cow" data-no-select2 data-url="{{ route('reporting.animals', 'cows') }}">
          <option value=""></option>
          @if ($selectedCow) <option value="{{ $selectedCow->Eartagno }}" selected>{{ $selectedCow->Eartagno }} · {{ $selectedCow->Name }}</option> @endif
        </select>
      </div>
      <div class="col-12 col-md-6 col-xl-3" data-for="bull">
        <label class="form-label" for="bull">Bull</label>
        <select class="form-select form-select-sm" id="bull" name="bull" data-no-select2 data-url="{{ route('reporting.animals', 'bulls') }}">
          <option value=""></option>
          @if ($selectedBull) <option value="{{ $selectedBull->Eartagno }}" selected>{{ $selectedBull->Eartagno }} · {{ $selectedBull->Name }}</option> @endif
        </select>
      </div>
      <div class="col-6 col-md-3 col-xl-2" data-for="semen_code">
        <label class="form-label" for="semen_code">Semen code</label>
        <input type="text" class="form-control form-control-sm" id="semen_code" name="semen_code" value="{{ $f['semen_code'] }}" placeholder="e.g. 10">
      </div>
      <div class="col-12 col-xl d-flex align-items-end gap-2 ms-xl-auto">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Generate</button>
        <a href="{{ route('reporting.index', ['type' => $type]) }}" class="btn btn-outline-secondary btn-sm" id="resetFilters">Reset</a>
      </div>
    </div>
  </div>
</form>

@if ($report)
  <div class="print-container mb-4">
    @if ($report['rows'])
      <div class="report-sheet">
        @include('pages.reporting._letterhead', ['title' => $title.' report'])

        @php
          $applied = collect([
            'From' => $f['from'], 'To' => $f['to'], 'District' => $f['district'], 'Sector' => $f['sector'], 'Veterinary' => $f['vet'],
            'Cow' => $f['cow'], 'Bull' => $f['bull'], 'PD result' => $f['status'], 'Semen code' => $f['semen_code'],
          ])->filter(fn ($v, $k) => $v !== '' && in_array(match ($k) { 'From', 'To' => 'date', 'Veterinary' => 'vet', 'PD result' => 'status', 'Semen code' => 'semen_code', default => strtolower($k) }, $appliesTo[$type], true));
        @endphp
        @if ($applied->isNotEmpty())
          <p class="small text-muted mb-2">Filters: {{ $applied->map(fn ($v, $k) => "$k: $v")->implode(' · ') }}</p>
        @endif

        <div class="table-responsive">
          <table id="reportTable" data-letterhead="true" class="table table-striped table-sm align-middle mb-0 sir-table" data-responsive="false" data-title="{{ $title }} report" data-page-length="25">
            <thead class="table-light">
              <tr>
                @foreach ($report['columns'] as [$label, $kind])
                  <th @if ($loop->first) data-priority="1" @endif @if ($kind) data-filter="{{ $kind }}" @endif>{{ $label }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach ($report['rows'] as $row)
                <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          <p class="mb-1">Done at Huye</p>
          <p class="mb-4">On {{ $now }}</p>
          <p class="mb-0">Veternary {{ auth()->user()->name }} signature &amp; stamp</p>
          <div style="height:3.5rem"></div>
        </div>

        <div class="no-print d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-3">
          <span class="text-muted small">{{ number_format(count($report['rows'])) }} {{ count($report['rows']) === 1 ? 'record' : 'records' }}</span>
          <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print / Save as PDF</button>
        </div>
      </div>
    @else
      <div class="empty-state no-print">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>No information found for these filters
      </div>
    @endif
  </div>
@else
  <div class="empty-state no-print">
    <i class="bi bi-file-earmark-bar-graph fs-1 d-block mb-2"></i>Pick a report, set the filters and press Generate
  </div>
@endif
@endsection

@push('scripts')
<script>
  (function () {
    var applies = @json($appliesTo);
    var $form = $('#reportForm');

    // Only the filters that belong to the chosen report are shown (and submitted).
    function showFilters(type) {
      var allowed = applies[type] || [];
      $form.find('[data-for]').each(function () {
        var on = allowed.indexOf($(this).data('for')) !== -1;
        $(this).toggleClass('d-none', !on).find('input, select').prop('disabled', !on);
      });
      $('#resetFilters').attr('href', @json(route('reporting.index')) + '?type=' + type);
    }

    $form.find('.report-types .nav-link').on('click', function (e) {
      e.preventDefault();
      var type = $(this).data('type');
      $('#reportType').val(type);
      $form.find('.report-types .nav-link').removeClass('active');
      $(this).addClass('active');
      showFilters(type);
    });

    ['cow', 'bull'].forEach(function (id) {
      var $el = $('#' + id);
      $el.select2({
        theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: 'Search by ear tag, name or owner',
        ajax: { url: $el.data('url'), dataType: 'json', delay: 250, data: function (p) { return { q: p.term || '' }; }, processResults: function (d) { return d; } }
      });
    });

    showFilters($('#reportType').val());
  })();
</script>
@endpush
