@extends('layouts.app')

@section('title', 'Cow Registration · SMART INSEMrecord')
@section('page_title', 'Registration')
@section('page_sub', 'Register cows and heifers. Registered cows are listed under Registered Cows.')

@section('actions')
  <a href="#registeredCows" class="btn btn-outline-primary"><i class="bi bi-list-ul me-1"></i> Registered Cows</a>
@endsection

@push('head')
<script src="{{ asset('js/rwanda-address.js') }}"></script>
<style>
    .reg-table th { white-space: nowrap; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; }
    .reg-table td { white-space: nowrap; vertical-align: middle; }
    .reg-card-title { font-weight: 700; display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
    .reg-card-title i { color: var(--bs-primary); }
    .reg-sticky-save { position: sticky; bottom: 0; z-index: 5; }
</style>
@endpush

@section('content')
@php
  $fieldError = function ($f) use ($errors) { return $errors->has($f) ? 'is-invalid' : ''; };
@endphp

@if ($errors->any())
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
@endif

<form action="{{ route('registration.store') }}" method="POST" id="cowForm" novalidate>
  @csrf
  <div class="row g-3">

    @include('pages.cows._form', ['cow' => null])

    <div class="col-12">
      <button type="submit" name="save" value="Save Information" class="btn btn-primary btn-lg w-100 w-md-auto"><i class="bi bi-check2-circle me-1"></i> Save Information</button>
    </div>
  </div>
</form>

@if ($searchResults !== null)
<div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-search me-1"></i> Cow Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if ($searchResults->isNotEmpty())
          @php
            $searchColumns = [
              'Name' => 'Name', 'Eartagno' => 'Ear Tag', 'Gender' => 'Gender', 'Breed' => 'Breed',
              'Cross_breed1' => 'Cross Breed 1', 'Cross_breed2' => 'Cross Breed 2', 'Breed_Percentage' => 'Breed Percentage',
              'Dob' => 'DOB', 'Number_Calving' => 'Number of Calving', 'Owner_names' => 'Owner Names',
              'National_Id' => 'National ID', 'Phone' => 'Phone', 'District' => 'District', 'Sector' => 'Sector',
              'Cell' => 'Cell', 'Village' => 'Village',
            ];
            foreach ([1, 2, 3] as $i) {
              $searchColumns += [
                "Calf_Eartagno_$i" => "Calf Ear Tag $i", "Breed_$i" => "Breed $i", "Dob_$i" => "DOB $i",
                "Weight_at_Birth_$i" => "Weight at Birth $i", "Birth_Times_$i" => "Birth Times $i",
                "Observation_$i" => "Observation $i", "Disability_$i" => "Disability $i",
              ];
            }
          @endphp
          <div>
            <table class="table table-hover align-middle reg-table sir-table" data-title="Cow Details" data-buttons="false">
              <thead>
                <tr>
                  @foreach ($searchColumns as $column => $label)
                    @php
                      $thAttr = match ($column) {
                          'Name', 'Eartagno' => ' data-priority="1"',
                          'Gender', 'Breed', 'District', 'Sector' => ' data-filter="select"',
                          'Owner_names' => ' data-filter="text"',
                          default => '',
                      };
                    @endphp
                    <th{!! $thAttr !!}>{{ $label }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach ($searchResults as $row)
                  <tr>
                    @foreach ($searchColumns as $column => $label)
                      <td>{{ $row->{$column} }}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="empty-state"><i class="bi bi-search"></i><div>Cow Eartagno Information Not Found</div></div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
  (function () {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    function postJson(url, data) {
      return fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
      }).then(function (response) { return response.json(); });
    }

    $('#cow_eartag').on('blur', function () {
      var earTag = $(this).val();
      if (!earTag) { return; }
      postJson(@json(route('registration.check')), { eartagno: earTag }).then(function (result) {
        if (result.exists) {
          swal({ title: 'Error!', text: 'Cow already exists', icon: 'warning' });
          return;
        }
        if (result && result.dob) {
          $('#dob').val(result.dob);
        }
      }).catch(function (error) {
        console.error('Error checking cow EarTag:', error);
      });
    });

    @include('pages.cows._form-js', ['cow' => null])
    @if ($searchResults !== null)
      bootstrap.Modal.getOrCreateInstance(document.getElementById('searchModal')).show();
      @if ($searchResults->isEmpty())
        swal({ title: 'Error!', text: 'Cow Eartagno Information Not Found', icon: 'warning' });
      @endif
    @endif
  })();
</script>
@endpush
