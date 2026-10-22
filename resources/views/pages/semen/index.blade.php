@extends('layouts.app')

@section('title', 'Semen List · SMART INSEMrecord')
@section('page_title', 'Semen List')
@section('page_sub', 'Semen information of registered bulls')
@section('actions')
  @can('semen.create')
    <a href="{{ route('semen.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Register Semen</a>
  @endcan
@endsection

@section('content')
  @php $canAct = auth()->user()->can('semen.view') || auth()->user()->can('semen.edit') || auth()->user()->can('semen.delete'); @endphp

  <div class="text-muted small mb-3">
    <span class="badge text-bg-primary">{{ number_format($rows->count()) }}</span> {{ $rows->count() === 1 ? 'record' : 'records' }}
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle sir-table sir-actions-left" data-responsive="false" data-title="Semen List" data-order='[[@if ($canAct) 1 @else 0 @endif,"asc"]]' data-page-length="15">
          <thead>
            <tr>
              @if ($canAct)<th data-orderable="false" data-export="false" data-priority="1">Actions</th>@endif
              <th data-filter="text" data-priority="1">Eartag</th>
              <th data-filter="text">Name</th>
              <th data-filter="select">Breed</th>
              <th>Dob</th>
              <th data-filter="text">International Code</th>
              <th>AIC No</th>
              <th>Bull No</th>
              <th data-type="num">Batches</th>
              <th>Semen Codes</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $r)
              <tr>
                @if ($canAct)
                  <td class="text-nowrap">
                    <x-row-actions>
                      @can('semen.view')
                        <li><a class="dropdown-item" href="{{ route('semen.show', $r) }}"><i class="bi bi-eye me-2 text-secondary"></i>View</a></li>
                      @endcan
                      @can('semen.edit')
                        <li><a class="dropdown-item" href="{{ route('semen.edit', $r) }}"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit</a></li>
                      @endcan
                      @can('semen.delete')
                        <li>
                          <form action="{{ route('semen.destroy', $r) }}" method="POST" class="delete-semen-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                          </form>
                        </li>
                      @endcan
                    </x-row-actions>
                  </td>
                @endif
                <td class="fw-semibold">{{ $r->bull_eartag }}</td>
                <td>{{ $r->bull?->Name ?? '—' }}</td>
                <td>{{ $r->bull?->Breed ?? '—' }}</td>
                <td>{{ $r->bull?->Dob ?? '—' }}</td>
                <td>{{ $r->international_code ?: '—' }}</td>
                <td>{{ $r->aic_no ?: '—' }}</td>
                <td>{{ $r->bull_no ?: '—' }}</td>
                <td>{{ $r->lots->count() }}</td>
                <td>{{ $r->lots->pluck('semen_code')->implode(', ') ?: '—' }}</td>
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
    $(document).on('submit', '.delete-semen-form', function (event) {
      event.preventDefault();
      var form = this;
      swal({ title: 'Are you sure?', text: 'This semen record will be deleted.', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (confirmed) { if (confirmed) { form.submit(); } });
    });
  </script>
@endpush
