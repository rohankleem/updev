//File: public_html\wp-content\plugins\unipixel\js\unipixel-consent-popup.js

(function () {
  'use strict';

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
    const overlay = document.createElement('div');
    overlay.id = 'unipixel-consent-overlay';
    document.body.appendChild(overlay);
    return overlay;
  }

  function removeOverlay() {
    const overlay = document.getElementById('unipixel-consent-overlay');
    if (overlay) overlay.remove();
  }

  // === Banner ===
  function showConsentBanner() {
    if (hasConsentCookie()) return;

    const overlay = createOverlay();

    const banner = document.createElement('div');
    banner.id = 'unipixel-consent-banner';
    banner.className = 'unipixel-card unipixel-banner';
    banner.innerHTML = `
      <h3 class="unipixel-title">Your Privacy Choices</h3>
      <p class="unipixel-text">
        
This site uses cookies or similar technologies for technical purposes and, with your consent, for functionality, experience, measurement and “marketing (personalized ads)”.
You can choose which categories you’re happy for us to use before continuing, or by clicking Accept.
      </p>
      <div class="unipixel-buttons">
        <button id="upx-adjust" class="upx-btn upx-btn-outline">Adjust preferences</button>
        <button id="upx-ok" class="upx-btn upx-btn-dark-main">Accept all</button>
      </div>
    `;
    document.body.appendChild(banner);

    document.getElementById('upx-ok').addEventListener('click', () => {
      const summary = { necessary: true, functional: true, performance: true, marketing: true };
      setConsentCookie(summary);
      banner.remove();
      removeOverlay();
    });

    document.getElementById('upx-adjust').addEventListener('click', showPreferencesPanel);
  }

  // === Preferences panel ===
  function showPreferencesPanel() {
    const panel = document.createElement('div');
    panel.id = 'unipixel-consent-panel';
    panel.className = 'unipixel-card unipixel-panel';

    panel.innerHTML = `
      <h3 class="unipixel-title">Manage Your Preferences</h3>
      <p class="unipixel-text-small">
        You can control which types of events are allowed to be sent from this site.
      </p>

      <div class="upx-switch">
        <input type="checkbox" id="upx-functional" checked>
        <label for="upx-functional"><span></span>
          <strong>Functional cookies</strong> — used to keep your preferences saved (like this consent choice)
          and enable essential plugin functionality.
        </label>
      </div>

      <div class="upx-switch">
        <input type="checkbox" id="upx-performance" checked>
        <label for="upx-performance"><span></span>
          <strong>Performance cookies</strong> — allow anonymous analytics data
          for improving how conversion events (like <em>page_view</em> or <em>add_to_cart</em>) are tracked and measured.
        </label>
      </div>

      <div class="upx-switch">
        <input type="checkbox" id="upx-marketing" checked>
        <label for="upx-marketing"><span></span>
          <strong>Marketing cookies</strong> — enable tracking for advertising platforms like
          Meta, Google Ads, and TikTok, so that conversions can be reported back to those platforms.
        </label>
      </div>

      <p class="unipixel-text-small">
        <strong>Necessary cookies</strong> are always on and required for the site to function correctly.
        They do not include any marketing or analytics data.
      </p>

      <div class="unipixel-buttons">
        <button id="upx-cancel" class="upx-btn upx-btn-outline">Cancel</button>
        <button id="upx-save" class="upx-btn upx-btn-dark">Save preferences</button>
      </div>
    `;

    document.body.appendChild(panel);

    document.getElementById('upx-save').addEventListener('click', () => {
      const summary = {
        necessary: true,
        functional: document.getElementById('upx-functional').checked,
        performance: document.getElementById('upx-performance').checked,
        marketing: document.getElementById('upx-marketing').checked
      };
      setConsentCookie(summary);
      panel.remove();
      document.getElementById('unipixel-consent-banner')?.remove();
      removeOverlay();
    });

    document.getElementById('upx-cancel').addEventListener('click', () => panel.remove());
  }

  window.addEventListener('load', showConsentBanner);
})();
