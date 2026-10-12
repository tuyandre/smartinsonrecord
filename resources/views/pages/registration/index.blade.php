@extends('layouts.app')

@section('title', 'Cow Registration · SMART INSEMrecord')
@section('page_title', 'Registration')
@section('page_sub', 'Register cows and heifers, and manage registered records.')

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

<div class="row g-3 mb-3">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-heart-pulse"></i></div>
      <div class="value">{{ $totalInseminated }}</div>
      <div class="label">Inseminated Cows</div>
      <div class="hint">Entire Number</div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="icon green"><i class="bi bi-person-badge"></i></div>
      <div class="value">{{ $totalVets }}</div>
      <div class="label">Veternarians</div>
      <div class="hint">+1(New)</div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="icon amber"><i class="bi bi-droplet"></i></div>
      <div class="value">{{ $bullCount }}</div>
      <div class="label">Semens</div>
      <div class="hint">From {{ implode(', ', $bullNames) }}</div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="icon violet"><i class="bi bi-gender-male"></i></div>
      <div class="value">{{ $bullCount }}</div>
      <div class="label">Bulls</div>
      <div class="hint">Includes {{ implode(', ', $bullNames) }}</div>
    </div>
  </div>
</div>

@if ($errors->any())
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the highlighted fields below.</div>
@endif

<form action="{{ route('registration.store') }}" method="POST" id="registerForm" novalidate>
  @csrf
  <div class="row g-3">

    {{-- Cow info --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-info-circle"></i> Cow or Heifer Information</div>
          <p class="text-muted small">Provide below information related to the cow or Heifer</p>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="cow_name" class="form-label">Name</label>
              <input type="text" class="form-control {{ $fieldError('cow_name') }}" name="cow_name" id="cow_name" value="{{ old('cow_name') }}" placeholder="EarTag Number/Iherena" maxlength="20" />
              @error('cow_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="cow_eartag" class="form-label">EarTag Number</label>
              <input type="number" class="form-control {{ $fieldError('cow_eartag') }}" name="cow_eartag" id="cow_eartag" value="{{ old('cow_eartag') }}" placeholder="EarTag Number/Iherena" />
              @error('cow_eartag') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label d-block">Gender</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" checked="checked" name="gender" value="Female" id="genderFemale">
                <label class="form-check-label" for="genderFemale">Female</label>
              </div>
              @error('gender') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="dob" class="form-label">Date of Birth</label>
              <input type="date" class="form-control {{ $fieldError('dob') }}" name="dob" value="{{ old('dob') }}" placeholder="Date of Birth/Igihe yavukiye" id="dob" />
              @error('dob') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="calves" class="form-label">Number of Calving</label>
              <input type="number" class="form-control {{ $fieldError('calves') }}" name="calves" id="calves" value="{{ old('calves') }}" placeholder="calves/Inyana ifite" />
              @error('calves') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Breed --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-diagram-3"></i> Breed</div>
          <p class="text-muted small">Single breed or a cross of two breeds.</p>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="breedIdentification">Breed Identification</label>
              <select class="form-select {{ $fieldError('breed_identification') }}" name="breed_identification" id="breedIdentification">
                <option value="single" @selected(old('breed_identification', 'single') === 'single')>Single Breed</option>
                <option value="cross" @selected(old('breed_identification') === 'cross')>Cross Breed</option>
              </select>
              @error('breed_identification') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>

          <div id="singleBreedSection" class="mt-3">
            <label for="breedSelect" class="form-label">Breed</label>
            <select class="form-select {{ $fieldError('breed') }}" name="breed" id="breedSelect">
              <option value="">-----Choose Breed---------</option>
              @foreach ($breeds as $breed)
                <option value="{{ $breed }}" @selected(old('breed') === $breed)>{{ $breed }}</option>
              @endforeach
            </select>
            @error('breed') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div id="crossBreedSection" class="mt-3" style="display: none;">
            <label class="form-label">Cross Breed</label>
            <div class="row g-2">
              <div class="col-12 col-sm-8">
                <select class="form-select {{ $fieldError('cross_breed1') }}" name="cross_breed1" id="crossBreed1" aria-label="Cross breed 1">
                  <option value="">-----Choose Breed---------</option>
                  @foreach ($breeds as $breed)
                    <option value="{{ $breed }}" @selected(old('cross_breed1') === $breed)>{{ $breed }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-sm-4">
                <div class="input-group">
                  <input type="number" class="form-control {{ $fieldError('cross_breed1_percentage') }}" name="cross_breed1_percentage" id="crossBreed1Percentage" value="{{ old('cross_breed1_percentage') }}" placeholder="...%" min="0" max="100" aria-label="Percentage 1">
                  <span class="input-group-text">%</span>
                </div>
              </div>
              <div class="col-12 col-sm-8">
                <select class="form-select {{ $fieldError('cross_breed2') }}" name="cross_breed2" id="crossBreed2" aria-label="Cross breed 2">
                  <option value="">-----Choose Breed---------</option>
                  @foreach ($breeds as $breed)
                    <option value="{{ $breed }}" @selected(old('cross_breed2') === $breed)>{{ $breed }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-sm-4">
                <div class="input-group">
                  <input type="number" class="form-control {{ $fieldError('cross_breed2_percentage') }}" name="cross_breed2_percentage" id="crossBreed2Percentage" value="{{ old('cross_breed2_percentage') }}" placeholder="...%" min="0" max="100" aria-label="Percentage 2">
                  <span class="input-group-text">%</span>
                </div>
              </div>
            </div>
            @foreach (['cross_breed1', 'cross_breed1_percentage', 'cross_breed2', 'cross_breed2_percentage'] as $f)
              @error($f) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Owner --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-person"></i> Owner's Information</div>
          <p class="text-muted small">Provide observed information</p>
          <div class="row g-3">
            <div class="col-12">
              <label for="owner_name" class="form-label">Names</label>
              <input type="text" class="form-control {{ $fieldError('owner_name') }}" name="owner_name" id="owner_name" value="{{ old('owner_name') }}" placeholder="Owner_name/nyir'inka" maxlength="25" />
              @error('owner_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="nid" class="form-label">National ID</label>
              <input type="number" class="form-control {{ $fieldError('nid') }}" name="nid" id="nid" value="{{ old('nid') }}" placeholder="National Id/Irangamuntu" />
              @error('nid') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="Phone" class="form-label">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1">+250</span>
                <input type="number" class="form-control {{ $fieldError('Phone') }}" placeholder="Phone" name="Phone" id="Phone" value="{{ old('Phone') }}">
              </div>
              @error('Phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Location --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-geo-alt"></i> Location</div>
          <p class="text-muted small">Province, district, sector, cell and village.</p>
          @include('partials.location-fields', ['id' => 'createLocation', 'prefix' => 'loc_'])
        </div>
      </div>
    </div>

    <div class="col-12">
      <button type="submit" name="save" value="Save Information" class="btn btn-primary btn-lg w-100 w-md-auto"><i class="bi bi-check2-circle me-1"></i> Save Information</button>
    </div>
  </div>
</form>

{{-- Registered cows list --}}
<div class="card mt-4" id="registeredCows">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
      <div>
        <div class="reg-card-title mb-0"><i class="bi bi-table"></i> Registered information</div>
        <div class="text-muted small">View/Modify cow information</div>
      </div>
    </div>

      <div>
        <table class="table table-hover align-middle reg-table sir-table" id="cowsTable" data-title="Registered Cows" data-order='[[0,"asc"]]'>
          <thead>
            <tr>
              <th data-priority="1">Name</th>
              <th data-priority="1">Ear Tag</th>
              <th data-filter="select">Gender</th>
              <th data-filter="select">Breed</th>
              <th>Cross Breed 1</th>
              <th>Cross Breed 2</th>
              <th>Breed Percentage</th>
              <th>DOB</th>
              <th>Number of Calving</th>
              <th data-filter="text">Owner Names</th>
              <th>National ID</th>
              <th>Phone</th>
              <th data-filter="select">District</th>
              <th data-filter="select">Sector</th>
              <th>Cell</th>
              <th>Village</th>
              <th class="text-end" data-priority="1" data-orderable="false" data-export="false">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($cows as $cow)
              <tr>
                <td class="fw-semibold">{{ $cow->Name }}</td>
                <td>{{ $cow->Eartagno }}</td>
                <td>{{ $cow->Gender }}</td>
                <td>{{ $cow->Breed }}</td>
                <td>{{ $cow->Cross_breed1 }}</td>
                <td>{{ $cow->Cross_breed2 }}</td>
                <td>{{ $cow->Breed_Percentage }}</td>
                <td>{{ $cow->Dob }}</td>
                <td>{{ $cow->Number_Calving }}</td>
                <td>{{ $cow->Owner_names }}</td>
                <td>{{ $cow->National_Id }}</td>
                <td>{{ $cow->Phone }}</td>
                <td>{{ $cow->District }}</td>
                <td>{{ $cow->Sector }}</td>
                <td>{{ $cow->Cell }}</td>
                <td>{{ $cow->Village }}</td>
                <td class="text-end">
                  <div class="table-actions d-inline-flex gap-1">
                    @can('cows.edit')
                      <button type="button" class="btn btn-sm btn-outline-primary btn-edit-cow" data-eartagno="{{ $cow->Eartagno }}" title="Edit" aria-label="Edit {{ $cow->Name }}"><i class="bi bi-pencil-square"></i></button>
                    @endcan
                    @can('cows.delete')
                      <form action="{{ route('registration.destroy', $cow->Eartagno) }}" method="POST" class="d-inline delete-cow-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete {{ $cow->Name }}"><i class="bi bi-trash"></i></button>
                      </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
  </div>
</div>

{{-- Edit modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil-square me-1"></i> Edit Cow Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" id="editForm" action="">
        <div class="modal-body">
          @csrf
          @method('PUT')
          <input type="hidden" id="eartagno" name="eartagno">
          <div class="row g-3">
            @foreach ([
              ['name', 'Name'], ['gender', 'Gender'], ['breed', 'Breed'], ['cross_breed1', 'Cross Breed 1'],
              ['cross_breed2', 'Cross Breed 2'], ['breed_percentage', 'Breed Percentage'], ['dob', 'DOB'],
              ['number_calving', 'Number of Calving'], ['owner_names', 'Owner Names'], ['national_id', 'National ID'],
              ['phone', 'Phone'],
            ] as [$field, $label])
              <div class="col-12 col-md-6">
                <label for="edit_{{ $field }}" class="form-label">{{ $label }}</label>
                <input type="text" class="form-control" id="edit_{{ $field }}" name="{{ $field }}">
              </div>
            @endforeach
          </div>
          <hr>
          <div class="fw-semibold mb-2"><i class="bi bi-geo-alt me-1"></i> Location</div>
          @include('partials.location-fields', ['id' => 'editLocation', 'prefix' => 'edit_', 'errors' => new \Illuminate\Support\ViewErrorBag])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" name="update"><i class="bi bi-check2 me-1"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

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
        } else if (result && result.found === false) {
          alert('Cow not found in the generations table.');
        }
      }).catch(function (error) {
        console.error('Error checking cow EarTag:', error);
      });
    });

    function toggleBreed() {
      var single = document.getElementById('breedIdentification').value === 'single';
      document.getElementById('singleBreedSection').style.display = single ? 'block' : 'none';
      document.getElementById('crossBreedSection').style.display = single ? 'none' : 'block';
    }

    document.getElementById('breedIdentification').addEventListener('change', function () {
      toggleBreed();
      if (this.value === 'single') {
        document.getElementById('crossBreed1').value = '';
        document.getElementById('crossBreed1Percentage').value = '';
        document.getElementById('crossBreed2').value = '';
        document.getElementById('crossBreed2Percentage').value = '';
      } else {
        document.getElementById('breedSelect').value = '';
      }
    });
    toggleBreed();

    RwandaAddress.attach(document.getElementById('createLocation'), {
      district: @json(old('district')), sector: @json(old('sector')),
      cell: @json(old('cell')), village: @json(old('village'))
    });
    $(document).on('click', '.btn-edit-cow', function () {
      var eartagno = $(this).data('eartagno');
      postJson(@json(route('registration.details')), { eartagno: eartagno }).then(function (cow) {
        if (!cow) { return; }
        var map = {
          name: 'Name', gender: 'Gender', breed: 'Breed', cross_breed1: 'Cross_breed1', cross_breed2: 'Cross_breed2',
          breed_percentage: 'Breed_Percentage', dob: 'Dob', number_calving: 'Number_Calving', owner_names: 'Owner_names',
          national_id: 'National_Id', phone: 'Phone'
        };
        $('#eartagno').val(cow.Eartagno);
        Object.keys(map).forEach(function (field) { $('#edit_' + field).val(cow[map[field]]); });
        RwandaAddress.attach(document.getElementById('editLocation'), {
          district: cow.District, sector: cow.Sector, cell: cow.Cell, village: cow.Village
        });
        $('#editForm').attr('action', @json(url('registration')) + '/' + cow.Eartagno);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
      });
    });

    $(document).on('submit', '.delete-cow-form', function (event) {
      event.preventDefault();
      var form = this;
      swal({
        title: 'Are you sure?',
        text: 'This cow record will be deleted.',
        icon: 'warning',
        buttons: true,
        dangerMode: true
      }).then(function (confirmed) {
        if (confirmed) { form.submit(); }
      });
    });

    @if ($searchResults !== null)
      bootstrap.Modal.getOrCreateInstance(document.getElementById('searchModal')).show();
      @if ($searchResults->isEmpty())
        swal({ title: 'Error!', text: 'Cow Eartagno Information Not Found', icon: 'warning' });
      @endif
    @endif
  })();
</script>
@endpush
