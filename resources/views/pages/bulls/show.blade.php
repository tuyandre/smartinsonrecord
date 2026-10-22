@extends('layouts.app')

@section('title', 'Bull '.$bull->Eartagno.' · SMART INSEMrecord')
@section('page_title', $bull->Name.' · '.$bull->Eartagno)
@section('page_sub', 'Bull details')
@section('actions')
  <a href="{{ route('bulls.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @can('bulls.edit')
    <a href="{{ route('bulls.edit', $bull->Eartagno) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
  @endcan
@endsection

@section('content')
  @php
    $groups = [
      'Bull' => ['Name' => 'Name', 'Eartagno' => 'Ear tag', 'Gender' => 'Gender', 'Dob' => 'Date of birth'],
      'Breed' => ['Breed' => 'Breed', 'Cross_breed1' => 'Cross breed 1', 'Cross_breed2' => 'Cross breed 2', 'Breed_Percentage' => 'Breed %'],
      'Owner' => ['Owner_names' => 'Owner', 'National_Id' => 'National ID', 'Phone' => 'Phone'],
      'Location' => ['District' => 'District', 'Sector' => 'Sector', 'Cell' => 'Cell', 'Village' => 'Village'],
    ];
  @endphp

  <div class="row g-3 mb-3">
    @foreach ($groups as $title => $fields)
      <div class="col-12 col-lg-6">
        <div class="card h-100">
          <div class="card-header">{{ $title }}</div>
          <div class="card-body">
            <div class="row g-3">
              @foreach ($fields as $field => $label)
                <div class="col-6">
                  <div class="small text-muted">{{ $label }}</div>
                  <div class="fw-medium">{{ $bull->$field !== null && $bull->$field !== '' ? $bull->$field : '—' }}</div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>


@endsection
