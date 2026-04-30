//File: public_html\wp-content\plugins\unipixel\js\unipixel-consent-popup.js

(function () {
  'use strict';

  // Strings come from PHP via wp_localize_script. All values are already sanitised
  // server-side (short fields via sanitize_text_field, rich fields via wp_kses with
  // a strict allowlist). Short strings are assigned via textContent (no HTML ever);
  // rich strings are assigned via innerHTML only for the named fields below.
  var S = (typeof window.UnipixelConsentStrings === 'object' && window.UnipixelConsentStrings) || {};
  var C = (typeof window.UnipixelConsentConfig === 'object' && window.UnipixelConsentConfig) || {};

  function str(key, fallback) {
    return (typeof S[key] === 'string' && S[key] !== '') ? S[key] : (fallback || '');
  }

  function el(tag, opts) {
    var node = document.createElement(tag);
    if (!opts) return node;
    if (opts.id) node.id = opts.id;
    if (opts.className) node.className = opts.className;
    if (opts.text) node.textContent = opts.text;
    if (opts.html) node.innerHTML = opts.html;
    if (opts.attrs) {
      for (var k in opts.attrs) {
        if (Object.prototype.hasOwnProperty.call(opts.attrs, k)) {
          node.setAttribute(k, opts.attrs[k]);
        }
      }
    }
    return node;
  }

  function setConsentCookie(summary) {
    document.cookie =
      'unipixel_consent_summary=' +
      encodeURIComponent(JSON.stringify(summary)) +
      '; path=/; SameSite=Lax';
  }

  function hasConsentCookie() {
    return document.cookie.includes('unipixel_consent_summary=');
  }

  function createOverlay() {
    var overlay = el('div', { id: 'unipixel-consent-overlay' });
    document.body.appendChild(overlay);
    return overlay;
  }

  function removeOverlay() {
    var overlay = document.getElementById('unipixel-consent-overlay');
    if (overlay) overlay.remove();
  }

  // === Banner ===
  function showConsentBanner() {
    if (hasConsentCookie()) return;

    var style = (typeof C.style === 'string' && C.style) ? C.style : 'centred';
    var forceChoice = C.force_choice == null ? true : !!parseInt(C.force_choice, 10);

    if (forceChoice) {
      createOverlay();
    }

    var banner = el('div', { id: 'unipixel-consent-banner', className: 'unipixel-card unipixel-banner unipixel-style-' + style });

    banner.appendChild(el('h3', { className: 'unipixel-title', text: str('title', 'Your Privacy Choices') }));
    banner.appendChild(el('p', { className: 'unipixel-text', html: str('body', '') }));

    var btns = el('div', { className: 'unipixel-buttons' });

    var rejectBtn = null;
    if (C.show_reject) {
      rejectBtn = el('button', { id: 'upx-reject', className: 'upx-btn upx-btn-outline', text: str('btn_reject', 'Reject all') });
      btns.appendChild(rejectBtn);
    }

    var adjustBtn = el('button', { id: 'upx-adjust', className: 'upx-btn upx-btn-outline', text: str('btn_adjust', 'Adjust preferences') });
    var okBtn     = el('button', { id: 'upx-ok',     className: 'upx-btn upx-btn-dark-main', text: str('btn_accept', 'Accept all') });
    btns.appendChild(adjustBtn);
    btns.appendChild(okBtn);
    banner.appendChild(btns);

    document.body.appendChild(banner);

    okBtn.addEventListener('click', function () {
      setConsentCookie({ necessary: true, functional: true, performance: true, marketing: true });
      banner.remove();
      removeOverlay();
    });

    if (rejectBtn) {
      rejectBtn.addEventListener('click', function () {
        setConsentCookie({ necessary: true, functional: false, performance: false, marketing: false });
        banner.remove();
        removeOverlay();
      });
    }

    adjustBtn.addEventListener('click', showPreferencesPanel);
  }

  // === Preferences panel ===
  function showPreferencesPanel() {
    var style = (typeof C.style === 'string' && C.style) ? C.style : 'centred';
    // Preferences panel always uses the centred-card layout (it has more content
    // than fits in a corner card or top/bottom bar). The popup's chosen style
    // applies to the banner only.
    var panel = el('div', { id: 'unipixel-consent-panel', className: 'unipixel-card unipixel-panel unipixel-style-centred' });

    panel.appendChild(el('h3', { className: 'unipixel-title', text: str('panel_title', 'Manage Your Preferences') }));
    panel.appendChild(el('p', { className: 'unipixel-text-small', html: str('panel_body', '') }));

    // Build one switch row for a category. descHtml is the kses-cleaned rich description.
    function makeSwitch(id, labelText, descHtml) {
      var wrap = el('div', { className: 'upx-switch' });
      var input = el('input', { id: id, attrs: { type: 'checkbox', checked: 'checked' } });
      input.checked = true;
      var label = el('label', { attrs: { for: id } });
      label.appendChild(el('span'));

      var strong = el('strong', { text: labelText });
      label.appendChild(strong);

      var descSpan = el('span', { className: 'upx-switch-desc', html: ' — ' + descHtml });
      label.appendChild(descSpan);

      wrap.appendChild(input);
      wrap.appendChild(label);
      return wrap;
    }

    panel.appendChild(makeSwitch(
      'upx-functional',
      str('cat_functional_label', 'Functional cookies'),
      str('cat_functional_desc', '')
    ));
    panel.appendChild(makeSwitch(
      'upx-performance',
      str('cat_performance_label', 'Performance cookies'),
      str('cat_performance_desc', '')
    ));
    panel.appendChild(makeSwitch(
      'upx-marketing',
      str('cat_marketing_label', 'Marketing cookies'),
      str('cat_marketing_desc', '')
    ));

    panel.appendChild(el('p', { className: 'unipixel-text-small', html: str('panel_footer', '') }));

    var btns = el('div', { className: 'unipixel-buttons' });
    var cancelBtn = el('button', { id: 'upx-cancel', className: 'upx-btn upx-btn-outline', text: str('btn_cancel', 'Cancel') });
    var saveBtn   = el('button', { id: 'upx-save',   className: 'upx-btn upx-btn-dark',    text: str('btn_save', 'Save preferences') });
    btns.appendChild(cancelBtn);
    btns.appendChild(saveBtn);
    panel.appendChild(btns);

    document.body.appendChild(panel);

    saveBtn.addEventListener('click', function () {
      var summary = {
        necessary: true,
        functional: document.getElementById('upx-functional').checked,
        performance: document.getElementById('upx-performance').checked,
        marketing: document.getElementById('upx-marketing').checked
      };
      setConsentCookie(summary);
      panel.remove();
      var banner = document.getElementById('unipixel-consent-banner');
      if (banner) banner.remove();
      removeOverlay();
    });

    cancelBtn.addEventListener('click', function () { panel.remove(); });
  }

  window.addEventListener('load', showConsentBanner);
})();
