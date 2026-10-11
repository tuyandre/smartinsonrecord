@php
  $columns = [
      'Eartag' => 'Eartag', 'Breed' => 'Breed', 'Dob' => 'Dob', 'Name' => 'Name',
      'registration_no_1' => 'Registration No 1', 'freezing_date_1' => 'Freezing Date 1', 'Semen_code_1' => 'Semen Code 1',
      'registration_no_2' => 'Registration No 2', 'freezing_date_2' => 'Freezing Date 2', 'semen_code_2' => 'Semen Code 2',
      'registration__code_3' => 'Registration No 3', 'freezing_date_3' => 'Freezing Date 3', 'semen_code_3' => 'Semen Code 3',
      'registration_no_4' => 'Registration No 4', 'freezing_date_4' => 'Freezing Date 4', 'semen_code_4' => 'Semen Code 4',
      'registration_no_5' => 'Registration No 5', 'freezing_date_5' => 'Freezing Date 5', 'semen_code_5' => 'Semen Code 5',
      'international_code' => 'International Code', 'aic_no' => 'AIC No', 'bull_no' => 'Bull No',
  ];
@endphp

<div class="report-sheet">
  @include('pages.reporting._letterhead', ['title' => 'Available Bulls and Semen Information'])

  <div class="table-responsive">
    <table class="table table-striped table-sm align-middle mb-0 sir-table" data-responsive="false" data-title="Available Bulls and Semen Information" data-paging="false" data-buttons="false" data-order='[[0,"asc"]]'>
      <thead class="table-light">
        <tr>
          @foreach ($columns as $key => $label)
            <th @if ($loop->first) data-priority="1" @endif @if (in_array($key, ['Eartag', 'Name', 'bull_no', 'international_code'], true)) data-filter="text" @elseif ($key === 'Breed') data-filter="select" @endif>{{ $label }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach ($semen as $row)
          <tr>
            @foreach ($columns as $key => $label)
              <td>{{ $row[$key] ?? '' }}</td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <p class="mb-1">Done at Huye</p>
    <p class="mb-4">On {{ $now }}</p>
    <p class="mb-0">Veterinary signature &amp; stamp</p>
    <div style="height:3.5rem"></div>
  </div>

  <div class="no-print d-flex flex-column flex-sm-row justify-content-between gap-2 mt-3">
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export to excel
    </button>
    <button type="button" class="btn btn-primary" onclick="window.print()">
      <i class="bi bi-printer me-1"></i>Print Report
    </button>
  </div>
</div>
