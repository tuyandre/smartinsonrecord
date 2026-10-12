{{--
  Cascading Rwanda address selects (province > district > sector > cell > village).
  Props: $id (container id), $prefix (id prefix), $values (selected names), $errors bag.
  Province is a UI filter only; district, sector, cell and village are submitted.
--}}
@php
  $id = $id ?? 'locationFields';
  $prefix = $prefix ?? '';
  $fieldError = fn ($f) => $errors->has($f) ? 'is-invalid' : '';
  $levels = ['province' => 'Province', 'district' => 'District', 'sector' => 'Sector', 'cell' => 'Cell', 'village' => 'Village'];
@endphp
<div class="row g-3" id="{{ $id }}" data-src="{{ route('locations') }}">
  @foreach ($levels as $level => $label)
    <div class="col-12 col-md-6">
      <label for="{{ $prefix.$level }}" class="form-label">{{ $label }}</label>
      <select class="form-select {{ $level === 'province' ? '' : $fieldError($level) }}" @if ($level !== 'province') name="{{ $level }}" @endif
              id="{{ $prefix.$level }}" data-level="{{ $level }}" data-placeholder="---Choose {{ $label }}---">
        <option value="--">---Choose {{ $label }}---</option>
      </select>
      @if ($level !== 'province') @error($level) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror @endif
    </div>
  @endforeach
</div>
