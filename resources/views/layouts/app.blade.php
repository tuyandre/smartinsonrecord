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
  <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/select2/select2-bootstrap-5-theme.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('head')
</head>
<body>
  @php
    $user = auth()->user();
    // Entry = single link [route, label, icon, activePattern]
    //       or a feature group ['group', label, icon, [[route, label, activePattern], ...]] (create + list together)
    $nav = [
      'Main' => [
        ['dashboard', 'Dashboard', 'bi-speedometer2', 'dashboard'],
      ],
      'Features' => [
        ['group', 'Cows', 'bi-tag', [
          ['registration.index', 'Register Cow', 'registration.*'],
          ['records.cows', 'Registered Cows', 'records.cows'],
        ]],
        ['group', 'Bulls', 'bi-gender-male', [
          ['bulls.create', 'Register Bull', 'bulls.create'],
          ['bulls.index', 'Registered Bulls', 'bulls.index'],
        ]],
        ['group', 'Semen', 'bi-droplet-half', [
          ['semen.create', 'Register Semen', 'semen.create'],
          ['semen.index', 'Semen List', 'semen.index'],
        ]],
        ['group', 'Inseminations', 'bi-heart-pulse', [
          ['insemination.index', 'New Insemination', 'insemination.*'],
          ['records.inseminations', 'Insemination List', 'records.inseminations'],
        ]],
        ['group', 'Calves', 'bi-emoji-smile', [
          ['newborn.index', 'Register Calf', 'newborn.*'],
          ['records.calves', 'Calf List', 'records.calves'],
        ]],
        ['group', 'PD', 'bi-clipboard2-pulse', [
          ['pd.index', 'New PD Info', 'pd.*'],
          ['records.pd', 'PD Records', 'records.pd'],
        ]],
      ],
      'Analysis' => [
        ['pedigree.index', 'Pedigree', 'bi-diagram-3', 'pedigree.*'],
        ['reporting.index', 'Reports', 'bi-file-earmark-bar-graph', 'reporting.*'],
      ],
      'Administration' => [
        ['group', 'Veterinarians', 'bi-person-badge', [
          ['veterinarians.index', 'Veterinarians List', 'veterinarians.index'],
          ['veterinarians.create', 'Register Veterinarian', 'veterinarians.create'],
          ['veterinarians.import', 'Import List', 'veterinarians.import'],
        ]],
        ['users.index', 'User Management', 'bi-people', 'users.*'],
        ['roles.index', 'Roles &amp; Permissions', 'bi-shield-lock', 'roles.*'],
      ],
    ];

    // each link is shown only when the user holds the permission its route requires
    $can = function ($route) use ($user) {
      $needs = \App\Support\PermissionCatalog::ROUTES[$route] ?? null;

      return ! $needs || $user->hasPermission($needs);
    };
    $nav = collect($nav)
      ->map(function ($items) use ($can) {
        $out = [];
        foreach ($items as $item) {
          if ($item[0] === 'group') {
            $subs = array_values(array_filter($item[3], fn ($sub) => $can($sub[0])));
            if ($subs) {
              $out[] = ['group', $item[1], $item[2], $subs];
            }
          } elseif ($can($item[0])) {
            $out[] = $item;
          }
        }

        return $out;
      })
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
            @foreach ($items as $item)
              @if ($item[0] === 'group')
                @php
                  $open = collect($item[3])->contains(fn ($sub) => request()->routeIs($sub[2]));
                  $gid = 'nav-'.\Illuminate\Support\Str::slug($item[1]);
                @endphp
                <li class="nav-item">
                  <a class="nav-link nav-group {{ $open ? 'active-group' : '' }}" data-bs-toggle="collapse" href="#{{ $gid }}" role="button" aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $gid }}">
                    <i class="bi {{ $item[2] }}"></i><span>{{ $item[1] }}</span><i class="bi bi-chevron-down chev ms-auto"></i>
                  </a>
                  <ul class="nav flex-column sir-subnav collapse {{ $open ? 'show' : '' }}" id="{{ $gid }}">
                    @foreach ($item[3] as [$routeName, $label, $pattern])
                      <li><a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($routeName) }}"><span>{{ $label }}</span></a></li>
                    @endforeach
                  </ul>
                </li>
              @else
                @php [$routeName, $label, $icon, $pattern] = $item; @endphp
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($routeName) }}">
                    <i class="bi {{ $icon }}"></i><span>{!! $label !!}</span>
                  </a>
                </li>
              @endif
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
        <div class="dropdown">
          <button class="btn btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
            <i class="bi bi-bell"></i>
            @if (count($notifications))
              <span class="badge rounded-pill text-bg-success badge-dot">{{ count($notifications) }}</span>
            @endif
          </button>
          <ul class="dropdown-menu dropdown-menu-end p-0" style="width:340px;max-height:380px;overflow-y:auto">
            <li class="px-3 py-2 fw-medium border-bottom">Notifications</li>
            @forelse (array_slice($notifications, 0, 15) as $n)
              <li>
                <a class="dropdown-item d-flex gap-2 py-2 text-wrap" href="{{ $n['url'] }}">
                  <i class="bi {{ $n['icon'] }} mt-1"></i>
                  <span class="small">{{ $n['text'] }}<br><span class="text-muted">{{ \Carbon\Carbon::createFromTimestamp($n['time'])->diffForHumans() }}</span></span>
                </a>
              </li>
            @empty
              <li class="px-3 py-3 small text-muted">No new notifications</li>
            @endforelse
          </ul>
        </div>
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
  <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
  <script src="{{ asset('js/sir-datatable.js') }}"></script>
  <script src="{{ asset('js/sir-select2.js') }}"></script>
  <script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
  </script>
  @include('partials.swal')
  @stack('scripts')
</body>
</html>
