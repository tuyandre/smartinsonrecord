@extends('layouts.app')

@section('title', 'Edit Semen · SMART INSEMrecord')
@section('page_title', 'Edit Semen')
@section('page_sub', $semen->bull_eartag.' · '.$semen->bull?->Name)
@section('actions')
  <a href="{{ route('semen.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
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

  <form action="{{ route('semen.update', $semen) }}" method="POST" novalidate>
    @csrf
    @method('PUT')
    <div class="row g-3">
      @include('pages.semen._form')
      <div class="col-12 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-1"></i> Update Information</button>
        <a href="{{ route('semen.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
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
