@extends('layouts.app')

@section('title', 'Access denied · SMART INSEMrecord')

@section('content')
  <div class="card mx-auto" style="max-width: 560px">
    <div class="card-body text-center py-5">
      <div class="display-4 text-danger mb-2"><i class="bi bi-shield-lock"></i></div>
      <h1 class="h4">Access denied</h1>
      <p class="text-muted mb-4">{{ $exception->getMessage() ?: 'You do not have permission to open this page.' }} Ask an administrator to give your role access.</p>
      <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('landing') }}" class="btn btn-outline-primary">Go back</a>
    </div>
  </div>
@endsection
