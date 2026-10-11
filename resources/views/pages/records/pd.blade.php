@extends('layouts.app')

@section('title', 'PD Records · SMART INSEMrecord')
@section('page_title', 'PD Records')
@section('page_sub', 'Recorded data · pregnancy diagnosis results')
@section('actions')
  <a href="{{ route('pd.index') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Record PD</a>
@endsection

@section('content')
  @include('pages.records._toolbar')

  <div class="card">
    <div class="card-body">
      <table class="table table-hover align-middle sir-table" data-title="PD Records" data-order='[[0,"desc"]]' data-page-length="15">
        <thead>
          <tr>
            <th data-type="num">#</th>
            <th data-filter="text" data-priority="1">Cow</th>
            <th data-priority="3">Owner</th>
            <th data-filter="select" data-priority="2">Observation</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            @php
              $obs = trim((string) $r->Observation);
              $cls = strcasecmp($obs, 'Conception') === 0 ? 'success' : (stripos($obs, 'not') !== false ? 'warning' : 'secondary');
            @endphp
            <tr>
              <td class="text-muted">{{ $r->pd_id }}</td>
              <td><span class="fw-semibold">{{ $r->Eartagno }}</span> <span class="small text-muted">{{ $r->cow_name ?? '' }}</span></td>
              <td>{{ $r->owner ?? '—' }}</td>
              <td><span class="badge text-bg-{{ $cls }}">{{ $obs ?: '—' }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
