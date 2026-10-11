<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign up · SMART INSEMrecord</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/sweetalert/sweetalert.min.js') }}"></script>
  <style>
    .auth-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem 1rem; background: linear-gradient(160deg, #e8f0fc 0%, var(--sir-bg) 55%); }
    .auth-card { width: 100%; max-width: 640px; border: 1px solid var(--sir-border); border-radius: 1rem; box-shadow: 0 10px 30px rgba(18,74,156,.08); }
    .auth-logo { max-width: 150px; width: 100%; height: auto; }
    #strengthTrack { height: 6px; border-radius: 3px; background: var(--sir-border); overflow: hidden; margin-top: .5rem; }
    #strengthIndicator { height: 100%; width: 0; border-radius: 3px; transition: width .2s, background-color .2s; }
  </style>
</head>
<body>
  <main class="auth-wrap">
    <div class="text-center mb-4">
      <img class="auth-logo" src="{{ asset('landing/img/hero-img.png') }}" alt="SMART INSEMrecord logo">
    </div>

    <div class="card auth-card">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold text-center mb-1">Create your account</h1>
        <p class="text-center text-muted mb-4">Enter your National ID and we fill in your veterinary details.</p>

        <form method="POST" action="{{ route('sign-up') }}">
          @csrf
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="national_id" name="nid" value="{{ old('nid') }}" placeholder="National ID" required title="Only Rwanda National Id Allowed">
                <label for="national_id">National ID</label>
              </div>
              @error('nid') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="username" name="names" value="{{ old('names') }}" placeholder="Names" readonly>
                <label for="username">Names</label>
              </div>
              @error('names') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="rcvd_id" name="rcvd" value="{{ old('rcvd') }}" placeholder="RCVD Id" readonly>
                <label for="rcvd_id">RCVD ID</label>
              </div>
              @error('rcvd') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="Email" readonly>
                <label for="email">Email</label>
              </div>
              @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="username_login" name="username" value="{{ old('username') }}" placeholder="Username">
                <label for="username_login">Username (optional)</label>
              </div>
              @error('username') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Phone">
                <label for="phone">Phone (optional)</label>
              </div>
              @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <div class="form-floating">
                <input type="password" class="form-control" id="password" name="pwd" placeholder="Choose password" required oninput="checkPasswordStrength()">
                <label for="password">Password</label>
              </div>
              <div id="strengthTrack"><div id="strengthIndicator"></div></div>
              <small id="strengthMessage" class="text-muted"></small>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="togglePassword">
                <label class="form-check-label" for="togglePassword">Show Password</label>
              </div>
              @error('pwd') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <div class="form-floating">
                <input type="password" class="form-control" id="retype-password" name="repwd" placeholder="Retype password" required>
                <label for="retype-password">Retype Password</label>
              </div>
              @error('repwd') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="form2Example33">
                <label class="form-check-label" for="form2Example33">Am Veterinary</label>
              </div>
            </div>

            <div class="col-12 d-grid">
              <input type="submit" class="btn btn-primary btn-lg" name="sign" value="Sign up">
            </div>
          </div>
        </form>

        <p class="text-center mt-4 mb-0">Back to <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Login</a></p>
      </div>
    </div>

    <p class="text-center text-muted small mt-4 mb-0">&copy; SMART INSEMrecord 2025. All rights reserved.</p>
  </main>

  <script>
    document.getElementById('togglePassword').addEventListener('change', function () {
      document.getElementById('password').type = this.checked ? 'text' : 'password';
    });

    function checkPasswordStrength() {
      var password = document.getElementById('password').value;
      var bar = document.getElementById('strengthIndicator');
      var msg = document.getElementById('strengthMessage');
      var strength = 0;
      if (password.length >= 8) strength++;
      if (/[A-Z]/.test(password)) strength++;
      if (/[a-z]/.test(password)) strength++;
      if (/[0-9]/.test(password)) strength++;
      if (/[\W_]/.test(password)) strength++;

      if (password.length === 0) {
        bar.style.backgroundColor = ''; msg.textContent = '';
      } else if (strength <= 2) {
        bar.style.backgroundColor = '#dc3545'; msg.textContent = 'Weak password';
      } else if (strength < 5) {
        bar.style.backgroundColor = '#f0ad00'; msg.textContent = 'Medium strength';
      } else {
        bar.style.backgroundColor = '#14c671'; msg.textContent = 'Strong password';
      }
      bar.style.width = (strength / 5 * 100) + '%';
    }

    $(function () {
      function clearLookup(border) {
        $('#username').val(''); $('#rcvd_id').val(''); $('#email').val('');
        $('#national_id').css('border-color', border);
      }
      $('#national_id').on('keyup', function () {
        var national_id = $('#national_id').val();
        if (national_id.length === 0) { clearLookup(''); return; }
        $.ajax({
          url: @json(route('sign-up.lookup')),
          method: 'POST',
          data: { national_id: national_id, _token: @json(csrf_token()) },
          dataType: 'json',
          success: function (response) {
            if (response.length > 0) {
              $('#username').val(response[0].Vet_Names);
              $('#rcvd_id').val(response[0].Rcvd_id);
              $('#email').val(response[0].email ? response[0].email : '');
              $('#national_id').css('border-color', '#14c671');
            } else {
              clearLookup('#dc3545');
            }
          },
          error: function () { clearLookup('#dc3545'); }
        });
      });
    });
  </script>
  @include('partials.swal')
</body>
</html>
