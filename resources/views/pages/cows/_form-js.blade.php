{{-- Breed picker + address chain for the shared cow form. Expects $breeds; optional $cow. --}}
    var breedOptions = @json($breeds);
    var crossIdx = document.querySelectorAll('#crossRows .cross-row').length;

    function crossTotal() {
      var total = 0;
      document.querySelectorAll('#crossRows .cross-pct').forEach(function (el) { total += parseInt(el.value, 10) || 0; });
      var out = document.getElementById('crossTotal');
      out.textContent = 'Total: ' + total + '% / 100%';
      out.className = 'small fw-medium ' + (total === 100 ? 'text-success' : 'text-danger');
      return total;
    }

    function toggleBreed() {
      var single = document.getElementById('breedIdentification').value === 'single';
      document.getElementById('singleBreedSection').style.display = single ? 'block' : 'none';
      document.getElementById('crossBreedSection').style.display = single ? 'none' : 'block';
      // Only the visible section's fields are mandatory.
      document.getElementById('breedSelect').required = single;
      document.querySelectorAll('#crossRows [data-cross-field]').forEach(function (el) { el.required = !single; });
      crossTotal();
    }

    function updateRemoveButtons() {
      var rows = document.querySelectorAll('#crossRows .cross-row');
      rows.forEach(function (row) { row.querySelector('.cross-remove').disabled = rows.length <= 2; });
      document.getElementById('addCrossBreed').disabled = rows.length >= breedOptions.length;
    }

    document.getElementById('addCrossBreed').addEventListener('click', function () {
      var i = crossIdx++;
      var opts = '<option value="">-----Choose Breed---------</option>' + breedOptions.map(function (b) { return '<option value="' + b + '">' + b + '</option>'; }).join('');
      var row = document.createElement('div');
      row.className = 'row g-2 align-items-center cross-row';
      row.innerHTML =
        '<div class="col-7 col-sm-8"><select class="form-select" name="cross_breeds[' + i + '][breed]" aria-label="Breed" data-cross-field required>' + opts + '</select></div>' +
        '<div class="col-5 col-sm-3"><div class="input-group"><input type="number" class="form-control cross-pct" name="cross_breeds[' + i + '][percentage]" placeholder="..." min="1" max="100" aria-label="Percentage" data-cross-field required><span class="input-group-text">%</span></div></div>' +
        '<div class="col-12 col-sm-1 text-sm-end"><button type="button" class="btn btn-sm btn-outline-danger cross-remove" title="Remove breed" aria-label="Remove breed"><i class="bi bi-x-lg"></i></button></div>';
      document.getElementById('crossRows').appendChild(row);
      updateRemoveButtons();
      crossTotal();
    });

    document.getElementById('crossRows').addEventListener('click', function (e) {
      var btn = e.target.closest('.cross-remove');
      if (!btn || btn.disabled) { return; }
      btn.closest('.cross-row').remove();
      updateRemoveButtons();
      crossTotal();
    });
    document.getElementById('crossRows').addEventListener('input', crossTotal);

    $('#breedIdentification').on('change', toggleBreed); // jQuery: Select2 only fires jQuery change events
    document.getElementById('cowForm').addEventListener('submit', function (e) {
      if (document.getElementById('breedIdentification').value !== 'cross') { return; }
      var picked = [];
      var dup = false;
      document.querySelectorAll('#crossRows select').forEach(function (sel) {
        if (sel.value && picked.indexOf(sel.value) !== -1) { dup = true; }
        picked.push(sel.value);
      });
      var message = dup ? 'Each breed can only be used once.' : (crossTotal() !== 100 ? 'The breed percentages must add up to exactly 100%.' : '');
      if (message) {
        e.preventDefault();
        swal({ title: 'Check the breeds', text: message, icon: 'warning' });
      }
    });
    updateRemoveButtons();
    toggleBreed();

    RwandaAddress.attach(document.getElementById('cowLocation'), {
      district: @json(old('district', $cow->District ?? null)), sector: @json(old('sector', $cow->Sector ?? null)),
      cell: @json(old('cell', $cow->Cell ?? null)), village: @json(old('village', $cow->Village ?? null))
    });
