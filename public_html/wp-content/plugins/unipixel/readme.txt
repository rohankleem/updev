=== UniPixel: Meta, Pinterest, TikTok, Google & Microsoft Server-Side Tracking for WooCommerce ===
Contributors: buildiodev
Tags: meta conversion api, pinterest conversions api, server-side tracking, tiktok events api, woocommerce pixel
Requires at least: 5.0
Tested up to: 6.9.4
Requires PHP: 7.0
Stable tag: 2.6.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Send conversion events from your WordPress server to Meta, Pinterest, TikTok, Google and Microsoft. No cloud, no GTM. WooCommerce and custom events.

== Description ==

Ad blockers, iOS privacy changes and cookie restrictions mean your browser-based tracking pixel is only reporting a fraction of your actual conversions. Your ads cannot optimise on data they never receive.

UniPixel fixes this by sending conversion events directly from your WordPress server to Meta, Pinterest, TikTok, Google and Microsoft — bypassing the browser entirely. No external cloud hosting, no GTM server container. Install, paste your credentials, and server-side tracking is live.

### What It Does

- **Meta Conversion API (CAPI)** — server-side and client-side event sending with full deduplication.
- **Pinterest Conversions API** — server-side and client-side tracking with full deduplication.
- **TikTok Events API** — server-side and client-side tracking with automatic deduplication.
- **GA4 Measurement Protocol** — server-side event delivery to Google Analytics and Google Ads.
- **Microsoft UET & Conversions API** — client-side tracking via UET with early support for Microsoft's Conversions API (CAPI) pilot program. WooCommerce events and custom events supported.
- **WooCommerce events** — ViewContent, AddToCart, InitiateCheckout and Purchase tracked automatically with full product data (names, categories, variants, values).
- **Custom events** — create your own click, view and interaction events for any WordPress page. Not limited to WooCommerce.
- **Automatic deduplication** — each event gets a unique ID shared between client and server, so platforms count it once.
- **Built-in consent management** — fully featured popup with built-in translations for 18 languages, editable wording per language, 5 layout styles (centred card / top or bottom bar / corner card), optional non-blocking mode, and a Reject all button toggle. Or skip the popup entirely and let UniPixel read consent from your existing CMP — supports OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, Moove GDPR, and CookieAdmin (Softaculous).
- **Live event testing** — real-time console shows events as they fire, with full payload data.
- **Event logging** — optional database storage of every event sent, for debugging and audit.

### Why Server-Side Tracking Matters

Around 43% of internet users run ad blockers. Safari limits cookies to 7 days. iOS App Tracking Transparency reduced reported conversions by 30-40% for many advertisers. When your tracking relies only on a browser pixel, these restrictions silently discard your conversion data.

Server-side tracking sends conversion data directly from your server to the ad platform API. Ad blockers cannot touch it. Cookie limits do not apply. The result: your ad platforms see your real conversions, optimise better, and your ad spend works harder.

### Why UniPixel

UniPixel sends API calls directly from your own WordPress server. No GTM server container, no external cloud, no routing your data through third-party services.

- **Meta, Pinterest, TikTok, Google and Microsoft** — server-side and client-side tracking for all five platforms.
- **No GTM required** — no server container setup, no custom subdomain, no separate hosting.
- **No external cloud** — your data goes from your server to the ad platform. Nothing in between.
- **Works on any WordPress site** — WooCommerce stores get automatic ecommerce event tracking. Non-WooCommerce sites use custom events for lead gen, SaaS, content or anything else.
- **Five-minute setup** — install, enter your Pixel ID and access token, enable the platform. Done.

Optional [Advanced Matching](https://unipixelhq.com/unipixel-docs/advanced-matching-setting-with-unipixel/) sends hashed user data (email, phone, address) alongside events to help platforms improve Event Match Quality.

== Installation ==

1. In WordPress Admin, go to Plugins > Add New.
2. Search for "UniPixel".
3. Click Install Now, then Activate.
4. Go to UniPixel in the admin menu to configure your platforms.

== Frequently Asked Questions ==

= What credentials do I need for Meta? =
Your Facebook Pixel ID (from Events Manager) and an Access Token (from Business Settings > System Users). Enter both in UniPixel > Meta Setup.

= What credentials do I need for Google? =
Your GA4 Measurement ID (format: G-XXXXXXXXXX) and a Measurement Protocol API Secret (from Admin > Data Streams). Optionally, a GTM Container ID if you use Google Tag Manager. Enter these in UniPixel > Google Setup.

= What credentials do I need for Pinterest? =
Your Pinterest Tag ID, Ad Account ID and a Conversions API Access Token (from Ads Manager > Conversions > Set Up API). Enter all three in UniPixel > Pinterest Setup.

= What credentials do I need for TikTok? =
Your TikTok Pixel ID and an Access Token (from Events Manager > Pixel Settings > Advanced Settings). Enter both in UniPixel > TikTok Setup.

= What credentials do I need for Microsoft? =
Your Microsoft UET Tag ID and a CAPI Access Token (from Microsoft Advertising > Conversion Tracking > UET Tag Settings). Enter both in UniPixel > Microsoft Setup.

= What if I already have a Meta pixel or Google tag on my site? =
Select "Pixel Already Included" in UniPixel settings. This prevents duplicate tracking scripts while still allowing UniPixel to send server-side events and custom events.

= How does deduplication work? =
Each event gets a unique event ID. The same ID is sent via the browser pixel and the server API call. Meta, Pinterest, TikTok and Google use this ID to merge duplicate events, counting them once instead of twice.

= Does this work without WooCommerce? =
Yes. WooCommerce events (Purchase, AddToCart, etc.) require WooCommerce. But PageView, custom click events and custom interaction events work on any WordPress site.

= Do I need a GTM server container? =
No. UniPixel sends server-side events directly from your WordPress server to each platform's API. No Google Tag Manager setup is required.

== Tips ==

= Deduplication =
UniPixel automatically prevents duplicate counting when both client-side and server-side events are enabled.

Each event is assigned a unique **event_id** when triggered. This same ID is sent to both the browser pixel and the server API, allowing platforms to merge identical events.

- **Meta (Facebook / Instagram)** — uses `event_id` to match Pixel and Conversion API events so they count once.
- **Pinterest** — uses `event_id` to match Tag and Conversions API events for full deduplication.
- **TikTok** — uses shared event IDs across Pixel and Events API for seamless deduplication.
- **Google (GA4 / Ads)** — uses shared `client_id` and `session_id` values for client/server matching. Works automatically for both client-first and server-first setups.
- **Microsoft (Bing / UET)** — uses `eventId` to match UET tag and Conversions API events for deduplication.

No extra setup is required — UniPixel handles ID creation and matching automatically.

== Privacy and 3rd Party Services ==

This plugin sends event data to external services for analytics and advertising purposes. Data is only sent to platforms you have enabled in UniPixel settings.

- **Meta (Facebook) Pixel and Conversion API**: Sends user event data (e.g., PageView, Purchase) to Meta for tracking and ad optimisation.
- **Pinterest Tag and Conversions API**: Sends event data to Pinterest for ad tracking and campaign optimisation.
- **TikTok Pixel and Events API**: Sends event data to TikTok for ad tracking and campaign optimisation.
- **Google Analytics and Measurement Protocol**: Sends event data to Google for analytics and conversion tracking via GA4 and Google Tag Manager.
- **Microsoft UET and Conversions API**: Sends event data to Microsoft for ad tracking and conversion optimisation via Bing Ads.

### Domains

This plugin communicates with the following domains:
- **Meta (Facebook)**:
  - https://www.facebook.com
  - https://graph.facebook.com
- **Google**:
  - https://www.google-analytics.com
  - https://www.googletagmanager.com
- **Pinterest**:
  - https://api.pinterest.com
  - https://s.pinimg.com
- **TikTok**:
  - https://business-api.tiktok.com
- **Microsoft**:
  - https://bat.bing.com
  - https://capi.uet.microsoft.com
- **Buildio**:
  - https://buildio.dev

### Data Sent

Data sent to these domains may include:
- User interactions (e.g., clicks, page views, purchases)
- IP address and user agent (collected by tracking mechanisms)
- Custom event data such as purchase amount, currency, and transaction ID (depending on configuration)
- Diagnostic information may be sent to Buildio to assist with plugin performance and bug fixes, including site URL, IP address and basic interactions. No sensitive information or keys are sent.

### Privacy and Terms

For more details on privacy policies and terms of use, please visit the following links:

- [Meta (Facebook) Privacy Policy](https://www.facebook.com/about/privacy)
- [Meta (Facebook) Terms of Service](https://www.facebook.com/terms.php)
- [Google Privacy Policy](https://policies.google.com/privacy)
- [Google Terms of Service](https://policies.google.com/terms)
- [Pinterest Privacy Policy](https://policy.pinterest.com/privacy-policy)
- [Pinterest Terms of Service](https://policy.pinterest.com/terms-of-service)
- [TikTok Privacy Policy](https://www.tiktok.com/legal/privacy-policy)
- [TikTok Terms of Service](https://www.tiktok.com/legal/terms-of-service)
- [Microsoft Privacy Statement](https://privacy.microsoft.com/privacystatement)
- [Microsoft Advertising Agreement](https://about.ads.microsoft.com/en-us/resources/policies/microsoft-advertising-agreement)
- [UniPixel Privacy Policy](https://unipixelhq.com/privacy)


== Changelog ==

= 2.6.7 =
* Renamed "Custom events" to "Site events" across the admin for clearer wording.
* Added help icons in the Event Manager.
* Fixed - issue with plugin debugging settings causing WordPress error in some scenarios.

= 2.6.6 =
* New: Centralised Event Manager. New admin page where you can set up a custom event across every platform in one go. Pick a conversion type (Lead, Newsletter Signup, Contact, Registration, Search or your own) and UniPixel fills in each platform's standard event name automatically.
* New: URL-based trigger for custom events. Fire when a visitor lands on a specific page (great for thank-you pages, lead pages, post-checkout pages). Pick a page from your site, match a wildcard pattern like /thank-you*, or fire on every page.
* New: Standard event name picker. When adding custom events you now choose from each platform's standard event list (Lead, Contact, Subscribe and the rest) instead of typing them by hand. Custom names are still supported.
* Improvement: Fire-once-per-session guard on URL events stops conversion events double-counting if the visitor reloads the page.
* Improvement: Google client/server mutual exclusion rule enforced inline in the Event Manager (Google permits one or the other for non-Purchase events).
* Improvement: Disabled-platform hint with quick links so you can enable a platform straight from the Event Manager.
* Improvement: Home dashboard reorganised. New full-width Event Manager card. Pinterest moved to the right with its brand pink.

= 2.6.5 =
* New: Optional "Reject all" button — give visitors a one-click way to decline tracking.
* New: Popup layout options — choose centred card, full-width top or bottom bar, or a small corner card to fit your site.
* New: Optional non-blocking mode — let visitors keep browsing while the popup stays visible (tracking still pauses until they choose).
* New: Now compatible with CookieAdmin (Softaculous) consent banner — UniPixel reads its choices automatically.
* Improvement: Popup is mobile-friendly — buttons stack cleanly on phones and corner layouts.
* Fix: Popup animation no longer drifts horizontally when it appears.

= 2.6.4 =
* New: Multi-language consent popup — built-in translations for 18 languages (Spanish, French, German, Italian, Portuguese, Dutch, Polish, Japanese, Chinese, Korean, Turkish, Arabic, Russian, Swedish, Czech and more), matched to each visitor's language automatically.
* New: Editable popup text — change any wording in any language from the admin to match your brand or jurisdiction.
* New: Popup language control — auto-detect the visitor's language or force a specific one.

= 2.6.3 =
* Fix: Improved compatibility with WordPress themes and configurations. Event tracking now works reliably across all WordPress sites.

= 2.6.1 =
* Fix: Resolved a compatibility issue that could prevent activation on some hosting environments.

= 2.6.0 =
* New: Microsoft WooCommerce events — Purchase, AddToCart, Checkout and ViewContent now tracked automatically for Microsoft Advertising, with support for Microsoft's new Conversions API (CAPI) pilot program.
* New: Microsoft admin experience — updated events page with one-click recommended settings.
* Improvement: AddToCart tracking now works across more WordPress and WooCommerce configurations, delivering more accurate conversion data to all five platforms.
* Improvement: Checkout events now register unique purchase intent more accurately, improving conversion reporting for ad platform optimisation.

= 2.5.4 =
* Fix: Resolved an issue affecting WooCommerce event tracking introduced in 2.5.3. All events now fire correctly.

= 2.5.3 =
* New: Advanced Matching — automatically sends hashed user data (email, phone, name, address) with events to Meta, TikTok and Pinterest. Improves Event Match Quality scores for better attribution. Enable via General Settings.
* Improvement: Event matching and data quality improvements across all platforms.

= 2.5.2 =
* New: Pinterest Conversions API — full server-side and client-side tracking with deduplication. WooCommerce events (Purchase, AddToCart, InitiateCheckout, ViewContent), PageView, and custom events all supported.

= 2.5.1 =
* New: Server-side tracking is now optional per platform — start with client-side only and enable server-side when ready.
* Improvement: Access tokens no longer required during initial setup. Add them later to activate server-side tracking.

= 2.5.0 =
* New: Consent support added for Complianz, CookieYes, and Moove GDPR — nine consent managers now supported in total.
* Improvement: Simplified consent settings — choose between UniPixel's built-in banner or your existing consent manager. UniPixel detects and respects choices automatically.
* Help and documentation improvements.

= 2.4.0 =
* Improvement: Richer product data — variant, category and item-level values now sent across all WooCommerce events for better reporting in Meta, TikTok and Google.
* Improvement: Better cookie handling for fbclid and gclid click attribution.
* Fix: TikTok deduplication — conversions are no longer double-counted.
* Fix: AddToCart now reports the correct item value and correctly identifies variable products (sizes, colours, etc.).

= 2.3.2 =
* Improvement: Dashboard updates, all three platforms now shown on the home page.
* Improvement: Consent popup width adjustment and copy refinement.

= 2.3.1 =
* Bug fix: important code update to fix a function that created an error

= 2.3.0 =
* New: TikTok now added! Track events both client-side and server-side to TikTok's Event API.


= 2.2.0 =
* New: Comes with Consent Management Banner now built in to UniPixel. Easy configuration and one-click compliance for your site along with the benefits of server-side events.

= 2.1.4 =
* Minor updates and fixes:
  - Improved Orest Bida cookie consent detection and handling (2.1.1 – 2.1.3)
  - Small database schema update and upgrade fix (2.1.4)

= 2.1.0 =

* New: Control over both client-side setting and server-side setting for all of your events, including PageView, WooCommerce events and your own custom events.
* New: Easy to apply "Use Recommended Settings" button, to make setup much easier.
* New: Better settings for Google events, clearer separation of Client-side and Server-side choices
* New: Testing Console that supports seeing your events fire in real time to support better debugging and setup.
* New: Better options and control over storing/logging of all the vents that are fired, with more information too.
* New: Google PageView events now supports client-side and new server-side sending options.

= 1.15.1 =
* Fix: some events were not sending due to bug in Consent settings

= 1.15.0 =
* New feature: Honouring of Cookie Consent choices from supported systems: OneTrust, Cookiebot, or Osano. Turning this On means that UniPixel event tracking only takes place if the user has given consent in the relevant categories (e.g. Marketing or Performance). Optional setting.
* Improvement: ViewContent / view_item now filters out bots and crawlers to provide more accurate visitor data (server-side events were greater in qty than client side events)
* Fix: View Content for Google fixed to be correct event terminology, "view_item".

= 1.14.1 =
* Fix for "Enable Platform" setting not being honoured properly on some occasions when sending events.

= 1.14.0 =
* New feature to send 'ViewContent' / 'view_content' events for WooCommerce when products are viewed, providing additional information to Meta and Google. This helps populate reports in Google Analytics and Meta for better insights to things like "Purchase Journey", and adds to Sales Funnel insights.
* New feature to Turn-on or Turn-off platform Response Logging for WooCommerce server-side events (the response from Google or Meta). Provides option to improve performance if desired vs documenting the response from Google and Meta. Server-side Response Logging is turned-off by default for ViewContent events (to assist with performance for common occuring content views).
* Performance improvement: If Response Logging is turned off for Server-side events, this is now coupled with another update that allows remote post to the platform to be non-blocking, meaning that it doesn't hold up other WordPress processes and maintains seamless performance.

= 1.13.1 =
* Improved Meta Commerce tracking: Events like Purchase and AddToCart are now matched to your product catalog in Meta's Commerce Manager, enabling more accurate attribution and shop insights.

= 1.13.0 =
* Meta/Facebook tracking: Now with New Option to send PageView as Server-side
* Meta/Facebook tracking: Added "event_source_url" (Page URL) now sent for better event info
* Meta/Facebook tracking: Fix - removed currency info for non-applicable events

= 1.12.2 =
* Code improvements to handle Meta Disabled, Google Disabled, prevent unnecessary processes going on if Disabled.

= 1.12.1 =
* Google tracking quality update

= 1.12.0 =
* New improvements to Google tracking, now with better gclid handling for Google Ads conversion tracking (campaign conversion tracking). Fixed a problem with Google's tracking script (gtag) not loading properly in some scenarios. Improved debugging and testing features including new separate options for client-side and server-side debugging for Google DebugView.

= 1.11.1 =
* Added product name to Add To Cart and Checkout triggers for Google Analytics

= 1.11.0 =
* Bug fixes and event quality improvements for Google Analytics data sending. Some google events not being recognised because of missing "event_id". Introduction of "Enable Google DebugView" option in General Settings to support event feedback and testing in Google Analytics.

= 1.10.7 =
* Event quality updates and deduplication improvements, including more data sent for purchases.

= 1.10.6 =
* Initiate Checkout regularity updated

= 1.10.4 =
* Meta fbc matching improvement

= 1.10.3 =
* Update to timestamp being sent to Meta in fbc, using milliseconds now, to reduce Meta notification saying that timestamp in the future in some cases. This improves event matching quality in Meta.

= 1.10.1 =
* Fix for client_id not being sent in some cases for Google server-side sending, meaning that some woocommerce events were not being registered on Google.

= 1.10.0 =
* Improvements to Meta data-sending to address Meta's 'fbc' feedback warning, caused by differences in event_time being reported.
* Fixed currency value not being sent, fixed Cart Totals for Add To Cart events
* Changes to 'Session' handling (moved to using 'Transients', safer for WordPress).
* New feature in Admin to provide feedback, request features or report issues.
* New feature for Admin settings to turn on/off Logging tracking events in the Browser Console, to assist with debugging and setup.

= 1.9.3 =
* Fix for incorrect format of "fbc" value being sent in some instances to Meta.

= 1.9.2 =
* Fix for better consistency when sending 'fbc' to Meta, which helps with deduplication and increases conversion reporting

= 1.9.1 =
* Fix for Add to Cart event tracking for category "Add to Cart" buttons that use ajax

= 1.9.0 =
* IMPORTANT FIXES: Several fixes to address introduced bugs in 1.8.*, including re-factoring of WooCommerce server-side and client-side event sending. Addresses several issues identified in different WooCommerce versions where Add To Cart, Initiate Checkout and Purchases were not sending properly in some circumstances.

= 1.8.5 =
* Bug fix for Meta client side sending to improve event data consistency

= 1.8.4 =
* Bug fix causing error on non-woocommerce websites

= 1.8.2 =
* Updates to help text

= 1.8.1 =
* Bug fix for admin event updates creating duplicates in some scenarios

= 1.8.0 =
* New UX and easier setup for WooCommerce server side events, including improved event sending reliability.

= 1.7.1 =
* New diagnostics for better plugin performance and debugging.

= 1.7.0 =
* Admin UI improvements and additions, improved help text support and performance updates.

= 1.6.0 =
* Security and compatibility improvements. Upgrade for enhanced data handling and overall stability.

= 1.5.0 =
* Compatibility improvements. Upgrade for enhanced data handling and overall stability.

= 1.2.0 =
* Added Google Server side API integration.

= 1.1.1 =
* Initial release with Meta (Facebook) Conversion API integration.

== Upgrade Notice ==

= 2.6.0 =
New Microsoft WooCommerce events, broader AddToCart compatibility, and improved checkout event accuracy across all platforms.

= 2.5.4 =
Important fix for WooCommerce event tracking. Update recommended.

= 2.5.3 =
Advanced Matching now available — send hashed user data with events to improve Event Match Quality on Meta, TikTok and Pinterest. Plus event accuracy improvements across all platforms.

= 2.5.2 =
Pinterest Conversions API now supported — server-side and client-side tracking with full deduplication across all WooCommerce and custom events.

= 2.5.1 =
Server-side tracking is now optional per platform — get started with client-side only and add server-side when ready.

= 2.5.0 =
Recommended update — three new consent managers supported (Complianz, CookieYes, Moove GDPR), simplified consent settings, and setup documentation links added throughout.

= 2.4.0 =
Recommended update — richer product data for better ad reporting, TikTok deduplication fix, and corrected AddToCart values.

= 1.7.0 =
* Includes admin UI improvements, improved help text support and performance updates.

= 1.6.0 =
* Security and compatibility improvements.

= 1.5.0 =
* Significant security and compatibility improvements. Upgrade for enhanced data handling and overall stability.

= 1.1.1 =
* Initial release.

== Screenshots ==

1. Main Home Page
2. Meta Platform and Event Setup
3. Google Platform and Event Setup

== License ==

This plugin is licensed under the GPLv2 or later.
