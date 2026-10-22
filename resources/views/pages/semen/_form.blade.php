{{--
  Shared semen form fields (register + edit). Expects $maxLots; $bulls when registering, $semen (model) when editing.
--}}
@php
  $editing = isset($semen) && $semen;
  $fieldError = fn ($f) => $errors->has($f) ? 'is-invalid' : '';
  $lots = old('lots', $editing
      ? $semen->lots->map(fn ($l) => ['registration_no' => $l->registration_no, 'freezing_date' => $l->freezing_date->format('Y-m-d'), 'semen_code' => $l->semen_code])->all()
      : [['registration_no' => '', 'freezing_date' => '', 'semen_code' => '']]);
  $lots = array_values($lots);
@endphp

<div class="col-12 col-lg-5">
  <div class="card h-100">
    <div class="card-body">
      <div class="reg-card-title"><i class="bi bi-gender-male"></i> Bull</div>
      <p class="text-muted small">Semen is recorded against a registered bull.</p>
      <div class="row g-3">
        <div class="col-12">
          <label for="bull_eartag" class="form-label">Bull</label>
          @if ($editing)
            <input type="text" class="form-control" value="{{ $semen->bull_eartag }} ({{ $semen->bull?->Name }})" disabled>
          @else
            <select class="form-select {{ $fieldError('bull_eartag') }}" name="bull_eartag" id="bull_eartag" required>
              <option value="">------Select Bull----------</option>
              @foreach ($bulls as $bull)
                <option value="{{ $bull->Eartagno }}" @selected(old('bull_eartag') === (string) $bull->Eartagno)>{{ $bull->Eartagno }} ({{ $bull->Name }})</option>
              @endforeach
            </select>
            @error('bull_eartag') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            @if ($bulls->isEmpty())
              <div class="form-text">Every registered bull already has semen information, or no bull is registered yet.</div>
            @endif
          @endif
        </div>
        @foreach (['international_code' => 'International Code', 'aic_no' => 'AIC No', 'bull_no' => 'Bull No'] as $field => $label)
          <div class="col-12 col-md-4 col-lg-12 col-xl-4">
            <label for="{{ $field }}" class="form-label">{{ $label }}</label>
            <input type="text" maxlength="30" class="form-control {{ $fieldError($field) }}" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $semen->$field ?? '') }}">
            @error($field) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<div class="col-12 col-lg-7">
  <div class="card h-100">
    <div class="card-body">
      <div class="reg-card-title"><i class="bi bi-droplet-half"></i> Semen batches</div>
      <p class="text-muted small">Up to {{ $maxLots }} frozen batches, each with its own semen code.</p>

      <div id="lotRows" class="d-grid gap-2">
        @foreach ($lots as $i => $lot)
          <div class="row g-2 align-items-end lot-row">
            <div class="col-12 col-sm-4">
              <label class="form-label small mb-1">Registration No</label>
              <input type="text" maxlength="30" class="form-control {{ $fieldError("lots.$i.registration_no") }}" name="lots[{{ $i }}][registration_no]" value="{{ $lot['registration_no'] ?? '' }}" required>
            </div>
            <div class="col-6 col-sm-3">
              <label class="form-label small mb-1">Freezing Date</label>
              <input type="date" class="form-control {{ $fieldError("lots.$i.freezing_date") }}" name="lots[{{ $i }}][freezing_date]" value="{{ $lot['freezing_date'] ?? '' }}" required>
            </div>
            <div class="col-6 col-sm-3">
              <label class="form-label small mb-1">Semen Code</label>
              <input type="text" maxlength="20" class="form-control {{ $fieldError("lots.$i.semen_code") }}" name="lots[{{ $i }}][semen_code]" value="{{ $lot['semen_code'] ?? '' }}" required>
            </div>
            <div class="col-12 col-sm-2 text-sm-end">
              <button type="button" class="btn btn-sm btn-outline-danger lot-remove" title="Remove batch" aria-label="Remove batch"><i class="bi bi-x-lg"></i></button>
            </div>
          </div>
        @endforeach
      </div>

      @error('lots') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      @foreach ($errors->get('lots.*') as $messages)
        @foreach (array_unique($messages) as $message) <div class="invalid-feedback d-block">{{ $message }}</div> @endforeach
      @endforeach

      <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addLot"><i class="bi bi-plus-lg me-1"></i>Add batch</button>
    </div>
  </div>
</div>
