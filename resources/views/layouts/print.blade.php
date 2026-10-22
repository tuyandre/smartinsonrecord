<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Print') · SMART INSEMrecord</title>
  <link rel="shortcut icon" href="{{ asset('admin-assets/images/favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <style>
    body { background: #eef2f8; }
    .sheet { max-width: 210mm; margin: 1rem auto; background: #fff; padding: 12mm; box-shadow: 0 2px 12px rgba(0,0,0,.12); }
    .section-title { background: #f1f4f9; border: 1px solid #dee2e6; border-radius: .3rem; padding: .25rem .6rem; font-weight: 600; margin: .6rem 0 .3rem; }
    .sheet .list-group-item { padding: .25rem .6rem; }
    .sheet table { font-size: .85rem; }
    @page { size: A4 portrait; margin: 12mm; }
    @media print {
      body { background: #fff; font-size: 10.5pt; }
      .sheet { margin: 0; padding: 0; box-shadow: none; max-width: none; }
      .no-print { display: none !important; }
      .sheet > * { break-inside: avoid; }
    }
  </style>
</head>
<body>
  <div class="container-fluid no-print py-2 d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    <a href="{{ $back }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  </div>

  <div class="sheet">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ asset('admin-assets/images/Coat.png') }}" alt="Logo" width="40" height="40">
      <div class="text-center fw-semibold">Huye District<br>Smart InsemRecord<br>Ngoma</div>
      <img src="{{ asset('admin-assets/images/RAB_Logo.png') }}" alt="Logo" height="40">
    </div>
    <hr class="my-2">
    <h2 class="text-center h4 mb-0">@yield('heading')</h2>
    <p class="text-center mb-2">on:&nbsp;&nbsp;{{ date('d F Y') }}</p>

    @yield('content')

    <div class="mt-4">
      <p class="mb-1">Done at Huye</p>
      <p class="mb-3">On {{ date('Y-m-d') }}</p>
      <p class="mb-0">Veterinary {{ auth()->user()->name }} signature &amp; stamp</p>
      <p class="mb-0">_________________________</p>
    </div>
  </div>

  <script>
    window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
  </script>
</body>
</html>
