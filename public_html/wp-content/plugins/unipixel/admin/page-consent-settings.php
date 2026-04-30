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
        'consent_honour'           => 0,
        'consent_ui'               => 0,
        'consent_ui_style'         => 1, // Default style ID for future use
        'consent_locale_override'  => 'auto',
        'consent_show_reject'      => 0,
        'consent_popup_style'      => 'centred',
        'consent_force_choice'     => 1,
    );

    // Retrieve consent settings from the database; if none exist, use defaults.
    $consentOptions = get_option('unipixel_consent_settings', false);
    if (false === $consentOptions) {
        $consentOptions = $consentDefaults;
        update_option('unipixel_consent_settings', $consentOptions);
    }

    // Ensure keys exist for forward compatibility
    $consentOptions = array_merge($consentDefaults, (array) $consentOptions);

    $consent_locale_override = isset($consentOptions['consent_locale_override']) ? $consentOptions['consent_locale_override'] : 'auto';
    $consent_show_reject     = isset($consentOptions['consent_show_reject']) ? (int) $consentOptions['consent_show_reject'] : 0;
    $consent_popup_style     = isset($consentOptions['consent_popup_style']) ? unipixel_consent_normalise_popup_style($consentOptions['consent_popup_style']) : 'centred';
    $consent_force_choice    = isset($consentOptions['consent_force_choice']) ? (int) $consentOptions['consent_force_choice'] : 1;
    $popup_styles            = unipixel_consent_get_popup_styles();
    $icon_allow              = unipixel_get_popover_allowlist();
    $available_locales       = unipixel_consent_available_locales();
    $added_locales           = unipixel_consent_get_override_locales();
    $consent_string_defaults = unipixel_consent_string_defaults();

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
            <b>Using a third party Consent Manager?</b> Good news, UniPixel reads consent choices from OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, Moove GDPR, and CookieAdmin (Softaculous). If yours is on this list, you're set &mdash; nothing else to configure.
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
                        <option value="unipixel" <?php selected($consent_ui_normalised, 'unipixel'); ?>>Use UniPixel's Consent Manager</option>
                        <option value="thirdparty" <?php selected($consent_ui_normalised, 'thirdparty'); ?>>Use Third Party Consent Manager</option>
                    </select>
                    <div id="consentHelperUnipixel" class="form-text mt-2" style="display:none;">
                        Don't have a consent banner? UniPixel includes one. Your visitors see a consent popup, make their choices, and UniPixel respects them automatically.
                    </div>
                    <div id="consentHelperThirdparty" class="mt-2" style="display:none;">
                        UniPixel detects and respects consent choices from your existing consent manager. Supported: OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, Moove GDPR, and CookieAdmin (Softaculous). No extra configuration needed &mdash; UniPixel reads your visitors' consent cookies automatically and only sends events when consent has been given.
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
                <div class="col-3 col-sm-3">
                    <label for="consentLocaleOverride" class="form-label fw-semibold">Popup language</label>
                </div>
                <div class="col-9 col-sm-9">
                    <select id="consentLocaleOverride" name="consent_locale_override" class="form-select w-auto">
                        <option value="auto" <?php selected($consent_locale_override, 'auto'); ?>>Auto-detect (use visitor or site locale)</option>
                        <?php foreach ($available_locales as $code => $label) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($consent_locale_override, $code); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text mt-2">
                        Auto-detect follows the visitor's WordPress locale, then falls back to the site language and finally to English.
                        Override to force a specific language for every visitor.
                    </div>
                    <?php
                    if ($consent_locale_override !== 'auto' && $consent_locale_override !== 'en_US') {
                        $has_override    = !empty(unipixel_consent_get_overrides_for_locale($consent_locale_override));
                        $has_translation = unipixel_consent_has_translation_for_locale($consent_locale_override);
                        if (!$has_override && !$has_translation) {
                            $forced_label = unipixel_consent_locale_label($consent_locale_override);
                    ?>
                        <div class="alert alert-warning mt-2 mb-0 py-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <strong><?php echo esc_html($forced_label); ?></strong> is forced, but no overrides and no community translation exist for it yet &mdash;
                            visitors will see the English default strings.
                            <br />Either add overrides in the <strong>Popup Languages &amp; Content</strong> section below,
                            or place <code>unipixel-<?php echo esc_html($consent_locale_override); ?>.mo</code>
                            in <code>wp-content/languages/plugins/</code> (update-safe &mdash; survives plugin auto-updates).
                        </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>

            <hr />

            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="enableConsentShowReject" class="form-check-label fw-semibold">
                        Show "Reject all" button
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                            id="enableConsentShowReject"
                            name="enableConsentShowReject"
                            value="1"
                            class="form-check-input"
                            <?php checked($consent_show_reject, 1); ?>>
                    </div>
                    <div class="form-text mt-2">
                        Off by default to keep the popup minimal. When on, visitors see a third button next to "Adjust preferences" and "Accept all".
                        Clicking <strong>Reject all</strong> sets all non-essential categories (functional, performance, marketing) to <strong>off</strong> &mdash; no tracking will be sent.
                    </div>
                </div>
            </div>

            <hr />

            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label class="form-label fw-semibold">
                        Popup style
                        <?php echo wp_kses(unipixel_get_help_icon('Consent_PopupStyle'), $icon_allow); ?>
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="d-flex flex-wrap gap-2 unipixel-style-tiles">
                        <?php foreach ($popup_styles as $style_id => $style_info) : ?>
                            <label class="unipixel-style-tile <?php echo ($consent_popup_style === $style_id) ? 'selected' : ''; ?>"
                                   for="popupStyle_<?php echo esc_attr($style_id); ?>"
                                   style="cursor: pointer; border: 2px solid <?php echo ($consent_popup_style === $style_id) ? '#2271b1' : '#ddd'; ?>; border-radius: 0.5rem; padding: 0.5rem; min-width: 130px; transition: border-color 0.15s;">
                                <input type="radio"
                                       name="consent_popup_style"
                                       id="popupStyle_<?php echo esc_attr($style_id); ?>"
                                       value="<?php echo esc_attr($style_id); ?>"
                                       <?php checked($consent_popup_style, $style_id); ?>
                                       style="display:none;">
                                <div class="unipixel-style-preview" data-style="<?php echo esc_attr($style_id); ?>"
                                     style="position: relative; width: 100%; height: 70px; background: #f0f0f1; border-radius: 0.25rem; overflow: hidden;">
                                    <?php
                                    // Inline mini-preview: a solid tile representing where the popup sits
                                    $tile_style = '';
                                    switch ($style_id) {
                                        case 'centred':      $tile_style = 'left:25%; right:25%; bottom:8px; height:18px;'; break;
                                        case 'bottom-bar':   $tile_style = 'left:0; right:0; bottom:0; height:18px; border-radius:0;'; break;
                                        case 'top-bar':      $tile_style = 'left:0; right:0; top:0; height:18px; border-radius:0;'; break;
                                        case 'bottom-left':  $tile_style = 'left:6px; bottom:6px; width:42px; height:24px;'; break;
                                        case 'bottom-right': $tile_style = 'right:6px; bottom:6px; width:42px; height:24px;'; break;
                                    }
                                    ?>
                                    <span style="position:absolute; background:#2271b1; border-radius:3px; <?php echo esc_attr($tile_style); ?>"></span>
                                </div>
                                <div class="text-center small fw-semibold mt-2" style="line-height: 1.2;">
                                    <?php echo esc_html($style_info['label']); ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <script>
                    (function () {
                        document.querySelectorAll('.unipixel-style-tile input[type="radio"]').forEach(function (input) {
                            input.addEventListener('change', function () {
                                document.querySelectorAll('.unipixel-style-tile').forEach(function (tile) {
                                    tile.classList.remove('selected');
                                    tile.style.borderColor = '#ddd';
                                });
                                var sel = input.closest('.unipixel-style-tile');
                                sel.classList.add('selected');
                                sel.style.borderColor = '#2271b1';
                            });
                        });
                    })();
                    </script>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="enableConsentForceChoice" class="form-check-label fw-semibold">
                        Force choice (dimmed backdrop)
                        <?php echo wp_kses(unipixel_get_help_icon('Consent_ForceChoice'), $icon_allow); ?>
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                            id="enableConsentForceChoice"
                            name="enableConsentForceChoice"
                            value="1"
                            class="form-check-input"
                            <?php checked($consent_force_choice, 1); ?>>
                    </div>
                    <div class="form-text mt-2">
                        On: dimmed backdrop, visitors must click a button before they can use the page.<br>
                        Off: popup is non-blocking &mdash; visitors can browse freely while it stays visible. Tracking still doesn't fire until they make a choice either way.
                    </div>
                </div>
            </div>

            <hr />

            <div class="mb-3 row">
                <div class="col-sm-12">
                    <input type="submit" value="Save Settings" class="btn btn-primary">
                </div>
            </div>
        </form>

        <hr />

        <h2 class="mt-4"><i class="fa-solid fa-flask"></i> Test the popup</h2>
        <p>
            Use these tools to preview and reset the consent popup as it appears to your visitors.
            Resetting clears <strong>this browser's</strong> consent cookie only &mdash; visitor cookies are unaffected.
        </p>

        <div class="card p-3 mb-4" style="max-width: 700px;">
            <div class="mb-3">
                <strong>Current consent cookie (this browser):</strong>
                <div id="upx-cookie-state" class="mt-1 small"
                     style="font-family: monospace; padding: 0.5rem 0.75rem; background: #f6f7f7; border-radius: 0.25rem;">
                    <em>checking&hellip;</em>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="fa-solid fa-up-right-from-square"></i> Open frontend (new tab)
                </a>
                <button type="button" id="upx-reset-consent" class="btn btn-outline-danger">
                    <i class="fa-solid fa-rotate-left"></i> Reset / clear my consent choice
                </button>
            </div>

            <div id="upx-reset-feedback" class="alert alert-success mt-3 mb-0 py-2" role="alert" style="display:none;"></div>
        </div>

        <script>
        (function () {
            function readConsentCookie() {
                var raw = document.cookie.split('; ').find(function (c) { return c.indexOf('unipixel_consent_summary=') === 0; });
                if (!raw) return null;
                try {
                    return JSON.parse(decodeURIComponent(raw.split('=')[1]));
                } catch (e) {
                    return { _raw: raw.split('=')[1] };
                }
            }

            function renderCookieState() {
                var box = document.getElementById('upx-cookie-state');
                if (!box) return;
                var summary = readConsentCookie();
                if (!summary) {
                    box.innerHTML = '<em>No choice made yet &mdash; the popup will show on next page load.</em>';
                    return;
                }
                var lines = [];
                ['necessary', 'functional', 'performance', 'marketing'].forEach(function (cat) {
                    if (summary.hasOwnProperty(cat)) {
                        var v = summary[cat];
                        var icon = v ? '✅' : '❌';
                        lines.push(icon + ' <strong>' + cat + '</strong>: ' + (v ? 'allowed' : 'denied'));
                    }
                });
                box.innerHTML = lines.length ? lines.join('<br>') : '<em>(unrecognised cookie content)</em>';
            }

            function showFeedback(msg) {
                var box = document.getElementById('upx-reset-feedback');
                box.textContent = msg;
                box.style.display = 'block';
                clearTimeout(box._t);
                box._t = setTimeout(function () { box.style.display = 'none'; }, 3500);
            }

            document.getElementById('upx-reset-consent').addEventListener('click', function () {
                document.cookie = 'unipixel_consent_summary=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
                renderCookieState();
                showFeedback('Cookie cleared. Reload the frontend to see the popup again.');
            });

            renderCookieState();
        })();
        </script>

        <h2 class="mt-4"><i class="fa-solid fa-language"></i> Popup Languages &amp; Content</h2>
        <p>
            Edit the text the consent popup shows visitors. You can change the default English wording to match your brand, jurisdiction, or legal counsel's guidance,
            and add translations for any other language your site supports.
        </p>
        <p class="text-muted">
            <strong>Merge order:</strong> admin override &gt; WordPress community translation (.po/.mo) &gt; built-in English default.
            Empty fields fall back to the default &mdash; you only have to fill in what you want to change.
        </p>
        <p class="text-muted small">
            <i class="fa-solid fa-circle-info"></i>
            Changes here save instantly via their own buttons (Add language &middot; Save <em>locale</em> &middot; Remove this language).
            The "Save Settings" button above doesn't apply to this section.
        </p>

        <?php
        $bundled = unipixel_consent_get_bundled_locales();
        if (!empty($bundled)) :
        ?>
            <div class="alert alert-info py-2 mb-3" style="font-size: 0.95em;">
                <strong><i class="fa-solid fa-circle-check"></i> Automatically supported (no setup needed):</strong>
                <br />
                UniPixel ships with bundled translations for
                <strong><?php echo count($bundled); ?> languages</strong> &mdash;
                visitors in these locales see a translated popup out of the box. You only need to add a language below if you want to override the wording, or if the locale isn't in this list.
                <details class="mt-2">
                    <summary style="cursor: pointer;">Show the list</summary>
                    <ul class="mt-2 mb-0" style="columns: 2;">
                        <?php foreach ($bundled as $code) : ?>
                            <li><code><?php echo esc_html($code); ?></code> &mdash; <?php echo esc_html(unipixel_consent_locale_label($code)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            </div>
        <?php endif; ?>

        <!-- Feedback for i18n actions -->
        <div id="consent-i18n-feedback" class="alert" role="alert" style="display: none;"></div>

        <div class="d-flex align-items-center gap-2 mb-3">
            <label for="consentI18nAddLocale" class="form-label mb-0 fw-semibold">Add/Customise language:</label>
            <select id="consentI18nAddLocale" class="form-select w-auto">
                <?php foreach ($available_locales as $code => $label) :
                    if (in_array($code, $added_locales, true)) { continue; } ?>
                    <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="consentI18nAddBtn" class="btn btn-outline-primary">
                <i class="fa-solid fa-plus"></i> Add
            </button>
        </div>

        <div id="consentI18nAccordion" class="accordion">
            <?php if (empty($added_locales)) : ?>
                <div class="text-muted fst-italic py-3" id="consentI18nEmptyMsg">
                    No languages added yet. All visitors see the default English strings (or a community translation if one exists for their locale).
                </div>
            <?php endif; ?>

            <?php foreach ($added_locales as $locale) :
                $overrides = unipixel_consent_get_overrides_for_locale($locale);
                $accordionId = 'consent-i18n-' . sanitize_html_class($locale);
            ?>
                <div class="accordion-item consent-i18n-locale-item" data-locale="<?php echo esc_attr($locale); ?>">
                    <h2 class="accordion-header" id="heading-<?php echo esc_attr($accordionId); ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-<?php echo esc_attr($accordionId); ?>"
                                aria-expanded="false"
                                aria-controls="collapse-<?php echo esc_attr($accordionId); ?>">
                            <strong><?php echo esc_html(unipixel_consent_locale_label($locale)); ?></strong>
                            <span class="text-muted ms-2">(<?php echo esc_html($locale); ?>)</span>
                            <?php if (!empty($overrides)) : ?>
                                <span class="badge bg-success ms-3"><?php echo count($overrides); ?> customised</span>
                            <?php else : ?>
                                <span class="badge bg-secondary ms-3">Using defaults</span>
                            <?php endif; ?>
                        </button>
                    </h2>

                    <div id="collapse-<?php echo esc_attr($accordionId); ?>"
                         class="accordion-collapse collapse"
                         aria-labelledby="heading-<?php echo esc_attr($accordionId); ?>"
                         data-bs-parent="#consentI18nAccordion">
                        <div class="accordion-body">
                            <form class="consent-i18n-form" data-locale="<?php echo esc_attr($locale); ?>">

                                <?php foreach ($consent_string_defaults as $key => $info) :
                                    $limits       = unipixel_consent_string_limits();
                                    $cap          = isset($limits[$key]) ? $limits[$key] : '';
                                    $currentValue = isset($overrides[$key]) ? $overrides[$key] : '';
                                    $defaultText  = $info['default'];
                                    $isRich       = ($info['type'] === 'rich');
                                ?>
                                    <div class="mb-4 consent-i18n-field" data-key="<?php echo esc_attr($key); ?>">
                                        <label class="form-label fw-semibold"><?php echo esc_html($info['label']); ?></label>
                                        <div class="small text-muted mb-1">
                                            <strong>Default:</strong> <?php echo $isRich ? wp_kses($defaultText, unipixel_consent_allowed_html()) : esc_html($defaultText); ?>
                                        </div>
                                        <?php if ($isRich) : ?>
                                            <textarea class="form-control consent-i18n-input"
                                                      name="<?php echo esc_attr($key); ?>"
                                                      rows="3"
                                                      maxlength="<?php echo esc_attr($cap); ?>"
                                                      placeholder="Leave empty to use the default"><?php echo esc_textarea($currentValue); ?></textarea>
                                            <div class="form-text">Limited HTML allowed: <code>&lt;a href&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;br&gt;</code>. Up to <?php echo esc_html($cap); ?> characters.</div>
                                        <?php else : ?>
                                            <input type="text"
                                                   class="form-control consent-i18n-input"
                                                   name="<?php echo esc_attr($key); ?>"
                                                   maxlength="<?php echo esc_attr($cap); ?>"
                                                   placeholder="Leave empty to use the default"
                                                   value="<?php echo esc_attr($currentValue); ?>">
                                            <div class="form-text">Plain text. Up to <?php echo esc_html($cap); ?> characters.</div>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-link consent-i18n-reset p-0 mt-1">
                                            <i class="fa-solid fa-rotate-left"></i> Clear override
                                        </button>
                                    </div>
                                <?php endforeach; ?>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <button type="button" class="btn btn-outline-danger consent-i18n-delete">
                                        <i class="fa-solid fa-trash"></i> Remove this language
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        Save <?php echo esc_html($locale); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Hidden template used by JS when a new locale is added live -->
        <template id="consentI18nItemTemplate">
            <div class="accordion-item consent-i18n-locale-item" data-locale="__LOCALE__">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-consent-i18n-__LOCALE_SLUG__"
                            aria-expanded="false">
                        <strong>__LABEL__</strong>
                        <span class="text-muted ms-2">(__LOCALE__)</span>
                        <span class="badge bg-secondary ms-3">Using defaults</span>
                    </button>
                </h2>
                <div id="collapse-consent-i18n-__LOCALE_SLUG__" class="accordion-collapse collapse" data-bs-parent="#consentI18nAccordion">
                    <div class="accordion-body">
                        <form class="consent-i18n-form" data-locale="__LOCALE__">
                            __FIELDS__
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button type="button" class="btn btn-outline-danger consent-i18n-delete">
                                    <i class="fa-solid fa-trash"></i> Remove this language
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Save __LOCALE__
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        <!-- Field template — used to build the language form for newly added locales -->
        <template id="consentI18nFieldTemplate">
            <?php foreach ($consent_string_defaults as $key => $info) :
                $limits = unipixel_consent_string_limits();
                $cap = isset($limits[$key]) ? $limits[$key] : '';
                $defaultText = $info['default'];
                $isRich = ($info['type'] === 'rich');
            ?>
                <div class="mb-4 consent-i18n-field" data-key="<?php echo esc_attr($key); ?>">
                    <label class="form-label fw-semibold"><?php echo esc_html($info['label']); ?></label>
                    <div class="small text-muted mb-1">
                        <strong>Default:</strong> <?php echo $isRich ? wp_kses($defaultText, unipixel_consent_allowed_html()) : esc_html($defaultText); ?>
                    </div>
                    <?php if ($isRich) : ?>
                        <textarea class="form-control consent-i18n-input"
                                  name="<?php echo esc_attr($key); ?>"
                                  rows="3"
                                  maxlength="<?php echo esc_attr($cap); ?>"
                                  placeholder="Leave empty to use the default"></textarea>
                        <div class="form-text">Limited HTML allowed: <code>&lt;a href&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;br&gt;</code>. Up to <?php echo esc_html($cap); ?> characters.</div>
                    <?php else : ?>
                        <input type="text" class="form-control consent-i18n-input"
                               name="<?php echo esc_attr($key); ?>"
                               maxlength="<?php echo esc_attr($cap); ?>"
                               placeholder="Leave empty to use the default"
                               value="">
                        <div class="form-text">Plain text. Up to <?php echo esc_html($cap); ?> characters.</div>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-link consent-i18n-reset p-0 mt-1">
                        <i class="fa-solid fa-rotate-left"></i> Clear override
                    </button>
                </div>
            <?php endforeach; ?>
        </template>

    </div>
<?php
}
?>