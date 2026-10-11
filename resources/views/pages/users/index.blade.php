@extends('layouts.app')

@section('title', 'User Management · SMART INSEMrecord')
@section('page_title', 'User Management')
@section('page_sub', 'Manage who can sign in and what they can do.')

@section('actions')
  @can('roles.manage')
    <a href="{{ route('roles.index') }}" class="btn btn-outline-primary"><i class="bi bi-shield-lock me-1"></i> Roles &amp; Permissions</a>
  @endcan
  @can('users.create')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" data-mode="create">
      <i class="bi bi-person-plus me-1"></i> Add User
    </button>
  @endcan
@endsection

@push('head')
  <style>
    .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(29,111,220,.12); color: var(--sir-primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
  </style>
@endpush

@section('content')
  @php
    $roleClass = fn ($u) => $u->isSuperAdmin() ? 'text-bg-danger' : ($u->role ? 'text-bg-primary' : 'text-bg-secondary');
    $canManage = fn ($u) => in_array($u->id, $manageable, true);
  @endphp

  <div class="card">
    <div class="card-body">
      <table class="table table-hover align-middle sir-table" data-title="Users" data-order='[[1,"asc"]]'>
        <thead>
          <tr>
            <th data-type="num">#</th>
            <th data-priority="1" data-filter="text">Name</th>
            <th>RCVD ID</th>
            <th>Username</th>
            <th data-filter="text">Email</th>
            <th>Phone</th>
            <th data-filter="select">Role</th>
            <th class="text-end" data-priority="1" data-orderable="false" data-export="false">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $user)
            <tr>
              <td>{{ $user->id }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="user-avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                  <span>{{ $user->name }} @if ($user->is(auth()->user()))<span class="badge text-bg-success">You</span>@endif</span>
                </div>
              </td>
              <td>{{ $user->rcvd_id }}</td>
              <td>{{ $user->username }}</td>
              <td>{{ $user->email }}</td>
              <td>{{ $user->phone }}</td>
              <td><span class="badge {{ $roleClass($user) }}">{{ $user->roleLabel() }}</span></td>
              <td class="text-end">
                @if ($canManage($user))
                  <div class="table-actions justify-content-end">
                    @can('users.edit')
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#userModal" data-mode="edit"
                      data-action="{{ route('users.update', $user) }}"
                      data-name="{{ $user->name }}" data-rcvd="{{ $user->rcvd_id }}" data-username="{{ $user->username }}"
                      data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" data-role="{{ $user->role_id }}"><i class="bi bi-pencil-square"></i> Edit</button>
                    @endcan
                    @unless ($user->is(auth()->user()))
                      @can('users.delete')
                      <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline js-delete" data-name="{{ $user->name }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                      </form>
                      @endcan
                    @endunless
                  </div>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
      <form method="POST" id="userForm" class="modal-content" action="{{ route('users.store') }}">
        @csrf
        <input type="hidden" name="_method" id="userMethod" value="POST" disabled>
        <div class="modal-header">
          <h5 class="modal-title" id="userModalTitle"><i class="bi bi-person-plus me-1"></i> Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="u_name" class="form-label">Names</label>
              <input type="text" class="form-control" id="u_name" name="name" required>
              @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="u_rcvd" class="form-label">RCVD ID</label>
              <input type="text" class="form-control" id="u_rcvd" name="rcvd_id" required>
              @error('rcvd_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="u_username" class="form-label">Username</label>
              <input type="text" class="form-control" id="u_username" name="username">
              @error('username') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="u_email" class="form-label">Email</label>
              <input type="email" class="form-control" id="u_email" name="email" required>
              @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="u_phone" class="form-label">Phone</label>
              <input type="tel" class="form-control" id="u_phone" name="phone">
              @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="u_role" class="form-label">Role</label>
              <select class="form-select" id="u_role" name="role_id" required>
                @foreach ($roles as $role)
                  <option value="{{ $role->id }}">{{ $role->label }}</option>
                @endforeach
              </select>
              @error('role_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="u_password" class="form-label">Password <small class="text-muted" id="pwHint"></small></label>
              <input type="password" class="form-control" id="u_password" name="password" autocomplete="new-password">
              @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <label for="u_password_confirmation" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="u_password_confirmation" name="password_confirmation" autocomplete="new-password">
            </div>
          </div>
        </div>
        <div class="modal-footer flex-column-reverse flex-sm-row">
          <button type="button" class="btn btn-light w-100 w-sm-auto" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    var userModalEl = document.getElementById('userModal');

    userModalEl.addEventListener('show.bs.modal', function (event) {
      if (!event.relatedTarget) { return; } // reopened after a validation error: keep the submitted values
      var button = $(event.relatedTarget);
      var editing = button.data('mode') === 'edit';
      var form = document.getElementById('userForm');

      form.reset();
      form.action = editing ? button.data('action') : @json(route('users.store'));
      $('#userMethod').prop('disabled', !editing).val('PUT');
      $('#userModalTitle').html(editing ? '<i class="bi bi-pencil-square me-1"></i> Edit User' : '<i class="bi bi-person-plus me-1"></i> Add User');
      $('#u_password').prop('required', !editing);
      $('#pwHint').text(editing ? '(leave blank to keep current)' : '');

      if (editing) {
        $('#u_name').val(button.data('name'));
        $('#u_rcvd').val(button.data('rcvd'));
        $('#u_username').val(button.data('username'));
        $('#u_email').val(button.data('email'));
        $('#u_phone').val(button.data('phone'));
        $('#u_role').val(button.data('role'));
      }
    });

    $(document).on('submit', '.js-delete', function (e) {
      e.preventDefault();
      var form = this;
      swal({ title: 'Delete user?', text: 'Remove ' + $(form).data('name') + '?', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) { if (ok) { form.submit(); } });
    });

    @if ($errors->any() && old('rcvd_id'))
      $(function () { new bootstrap.Modal(userModalEl).show(); });
    @endif
  </script>
@endpush
