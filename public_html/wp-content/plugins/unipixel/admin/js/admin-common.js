// XOR UI helper: uncheck partner checkboxes in the same row when marked as non-dedup
(function ($) {

  $(document).on('change', 'table tr input[type="checkbox"][data-nondedup]', function () {
    if (!this.checked) return;
    var $row = $(this).closest('tr');
    $row.find('input[type="checkbox"][data-nondedup]').not(this).prop('checked', false);
  });




})(jQuery);


// ===============================
// Google Custom Events: add data-nondedup except for "purchase"
// ===============================
(function ($) {
  /** Identify Google admin pages via your shell marker. */
  function isGoogleAdminPage() {
    return $('.UniPixelShell[data-platform="google"]').length > 0;
  }

  /** Read the custom event name in a row (ajax-event-settings.js tables). */
  function getEventNameFromRow($row) {
    return String($row.find('input[name="event_name[]"]').val() || '').trim();
  }

  /** True when this row should behave as non-dedup (XOR). */
  function shouldBeNonDedup($row) {
    if (!isGoogleAdminPage()) return false;
    const name = getEventNameFromRow($row).toLowerCase();
    // For Google: all custom events XOR, except "purchase"
    return name !== 'purchase';
  }

  /** Apply/remove the attribute on the client/server pair for a Custom Events row. */
  function syncNonDedupForCustomRow($row) {
    // Only rows from the Custom Events table have these names
    const $pair = $row.find('input[name="send_client[]"], input[name="send_server[]"]');
    if ($pair.length === 0) return; // not a custom-events row

    if (shouldBeNonDedup($row)) {
      $pair.attr('data-nondedup', '');
    } else {
      $pair.removeAttr('data-nondedup');
    }
  }

  /** Initial pass over already-rendered Custom Events rows. */
  function tagExistingCustomRows() {
    if (!isGoogleAdminPage()) return;
    $('#event-settings-table tbody tr').each(function () {
      syncNonDedupForCustomRow($(this));
    });
  }

  /** Keep in sync if the user edits the event name (e.g., types "purchase"). */
  function bindCustomNameChange() {
    $(document).on('input change', '#event-settings-table tbody input[name="event_name[]"]', function () {
      if (!isGoogleAdminPage()) return;
      syncNonDedupForCustomRow($(this).closest('tr'));
    });
  }

  /** Watch for dynamically added Custom Events rows (via "Add Event"). */
  function observeCustomTable() {
    if (!isGoogleAdminPage()) return;
    const tbody = document.querySelector('#event-settings-table tbody');
    if (!tbody) return;

    new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        $(m.addedNodes).filter('tr').each(function () {
          syncNonDedupForCustomRow($(this));
        });
        $(m.addedNodes).find('tr').each(function () {
          syncNonDedupForCustomRow($(this));
        });
      });
    }).observe(tbody, { childList: true, subtree: true });
  }

  // Boot
  tagExistingCustomRows();
  bindCustomNameChange();
  observeCustomTable();


  document.addEventListener('DOMContentLoaded', function () {
    const triggers = document.querySelectorAll('[data-bs-toggle="popover"]');

    triggers.forEach(el => {
      const pop = new bootstrap.Popover(el, {
        trigger: 'manual',
        html: true,
        container: 'body',
        customClass: 'UniPixelPopover'
      });

      let hideTimeout;

      el.addEventListener('mouseenter', () => {
        clearTimeout(hideTimeout);
        pop.show();

        const popEl = bootstrap.Popover.getInstance(el)._popover || document.querySelector('.popover');
        if (popEl) {
          popEl.addEventListener('mouseenter', () => clearTimeout(hideTimeout));
          popEl.addEventListener('mouseleave', () => {
            hideTimeout = setTimeout(() => pop.hide(), 50);
          });
        }
      });

      el.addEventListener('mouseleave', () => {
        hideTimeout = setTimeout(() => pop.hide(), 50);
      });
    });
  });


})(jQuery);



