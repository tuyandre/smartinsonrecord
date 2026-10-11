@extends('layouts.app')

@section('title', 'Inseminations · SMART INSEMrecord')
@section('page_title', 'Inseminations')
@section('page_sub', 'Recorded data · up to three mating rounds per cow')
@section('actions')
  <a href="{{ route('insemination.index') }}" class="btn btn-primary"><i class="bi bi-heart-pulse me-1"></i>New Insemination</a>
@endsection

@section('content')
  @include('pages.records._toolbar')
  @php
    /** Normalise a stored date string to ISO YYYY-MM-DD when possible (so the date filter works). */
    $iso = function ($d) {
        $d = trim((string) $d);
        if ($d === '') { return '—'; }
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $d)) { return substr($d, 0, 10); }
        if (preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{4})$#', $d, $m)) { return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]); }
        return $d;
    };
    $val = fn ($v) => trim((string) $v) !== '' ? trim((string) $v) : '—';

    // column names of each mating round in the legacy table
    $rounds = [
      1 => ['mating' => 'Mating_Time_1', 'heat' => 'Heat_Observation_Date_1', 'hrs' => 'Hours_Heat_Observation_1', 'date' => 'Insemination_Date_1', 'bull' => 'Bull_Used_1', 'obs' => 'Observation1', 'vet' => 'Veternary_Name1', 'rcvd' => 'Veternary_RCVD_ID1', 'email' => 'Vaternary_Email1'],
      2 => ['mating' => 'Mating_time_2', 'heat' => 'Heat_Observation_Date2', 'hrs' => 'Hours_Heat_Observation2', 'date' => 'Insemination_Date2', 'bull' => 'Bull_Used_2', 'obs' => 'Observation_2', 'vet' => 'Veternary_Name_2', 'rcvd' => 'Veternary_RCVD_ID_2', 'email' => 'Vaternary_Email_2'],
      3 => ['mating' => 'Mating_time_3', 'heat' => 'Heat_Observation_Date_3', 'hrs' => 'Hours_Heat_Observation_3', 'date' => 'Insemination_Date_3', 'bull' => 'Bull_Used_3', 'obs' => 'Observation_3', 'vet' => 'Veternary_Name_3', 'rcvd' => 'Veternary_RCVD_ID_3', 'email' => 'Vaternary_Email_3'],
    ];
    $r1 = $rounds[1];
  @endphp

  <div class="card">
    <div class="card-body">
      <table class="table table-hover align-middle sir-table" data-title="Inseminations" data-order='[[0,"asc"]]' data-page-length="15">
        <thead>
          <tr>
            <th data-filter="text" data-priority="1">Cow</th>
            <th data-filter="text" data-priority="3">Owner</th>
            <th data-priority="6">Prev. calving</th>
            <th data-filter="date" data-priority="2">Insemination Date</th>
            <th data-filter="select" data-priority="4">Bull</th>
            <th data-filter="select" data-priority="5">Veterinary</th>
            <th data-orderable="false" data-export="false" data-priority="1">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            @php
              $detail = [
                'tag' => (string) $r->Eartagno,
                'cow' => (string) ($r->cow_name ?? ''),
                'owner' => $val($r->owner),
                'calving' => $iso($r->Previous_Calving_Date),
                'rounds' => [],
              ];
              foreach ($rounds as $n => $c) {
                $done = trim((string) ($r->{$c['date']} ?? '')) !== '';
                $detail['rounds'][] = [
                  'no' => $n,
                  'done' => $done,
                  'fields' => $done ? [
                    'Insemination date' => $iso($r->{$c['date']}),
                    'Mating time' => $val($r->{$c['mating']}),
                    'Heat observation date' => $iso($r->{$c['heat']}),
                    'Heat hours' => $val($r->{$c['hrs']}),
                    'Bull used' => $val($r->{$c['bull']}),
                    'Observation' => $val($r->{$c['obs']}),
                    'Veterinary' => $val($r->{$c['vet']}),
                    'Veterinary RCVD ID' => $val($r->{$c['rcvd']}),
                    'Veterinary email' => $val($r->{$c['email']}),
                  ] : [],
                ];
              }
            @endphp
            <tr>
              <td><span class="fw-semibold">{{ $r->Eartagno }}</span> <span>{{ $r->cow_name ?? '' }}</span></td>
              <td>{{ $val($r->owner) }}</td>
              <td class="text-nowrap">{{ $iso($r->Previous_Calving_Date) }}</td>
              <td class="text-nowrap">{{ $iso($r->{$r1['date']}) }}</td>
              <td>{{ $val($r->{$r1['bull']}) }}</td>
              <td>{{ $val($r->{$r1['vet']}) }}</td>
              <td class="text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-primary btn-view-insemination" data-detail="{{ json_encode($detail) }}">
                  <i class="bi bi-eye me-1"></i>View
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="inseminationModal" tabindex="-1" aria-labelledby="inseminationModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="inseminationModalTitle">Insemination details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="inseminationModalBody"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      var esc = function (s) { return $('<div>').text(s == null ? '' : String(s)).html(); };
      var modalEl = document.getElementById('inseminationModal');

      function field(label, value) {
        return '<div class="col-6 col-md-4"><div class="small text-muted">' + esc(label) + '</div>' +
               '<div class="fw-medium text-break">' + esc(value) + '</div></div>';
      }

      function render(d) {
        var html = '<div class="row g-3 mb-3">' +
          field('Ear tag', d.tag) + field('Cow', d.cow || '—') + field('Owner', d.owner) +
          field('Previous calving', d.calving) + '</div>';

        d.rounds.forEach(function (r) {
          html += '<div class="card mb-3"><div class="card-header d-flex justify-content-between align-items-center py-2">' +
                  '<span>Mating round ' + r.no + '</span>' +
                  (r.done ? '<span class="badge text-bg-success">Recorded</span>' : '<span class="badge text-bg-secondary">Not recorded</span>') +
                  '</div>';
          if (r.done) {
            html += '<div class="card-body"><div class="row g-3">';
            Object.keys(r.fields).forEach(function (k) { html += field(k, r.fields[k]); });
            html += '</div></div>';
          }
          html += '</div>';
        });
        return html;
      }

      // delegated, so it also works for buttons inside responsive child rows
      $(document).on('click', '.btn-view-insemination', function () {
        var d = $(this).data('detail');
        $('#inseminationModalTitle').text('Insemination details · ' + d.tag + (d.cow ? ' ' + d.cow : ''));
        $('#inseminationModalBody').html(render(d));
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      });
    })();
  </script>
@endpush
