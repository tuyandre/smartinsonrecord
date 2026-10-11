@extends('layouts.app')

@section('title', 'Calves · SMART INSEMrecord')
@section('page_title', 'Calves')
@section('page_sub', 'Recorded data · one row per calf born')
@section('actions')
  <a href="{{ route('newborn.index') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Record Newborn</a>
@endsection

@section('content')
  @include('pages.records._toolbar')
  @php
  /** Normalise a stored date string to ISO YYYY-MM-DD when possible (so the date filter works). */
  $iso = function ($d) {
      $d = trim((string) $d);
      if ($d === '') { return '—'; }
      if (preg_match('/^\d{4}-\d{2}-\d{2}/', $d)) { return substr($d, 0, 10); }
      if (preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{4})$#', $d, $m)) { return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]); }
      return $d;
  };
@endphp

  <div class="card">
    <div class="card-body">
      <table class="table table-hover align-middle sir-table" data-title="Calves" data-order='[[0,"asc"]]' data-page-length="15">
        <thead>
          <tr>
            <th data-filter="text" data-priority="1">Mother</th>
            <th data-priority="1">Calf</th>
            <th data-filter="select" data-priority="3">Breed</th>
            <th data-filter="date" data-priority="2">DOB</th>
            <th data-type="num">Weight (kg)</th>
            <th data-type="num">Birth times</th>
            <th data-filter="select" data-priority="4">Observation</th>
            <th data-filter="select">Disability</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              <td><span class="fw-semibold">{{ $r->mother_tag }}</span> <span class="small text-muted">{{ $r->mother_name ?? '' }}</span></td>
              <td><span class="fw-semibold">{{ $r->calf_tag }}</span> <span class="small text-muted">Calf {{ $r->calf_no }}</span></td>
              <td>{{ $r->breed ?: '—' }}</td>
              <td class="text-nowrap">{{ $iso($r->dob) }}</td>
              <td>{{ $r->weight }}</td>
              <td>{{ $r->birth_times }}</td>
              <td>{{ $r->observation ?: '—' }}</td>
              <td>{{ $r->disability ?: '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
