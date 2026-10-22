@extends('layouts.app')

@section('title', 'Register Semen · SMART INSEMrecord')
@section('page_title', 'Register Semen')
@section('page_sub', 'Record the semen batches of a registered bull.')

@section('actions')
  @can('semen.view')
    <a href="{{ route('semen.index') }}" class="btn btn-outline-primary"><i class="bi bi-list-ul me-1"></i> Semen List</a>
  @endcan
@endsection

@push('head')
  <style>
    .reg-card-title { font-weight: 700; display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
    .reg-card-title i { color: var(--bs-primary); }
  </style>
@endpush

@section('content')
  @if ($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
  @endif

  <form action="{{ route('semen.store') }}" method="POST" novalidate>
    @csrf
    <div class="row g-3">
      @include('pages.semen._form', ['semen' => null])
      <div class="col-12">
        <button type="submit" class="btn btn-primary btn-lg w-100 w-md-auto"><i class="bi bi-check2-circle me-1"></i> Save Information</button>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  (function () {
    @include('pages.semen._form-js')
  })();
</script>
@endpush
