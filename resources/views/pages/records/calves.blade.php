@extends('layouts.app')

@section('title', 'Calves · SMART INSEMrecord')
@section('page_title', 'Calves')
@section('page_sub', 'Recorded data · one row per calf born')
@section('actions')
  @can('calves.create')
    <a href="{{ route('newborn.index') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Record Newborn</a>
  @endcan
@endsection


@section('content')
  @include('pages.records._toolbar')
  @php
  $canAct = auth()->user()->can('calves.view') || auth()->user()->can('calves.edit') || auth()->user()->can('calves.delete');
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
      <table class="table table-hover align-middle sir-table sir-actions-left" data-responsive="false" data-title="Calves" data-order='[[@if ($canAct) 1 @else 0 @endif,"asc"]]' data-page-length="15">
        <thead>
          <tr>
            @if ($canAct)<th data-orderable="false" data-export="false" data-priority="1">Actions</th>@endif
            <th data-filter="text" data-priority="1">Mother</th>
            <th data-priority="1">Calf</th>
            <th data-filter="select" data-priority="3">Breed</th>
            <th data-filter="date" data-priority="2">DOB</th>
            <th data-type="num">Weight (kg)</th>
            <th data-type="num">Birth times</th>
            <th data-filter="select" data-priority="4">Observation</th>
            <th data-filter="select">Disability</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              @if ($canAct)
                <td class="text-nowrap">
                  <x-row-actions>
                    @can('calves.view')
                      <li><a class="dropdown-item" href="{{ route('calves.show', [$r->mother_tag, $r->calf_no]) }}"><i class="bi bi-eye me-2 text-secondary"></i>View</a></li>
                    @endcan
                    @can('calves.view')
                      <li><a class="dropdown-item" href="{{ route('print.calf', [$r->mother_tag, $r->calf_no]) }}" target="_blank" rel="noopener"><i class="bi bi-printer me-2 text-secondary"></i>Print</a></li>
                    @endcan
                    @can('calves.edit')
                      <li><a class="dropdown-item" href="{{ route('calves.edit', [$r->mother_tag, $r->calf_no]) }}"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit</a></li>
                    @endcan
                    @can('calves.delete')
                      <li>
                        <form action="{{ route('calves.destroy', [$r->mother_tag, $r->calf_no]) }}" method="POST" class="delete-calf-form">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                        </form>
                      </li>
                    @endcan
                  </x-row-actions>
                </td>
              @endif
              <td><span class="fw-semibold">{{ $r->mother_tag }}</span> <span class="small text-muted">{{ $r->mother_name ?? '' }}</span></td>
              <td><span class="fw-semibold">{{ $r->calf_tag }}</span> <span class="small text-muted">Calf {{ $r->calf_no }}</span></td>
              <td>{{ $r->breed ?: '—' }}</td>
              <td class="text-nowrap">{{ $iso($r->dob) }}</td>
              <td>{{ $r->weight }}</td>
              <td>{{ $r->birth_times }}</td>
              <td>{{ $r->observation ?: '—' }}</td>
              <td>{{ $r->disability ?: '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(document).on('submit', '.delete-calf-form', function (event) {
      event.preventDefault();
      var form = this;
      swal({ title: 'Are you sure?', text: 'This birth record will be deleted. The calf stays in its registered list.', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (confirmed) { if (confirmed) { form.submit(); } });
    });
  </script>
@endpush
