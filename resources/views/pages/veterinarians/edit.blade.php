@extends('layouts.app')

@section('title', 'Edit Veterinarian · SMART INSEMrecord')
@section('page_title', 'Edit Veterinarian')
@section('page_sub', $vet->rcvd_id.' · '.$vet->names)
@section('actions')
  @can('veterinarians.view')
    <a href="{{ route('veterinarians.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @endcan
@endsection

@section('content')
  @if ($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
  @endif
  <form method="POST" action="{{ route('veterinarians.update', $vet) }}" novalidate>
    @csrf
    @method('PUT')
    @include('pages.veterinarians._form')
    <div class="d-flex flex-wrap gap-2 mt-3">
      <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-1"></i> Update Information</button>
      <a href="{{ route('veterinarians.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
  </form>
@endsection
