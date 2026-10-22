@extends('layouts.app')

@section('title', 'Edit Bull · SMART INSEMrecord')
@section('page_title', 'Edit Bull')
@section('page_sub', $bull->Name.' · '.$bull->Eartagno)
@section('actions')
  <a href="{{ route('bulls.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
@endsection

@push('head')
  <script src="{{ asset('js/rwanda-address.js') }}"></script>
  <style>
    .reg-card-title { font-weight: 700; display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
    .reg-card-title i { color: var(--bs-primary); }
  </style>
@endpush

@section('content')
  @if ($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
  @endif

  <form action="{{ route('bulls.update', $bull->Eartagno) }}" method="POST" id="cowForm" novalidate>
    @csrf
    @method('PUT')
    <div class="row g-3">
      @include('pages.cows._form', ['cow' => $bull, 'sex' => 'Male', 'animal' => 'Bull'])
      <div class="col-12 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-1"></i> Update Information</button>
        <a href="{{ route('bulls.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  (function () {
    @include('pages.cows._form-js', ['cow' => $bull])
  })();
</script>
@endpush
