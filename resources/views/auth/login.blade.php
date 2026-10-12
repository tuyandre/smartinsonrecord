<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · SMART INSEMrecord</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/sweetalert/sweetalert.min.js') }}"></script>
  <style>
    .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem 1rem; background: linear-gradient(160deg, #e8f0fc 0%, var(--sir-bg) 55%); }
    .auth-card { width: 100%; max-width: 440px; border: 1px solid var(--sir-border); border-radius: 1rem; box-shadow: 0 10px 30px rgba(18,74,156,.08); }
    .auth-logo { max-width: 160px; width: 100%; height: auto; }
  </style>
</head>
<body>
  <main class="auth-wrap">
    <div class="w-100" style="max-width:440px">
      <div class="text-center mb-4">
        <img class="auth-logo" src="{{ asset('landing/img/hero-img.png') }}" alt="SMART INSEMrecord logo">
      </div>

      <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
          <h1 class="h4 fw-bold text-center mb-1">Welcome back</h1>
          <p class="text-center text-muted mb-4">Sign in with your username, email, phone or RCVD ID</p>

          <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="login" id="login" value="{{ old('login') }}" placeholder="Username, email or phone" title="Provide username, email or phone number" autofocus>
              <label for="login">Username, Email or Phone</label>
              @error('login') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" name="password" id="password" value="" placeholder="Password" title="Provide Password">
              <label for="password">Password</label>
              @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" value="" name="remember_me" id="remember_me">
              <label class="form-check-label text-secondary" for="remember_me">Keep me logged in</label>
            </div>
            <div class="d-grid">
              <input type="submit" class="btn btn-primary btn-lg" value="Log in" name="submit_login">
            </div>
          </form>
        </div>
      </div>

      <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2 gap-sm-4 mt-4 small">
        @if (Route::has('sign-up'))
          <a href="{{ route('sign-up') }}" class="text-decoration-none"><i class="bi bi-person-plus me-1"></i>Create new account</a>
        @endif
        <a href="{{ route('landing') }}" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Back to landing page</a>
      </div>
    </div>
  </main>
  @include('partials.swal')
</body>
</html>
