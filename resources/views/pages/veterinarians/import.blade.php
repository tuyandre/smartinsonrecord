@extends('layouts.app')

@section('title', 'Import Veterinarians · SMART INSEMrecord')
@section('page_title', 'Import Veterinarians')
@section('page_sub', 'Load the RCVD list from an Excel (.xlsx) or CSV file')
@section('actions')
  @can('veterinarians.view')
    <a href="{{ route('veterinarians.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @endcan
@endsection

@section('content')
  @php $result = session('import_result'); @endphp

  @if ($result)
    <div class="card mb-3">
      <div class="card-body">
        <h2 class="h6"><i class="bi bi-check2-circle text-success me-1"></i>Import finished</h2>
        <div class="d-flex flex-wrap gap-3 mb-2">
          <span><span class="badge text-bg-success">{{ $result['created'] }}</span> added</span>
          <span><span class="badge text-bg-primary">{{ $result['updated'] }}</span> updated</span>
          <span><span class="badge text-bg-{{ $result['skipped'] ? 'warning' : 'secondary' }}">{{ count($result['skipped']) }}</span> skipped</span>
        </div>
        @if ($result['skipped'])
          <ul class="small text-muted mb-0">
            @foreach (array_slice($result['skipped'], 0, 25) as $reason) <li>{{ $reason }}</li> @endforeach
            @if (count($result['skipped']) > 25) <li>… and {{ count($result['skipped']) - 25 }} more</li> @endif
          </ul>
        @endif
      </div>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('veterinarians.import.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        <label for="file" class="form-label">List of veterinarians</label>
        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.csv" required>
        @error('file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        <div class="form-text mt-2">
          The first row must hold the headings <strong>No, RCVD ID, National ID, Names, date of birth, District, Sector</strong>.
          The National ID must be exactly 16 digits (format that column as Text in Excel so it is not shortened). Dates are read as dd/mm/yyyy. Veterinarians are matched on the RCVD ID, so importing the same list again updates them instead of duplicating.
          <a href="{{ route('veterinarians.template') }}">Download a sample file</a>.
        </div>

        <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-upload me-1"></i>Import</button>
      </form>
    </div>
  </div>
@endsection
