@extends('layouts.app')

@section('title', 'Veterinarians · SMART INSEMrecord')
@section('page_title', 'Veterinarians')
@section('page_sub', 'National register of veterinarians (RCVD)')
@section('actions')
  @can('veterinarians.import')
    <a href="{{ route('veterinarians.import') }}" class="btn btn-outline-primary"><i class="bi bi-upload me-1"></i>Import</a>
  @endcan
  @can('veterinarians.create')
    <a href="{{ route('veterinarians.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Register Veterinarian</a>
  @endcan
@endsection

@section('content')
  @php
    $user = auth()->user();
    $canAct = $user->can('veterinarians.view') || $user->can('veterinarians.edit') || $user->can('veterinarians.delete') || $user->can('veterinarians.deactivate');
  @endphp

  <div class="text-muted small mb-3">
    <span class="badge text-bg-primary">{{ number_format($rows->count()) }}</span> {{ $rows->count() === 1 ? 'record' : 'records' }}
    · <span class="badge text-bg-success">{{ $rows->where('is_active', true)->count() }}</span> active
    · <span class="badge text-bg-secondary">{{ $rows->where('is_active', false)->count() }}</span> inactive
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle sir-table sir-actions-left" data-responsive="false" data-title="Veterinarians" data-order='[[@if ($canAct) 1 @else 0 @endif,"asc"]]' data-page-length="15">
          <thead>
            <tr>
              @if ($canAct)<th data-orderable="false" data-export="false" data-priority="1">Actions</th>@endif
              <th data-filter="text" data-priority="1">RCVD ID</th>
              <th data-filter="text">National ID</th>
              <th data-filter="text" data-priority="1">Names</th>
              <th>Date of birth</th>
              <th data-filter="select">District</th>
              <th data-filter="select">Sector</th>
              <th data-filter="select">Status</th>
              <th data-filter="select">Account</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $r)
              <tr>
                @if ($canAct)
                  <td class="text-nowrap">
                    <x-row-actions>
                      @can('veterinarians.view')
                        <li><a class="dropdown-item" href="{{ route('veterinarians.show', $r) }}"><i class="bi bi-eye me-2 text-secondary"></i>View</a></li>
                      @endcan
                      @can('veterinarians.edit')
                        <li><a class="dropdown-item" href="{{ route('veterinarians.edit', $r) }}"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit</a></li>
                      @endcan
                      @can('veterinarians.deactivate')
                        <li>
                          <form action="{{ route('veterinarians.toggle', $r) }}" method="POST" class="toggle-vet-form" data-name="{{ $r->names }}" data-active="{{ $r->is_active ? 1 : 0 }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="dropdown-item">
                              @if ($r->is_active)<i class="bi bi-slash-circle me-2 text-warning"></i>Deactivate @else<i class="bi bi-check-circle me-2 text-success"></i>Activate @endif
                            </button>
                          </form>
                        </li>
                      @endcan
                      @can('veterinarians.delete')
                        <li>
                          <form action="{{ route('veterinarians.destroy', $r) }}" method="POST" class="delete-vet-form" data-name="{{ $r->names }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                          </form>
                        </li>
                      @endcan
                    </x-row-actions>
                  </td>
                @endif
                <td class="fw-semibold">{{ $r->rcvd_id }}</td>
                <td>{{ $r->national_id ?: '—' }}</td>
                <td>{{ $r->names }}</td>
                <td class="text-nowrap">{{ $r->date_of_birth?->format('Y-m-d') ?? '—' }}</td>
                <td>{{ $r->district ?: '—' }}</td>
                <td>{{ $r->sector ?: '—' }}</td>
                <td><span class="badge {{ $r->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $r->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>{{ isset($accounts[strtolower($r->rcvd_id)]) ? 'Has account' : 'No account' }}</td>
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
    function confirmForm(selector, build) {
      $(document).on('submit', selector, function (event) {
        event.preventDefault();
        var form = this;
        var opts = build($(form));
        swal({ title: opts.title, text: opts.text, icon: 'warning', buttons: true, dangerMode: true })
          .then(function (ok) { if (ok) { form.submit(); } });
      });
    }
    confirmForm('.delete-vet-form', function ($f) { return { title: 'Are you sure?', text: $f.data('name') + ' will be removed from the register.' }; });
    confirmForm('.toggle-vet-form', function ($f) {
      return $f.data('active') ? { title: 'Deactivate?', text: $f.data('name') + ' will be marked inactive.' } : { title: 'Activate?', text: $f.data('name') + ' will be marked active again.' };
    });
  </script>
@endpush
