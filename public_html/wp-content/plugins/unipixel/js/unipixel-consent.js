// File: public_html\wp-content\plugins\unipixel\js\unipixel-consent.js

(function () {
	'use strict';


	var enableLogging_SendEvents = false;
	if (typeof UniPixelConsoleState !== 'undefined' && UniPixelConsoleState.logSendEvents === true) {
		enableLogging_SendEvents = true;
	}


	function log_Send(message, data) {
		if (enableLogging_SendEvents && typeof UniPixelConsoleLogger !== 'undefined') {
			UniPixelConsoleLogger.log('SEND', message, data);
		}
	}

	/**
	 * Get cookie value by name
	 */
	function getCookie(name) {
		const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
		return match ? decodeURIComponent(match[1]) : null;
	}

	/**
	 * OneTrust parser
	 * OptanonConsent cookie has groups like: groups=C0001:1,C0002:0,...
	 * We map them to our categories.
	 */
	function parseOneTrust() {
		const consent = getCookie('OptanonConsent');
		if (!consent) return {};

		const parsed = {};
		const groupsMatch = consent.match(/groups=([^;]+)/);
		if (!groupsMatch) return {};

		const pairs = groupsMatch[1].split(',');
		pairs.forEach(pair => {
			const [group, value] = pair.split(':');
			if (group && value) {
				// Example: simplistic mapping—needs to match your server-side mapping
				if (group === 'C0001') parsed.marketing = (value === '1');
				if (group === 'C0002') parsed.performance = (value === '1');
				if (group === 'C0003') parsed.functional = (value === '1');
			}
		});

		return parsed;
	}

	/**
	 * Silktide parser
	 * CookieControl cookie is JSON with { categories: { marketing: true, ... } }
	 */
	function parseSilktide() {
		const cookie = getCookie('CookieControl');
		if (!cookie) return {};

		try {
			const json = JSON.parse(cookie);
			if (!json || !json.categories) return {};
			return {
				marketing: !!json.categories.marketing,
				performance: !!json.categories.performance,
				functional: !!json.categories.functional
			};
		} catch (e) {
			return {};
		}
	}

	/**
	 * Cookiebot parser
	 * CookieConsent cookie is JSON with booleans for categories
	 */
	function parseCookiebot() {
		const cookie = getCookie('CookieConsent');
		if (!cookie) return {};

		try {
			const json = JSON.parse(cookie);
			return {
				necessary: !!json.necessary,
				performance: !!(json.statistics || json.preferences),
				marketing: !!json.marketing
			};
		} catch (e) {
			return {};
		}
	}

	/**
	 * Osano parser
	 * osano_consentmanager cookie is JSON with { consent: { marketing: true, ... } }
	 */
	function parseOsano() {
		const cookie = getCookie('osano_consentmanager');
		if (!cookie) return {};

		try {
			const json = JSON.parse(cookie);
			if (!json || !json.consent) return {};
			return {
				marketing: !!json.consent.marketing,
				performance: !!json.consent.performance,
				functional: !!json.consent.functional
			};
		} catch (e) {
			return {};
		}
	}


	/**
 * Orest Bida CMP parser
 * cookie_consent_user_accepted_categories cookie holds an array or string of accepted categories
 */
	function parseOrestBida() {
		const cookie = getCookie('cc_cookie') || getCookie('cookie_consent');
		if (!cookie) return {};

		try {
			const json = JSON.parse(cookie);
			const accepted = json.categories || json.acceptedCategories || [];
			return {
				necessary: accepted.includes('necessary'),
				functional: accepted.includes('functional') || accepted.includes('preferences'),
				performance: accepted.includes('performance') || accepted.includes('analytics') || accepted.includes('statistics'),
				marketing: accepted.includes('marketing') || accepted.includes('advertising')
			};
		} catch (e) {
			return {};
		}
	}


	/**
	 * Complianz parser
	 * Uses individual cookies per category: cmplz_marketing, cmplz_statistics, cmplz_functional, cmplz_preferences
	 * Values are 'allow' or 'deny'
	 */
	function parseComplianz() {
		const marketing = getCookie('cmplz_marketing');
		const statistics = getCookie('cmplz_statistics');
		const functional = getCookie('cmplz_functional');
		const preferences = getCookie('cmplz_preferences');

		if (!marketing && !statistics && !functional && !preferences) return {};

		return {
			necessary: true,
			marketing: marketing === 'allow',
			performance: statistics === 'allow',
			functional: functional === 'allow' || preferences === 'allow'
		};
	}

	/**
	 * CookieYes parser
	 * cookieyes-consent cookie has comma-separated key:value pairs
	 * e.g. "consentid:xxx,consent:yes,necessary:yes,functional:yes,analytics:yes,performance:yes,advertisement:yes"
	 */
	function parseCookieYes() {
		const cookie = getCookie('cookieyes-consent');
		if (!cookie) return {};

		const parsed = {};
		const pairs = cookie.split(',');
		pairs.forEach(function (pair) {
			const parts = pair.split(':');
			if (parts.length >= 2) {
				parsed[parts[0].trim()] = parts[1].trim();
			}
		});

		return {
			necessary: true,
			functional: parsed.functional === 'yes',
			performance: parsed.analytics === 'yes' || parsed.performance === 'yes',
			marketing: parsed.advertisement === 'yes'
		};
	}

	/**
	 * Moove GDPR parser
	 * moove_gdpr_popup cookie has comma-separated key:value pairs
	 * e.g. "strictly:1,thirdparty:0,advanced:0"
	 */
	function parseMooveGdpr() {
		const cookie = getCookie('moove_gdpr_popup');
		if (!cookie) return {};

		const parsed = {};
		const pairs = cookie.split(',');
		pairs.forEach(function (pair) {
			const parts = pair.split(':');
			if (parts.length === 2) {
				parsed[parts[0].trim()] = parts[1].trim();
			}
		});

		return {
			necessary: true,
			marketing: parsed.thirdparty === '1',
			performance: parsed.advanced === '1'
		};
	}


	function unipixel_applyConsentToGtag() {
		// Wait until gtag() is available
		if (typeof gtag !== 'function') {
			setTimeout(unipixel_applyConsentToGtag, 100);
			return;
		}

		try {
			const match = document.cookie.match(/(?:^|; )unipixel_consent_summary=([^;]*)/);
			if (!match) return;

			const summary = JSON.parse(decodeURIComponent(match[1]));

			gtag('consent', 'update', {
				'ad_storage': summary.marketing ? 'granted' : 'denied',
				'analytics_storage': summary.performance ? 'granted' : 'denied',
				'functionality_storage': summary.functional ? 'granted' : 'denied',
				'security_storage': 'granted'
			});

			console.log('UniPixel → Google consent updated:', summary);

		} catch (e) {
			console.warn('UniPixel → Google consent mapping failed', e);
		}
	}


	function unipixel_applyConsentToMicrosoftUET() {
		if (typeof window.uetq === 'undefined') return;

		try {
			const match = document.cookie.match(/(?:^|; )unipixel_consent_summary=([^;]*)/);
			if (!match) return;

			const summary = JSON.parse(decodeURIComponent(match[1]));
			window.uetq.push('consent', 'ad_storage', summary.marketing ? 'granted' : 'denied');

			console.log('UniPixel → Microsoft UET consent updated:', summary.marketing ? 'granted' : 'denied');

		} catch (e) {
			console.warn('UniPixel → Microsoft UET consent mapping failed', e);
		}
	}


	/**
	 * Main function to create the unified summary and store it
	 */
	window.unipixelCreateConsentSummaryFromVendors = function () {
		const summary = {
			necessary: true,  // usually assumed
			functional: null,
			performance: null,
			marketing: null
		};

		// Parse each vendor and merge
		Object.assign(summary, parseOneTrust());
		Object.assign(summary, parseSilktide());
		Object.assign(summary, parseOsano());
		Object.assign(summary, parseCookiebot());
		Object.assign(summary, parseOrestBida());
		Object.assign(summary, parseComplianz());
		Object.assign(summary, parseCookieYes());
		Object.assign(summary, parseMooveGdpr());


		// Write the final summary cookie
		document.cookie = 'unipixel_consent_summary=' +
			encodeURIComponent(JSON.stringify(summary)) +
			'; path=/; SameSite=Lax';
	};


	// On window load, run it automatically if enabled in settings
	window.addEventListener('load', () => {
		if (typeof window.UniPixelSettings === 'undefined') {
			//log_Send('UniPixel | Consent Settings | UniPixelSettings not found | Allowing events');
			return;
		}

		if (typeof window.UniPixelSettings.consent_honour === 'undefined') {
			//log_Send('UniPixel | Consent Settings | Setting not found | Allowing events');
			return;
		}

		if (window.UniPixelSettings.consent_honour != 1) { //handle string too
			//log_Send('UniPixel | Consent Settings | Setting is Off | Allowing events');
			return;
		} else {
			//log_Send('UniPixel | Consent Settings | Setting is On | Will check for consent choices being present');
		}

		if (typeof window.unipixelCreateConsentSummaryFromVendors !== 'function') {
			//log_Send('UniPixel | Consent Settings | Summary function not found.');
			return;
		}

		if (
			window.UniPixelSettings &&
			window.UniPixelSettings.consent_honour == 1 &&
			window.UniPixelSettings.consent_ui === 'unipixel'
		) {
			// UniPixel manages consent itself → don't auto-build summary here
			return;
		}

		window.unipixelCreateConsentSummaryFromVendors();

		unipixel_applyConsentToGtag();
		unipixel_applyConsentToMicrosoftUET();
	});


	window.unipixelCheckConsentForEvent = function () {
		try {
			if (!window.UniPixelSettings || parseInt(window.UniPixelSettings.consent_honour, 10) !== 1) {
				log_Send('UniPixel | Consent Check | Honour-Consent setting is OFF: allowing event.');
				return true; // Consent checking disabled, allow event
			}

			var cookie = document.cookie.match(/(?:^|; )unipixel_consent_summary=([^;]*)/);
			if (!cookie) {
				log_Send('UniPixel | Consent Check | Honour-Consent setting is ON, but no consent cookie found: blocking event.');
				return false; // Consent checking ON, no cookie = treat as no consent
			}

			var summary = JSON.parse(decodeURIComponent(cookie[1]));

			if (!summary.marketing || !summary.performance) {
				log_Send('UniPixel | Consent Check | Honour-Consent setting is ON and choices say not allowed: blocking event | marketing: ' + summary.marketing + ' | performance: ' + summary.performance);
				return false;
			}

			log_Send('UniPixel | Consent | Honour Consent is ON and choices allow event: allowing event | marketing: ' + summary.marketing + ' | performance: ' + summary.performance);
			return true;

		} catch (e) {
			log_Send('UniPixel | Consent | Consent check failed, blocking event by default.', e);
			return false;
		}
	};




})();
