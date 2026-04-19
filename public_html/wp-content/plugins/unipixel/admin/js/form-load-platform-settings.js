// Toggle 'required' + dim wrappers based on platform_enabled and serverside_global_enabled
jQuery(function ($) {
  function applyForCurrentPage() {
    var isOn          = $('#platform_enabled').is(':checked');
    var isServerSide  = $('#serverside_global_enabled').is(':checked');
    var $wrap         = $('#platform-fields');
    var $ssWrap       = $('#serverside-fields');

    // Dim platform-fields when platform OFF
    if ($wrap.length) {
      $wrap.css('opacity', isOn ? '' : 0.5);
    }

    // Dim serverside-fields when server-side OFF
    if ($ssWrap.length) {
      $ssWrap.css('opacity', isServerSide ? '' : 0.5);
    }

    // Required rules: pixel_id always required when platform on; access_token only when server-side on
    $('#pixel_id').prop('required', isOn);
    $('#access_token').prop('required', isOn && isServerSide);

    // Google-only (no-op on Meta/TikTok page if elements not present)
    var $gtmRow   = $('#gtm_container_id_row');
    var hasRadios = $('input[name="pixel_setting"]').length > 0;
    if ($gtmRow.length) {
      var isGtm = hasRadios && ($('input[name="pixel_setting"]:checked').val() === 'gtm');
      if (hasRadios) $gtmRow.toggleClass('d-none', !isGtm);
      $('#additional_id').prop('required', isOn && isGtm);
    }
  }

  // Initial state + listeners
  applyForCurrentPage();
  $(document).on('change', '#platform_enabled, #serverside_global_enabled, input[name="pixel_setting"]', applyForCurrentPage);
});
