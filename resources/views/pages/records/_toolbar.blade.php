{{-- Expects: $rows (collection), $truncated, $max, optional $noun --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="text-muted small">
    <span class="badge text-bg-primary">{{ number_format($rows->count()) }}</span>
    {{ $rows->count() === 1 ? 'record' : 'records' }}
  </div>
  <a class="btn btn-outline-primary btn-sm" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" title="Download the complete dataset from the server"><i class="bi bi-download me-1"></i>Export all (CSV)</a>
</div>
@if($truncated)
  <div class="alert alert-warning small py-2">
    Showing the first {{ number_format($max) }} records only. Use <strong>Export all (CSV)</strong> to get the complete list.
  </div>
@endif
