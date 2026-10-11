@extends('layouts.app')

@section('title', 'Pedigree · SMART INSEMrecord')
@section('page_title', 'Pedigree')
@section('page_sub')<span id="clock" class="fw-medium"></span> · Family Tree @endsection

@push('head')
  <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
  <script src="https://d3js.org/d3.v7.min.js"></script>
  <style>
    #tree-scroll { overflow: auto; -webkit-overflow-scrolling: touch; border: 1px solid var(--bs-border-color); border-radius: .5rem; background: #fff; }
    #tree-container svg { display: block; height: auto; }
    #tree-container text { font-size: 12px; fill: #1f2937; }
  </style>
@endpush

@section('content')
  <div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon"><i class="bi bi-heart-pulse"></i></div>
        <div>
          <div class="value">{{ $totalInseminated }}</div>
          <div class="label fw-medium">Inseminated Cows</div>
          <div class="hint">Entire Number</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon green"><i class="bi bi-person-badge"></i></div>
        <div>
          <div class="value">{{ $totalVets }}</div>
          <div class="label fw-medium">Veternaries</div>
          <div class="hint">+1(New)</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon amber"><i class="bi bi-droplet-half"></i></div>
        <div class="overflow-hidden">
          <div class="value">{{ $bullCount }}</div>
          <div class="label fw-medium">Semens</div>
          <div class="hint text-truncate" title="{{ $bullNames }}">From {{ $bullNames }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="icon violet"><i class="bi bi-gem"></i></div>
        <div class="overflow-hidden">
          <div class="value">{{ $bullCount }}</div>
          <div class="label fw-medium">Bulls</div>
          <div class="hint text-truncate" title="{{ $bullNames }}">Includes {{ $bullNames }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6 mb-0"><i class="bi bi-diagram-3 text-primary me-1"></i>Family Tree</h2>
          <p class="text-muted small">Search any cow ear tag to see its mother and calves</p>
          <form method="GET" action="{{ route('pedigree.index') }}" class="mb-3">
            <label for="eartagno" class="form-label">Enter Cow Ear Tag</label>
            <div class="input-group">
              <input type="text" class="form-control" id="eartagno" name="eartagno" value="{{ request('eartagno') }}" placeholder="Search any eartag number ..." />
              <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
            </div>
          </form>
          <div id="tree-scroll" class="{{ $searched ? '' : 'd-none' }}">
            <div id="tree-container"></div>
          </div>
          @unless ($searched)
            <div class="empty-state"><i class="bi bi-diagram-3"></i>Search an ear tag to view its family tree</div>
          @endunless
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

  @if ($searched)
  (function () {
    const treeData = @json($treeData);
    const treeMap = {};

    treeData.forEach(function (node) {
      if (!treeMap[node.Mother]) {
        treeMap[node.Mother] = { name: node.Mother, children: [] };
      }
      if (node.Calf_1) treeMap[node.Mother].children.push({ name: node.Calf_1 });
      if (node.Calf_2) treeMap[node.Mother].children.push({ name: node.Calf_2 });
      if (node.Calf_3) treeMap[node.Mother].children.push({ name: node.Calf_3 });
    });

    const root = { name: "Root", children: Object.values(treeMap) };
    const hierarchy = d3.hierarchy(root);
    const leaves = hierarchy.leaves().length;

    // Natural size grows with the number of leaves; the SVG scales to the card
    // width (min 520px), and the wrapper scrolls horizontally on small screens.
    const margin = 40;
    const width = Math.max(600, leaves * 90);
    const height = 400;
    d3.tree().size([width - margin * 2, height - margin * 2])(hierarchy);

    const svg = d3.select("#tree-container").append("svg")
      .attr("viewBox", "0 0 " + width + " " + height)
      .attr("preserveAspectRatio", "xMidYMin meet")
      .style("width", "100%")
      .style("min-width", Math.min(width, 520) + "px");
    const g = svg.append("g").attr("transform", "translate(" + margin + "," + margin + ")");

    g.selectAll(".link")
      .data(hierarchy.links())
      .enter().append("line")
      .attr("x1", d => d.source.x).attr("y1", d => d.source.y)
      .attr("x2", d => d.target.x).attr("y2", d => d.target.y)
      .attr("stroke", "#9db4d3").attr("stroke-width", 1.5);

    g.selectAll(".node")
      .data(hierarchy.descendants())
      .enter().append("circle")
      .attr("cx", d => d.x).attr("cy", d => d.y)
      .attr("r", 6).attr("fill", "#1d6fdc");

    g.selectAll(".label")
      .data(hierarchy.descendants())
      .enter().append("text")
      .attr("x", d => d.x).attr("y", d => d.y - 12)
      .attr("text-anchor", "middle")
      .text(d => d.data.name);
  })();
  @endif
</script>
@endpush
