<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Render the General Settings page.
 */
function unipixel_page_consent_settings()
{



    $consentDefaults = array(
        'consent_honour'    => 0,
        'consent_ui'        => 0,
        'consent_ui_style'  => 1, // Default style ID for future use
    );

    // Retrieve consent settings from the database; if none exist, use defaults.
    $consentOptions = get_option('unipixel_consent_settings', false);
    if (false === $consentOptions) {
        $consentOptions = $consentDefaults;
        update_option('unipixel_consent_settings', $consentOptions);
    }

?>
    <div class="UniPixelShell position-relative">
        <div class="d-flex justify-content-between align-items-start">
            <h1 class="mb-2"><i class="fa-solid fa-cookie-bite"></i> Consent Settings</h1>
            <?php unipixel_render_feedback_buttons(); ?>
        </div>

        <p>
            In some locations, local policies may require that websites comply with privacy regulations like the GDPR and the ePrivacy Directive, and that user consent before sending tracking information ("Cookie Consent").
        </p>
        <p>
            <b>UniPixel comes built-in with the option of Cookie Consent management</b>, including a user-facing consent pop-up. Event data is then respected based on that user's selections.
        </p>
        <p>
            <b>Using a third party Consent Manager?</b> Good news, UniPixel reads consent choices from OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, and Moove GDPR. If yours is on this list, you're set &mdash; nothing else to configure.
        </p>

        <p>
            Turning consent "On" means that UniPixel event tracking only takes place if the user has given consent in the relevant categories (e.g. Marketing or Performance).
            If consent is turned "Off", all events are sent regardless &mdash; no consent checking takes place.
        </p>

        <ul style="margin-left: 1.2em; padding-left: 1em; list-style: disc;">
            <li>When enabled, explicit consent choices must be present in the user's browser (set by one of the supported systems above) for events to be sent.</li>
            <li>If no consent choices are found, events will not be sent.</li>
        </ul>



        <!-- Feedback Message Container -->
        <div id="general-settings-feedback-message" class="alert" role="alert" style="display: none;"></div>

        <!-- Loader Element with Spinner -->
        <div id="general-settings-form-loader" class="d-flex justify-content-center align-items-center position-absolute w-100 h-100 d-none" style="top: 0; left: 0; z-index:1000;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <hr />
        <form id="consentSettingsForm" class="form-horizontal">

            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="enableConsentHonour" class="form-check-label  fw-semibold"">
                        Honour User Consent Settings
                    </label>
                </div>
                <div class=" col-9 col-sm-9">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                id="enableConsentHonour"
                                name="enableConsentHonour"
                                value="1"
                                class="form-check-input"
                                <?php checked($consentOptions['consent_honour'], true); ?>>
                        </div>
                </div>
            </div>


            <?php
                // Backward compatibility: any saved value that isn't 'unipixel' maps to 'thirdparty'
                $consent_ui_raw = $consentOptions['consent_ui'] ?? 'unipixel';
                $consent_ui_normalised = ($consent_ui_raw === 'unipixel') ? 'unipixel' : 'thirdparty';
            ?>
            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="consentUI" class="form-label fw-semibold">Consent Banner</label>
                </div>
                <div class="col-9 col-sm-9">
                    <select id="consentUI" name="consent_ui" class="form-select w-auto">
                        <option value="unipixel" <?php selected($consent_ui_normalised, 'unipixel'); ?>>UniPixel Consent Banner</option>
                        <option value="thirdparty" <?php selected($consent_ui_normalised, 'thirdparty'); ?>>Use my existing Consent Manager</option>
                    </select>
                    <div id="consentHelperUnipixel" class="form-text mt-2" style="display:none;">
                        Don't have a consent banner? UniPixel includes one. Your visitors see a consent popup, make their choices, and UniPixel respects them automatically.
                    </div>
                    <div id="consentHelperThirdparty" class="mt-2" style="display:none;">
                        UniPixel detects and respects consent choices from your existing consent manager. Supported: OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, and Moove GDPR. No extra configuration needed &mdash; UniPixel reads your visitors' consent cookies automatically and only sends events when consent has been given.
                    </div>
                </div>
            </div>
            <script>
            (function(){
                var sel = document.getElementById('consentUI');
                var helpUni = document.getElementById('consentHelperUnipixel');
                var helpThird = document.getElementById('consentHelperThirdparty');
                function toggleHelper() {
                    helpUni.style.display = (sel.value === 'unipixel') ? 'block' : 'none';
                    helpThird.style.display = (sel.value === 'thirdparty') ? 'block' : 'none';
                }
                sel.addEventListener('change', toggleHelper);
                toggleHelper();
            })();
            </script>





            <hr />

            <div class="mb-3 row">
                <div class="col-sm-12">
                    <input type="submit" value="Save Settings" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>
<?php
}
?>