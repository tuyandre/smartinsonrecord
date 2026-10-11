<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'SMART INSEMrecord')</title>
  <link rel="shortcut icon" href="{{ asset('admin-assets/images/favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/datatables/datatables.bundle.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('head')
</head>
<body>
  @php
    $user = auth()->user();
    $nav = [
      'Main' => [
        ['dashboard', 'Dashboard', 'bi-speedometer2', 'dashboard'],
      ],
      'Data Entry' => [
        ['registration.index', 'Cow Registration', 'bi-plus-circle', 'registration.*'],
        ['insemination.index', 'Cow Insemination', 'bi-heart-pulse', 'insemination.*'],
        ['newborn.index', 'New Born Calf', 'bi-emoji-smile', 'newborn.*'],
        ['pd.index', 'PD Info', 'bi-clipboard2-pulse', 'pd.*'],
      ],
      'Recorded Data' => [
        ['records.cows', 'Registered Cows', 'bi-list-ul', 'records.cows'],
        ['records.inseminations', 'Inseminations', 'bi-list-check', 'records.inseminations'],
        ['records.calves', 'Calves', 'bi-journal-text', 'records.calves'],
        ['records.pd', 'PD Records', 'bi-journal-medical', 'records.pd'],
      ],
      'Analysis' => [
        ['pedigree.index', 'Pedigree', 'bi-diagram-3', 'pedigree.*'],
        ['reporting.index', 'Semen Info &amp; Reporting', 'bi-file-earmark-bar-graph', 'reporting.*'],
      ],
    ];
    $nav['Administration'] = [
      ['users.index', 'User Management', 'bi-people', 'users.*'],
      ['roles.index', 'Roles &amp; Permissions', 'bi-shield-lock', 'roles.*'],
    ];

    // each menu item is shown only when the user holds the permission its route requires
    $nav = collect($nav)
      ->map(fn ($items) => array_values(array_filter($items, function ($item) use ($user) {
        $needs = \App\Support\PermissionCatalog::ROUTES[$item[0]] ?? null;

        return ! $needs || $user->hasPermission($needs);
      })))
      ->filter()
      ->all();
  @endphp

  <aside class="sir-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Main menu">
    <div class="sir-brand">
      <a href="{{ route('dashboard') }}"><img src="{{ asset('admin-assets/images/logo.png') }}" alt="SMART INSEMrecord"></a>
      <button type="button" class="btn-close btn-close-white d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-block p-0 pb-4">
      <div class="sir-profile">
        <img src="{{ asset('admin-assets/images/faces/avatar.png') }}" alt="">
        <div class="overflow-hidden">
          <div class="name text-truncate">{{ $user->name }}</div>
          <div class="role">{{ $user->roleLabel() }}</div>
        </div>
      </div>
      <nav class="sir-nav">
        @foreach ($nav as $group => $items)
          <div class="sir-nav-title">{{ $group }}</div>
          <ul class="nav flex-column">
            @foreach ($items as [$routeName, $label, $icon, $pattern])
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($routeName) }}">
                  <i class="bi {{ $icon }}"></i><span>{!! $label !!}</span>
                </a>
              </li>
            @endforeach
          </ul>
        @endforeach
      </nav>
    </div>
  </aside>

  <div class="sir-main d-flex flex-column min-vh-100">
    <header class="sir-topbar">
      <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Open menu">
        <i class="bi bi-list fs-5"></i>
      </button>

      @can('dashboard.view')
        <form class="search d-none d-sm-flex" method="GET" action="{{ route('dashboard') }}" role="search">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" class="form-control" placeholder="Search cow by Eartagno or National ID" name="pattern" value="{{ request('pattern') }}">
            <button class="btn btn-primary" type="submit">Search</button>
          </div>
        </form>
      @endcan

      <div class="ms-auto d-flex align-items-center gap-2">
        @can('dashboard.view')
          <a class="btn btn-light d-sm-none" href="{{ route('dashboard') }}" aria-label="Search"><i class="bi bi-search"></i></a>
        @endcan
        <span class="d-none d-md-inline text-muted small">{{ $user->rcvd_id }}</span>
        @can('reports.view')
          <a class="btn btn-light position-relative" href="{{ route('reporting.index') }}" title="Available bulls">
            <i class="bi bi-bell"></i>
            <span class="badge rounded-pill text-bg-success badge-dot">{{ app(\App\Services\BullsApi::class)->count() }}</span>
          </a>
        @endcan
        <div class="dropdown">
          <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
            <img class="sir-avatar" src="{{ asset('admin-assets/images/faces/avatar1.png') }}" alt="">
            <span class="d-none d-md-inline">{{ $user->name }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="px-3 py-2">
              <div class="fw-medium">{{ $user->name }}</div>
              <div class="small text-muted">{{ $user->email }}</div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <main class="sir-content flex-grow-1">
      @hasSection('page_title')
        <div class="page-head">
          <div>
            <h1>@yield('page_title')</h1>
            @hasSection('page_sub')<div class="sub">@yield('page_sub')</div>@endif
          </div>
          @hasSection('actions')<div class="d-flex flex-wrap gap-2">@yield('actions')</div>@endif
        </div>
      @endif
      @yield('content')
    </main>

    <footer class="sir-footer d-flex flex-wrap justify-content-between gap-2">
      <span>© {{ date('Y') }} SMART INSEMrecord</span>
      <span>RAB Rwanda · SMART INSEMrecord</span>
    </footer>
  </div>

  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/sweetalert/sweetalert.min.js') }}"></script>
  <script src="{{ asset('vendor/datatables/datatables.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/daterangepicker/daterangepicker.bundle.min.js') }}"></script>
  <script src="{{ asset('js/sir-datatable.js') }}"></script>
  <script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
  </script>
  @include('partials.swal')
  @stack('scripts')
</body>
</html>
