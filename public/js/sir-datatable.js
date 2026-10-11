/*
 * SMART INSEMrecord — shared DataTables behaviour.
 *
 * Mark up any table with class="sir-table" (it must have a single <thead> row) and it becomes a
 * searchable, sortable, paginated, responsive table with export buttons and a filter bar.
 *
 * Table attributes (all optional):
 *   data-title="Registered Cows"   export file / print title
 *   data-paging="false"            show every row (use for printable reports)
 *   data-page-length="15"          rows per page
 *   data-order='[[0,"asc"]]'       initial sort
 *   data-buttons="false"           hide the export / column buttons
 *   data-responsive="false"        never collapse columns (use for wide printable reports; wrap in .table-responsive)
 *   data-filters-target="#id"      element that should receive the filter bar (default: above the table)
 *
 * Header cell attributes (all optional):
 *   data-filter="select|text|date" adds a filter control for that column to the filter bar (date = global DD/MM/YYYY range picker)
 *   data-orderable="false"         turn sorting off (e.g. action columns)
 *   data-export="false"            leave the column out of CSV/Excel/Print/Copy
 *   data-priority="1"              responsive priority (lower = hidden last)
 *   data-type="num"                numeric sorting
 */
(function ($) {
  'use strict';

  if (!$ || !$.fn || !$.fn.DataTable) { return; }

  var textOf = function (html) {
    return $('<div>').html(html == null ? '' : String(html)).text().replace(/\s+/g, ' ').trim();
  };
  var escapeRegex = function (s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); };
  var uid = 0;

  window.SIR = window.SIR || {};

  /**
   * The one date-range control used across the app (DD/MM/YYYY - DD/MM/YYYY, quick ranges).
   * onChange(fromIso, toIso) receives 'YYYY-MM-DD' strings, or '' '' when cleared.
   * Any input with class "sir-daterange" outside tables can use it too:  SIR.dateRange($input, cb)
   */
  SIR.DATE_FORMAT = 'DD/MM/YYYY';
  SIR.dateRange = function ($input, onChange) {
    if (!$.fn.daterangepicker || !window.moment) { return; }
    var m = window.moment;
    $input.daterangepicker({
      autoUpdateInput: false,
      showDropdowns: true,
      linkedCalendars: false,
      alwaysShowCalendars: true,
      opens: 'right',
      drops: 'auto',
      locale: { format: SIR.DATE_FORMAT, separator: ' - ', applyLabel: 'Apply', cancelLabel: 'Clear', customRangeLabel: 'Custom Range', firstDay: 1 },
      ranges: {
        'Today': [m(), m()],
        'Yesterday': [m().subtract(1, 'days'), m().subtract(1, 'days')],
        'Last 7 Days': [m().subtract(6, 'days'), m()],
        'Last 30 Days': [m().subtract(29, 'days'), m()],
        'This Month': [m().startOf('month'), m().endOf('month')],
        'Last Month': [m().subtract(1, 'month').startOf('month'), m().subtract(1, 'month').endOf('month')],
        'This Year': [m().startOf('year'), m().endOf('year')],
        'Last Year': [m().subtract(1, 'year').startOf('year'), m().subtract(1, 'year').endOf('year')]
      }
    });
    $input.on('apply.daterangepicker', function (ev, picker) {
      $(this).val(picker.startDate.format(SIR.DATE_FORMAT) + ' - ' + picker.endDate.format(SIR.DATE_FORMAT));
      onChange(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
    });
    $input.on('cancel.daterangepicker', function () {
      $(this).val('');
      onChange('', '');
    });
  };

  function exportColumns(table) {
    var cols = [];
    $(table).find('thead th').each(function (i) {
      if ($(this).data('export') !== false && $(this).data('export') !== 'false') { cols.push(i); }
    });
    return cols;
  }

  function buildFilters($table, api, id) {
    var cols = [];
    $table.find('thead th').each(function (i) {
      var kind = $(this).data('filter');
      if (kind) { cols.push({ index: i, kind: kind, label: $(this).text().trim() }); }
    });
    if (!cols.length) { return; }

    var collapseId = id + 'Filters';
    var $bar = $(
      '<section class="card sir-filter-card mb-3 no-print" aria-label="Filters">' +
        '<div class="card-header d-flex align-items-center gap-2 py-2">' +
          '<i class="bi bi-funnel text-primary"></i><span class="fw-medium">Filters</span>' +
          '<span class="badge rounded-pill text-bg-primary d-none sir-filter-count"></span>' +
          '<div class="ms-auto d-flex align-items-center gap-2">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary sir-filter-clear"><i class="bi bi-x-circle me-1"></i>Clear</button>' +
            '<button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="true" aria-controls="' + collapseId + '" title="Show / hide filters"><i class="bi bi-chevron-up"></i></button>' +
          '</div>' +
        '</div>' +
        '<div class="collapse' + (window.innerWidth >= 576 ? ' show' : '') + '" id="' + collapseId + '"><div class="card-body"><div class="sir-filters row g-3 align-items-end"></div></div></div>' +
      '</section>'
    );
    var $grid = $bar.find('.sir-filters');

    var updateCount = function () {
      var active = 0;
      $grid.find('select').each(function () { if (this.value) { active++; } });
      $grid.find('input').each(function () { if (this.value) { active++; } });
      $bar.find('.sir-filter-count').toggleClass('d-none', !active).text(active + ' active');
    };
    $bar.on('change input', updateCount);
    $bar.on('shown.bs.collapse hidden.bs.collapse', function (e) {
      $bar.find('[data-bs-toggle="collapse"] i').attr('class', 'bi ' + (e.type === 'shown' ? 'bi-chevron-up' : 'bi-chevron-down'));
    });

    cols.forEach(function (c) {
      var $col = $('<div class="col-6 col-md-4 col-xl-3"></div>');
      var $label = $('<label class="form-label small text-muted mb-1"></label>').text(c.label);
      $col.append($label);

      if (c.kind === 'select') {
        var $sel = $('<select class="form-select form-select-sm"><option value="">All</option></select>');
        var seen = {};
        api.column(c.index).data().each(function (v) {
          var t = textOf(v);
          if (t && t !== '—' && !seen[t]) { seen[t] = true; }
        });
        Object.keys(seen).sort(function (a, b) { return a.localeCompare(b, undefined, { numeric: true }); }).forEach(function (t) {
          $sel.append($('<option>').val(t).text(t));
        });
        $sel.on('change', function () {
          var v = this.value;
          api.column(c.index).search(v ? '^\\s*' + escapeRegex(v) + '\\s*$' : '', true, false).draw();
        });
        $col.append($sel);
      } else if (c.kind === 'date') {
        var range = { from: '', to: '' };
        var $range = $('<input type="text" class="form-control form-control-sm sir-daterange" placeholder="DD/MM/YYYY - DD/MM/YYYY" autocomplete="off" readonly>');
        $col.append($('<div class="input-group input-group-sm"></div>')
          .append($('<span class="input-group-text"><i class="bi bi-calendar3"></i></span>'), $range));

        $.fn.dataTable.ext.search.push(function (settings, data) {
          if (new $.fn.dataTable.Api(settings).table().node().id !== id) { return true; }
          if (!range.from && !range.to) { return true; }
          var cell = (data[c.index] || '').match(/\d{4}-\d{2}-\d{2}/);
          if (!cell) { return false; }
          return (!range.from || cell[0] >= range.from) && (!range.to || cell[0] <= range.to);
        });

        SIR.dateRange($range, function (from, to) { range.from = from; range.to = to; api.draw(); updateCount(); });
        $bar.on('sir:reset', function () { range.from = range.to = ''; $range.val(''); });
      } else {
        var $in = $('<input type="search" class="form-control form-control-sm" placeholder="Filter…">');
        $in.on('input', function () { api.column(c.index).search(this.value).draw(); });
        $col.append($in);
      }
      $grid.append($col);
    });

    $bar.find('.sir-filter-clear').on('click', function () {
      $grid.find('select').val('');
      $grid.find('input').val('');
      $bar.trigger('sir:reset');
      api.columns().every(function () { this.search(''); });
      api.search('').draw();
      updateCount();
    });

    // The filters are their own section, placed above the card that holds the table.
    var target = $table.data('filtersTarget');
    var container = $(api.table().container());
    if (target && $(target).length) { $(target).empty().append($bar); }
    else {
      var $card = container.closest('.card');
      ($card.length ? $card : container).before($bar);
    }
  }

  function init(el) {
    var $table = $(el);
    if ($.fn.dataTable.isDataTable(el)) { return; }

    if (!el.id) { el.id = 'sirTable' + (++uid); }
    var title = $table.data('title') || document.title.split('·')[0].trim();
    var paging = $table.data('paging') !== false && $table.data('paging') !== 'false';
    var withButtons = $table.data('buttons') !== false && $table.data('buttons') !== 'false';
    var cols = exportColumns(el);

    var columnDefs = [];
    $table.find('thead th').each(function (i) {
      var $th = $(this);
      var def = { targets: i };
      if ($th.data('orderable') === false || $th.data('orderable') === 'false') { def.orderable = false; def.searchable = false; }
      if ($th.data('priority')) { def.responsivePriority = parseInt($th.data('priority'), 10); }
      if ($th.data('type')) { def.type = String($th.data('type')); }
      columnDefs.push(def);
    });

    var order = $table.data('order');
    var buttons = withButtons ? [
      { extend: 'csvHtml5', text: '<i class="bi bi-filetype-csv me-1"></i>CSV', title: title, bom: true, exportOptions: { columns: cols } },
      { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', title: title, exportOptions: { columns: cols } },
      { extend: 'print', text: '<i class="bi bi-printer me-1"></i>Print', title: title, exportOptions: { columns: cols } },
      { extend: 'colvis', text: '<i class="bi bi-layout-three-columns me-1"></i>Columns' }
    ] : [];

    var api = $table.DataTable({
      responsive: !($table.data('responsive') === false || $table.data('responsive') === 'false'),
      autoWidth: false,
      paging: paging,
      pageLength: parseInt($table.data('pageLength'), 10) || 15,
      lengthMenu: [10, 15, 25, 50, 100],
      order: Array.isArray(order) ? order : [],
      columnDefs: columnDefs,
      layout: {
        topStart: withButtons ? { buttons: buttons } : null,
        topEnd: 'search',
        bottomStart: paging ? ['pageLength', 'info'] : 'info',
        bottomEnd: paging ? 'paging' : null
      },
      language: {
        search: '',
        searchPlaceholder: 'Search all columns…',
        emptyTable: 'No records found',
        zeroRecords: 'No matching records found',
        lengthMenu: 'Show _MENU_',
        info: 'Showing _START_–_END_ of _TOTAL_',
        infoFiltered: '(filtered from _MAX_)'
      }
    });

    buildFilters($table, api, el.id);
  }

  function initAll(root) {
    $(root || document).find('table.sir-table').each(function () { init(this); });
  }

  $(function () { initAll(); });

  // Tables inside modals/tabs are laid out while hidden: recalculate when they become visible.
  document.addEventListener('shown.bs.modal', function (e) {
    $(e.target).find('table.sir-table').each(function () {
      init(this);
      $(this).DataTable().columns.adjust().responsive.recalc();
    });
  });
  document.addEventListener('shown.bs.tab', function () {
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
  });

  window.SIR = window.SIR || {};
  window.SIR.initTables = initAll;
})(window.jQuery);
