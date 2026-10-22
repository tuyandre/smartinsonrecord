@extends('layouts.app')

@section('title', 'Registered Bulls · SMART INSEMrecord')
@section('page_title', 'Registered Bulls')
@section('page_sub', 'All bulls in the system')
@section('actions')
  @can('bulls.create')
    <a href="{{ route('bulls.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Register Bull</a>
  @endcan
@endsection

@section('content')
  <div class="text-muted small mb-3">
    <span class="badge text-bg-primary">{{ number_format($rows->count()) }}</span> {{ $rows->count() === 1 ? 'record' : 'records' }}
  </div>
  @php
  $canAct = auth()->user()->can('bulls.view') || auth()->user()->can('bulls.edit') || auth()->user()->can('bulls.delete');
  /** Normalise a stored date string to ISO YYYY-MM-DD when possible (so the date filter works). */
  $iso = function ($d) {
      $d = trim((string) $d);
      if ($d === '') { return '—'; }
      if (preg_match('/^\d{4}-\d{2}-\d{2}/', $d)) { return substr($d, 0, 10); }
      if (preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{4})$#', $d, $m)) { return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]); }
      return $d;
  };
@endphp

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
      <table class="table table-hover align-middle sir-table sir-actions-left" data-responsive="false" data-title="Registered Bulls" data-order='[[@if ($canAct) 1 @else 0 @endif,"asc"]]' data-page-length="15">
        <thead>
          <tr>
            @if ($canAct)<th data-orderable="false" data-export="false" data-priority="1">Actions</th>@endif
            <th data-priority="1">Eartag</th>
            <th data-priority="1">Name</th>
            <th data-filter="select" data-priority="3">Gender</th>
            <th data-filter="select" data-priority="2">Breed</th>
            <th>Cross breeds</th>
            <th data-type="num">Breed %</th>
            <th data-filter="date" data-priority="4">DOB</th>
            <th data-type="num">Calvings</th>
            <th data-filter="text" data-priority="2">Owner</th>
            <th>National ID</th>
            <th>Phone</th>
            <th data-filter="select">District</th>
            <th data-filter="select">Sector</th>
            <th>Cell</th>
            <th>Village</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              @if ($canAct)
                <td class="text-nowrap">
                  <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for {{ $r->Name }}">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                      @can('bulls.view')
                        <li><a class="dropdown-item" href="{{ route('bulls.show', $r->Eartagno) }}"><i class="bi bi-eye me-2 text-secondary"></i>View</a></li>
                      @endcan
                      @can('bulls.edit')
                        <li><a class="dropdown-item" href="{{ route('bulls.edit', $r->Eartagno) }}"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit</a></li>
                      @endcan
                      @can('bulls.delete')
                        <li>
                          <form action="{{ route('bulls.destroy', $r->Eartagno) }}" method="POST" class="delete-bull-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                          </form>
                        </li>
                      @endcan
                    </ul>
                  </div>
                </td>
              @endif
              <td class="fw-semibold">{{ $r->Eartagno }}</td>
              <td>{{ $r->Name }}</td>
              <td>{{ $r->Gender }}</td>
              <td>{{ $r->Breed }}</td>
              <td>{{ collect([$r->Cross_breed1, $r->Cross_breed2])->filter()->implode(' / ') ?: '—' }}</td>
              <td>{{ $r->Breed_Percentage }}</td>
              <td class="text-nowrap">{{ $iso($r->Dob) }}</td>
              <td>{{ $r->Number_Calving }}</td>
              <td>{{ $r->Owner_names }}</td>
              <td>{{ $r->National_Id }}</td>
              <td>{{ $r->Phone }}</td>
              <td>{{ $r->District }}</td>
              <td>{{ $r->Sector }}</td>
              <td>{{ $r->Cell }}</td>
              <td>{{ $r->Village }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    // Fixed positioning keeps the menu from being clipped by the scrolling table wrapper.
    function fixMenus() {
      document.querySelectorAll('.sir-actions-left [data-bs-toggle="dropdown"]').forEach(function (el) {
        bootstrap.Dropdown.getOrCreateInstance(el, { popperConfig: { strategy: 'fixed' } });
      });
    }
    fixMenus();
    $('.sir-actions-left').on('draw.dt init.dt', fixMenus);

    $(document).on('submit', '.delete-bull-form', function (event) {
      event.preventDefault();
      var form = this;
      swal({ title: 'Are you sure?', text: 'This bull record will be deleted.', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (confirmed) { if (confirmed) { form.submit(); } });
    });
  </script>
@endpush
