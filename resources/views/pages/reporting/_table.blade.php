@php
  $type = $report['type'];
  $rows = $report['rows'];
@endphp

<div class="report-sheet">
  @include('pages.reporting._letterhead', ['title' => 'Report on '.$today])

  @if ($type === 'cow')
    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle mb-0 sir-table" data-responsive="false" data-title="Cow Report" data-paging="false" data-buttons="false" data-order='[[0,"asc"]]'>
        <thead class="table-light">
          <tr>
            <th data-priority="1" data-filter="text">Eartagno</th>
            <th data-priority="1" data-filter="text">Cow Name</th>
            <th data-filter="select">Gender</th>
            <th data-filter="select">Breed</th>
            <th>Cross Breed 1</th>
            <th>Cross Breed 2</th>
            <th>Breed Percentage</th>
            <th data-filter="date">Date of Birth</th>
            <th data-type="num">Number of Calving</th>
            <th data-filter="text">Owner Name</th>
            <th>Owner NID</th>
            <th>Owner Phone</th>
            <th data-filter="select">District</th>
            <th data-filter="select">Sector</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($rows as $row)
            <tr>
              <td>{{ $row->Eartagno }}</td>
              <td>{{ $row->Name }}</td>
              <td>{{ $row->Gender }}</td>
              <td>{{ $row->Breed }}</td>
              <td>{{ $row->Cross_breed1 }}</td>
              <td>{{ $row->Cross_breed2 }}</td>
              <td>{{ $row->Breed_Percentage }}</td>
              <td>{{ $row->Dob }}</td>
              <td>{{ $row->Number_Calving }}</td>
              <td>{{ $row->Owner_names }}</td>
              <td>{{ $row->National_Id }}</td>
              <td>{{ $row->Phone }}</td>
              <td>{{ $row->District }}</td>
              <td>{{ $row->Sector }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @elseif ($type === 'status')
    <div class="table-responsive">
      <table class="table table-striped table-bordered table-sm align-middle mb-0 sir-table" data-responsive="false" data-title="Insemination Status Report" data-paging="false" data-buttons="false" data-order='[[0,"asc"]]'>
        <thead class="table-primary">
          <tr>
            <th data-priority="1" data-filter="text">Ear Tag Number</th>
            <th data-filter="select">Insemination Status</th>
            <th>Previous Calving Date</th>
            <th>Heat Observation Date</th>
            <th data-filter="date">Insemination Date</th>
            <th data-filter="select">Bull Used</th>
            <th data-filter="select">Veterinary Name</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($rows as $row)
            <tr>
              <td>{{ $row->ear_tag }}</td>
              <td>{{ $row->status }}</td>
              <td>{{ $row->previous_calving }}</td>
              <td>{{ $row->heat_date }}</td>
              <td>{{ $row->insemination_date }}</td>
              <td>{{ $row->bull }}</td>
              <td>{{ $row->vet }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    @foreach ($rows as $row)
      <div class="mb-3">
        <h6 class="bg-light rounded px-3 py-2 mb-2">Cow Information</h6>
        <ul class="list-group list-group-flush">
          <li class="list-group-item"><strong>Cow Eartagno Number:</strong>&nbsp;{{ $row->Eartagno }}</li>
          <li class="list-group-item"><strong>On:</strong>&nbsp;{{ $now }}</li>
        </ul>
      </div>
    @endforeach
    @php $located = in_array($type, ['district', 'sector'], true); @endphp
    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle mb-0 sir-table" data-responsive="false" data-title="Insemination Report" data-paging="false" data-buttons="false" data-order='[[0,"asc"]]'>
        <thead class="table-light">
          <tr>
            <th data-priority="1" data-filter="text">Eartagno</th>
            @if ($located)
              <th data-filter="select">{{ ucfirst($type) }}</th>
            @endif
            <th>Mating Time</th>
            <th>Previous Calving</th>
            <th data-filter="date">Heat Observation Date</th>
            <th>Hours of Heat Observation</th>
            <th data-filter="date">Insemination Date 1</th>
            <th data-filter="select">Bull Used</th>
            <th data-filter="select">Observation</th>
            <th data-filter="select">{{ $located ? 'Veterinary' : 'Vaternary' }} Name</th>
            <th>{{ $located ? 'Veterinary' : 'Vaternary' }} RCVD ID</th>
            <th>{{ $located ? 'Veterinary' : 'Vaternary' }} Email</th>
            <th>{{ $located ? 'Mating Time 2' : 'Matting Time 2' }}</th>
            <th>Heat Observation Date{{ $located ? ' 2' : '' }}</th>
            <th>Hours of Heat Observation{{ $located ? ' 2' : '' }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($rows as $row)
            <tr>
              <td>{{ $row->Eartagno }}</td>
              @if ($located)
                <td>{{ $report['label'] }}</td>
              @endif
              <td>{{ $row->Mating_Time_1 }}</td>
              <td>{{ $row->Previous_Calving_Date }}</td>
              <td>{{ $row->Heat_Observation_Date_1 }}</td>
              <td>{{ $row->Hours_Heat_Observation_1 }}</td>
              <td>{{ $row->Insemination_Date_1 }}</td>
              <td>{{ $row->Bull_Used_1 }}</td>
              <td>{{ $row->Observation1 }}</td>
              <td>{{ $row->Veternary_Name1 }}</td>
              <td>{{ $row->Veternary_RCVD_ID1 }}</td>
              <td>{{ $row->Vaternary_Email1 }}</td>
              <td>{{ $row->Mating_time_2 }}</td>
              <td>{{ $row->Heat_Observation_Date2 }}</td>
              <td>{{ $row->Hours_Heat_Observation2 }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  <div class="mt-4">
    <p class="mb-1">Done at Huye</p>
    <p class="mb-4">On {{ $now }}</p>
    <p class="mb-0">Veternary {{ auth()->user()->name }} signature &amp; stamp</p>
    <div style="height:3.5rem"></div>
  </div>

  <div class="no-print d-flex flex-column flex-sm-row justify-content-between gap-2 mt-3">
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Print Report
    </button>
    <button type="button" class="btn btn-primary" onclick="window.print()">
      <i class="bi bi-printer me-1"></i>Print Report
    </button>
  </div>
</div>
