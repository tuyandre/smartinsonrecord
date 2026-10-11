@extends('layouts.app')

@section('title', 'Registered Cows · SMART INSEMrecord')
@section('page_title', 'Registered Cows')
@section('page_sub', 'Recorded data · all cows in the system')
@section('actions')
  <a href="{{ route('registration.index') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Register Cow</a>
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
      <table class="table table-hover align-middle sir-table" data-title="Registered Cows" data-order='[[0,"asc"]]' data-page-length="15">
        <thead>
          <tr>
            <th data-priority="1">Eartag</th>
            <th data-priority="1">Name</th>
            <th data-filter="select" data-priority="3">Gender</th>
            <th data-filter="select" data-priority="2">Breed</th>
            <th>Cross breeds</th>
            <th data-type="num">Breed %</th>
            <th data-filter="date" data-priority="4">DOB</th>
            <th data-type="num">Calvings</th>
            <th data-filter="text" data-priority="2">Owner</th>
            <th>National ID</th>
            <th>Phone</th>
            <th data-filter="select">District</th>
            <th data-filter="select">Sector</th>
            <th>Cell</th>
            <th>Village</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              <td class="fw-semibold">{{ $r->Eartagno }}</td>
              <td>{{ $r->Name }}</td>
              <td>{{ $r->Gender }}</td>
              <td>{{ $r->Breed }}</td>
              <td>{{ collect([$r->Cross_breed1, $r->Cross_breed2])->filter()->implode(' / ') ?: '—' }}</td>
              <td>{{ $r->Breed_Percentage }}</td>
              <td class="text-nowrap">{{ $iso($r->Dob) }}</td>
              <td>{{ $r->Number_Calving }}</td>
              <td>{{ $r->Owner_names }}</td>
              <td>{{ $r->National_Id }}</td>
              <td>{{ $r->Phone }}</td>
              <td>{{ $r->District }}</td>
              <td>{{ $r->Sector }}</td>
              <td>{{ $r->Cell }}</td>
              <td>{{ $r->Village }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
