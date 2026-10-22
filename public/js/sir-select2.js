// Upgrade every <select> in the system to Select2 (opt out with data-no-select2).
(function ($) {
  function init(root) {
    $(root).find('select').not('[data-no-select2],.select2-hidden-accessible').each(function () {
      var $el = $(this);
      var $modal = $el.closest('.modal');
      $el.select2({
        theme: 'bootstrap-5',
        width: $el.closest('.dt-length, .dataTables_length').length ? 'auto' : '100%',
        placeholder: $el.find('option[value=""]').text() || undefined,
        dropdownParent: $modal.length ? $modal : $(document.body)
      });
      // Select2 ignores [readonly]; keep those closed.
      if ($el.is('[readonly]')) $el.on('select2:opening', function (e) { e.preventDefault(); });
    });
  }

  // Code that sets a value with .val(x) must refresh the Select2 display too.
  var origVal = $.fn.val;
  $.fn.val = function () {
    var out = origVal.apply(this, arguments);
    if (arguments.length) {
      this.filter('.select2-hidden-accessible').each(function () { $(this).trigger('change.select2'); });
    }
    return out;
  };

  $(function () {
    init(document);
    // Selects created later (DataTables filters / page length, JS-built forms).
    var queued = false;
    new MutationObserver(function () {
      if (queued) return;
      queued = true;
      setTimeout(function () { queued = false; init(document); }, 0);
    }).observe(document.body, { childList: true, subtree: true });
  });
  window.sirSelect2 = init;
})(jQuery);
