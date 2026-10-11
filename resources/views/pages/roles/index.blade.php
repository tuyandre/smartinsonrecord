@extends('layouts.app')

@section('title', 'Roles & Permissions · SMART INSEMrecord')
@section('page_title', 'Roles & Permissions')
@section('page_sub', 'Create roles, choose what each role can do, then assign them to users.')

@section('actions')
  <a href="{{ route('users.index') }}" class="btn btn-outline-primary"><i class="bi bi-people me-1"></i> Users</a>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal" data-mode="create">
    <i class="bi bi-shield-plus me-1"></i> New Role
  </button>
@endsection

@push('head')
  <style>
    .perm-group { border: 1px solid var(--sir-border); border-radius: .75rem; height: 100%; }
    .perm-group .perm-head { background: #f8faff; border-bottom: 1px solid var(--sir-border); border-radius: .75rem .75rem 0 0; padding: .5rem .85rem; }
    .perm-group .perm-body { padding: .6rem .85rem; }
  </style>
@endpush

@section('content')
  <div class="card">
    <div class="card-body">
      <table class="table table-hover align-middle sir-table" data-title="Roles" data-order='[[0,"asc"]]' data-buttons="false">
        <thead>
          <tr>
            <th data-priority="1">Role</th>
            <th>Description</th>
            <th data-type="num">Users</th>
            <th data-type="num">Permissions</th>
            <th class="text-end" data-priority="1" data-orderable="false" data-export="false">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($roles as $role)
            @php
              $payload = [
                'action' => route('roles.update', $role),
                'label' => $role->label,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('id')->all(),
              ];
            @endphp
            <tr>
              <td>
                <span class="fw-semibold">{{ $role->label }}</span>
                @if ($role->isSuperAdmin()) <span class="badge text-bg-danger">Locked</span>
                @elseif ($role->is_system) <span class="badge text-bg-secondary">System</span> @endif
              </td>
              <td class="text-muted">{{ $role->description ?: '—' }}</td>
              <td>{{ $role->users_count }}</td>
              <td>
                @if ($role->isSuperAdmin())
                  All ({{ $permissions->flatten()->count() }})
                @else
                  {{ $role->permissions->count() }} / {{ $permissions->flatten()->count() }}
                @endif
              </td>
              <td class="text-end">
                <div class="table-actions justify-content-end">
                  @if (in_array($role->id, $editable, true))
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#roleModal" data-mode="edit"
                      data-role="{{ json_encode($payload) }}"><i class="bi bi-pencil-square"></i> Edit</button>
                    @unless ($role->is_system)
                      <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline js-delete-role" data-name="{{ $role->label }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                      </form>
                    @endunless
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
      <form method="POST" id="roleForm" class="modal-content" action="{{ route('roles.store') }}">
        @csrf
        <input type="hidden" name="_method" id="roleMethod" value="PUT" disabled>
        <div class="modal-header">
          <h5 class="modal-title" id="roleModalTitle"><i class="bi bi-shield-plus me-1"></i> New Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-5">
              <label for="r_label" class="form-label">Role name</label>
              <input type="text" class="form-control" id="r_label" name="label" maxlength="100" required value="{{ old('label') }}">
              @error('label') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-7">
              <label for="r_description" class="form-label">Description</label>
              <input type="text" class="form-control" id="r_description" name="description" maxlength="255" value="{{ old('description') }}">
              @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h6 class="mb-0">Permissions <span class="badge rounded-pill text-bg-primary" id="permCount">0</span></h6>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="permAll">Select all</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="permNone">Clear</button>
            </div>
          </div>
          @error('permissions') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

          <div class="row g-3">
            @foreach ($permissions as $group => $items)
              <div class="col-12 col-md-6 col-xl-4">
                <div class="perm-group">
                  <div class="perm-head form-check mb-0">
                    <input class="form-check-input perm-group-toggle" type="checkbox" id="pg_{{ $loop->index }}">
                    <label class="form-check-label fw-semibold" for="pg_{{ $loop->index }}">{{ $group }}</label>
                  </div>
                  <div class="perm-body">
                    @foreach ($items as $permission)
                      @php $can = in_array($permission->id, $grantable, true); @endphp
                      <div class="form-check mb-1">
                        <input class="form-check-input perm-box" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="p_{{ $permission->id }}" @disabled(! $can)>
                        <label class="form-check-label {{ $can ? '' : 'text-muted' }}" for="p_{{ $permission->id }}" @if(! $can) title="You do not hold this permission" @endif>
                          {{ $permission->label }}
                          <small class="text-muted d-block">{{ $permission->name }}</small>
                        </label>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
        <div class="modal-footer flex-column-reverse flex-sm-row">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Save Role</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    var roleModalEl = document.getElementById('roleModal');
    var $boxes = $('.perm-box');

    function refreshPerms() {
      var enabled = $boxes.filter(':not(:disabled)');
      $('#permCount').text($boxes.filter(':checked').length + ' / ' + $boxes.length);
      $('.perm-group-toggle').each(function () {
        var inGroup = $(this).closest('.perm-group').find('.perm-box:not(:disabled)');
        var on = inGroup.filter(':checked').length;
        this.checked = inGroup.length > 0 && on === inGroup.length;
        this.indeterminate = on > 0 && on < inGroup.length;
        this.disabled = inGroup.length === 0;
      });
    }

    $boxes.on('change', refreshPerms);
    $('.perm-group-toggle').on('change', function () {
      $(this).closest('.perm-group').find('.perm-box:not(:disabled)').prop('checked', this.checked);
      refreshPerms();
    });
    $('#permAll').on('click', function () { $boxes.filter(':not(:disabled)').prop('checked', true); refreshPerms(); });
    $('#permNone').on('click', function () { $boxes.filter(':not(:disabled)').prop('checked', false); refreshPerms(); });

    roleModalEl.addEventListener('show.bs.modal', function (event) {
      if (!event.relatedTarget) { refreshPerms(); return; } // reopened after a validation error: keep submitted values
      var button = $(event.relatedTarget);
      var editing = button.data('mode') === 'edit';
      var form = document.getElementById('roleForm');

      form.reset();
      $boxes.prop('checked', false);
      form.action = @json(route('roles.store'));
      $('#roleMethod').prop('disabled', true);
      $('#roleModalTitle').html('<i class="bi bi-shield-plus me-1"></i> New Role');

      if (editing) {
        var r = button.data('role');
        form.action = r.action;
        $('#roleMethod').prop('disabled', false);
        $('#roleModalTitle').html('<i class="bi bi-pencil-square me-1"></i> Edit Role');
        $('#r_label').val(r.label);
        $('#r_description').val(r.description || '');
        $boxes.each(function () { this.checked = r.permissions.indexOf(parseInt(this.value, 10)) !== -1; });
      }
      refreshPerms();
    });

    $(document).on('submit', '.js-delete-role', function (e) {
      e.preventDefault();
      var form = this;
      swal({ title: 'Delete role?', text: 'Remove the role "' + $(form).data('name') + '"?', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) { if (ok) { form.submit(); } });
    });

    @if ($errors->any() && old('label') !== null)
      $(function () { new bootstrap.Modal(roleModalEl).show(); });
    @endif
    refreshPerms();
  </script>
@endpush
