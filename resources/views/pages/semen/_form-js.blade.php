{{-- Add / remove semen batch rows (1 to $maxLots). --}}
var maxLots = {{ $maxLots }};
var lotIdx = document.querySelectorAll('#lotRows .lot-row').length;

function refreshLots() {
  var rows = document.querySelectorAll('#lotRows .lot-row');
  rows.forEach(function (row) { row.querySelector('.lot-remove').disabled = rows.length <= 1; });
  document.getElementById('addLot').disabled = rows.length >= maxLots;
}

document.getElementById('addLot').addEventListener('click', function () {
  var i = lotIdx++;
  var row = document.createElement('div');
  row.className = 'row g-2 align-items-end lot-row';
  row.innerHTML =
    '<div class="col-12 col-sm-4"><label class="form-label small mb-1">Registration No</label><input type="text" maxlength="30" class="form-control" name="lots[' + i + '][registration_no]" required></div>' +
    '<div class="col-6 col-sm-3"><label class="form-label small mb-1">Freezing Date</label><input type="date" class="form-control" name="lots[' + i + '][freezing_date]" required></div>' +
    '<div class="col-6 col-sm-3"><label class="form-label small mb-1">Semen Code</label><input type="text" maxlength="20" class="form-control" name="lots[' + i + '][semen_code]" required></div>' +
    '<div class="col-12 col-sm-2 text-sm-end"><button type="button" class="btn btn-sm btn-outline-danger lot-remove" title="Remove batch" aria-label="Remove batch"><i class="bi bi-x-lg"></i></button></div>';
  document.getElementById('lotRows').appendChild(row);
  refreshLots();
});

document.getElementById('lotRows').addEventListener('click', function (e) {
  var btn = e.target.closest('.lot-remove');
  if (!btn || btn.disabled) { return; }
  btn.closest('.lot-row').remove();
  refreshLots();
});

refreshLots();
