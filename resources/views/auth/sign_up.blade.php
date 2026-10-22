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
    #vetFields:disabled { opacity: .55; }
    #vetFields { border: 0; padding: 0; margin: 0; min-width: 0; }
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
        <p class="text-center text-muted mb-4">Enter your National ID. Your details are read from the national veterinarians register; you add the rest.</p>

        <form method="POST" action="{{ route('sign-up') }}" id="signUpForm" novalidate>
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <div class="form-floating">
                <input type="text" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" class="form-control @error('nid') is-invalid @enderror" id="national_id" name="nid" value="{{ old('nid') }}" placeholder="National ID" required autofocus autocomplete="off">
                <label for="national_id">National ID</label>
              </div>
              <div id="lookupMessage" class="small mt-1"></div>
              @error('nid') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <fieldset id="vetFields" class="row g-3 mx-0 px-0" disabled>
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="names" placeholder="Names" readonly tabindex="-1">
                  <label for="names">Names (from the register)</label>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="rcvd_id" placeholder="RCVD ID" readonly tabindex="-1">
                  <label for="rcvd_id">RCVD ID (from the register)</label>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="district" placeholder="District" readonly tabindex="-1">
                  <label for="district">District</label>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="sector" placeholder="Sector" readonly tabindex="-1">
                  <label for="sector">Sector</label>
                </div>
              </div>

              <div class="col-12"><hr class="my-1"><div class="small text-muted">Not in the register: please fill these in</div></div>

              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                  <label for="email">Email</label>
                </div>
                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Phone" required>
                  <label for="phone">Phone</label>
                </div>
                @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <div class="form-floating">
                  <input type="text" class="form-control @error('username') is-invalid @enderror" id="username_login" name="username" value="{{ old('username') }}" placeholder="Username">
                  <label for="username_login">Username (optional)</label>
                </div>
                @error('username') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

              <div class="col-12">
                <div class="form-floating">
                  <input type="password" class="form-control @error('pwd') is-invalid @enderror" id="password" name="pwd" placeholder="Choose password" required oninput="checkPasswordStrength()">
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
                  <input type="password" class="form-control @error('repwd') is-invalid @enderror" id="retype-password" name="repwd" placeholder="Retype password" required>
                  <label for="retype-password">Retype Password</label>
                </div>
                @error('repwd') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="confirm" name="confirm" @checked(old('confirm'))>
                  <label class="form-check-label" for="confirm">I confirm that I am the veterinarian shown above</label>
                </div>
                @error('confirm') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary btn-lg" id="signUpBtn" disabled>Sign up</button>
              </div>
            </fieldset>
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
      var timer = null;
      var request = 0;

      // Everything below the National ID stays locked until the register confirms the veterinarian.
      function setFound(found, vet, message) {
        $('#vetFields').prop('disabled', !found);
        $('#signUpBtn').prop('disabled', !found);
        $('#names').val(found ? vet.names : '');
        $('#rcvd_id').val(found ? vet.rcvd_id : '');
        $('#district').val(found ? (vet.district || '') : '');
        $('#sector').val(found ? (vet.sector || '') : '');
        $('#national_id').toggleClass('is-valid', found).toggleClass('is-invalid', !found && !!message);
        $('#lookupMessage')
          .attr('class', 'small mt-1 ' + (found ? 'text-success' : 'text-danger'))
          .html(message ? $('<span>').text(message).prop('outerHTML') : '');
      }

      function lookup() {
        var nid = String($('#national_id').val()).trim();
        var current = ++request;
        if (nid.length === 0) { setFound(false, null, ''); return; }
        if (!/^\d+$/.test(nid)) { setFound(false, null, 'The National ID may contain digits only.'); return; }
        if (nid.length < 16) {
          // Not a full ID yet: keep the form locked and show how many digits are still missing.
          setFound(false, null, '');
          $('#lookupMessage').attr('class', 'small mt-1 text-muted').text(nid.length + ' of 16 digits entered');
          return;
        }

        $.ajax({
          url: @json(route('sign-up.lookup')),
          method: 'POST',
          data: { national_id: nid, _token: @json(csrf_token()) },
          dataType: 'json'
        }).done(function (r) {
          if (current !== request) { return; }          // an older answer: ignore
          if (r.found) { setFound(true, r, 'Veterinarian found: ' + r.names + '. Fill in the remaining fields.'); }
          else { setFound(false, null, r.message); }
        }).fail(function (xhr) {
          if (current !== request) { return; }
          setFound(false, null, xhr.status === 429 ? 'Too many attempts. Please wait a minute and try again.' : 'Could not check the register. Please try again.');
        });
      }

      $('#national_id').on('input', function () {
        clearTimeout(timer);
        timer = setTimeout(lookup, 350);
      });

      // Nothing is sent unless the register has confirmed the veterinarian.
      $('#signUpForm').on('submit', function (e) {
        if ($('#signUpBtn').prop('disabled')) { e.preventDefault(); }
      });

      if ($('#national_id').val()) { lookup(); }
    });
  </script>
  @include('partials.swal')
</body>
</html>
