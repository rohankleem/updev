/**
 * UniPixel – Apply Recommended Settings
 * -------------------------------------
 * Handles the "Apply Recommended Settings" button on each platform's event setup page.
 * Each platform has its own recommended settings object (Meta, Google, etc).
 */


(function ($) {
	'use strict';

	// ========================
	// Recommended Presets
	// ========================

	const UniPixelRecommendedSettings_Meta = {
		pageview: {
			pageview_send_clientside: true,
			pageview_send_serverside: true,
			send_server_log_response: false
		},
		woo: {
			// event_platform_ref → { send_client, send_server, send_server_log_response }
			InitiateCheckout: { send_client: true, send_server: true, send_server_log_response: false },
			Purchase: { send_client: true, send_server: true, send_server_log_response: true },
			ViewContent: { send_client: true, send_server: true, send_server_log_response: false },
			AddToCart: { send_client: true, send_server: true, send_server_log_response: false }
		},
		custom: {
			send_client: true,
			send_server: true,
			send_server_log_response: false
		}
	};

	const UniPixelRecommendedSettings_TikTok = {
		pageview: {
			pageview_send_clientside: false,
			pageview_send_serverside: false,
			send_server_log_response: false
		},
		woo: {
			// event_platform_ref → { send_client, send_server, send_server_log_response }
			InitiateCheckout: { send_client: true, send_server: true, send_server_log_response: false },
			Purchase: { send_client: true, send_server: true, send_server_log_response: true },
			ViewContent: { send_client: true, send_server: true, send_server_log_response: false },
			AddToCart: { send_client: true, send_server: true, send_server_log_response: false }
		},
		custom: {
			send_client: true,
			send_server: true,
			send_server_log_response: false
		}
	};

	const UniPixelRecommendedSettings_Google = {
		pageview: {
			pageview_send_clientside: true,
			pageview_send_serverside: false,
			send_server_log_response: false
		},
		woo: {
			begin_checkout: { send_client: true, send_server: false, send_server_log_response: false },
			purchase: { send_client: true, send_server: true, send_server_log_response: true },
			view_item: { send_client: true, send_server: false, send_server_log_response: false },
			add_to_cart: { send_client: true, send_server: false, send_server_log_response: false }
		},
		custom: {
			send_client: true,
			send_server: false,
			send_server_log_response: false
		}
	};

	const UniPixelRecommendedSettings_Pinterest = {
		pageview: {
			pageview_send_clientside: true,
			pageview_send_serverside: true,
			send_server_log_response: false
		},
		woo: {
			initiate_checkout: { send_client: true, send_server: true, send_server_log_response: false },
			checkout: { send_client: true, send_server: true, send_server_log_response: true },
			view_content: { send_client: true, send_server: true, send_server_log_response: false },
			add_to_cart: { send_client: true, send_server: true, send_server_log_response: false }
		},
		custom: {
			send_client: true,
			send_server: true,
			send_server_log_response: false
		}
	};

	const UniPixelRecommendedSettings_Microsoft = {
		pageview: {
			pageview_send_clientside: true,
			pageview_send_serverside: true,
			send_server_log_response: false
		},
		woo: {
			// event_platform_ref → { send_client, send_server, send_server_log_response }
			begin_checkout: { send_client: true, send_server: true, send_server_log_response: false },
			purchase: { send_client: true, send_server: true, send_server_log_response: true },
			view_item: { send_client: true, send_server: true, send_server_log_response: false },
			add_to_cart: { send_client: true, send_server: true, send_server_log_response: false }
		},
		custom: {
			send_client: true,
			send_server: true,
			send_server_log_response: false
		}
	};

	const UniPixelRecommendedSettings_Map = {
		"meta": UniPixelRecommendedSettings_Meta,
		"tiktok": UniPixelRecommendedSettings_TikTok,
		"google": UniPixelRecommendedSettings_Google,
		"pinterest": UniPixelRecommendedSettings_Pinterest,
		"microsoft": UniPixelRecommendedSettings_Microsoft
	};

	// ========================
	// Core Logic
	// ========================

	$(document).ready(function () {


		// Map numeric platform_id values to their names
		const platformMap = {
			1: 'meta',
			2: 'pinterest',
			3: 'tiktok',
			4: 'google',
			5: 'microsoft'
		};

		const currentPlatformId = $('#platform_id').val();
		const currentPlatform = platformMap[currentPlatformId] || 'meta';
		const settings = UniPixelRecommendedSettings_Map[currentPlatform];


		if (!settings) {
			return;
		}

		if (!settings) return; // No map found — ignore

		// Inject button if not already present (fallback safety)
		if (!$('#btnApplyRecommended').length) {
			const btn = $('<button>', {
				id: 'btnApplyRecommended',
				type: 'button',
				class: 'btn btn-outline-secondary me-2',
				text: 'Apply Recommended Settings'
			});
			$('#btnUniPixelUpdateAll').before(btn);
		}

		// Click handler
		$('#btnApplyRecommended').on('click', function () {
			// 1️⃣ PageView toggles
			for (const [id, state] of Object.entries(settings.pageview)) {
				$('#' + id).prop('checked', !!state);
			}

			// 2️⃣ WooCommerce events
			$('#woo-events-table tbody tr').each(function () {
				const eventRef = ($(this).data('event-platform-ref') || '').toLowerCase();
				const map = Object.keys(settings.woo).reduce((found, key) => {
					return key.toLowerCase() === eventRef ? settings.woo[key] : found;
				}, null);

				if (map) {
					$(this).find('input[name^="woo_send_client"]').prop('checked', map.send_client);
					$(this).find('input[name^="woo_send_server"]').prop('checked', map.send_server);
					$(this).find('input[name^="woo_event_logresponse"]').prop('checked', map.send_server_log_response);
				}
			});


			// 3️⃣ Custom events
			$('#event-settings-table tbody tr').each(function () {
				$(this).find('input[name^="send_client"]').prop('checked', settings.custom.send_client);
				$(this).find('input[name^="send_server"]').prop('checked', settings.custom.send_server);
				$(this).find('input[name^="send_server_log_response"]').prop('checked', settings.custom.send_server_log_response);
			});

			// 4️⃣ Confirmation modal
			setTimeout(showConfirmModal, 1000);
		});

		function showConfirmModal() {
			const modalHtml = `
            <div class="modal fade" id="applyRecommendedModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Apply Recommended Settings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Recommended tracking settings have been applied for <strong>${currentPlatform}</strong>, but not saved yet.</p>
                            <p>Would you like to save these changes now?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Don't Save Yet</button>
                            <button type="button" class="btn btn-primary" id="btnSaveNow">Save These Settings</button>
                        </div>
                    </div>
                </div>
            </div>`;

			$('body').append(modalHtml);
			const modal = new bootstrap.Modal(document.getElementById('applyRecommendedModal'));
			modal.show();

			$('#btnSaveNow').on('click', function () {
				modal.hide();

				setTimeout(function () {
					// ✅ simulate an actual manual click on the form’s submit button
					document.getElementById('btnUniPixelUpdateAll').click();
				}, 300);
			});

		}
	});


	// ====================================
	// Recommended Settings Help Modal
	// ====================================

	$('#btnRecommendedHelp').on('click', function (e) {
		e.preventDefault();

		const platform = $('[data-platform]').data('platform'); // "meta" or "google"
		let helpText = '';

		if (platform === 'meta') {
			helpText = `
            <p><strong>Meta Pixel Recommended Settings</strong></p>
            <p>
                Meta’s recommended setup sends events both from the browser (client-side)
                and from your server (server-side). This is known as <em>deduplication</em> — 
                Meta combines these two signals to ensure more reliable attribution while avoiding duplicates.
            </p>
            <p>
                For most events, it’s best to enable both <strong>Send Client-side</strong> and 
                <strong>Send Server-side</strong> so Meta can match conversions accurately.
                However, it’s safe to disable either if you prefer a single-source setup.
            </p>
            <p>
                The <strong>Log Server-side Response</strong> toggle controls whether UniPixel logs 
                the response from Meta’s Conversion API in your WordPress database.
                This only applies when <strong>Send Server-side</strong> is enabled.
            </p>
            <p>
                You can adjust these toggles to suit your needs — but applying the recommended 
                settings gives a strong balance between accuracy and simplicity for most sites.
            </p>
        `;
		}
		else if (platform === 'google') {
			helpText = `
            <p><strong>Google Events Recommended Settings</strong></p>
			<p>
			Unlike Meta, Google does not use deduplication practices, meaning that if you send both client and server-side events, 
			they will be tracked twice, resulting in double-up. 

			</p>
            
            <p>
                An exception is the <strong>Purchase</strong> event, where server-side data 
                can safely complement the client signal. This ensures completed orders are 
                reported even when ad blockers or tracking restrictions affect the browser signal.
            </p>

			<p>
				Client-side is the recommended default starting point because browser-run tags are simple to deploy, easy to verify in real time, and naturally capture rich on-page context (URL, referrer, device, clicks); consider moving select events to server-side when you want stronger delivery under blockers, first-party enrichment from server data.
            </p>
			<p>
				Note also, this interface prevents setting most Google events (except 'purchase') as both client-side and server-side - you'll notice one goes off when the other goes on. 
				This is to help with compliance around Google's "lack of deduplication" features and to prevent double counting.
			</p>
            <p>
                You can freely tweak these to your preference, but applying the recommended 
                settings provides the optimal, low-maintenance setup for most Google Analytics 
                and Ads configurations.
            </p>
        `;
		}
		else if (platform === 'microsoft') {
			helpText = `
            <p><strong>Microsoft Recommended Settings</strong></p>
            <p>
                Microsoft supports full deduplication between the UET browser tag and the
                Conversions API (CAPI) using a shared <em>eventId</em>. This means you can
                safely enable both <strong>Send Client-side</strong> and
                <strong>Send Server-side</strong> — Microsoft will combine the two signals
                and count each conversion only once.
            </p>
            <p>
                Sending both provides the best attribution accuracy: the browser signal captures
                real-time user context, while the server signal ensures delivery even when
                ad blockers or browser restrictions are present. Microsoft also uses the
                <em>msclkid</em> (click ID) captured automatically by UniPixel to improve
                attribution for users arriving from Microsoft Ads.
            </p>
            <p>
                The <strong>Log Server-side Response</strong> toggle controls whether UniPixel
                stores the response from Microsoft's Conversions API in your WordPress database.
                This is recommended for the Purchase event to verify successful delivery,
                but can be left off for high-frequency events like PageView to avoid unnecessary overhead.
            </p>
            <p>
                You can adjust these toggles to suit your needs — but the recommended settings
                give you the strongest tracking setup for most Microsoft Ads configurations.
            </p>
        `;
		}
		else if (platform === 'pinterest') {
			helpText = `
            <p><strong>Pinterest Recommended Settings</strong></p>
            <p>
                Pinterest supports full deduplication across client-side and server-side events
                using a shared <em>event_id</em>. This means you can safely enable both
                <strong>Send Client-side</strong> and <strong>Send Server-side</strong> —
                Pinterest will combine the two signals and count each conversion only once.
            </p>
            <p>
                Sending both provides the best attribution accuracy: the browser signal captures
                real-time user context, while the server signal ensures delivery even when
                ad blockers or browser restrictions are present.
            </p>
            <p>
                The <strong>Log Server-side Response</strong> toggle controls whether UniPixel
                stores the response from Pinterest's Conversions API in your WordPress database.
                This is recommended for the Purchase event to verify successful delivery.
            </p>
            <p>
                You can adjust these toggles to suit your needs — but the recommended settings
                give you the strongest tracking setup for most Pinterest Ads configurations.
            </p>
        `;
		}
		else {
			helpText = `
            <p><strong>Recommended Settings</strong></p>
            <p>
                These defaults represent the typical balance between client-side and server-side
                event sending for this platform. Adjust them as you wish, but for most cases,
                the recommended options provide the best compatibility and reliability.
            </p>
        `;
		}

		$('#recommendedHelpBody').html(helpText);
		const modal = new bootstrap.Modal(document.getElementById('recommendedHelpModal'));
		modal.show();
	});


})(jQuery);
