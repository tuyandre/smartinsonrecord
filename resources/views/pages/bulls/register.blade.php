@extends('layouts.app')

@section('title', 'Bull Registration · SMART INSEMrecord')
@section('page_title', 'Bull Registration')
@section('page_sub', 'Register bulls. Registered bulls are listed under Registered Bulls.')

@section('actions')
  @can('bulls.view')
    <a href="{{ route('bulls.index') }}" class="btn btn-outline-primary"><i class="bi bi-list-ul me-1"></i> Registered Bulls</a>
  @endcan
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

  <form action="{{ route('bulls.store') }}" method="POST" id="cowForm" novalidate>
    @csrf
    <div class="row g-3">
      @include('pages.cows._form', ['cow' => null, 'sex' => 'Male', 'animal' => 'Bull'])
      <div class="col-12">
        <button type="submit" class="btn btn-primary btn-lg w-100 w-md-auto"><i class="bi bi-check2-circle me-1"></i> Save Information</button>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  (function () {
    @include('pages.cows._form-js', ['cow' => null])
  })();
</script>
@endpush
