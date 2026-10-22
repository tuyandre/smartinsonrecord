@extends('layouts.app')

@section('title', 'Register Veterinarian · SMART INSEMrecord')
@section('page_title', 'Register Veterinarian')
@section('page_sub', 'Add a veterinarian to the national register')
@section('actions')
  @can('veterinarians.view')
    <a href="{{ route('veterinarians.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @endcan
@endsection

@section('content')
  @if ($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
  @endif
  <form method="POST" action="{{ route('veterinarians.store') }}" novalidate>
    @csrf
    @include('pages.veterinarians._form')
    <button type="submit" class="btn btn-primary btn-lg mt-3"><i class="bi bi-check2-circle me-1"></i> Save Information</button>
  </form>
@endsection
