@props(['label' => 'Actions'])
{{-- Row actions menu: put <li> items in the slot. Placed in the first column of a .sir-actions-left table. --}}
<div class="dropdown">
  <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ $label }}">
    <i class="bi bi-three-dots-vertical"></i>
  </button>
  <ul class="dropdown-menu">{{ $slot }}</ul>
</div>
