{{--
  Shared cow form fields (register + edit). Expects $breeds; optional $cow (Cow model) when editing.
--}}
@php
  $editing = isset($cow) && $cow;
  $sex = $sex ?? 'Female';
  $animal = $animal ?? 'Cow or Heifer';
  $fieldError = function ($f) use ($errors) { return $errors->has($f) ? 'is-invalid' : ''; };

  // Rebuild the breed picker from the stored text, e.g. "Ankole (50%)" + "Jersey (30%), Fresian (20%)".
  $crossDefault = [['breed' => '', 'percentage' => ''], ['breed' => '', 'percentage' => '']];
  $identification = 'single';
  if ($editing) {
      preg_match_all('/([^,()]+?)\s*\((\d+)%\)/', ($cow->Cross_breed1 ?? '').', '.($cow->Cross_breed2 ?? ''), $m, PREG_SET_ORDER);
      $parsed = array_map(fn ($x) => ['breed' => trim($x[1]), 'percentage' => $x[2]], $m);
      if (count($parsed) >= 2) {
          $identification = 'cross';
          $crossDefault = $parsed;
      }
  }
  $d = [
      'cow_name' => $editing ? $cow->Name : null,
      'cow_eartag' => $editing ? $cow->Eartagno : null,
      'gender' => $sex,
      'dob' => $editing ? $cow->Dob : null,
      'calves' => $editing ? $cow->Number_Calving : null,
      'owner_name' => $editing ? $cow->Owner_names : null,
      'nid' => $editing ? $cow->National_Id : null,
      'Phone' => $editing ? $cow->Phone : null,
      'breed' => $editing && $identification === 'single' ? $cow->Breed : null,
  ];
@endphp

    {{-- Cow info --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="reg-card-title"><i class="bi bi-info-circle"></i> {{ $animal }} Information</div>
          <p class="text-muted small">Provide below information related to the {{ strtolower($animal) }}</p>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="cow_name" class="form-label">Name</label>
              <input type="text" class="form-control {{ $fieldError('cow_name') }}" name="cow_name" id="cow_name" value="{{ old('cow_name', $d['cow_name']) }}" placeholder="EarTag Number/Iherena" maxlength="20" />
              @error('cow_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="cow_eartag" class="form-label">EarTag Number</label>
              <input type="text" maxlength="30" pattern="[A-Za-z0-9]+" title="Letters and digits only" class="form-control {{ $fieldError('cow_eartag') }}" name="cow_eartag" id="cow_eartag" @disabled($editing) value="{{ old('cow_eartag', $d['cow_eartag']) }}" placeholder="EarTag Number/Iherena" />
              @error('cow_eartag') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label d-block">Gender</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" checked="checked" onclick="return false;" name="gender" value="{{ $sex }}" id="genderSex">
                <label class="form-check-label" for="genderSex">{{ $sex }}</label>
              </div>
              @error('gender') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="dob" class="form-label">Date of Birth</label>
              <input type="date" class="form-control {{ $fieldError('dob') }}" name="dob" value="{{ old('dob', $d['dob']) }}" placeholder="Date of Birth/Igihe yavukiye" id="dob" />
              @error('dob') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="calves" class="form-label">Number of Calving</label>
              <input type="number" class="form-control {{ $fieldError('calves') }}" name="calves" id="calves" value="{{ old('calves', $d['calves']) }}" placeholder="calves/Inyana ifite" />
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
                <option value="single" @selected(old('breed_identification', $identification) === 'single')>Single Breed</option>
                <option value="cross" @selected(old('breed_identification', $identification) === 'cross')>Cross Breed</option>
              </select>
              @error('breed_identification') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>

          <div id="singleBreedSection" class="mt-3">
            <label for="breedSelect" class="form-label">Breed</label>
            <select class="form-select {{ $fieldError('breed') }}" name="breed" id="breedSelect">
              <option value="">-----Choose Breed---------</option>
              @foreach ($breeds as $breed)
                <option value="{{ $breed }}" @selected(old('breed', $d['breed']) === $breed)>{{ $breed }}</option>
              @endforeach
            </select>
            @error('breed') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div id="crossBreedSection" class="mt-3" style="display: none;">
            <label class="form-label">Cross Breed <span class="text-muted small">(two or more breeds, total must be 100%)</span></label>
            @php
              $crossRows = old('cross_breeds', $crossDefault);
            @endphp
            <div id="crossRows" class="d-grid gap-2">
              @foreach ($crossRows as $i => $row)
                <div class="row g-2 align-items-center cross-row">
                  <div class="col-7 col-sm-8">
                    <select class="form-select {{ $fieldError("cross_breeds.$i.breed") }}" name="cross_breeds[{{ $i }}][breed]" aria-label="Breed" data-cross-field>
                      <option value="">-----Choose Breed---------</option>
                      @foreach ($breeds as $breed)
                        <option value="{{ $breed }}" @selected(($row['breed'] ?? '') === $breed)>{{ $breed }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-5 col-sm-3">
                    <div class="input-group">
                      <input type="number" class="form-control cross-pct {{ $fieldError("cross_breeds.$i.percentage") }}" name="cross_breeds[{{ $i }}][percentage]" value="{{ $row['percentage'] ?? '' }}" placeholder="..." min="1" max="100" aria-label="Percentage" data-cross-field>
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                  <div class="col-12 col-sm-1 text-sm-end">
                    <button type="button" class="btn btn-sm btn-outline-danger cross-remove" title="Remove breed" aria-label="Remove breed"><i class="bi bi-x-lg"></i></button>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="d-flex align-items-center justify-content-between mt-2">
              <button type="button" class="btn btn-sm btn-outline-primary" id="addCrossBreed"><i class="bi bi-plus-lg me-1"></i>Add breed</button>
              <span class="small fw-medium" id="crossTotal">Total: 0% / 100%</span>
            </div>
            @foreach (['cross_breeds'] as $f)
              @error($f) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            @endforeach
            @foreach ($errors->get('cross_breeds.*') as $messages)
              @foreach (array_unique($messages) as $message) <div class="invalid-feedback d-block">{{ $message }}</div> @endforeach
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
              <input type="text" class="form-control {{ $fieldError('owner_name') }}" name="owner_name" id="owner_name" value="{{ old('owner_name', $d['owner_name']) }}" placeholder="Owner_name/nyir'inka" maxlength="25" />
              @error('owner_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="nid" class="form-label">National ID</label>
              <input type="number" class="form-control {{ $fieldError('nid') }}" name="nid" id="nid" value="{{ old('nid', $d['nid']) }}" placeholder="National Id/Irangamuntu" />
              @error('nid') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="Phone" class="form-label">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1">+250</span>
                <input type="number" class="form-control {{ $fieldError('Phone') }}" placeholder="Phone" name="Phone" id="Phone" value="{{ old('Phone', $d['Phone']) }}">
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
          @include('partials.location-fields', ['id' => 'cowLocation', 'prefix' => 'loc_'])
        </div>
      </div>
    </div>

