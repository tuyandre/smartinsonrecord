@extends('layouts.app')

@section('title', 'Pedigree · SMART INSEMrecord')
@section('page_title', 'Pedigree')
@section('page_sub', 'Family tree of any registered cow or bull')

@push('head')
  <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
  <style>
    .ped-scroll { overflow-x: auto; padding: 1rem .5rem; border: 1px solid var(--bs-border-color); border-radius: .6rem; background: #fff; }
    .ped-tree, .ped-tree ul { list-style: none; margin: 0; padding: 0; }
    .ped-tree { display: flex; justify-content: center; min-width: max-content; }
    .ped-tree ul { display: flex; justify-content: center; padding-top: 1.1rem; position: relative; }
    .ped-tree li { position: relative; text-align: center; padding: 1.1rem .45rem 0; }
    .ped-tree > li { padding-top: 0; }
    /* connectors */
    .ped-tree li::before, .ped-tree li::after { content: ''; position: absolute; top: 0; right: 50%; width: 50%; height: 1.1rem; border-top: 2px solid #b8c6dc; }
    .ped-tree li::after { right: auto; left: 50%; border-left: 2px solid #b8c6dc; }
    .ped-tree li:only-child::before, .ped-tree li:only-child::after { display: none; }
    .ped-tree li:only-child { padding-top: 0; }
    .ped-tree li:first-child::before, .ped-tree li:last-child::after { border: 0 none; }
    .ped-tree li:last-child::before { border-right: 2px solid #b8c6dc; border-radius: 0 .4rem 0 0; }
    .ped-tree li:first-child::after { border-radius: .4rem 0 0 0; }
    .ped-tree ul::before { content: ''; position: absolute; top: 0; left: 50%; border-left: 2px solid #b8c6dc; height: 1.1rem; }
    .ped-node { display: inline-block; min-width: 118px; max-width: 170px; padding: .4rem .6rem; border: 1px solid var(--bs-border-color); border-radius: .6rem; background: #fff; box-shadow: 0 1px 3px rgba(16, 24, 40, .06); }
    .ped-node.is-cow { border-top: 3px solid #1d6fdc; }
    .ped-node.is-bull { border-top: 3px solid #7c4dff; }
    .ped-node.is-root { background: #f4f8ff; box-shadow: 0 2px 8px rgba(29, 111, 220, .18); }
    .ped-node.is-unknown { border-style: dashed; color: var(--bs-secondary-color); }
    .ped-role { font-size: .65rem; text-transform: uppercase; letter-spacing: .05em; color: var(--bs-secondary-color); }
    .ped-tag { font-weight: 700; font-size: .85rem; }
    .ped-name { font-size: .8rem; }
    .ped-meta { font-size: .68rem; color: var(--bs-secondary-color); }
    @media print { .page-head, .no-print { display: none !important; } .ped-scroll { overflow: visible; border: 0; } }
  </style>
@endpush

@section('content')
  <div class="row g-3">
    <div class="col-12 col-xl-8">
      <div class="card mb-3 no-print">
        <div class="card-body">
          <h2 class="h6 mb-0"><i class="bi bi-diagram-3 text-primary me-1"></i>Find an animal</h2>
          <p class="text-muted small">Search a registered cow or bull to see its parents, grandparents and calves.</p>
          <form method="GET" action="{{ route('pedigree.index') }}" id="pedForm">
            <div class="input-group">
              <select class="form-select" id="eartagno" name="eartagno" data-no-select2 data-url="{{ route('pedigree.search') }}" required>
                <option value=""></option>
                @if ($root) <option value="{{ $root['tag'] }}" selected>{{ $root['tag'] }} · {{ $root['name'] }}</option> @endif
              </select>
              <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Show tree</button>
            </div>
          </form>
        </div>
      </div>

      @if ($root)
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
              <h2 class="h6 mb-0"><i class="bi bi-arrow-up-circle text-primary me-1"></i>Ancestors <span class="text-muted fw-normal small">· {{ $root['tag'] }} with up to 3 generations</span></h2>
              <button type="button" class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
            </div>
            <div class="ped-scroll">
              <ul class="ped-tree">@include('pages.pedigree._node', ['node' => $ancestors, 'isRoot' => true])</ul>
            </div>
            @if (empty($ancestors['children']))
              <p class="text-muted small mt-2 mb-0">No parents are recorded for this animal. Parents appear once it is registered as a calf on the New Born page.</p>
            @endif
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h2 class="h6 mb-2"><i class="bi bi-arrow-down-circle text-primary me-1"></i>Offspring <span class="text-muted fw-normal small">· calves and their calves</span></h2>
            <div class="ped-scroll">
              <ul class="ped-tree">@include('pages.pedigree._node', ['node' => $descendants, 'isRoot' => true])</ul>
            </div>
            @if (empty($descendants['children']))
              <p class="text-muted small mt-2 mb-0">No calves recorded yet.</p>
            @endif
          </div>
        </div>
      @elseif ($query !== '')
        <div class="card">
          <div class="card-body">
            <div class="empty-state mb-0"><i class="bi bi-search"></i>No cow or bull found with ear tag “{{ $query }}”</div>
            @if ($suggestions->isNotEmpty())
              <p class="small text-muted mt-3 mb-1">Did you mean:</p>
              <div class="d-flex flex-wrap gap-2">
                @foreach ($suggestions as $s)
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('pedigree.index', ['eartagno' => $s['tag']]) }}"><i class="bi {{ $s['kind'] === 'bull' ? 'bi-gender-male' : 'bi-gender-female' }} me-1"></i>{{ $s['tag'] }} · {{ $s['name'] }}</a>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="card">
          <div class="card-body"><div class="empty-state mb-0"><i class="bi bi-diagram-3"></i>Search an ear tag to view its family tree</div></div>
        </div>
      @endif
    </div>

    <div class="col-12 col-xl-4 no-print">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6 mb-0">Annual Insemination Survey</h2>
          <p class="text-muted small">Inseminations recorded per month</p>
          <div style="position:relative;height:300px"><canvas id="inseminationChart"></canvas></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  (function () {
    var $el = $('#eartagno');
    $el.select2({
      theme: 'bootstrap-5', width: '1%', placeholder: 'Search by ear tag, name or owner', allowClear: true,
      ajax: { url: $el.data('url'), dataType: 'json', delay: 250, data: function (p) { return { q: p.term || '' }; }, processResults: function (d) { return d; } }
    });

    new Chart(document.getElementById('inseminationChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: @json($months),
        datasets: [{ label: 'Number of Inseminated Cows', data: @json($counts), backgroundColor: 'rgba(20,198,113,.65)', borderRadius: 6 }]
      },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
  })();
</script>
@endpush
