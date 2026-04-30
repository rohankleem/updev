# Feature: Consent Popup Localization (Multi-Language + Editable)

**Status:** Not started
**Bucket:** UX, Platform Coverage
**Effort:** Days (3–5 of focused work; phased delivery possible)
**Priority:** High — called out as a **dealbreaker** in user feedback (plugin could not be used in production by a multi-region store because the popup was English-only).

---

## Summary

The built-in consent popup currently renders English strings hardcoded in `js/unipixel-consent-popup.js`. Two separate limitations collapse into one feature:

1. **No localization** — a Belgian store serving Dutch/French/German customers can't translate the popup.
2. **No content editability** — even within a single language, the store can't change the wording to match their brand voice, legal counsel's phrasing, or jurisdiction-specific requirements (GDPR vs CCPA vs Australian Privacy Act).

These are the same problem because the user is always **authoring the content** — either copying the default and tweaking it, or writing it for a new language. The plugin's job is to give them a safe, WordPress-native way to do this.

---

## What needs to be translatable

From `unipixel-consent-popup.js` today, the user-facing strings are:

| Key | Default English | Type |
|---|---|---|
| `title` | "Your Privacy Choices" | short text |
| `body` | (intro paragraph) | rich text (allow `<a>`, `<strong>`, `<br>`) |
| `btn_accept` | "Accept all" | short text |
| `btn_adjust` | "Adjust preferences" | short text |
| `panel_title` | "Manage Your Preferences" | short text |
| `panel_body` | (intro inside the manage panel) | rich text |
| `cat_functional_label` | "Functional" | short text |
| `cat_functional_desc` | (description) | rich text |
| `cat_performance_label` | "Performance" | short text |
| `cat_performance_desc` | (description) | rich text |
| `cat_marketing_label` | "Marketing" | short text |
| `cat_marketing_desc` | (description) | rich text |
| `panel_footer` | (closing line) | rich text |
| `btn_cancel` | "Cancel" | short text |
| `btn_save` | "Save preferences" | short text |

~15 strings. Manageable. Final list confirmed during implementation.

---

## How it works

### Storage

New option: `unipixel_consent_strings_i18n`, keyed by WP locale code → string key → value.

```php
[
  'en_US' => [
    'title' => 'Your Privacy Choices',
    'body'  => '...',
    // ...
  ],
  'fr_FR' => [ /* overrides */ ],
  'nl_BE' => [ /* overrides */ ],
]
```

Only overridden strings are stored. Missing keys fall back to the plugin defaults shipped in English (loaded via `load_plugin_textdomain()` so the WordPress translator community can .po-translate them independently). This keeps the options row compact and honours two translation paths in parallel — see "Two paths for translations" below.

### Locale detection (frontend)

Order of precedence:
1. Admin override (dropdown in settings: "force specific language" or "auto")
2. `get_user_locale()` if the visitor is logged in
3. `get_locale()` (site locale)
4. Fallback: English defaults

On page render, PHP resolves the active locale, merges stored overrides over defaults, and passes the final strings to the JS via `wp_localize_script()` — one object, already in the right language, already sanitised. The JS has no translation logic at all; it just reads `UnipixelConsentStrings.title` etc.

### Rendering (frontend)

Today the JS writes raw template strings via `innerHTML`. After this feature, those strings come from `wp_localize_script()`. Short-text fields are assigned via `.textContent` (no HTML ever). Rich-text fields use `.innerHTML` but only after the PHP side has run them through `wp_kses()` with a strict allowlist (see Security below). The JS itself does no HTML building of user content.

---

## Two paths for translations (and how they balance)

The "how do we generate translations" question has two legitimate answers. We support both:

### Path A — WordPress `.po/.mo` files (free community translation)

Ship `languages/unipixel-{locale}.po/.mo` in the plugin. All default strings wrapped in `__('…', 'unipixel')`. Anyone on translate.wordpress.org can contribute translations. These load automatically via `load_plugin_textdomain('unipixel', …)`.

**Strengths:** free, crowdsourced, standard WP pattern, ships with the plugin, no admin work for common locales.
**Weakness:** the store owner can't change the wording — it's the translator's choice.

### Path B — Admin UI override per language (store authorship)

Admin screen lets the store owner add a language, see each string with its current default (from .po, or from English fallback), and enter their own version. Overrides saved to `unipixel_consent_strings_i18n` option. Overrides always win over .po defaults.

**Strengths:** store owner controls wording, jurisdiction language, brand voice.
**Weakness:** they have to type it.

### How they balance

Merge order at runtime:
```
admin override  >  .po/.mo translation  >  English default
```

A store that doesn't care gets the .po translation for free. A store that cares tweaks specific strings in the admin UI. A store running a language with no .po yet authors every string themselves. **Nobody is blocked.**

### Optional: machine-translation helper button (nice-to-have, phase 3)

A "Translate from English" button next to each field that calls a free API (LibreTranslate self-hosted, or DeepL free tier with user-supplied key) to pre-fill the field. Admin reviews and edits. Not required for MVP — adds API dependency and key management. Flag this as phase-3 polish, not core.

---

## Admin UI

New tab on the existing Consent Settings page: **Languages**.

```
┌─ Consent Settings ─ [General] [Languages] [Categories] ─────────┐
│                                                                   │
│  Default language: English (from plugin — read-only)              │
│                                                                   │
│  Active languages:                                                │
│    ● French (fr_FR)           [Edit] [Remove]                     │
│    ● Dutch (Belgium) (nl_BE)  [Edit] [Remove]                     │
│                                                                   │
│  [+ Add language ▾]  (dropdown of WP-supported locales)           │
│                                                                   │
│  Auto-detect visitor language: [✓]                                │
│  Fallback language: [English ▾]                                   │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
```

Clicking Edit opens a per-language screen:

```
┌─ Edit: French (fr_FR) ──────────────────────────────────────────┐
│                                                                  │
│  Popup title                                                     │
│  Default: "Your Privacy Choices"                                 │
│  [ Vos choix en matière de confidentialité         ] [Reset]    │
│                                                                  │
│  Popup body (rich text)                                          │
│  Default: "We use cookies to improve your experience..."         │
│  [ Nous utilisons des cookies pour...              ] [Reset]    │
│                                                                  │
│  ... (one field per string, ~15 total)                           │
│                                                                  │
│  [Save changes]  [Preview popup in French]                       │
└──────────────────────────────────────────────────────────────────┘
```

Each field shows: the resolved default (from .po or English), an input for the override, a Reset-to-default button. Preview button opens the popup rendered with these strings so the admin can see what customers will see.

---

## WordPress plugin protocols to follow

- **Text domain**: `unipixel`. Already used. Confirm `load_plugin_textdomain('unipixel', …)` is wired in the plugin bootstrap.
- **All default strings wrapped**: `__('Your Privacy Choices', 'unipixel')` etc. Use `esc_html__()` / `esc_attr__()` at output points.
- **Settings API** for the admin form: register setting, nonce on submit (`wp_nonce_field` + `check_admin_referer`), capability check (`current_user_can('manage_options')`).
- **Option storage**: single serialised option `unipixel_consent_strings_i18n`. Keep it under the 1 MB option size limit — 15 strings × ~20 locales × 200 chars ≈ 60 KB, fine.
- **`wp_localize_script()`** to pass the resolved locale strings to the popup JS. No inline JSON blobs, no raw echo into scripts.
- **No breaking changes**: if `unipixel_consent_strings_i18n` is empty or missing, fall back to current hardcoded English behaviour — existing installs keep working.

---

## Security — script injection and data entry concerns

The popup renders **admin-authored content on every frontend page**. Any slip in sanitisation = stored XSS hitting every visitor. Handle with care.

### On save (admin side)

- **Capability check**: `current_user_can('manage_options')`. Consent settings are site-wide.
- **Nonce**: verify on every save.
- **Per-field sanitisation**:
  - Short-text fields (titles, button labels): `sanitize_text_field()` — strips tags, collapses whitespace, bounds length.
  - Rich-text fields (body, category descriptions): `wp_kses()` with a **strict** allowlist — `<a href>`, `<strong>`, `<em>`, `<br>` only. No `<script>`, no inline handlers (`onclick` etc., stripped by `wp_kses`), no `javascript:` URLs (stripped).
  - Locale keys: validate against the WP-registered locale list (`wp_get_available_translations()` or `get_available_languages()`). Reject unknown strings.
- **Length caps**: e.g. title ≤ 120 chars, body ≤ 2000 chars. Prevents option bloat and reduces abuse surface.

### On render (frontend)

- **Short-text fields → `.textContent`** in JS, never `.innerHTML`. If somehow an override slips through sanitisation with HTML, it renders as text, not markup.
- **Rich-text fields**: PHP runs `wp_kses()` again with the same allowlist (defence in depth — storage sanitisation may be bypassed in edge cases like direct DB writes), then `wp_localize_script()` carries the clean HTML. JS assigns via `.innerHTML` but only for these named fields.
- **Never concatenate strings into HTML in JS.** The popup template builds DOM with `createElement` + `textContent` for short text, `innerHTML` only for the kses-cleaned rich fields.
- **Output escaping at every echo point** in PHP: `esc_html()`, `esc_attr()`, `esc_js()` as appropriate.

### Data entry UX safeguards

- Live character counter per field.
- Preview button re-renders the popup in an iframe so admins see exactly what visitors get — including if their kses-cleaned HTML stripped something unexpected.
- Reset-to-default per field so admins can bail out of a broken override without guessing the original text.

---

## Is it easy to implement?

**Honest assessment: moderate, not easy.** The individual pieces are simple; the complexity is in doing the security and UX right.

### Straightforward parts (~1 day)
- Option schema + read/write
- Locale resolution function + PHP merge of overrides onto defaults
- `wp_localize_script()` wiring + JS refactor to consume the localized object instead of hardcoded strings

### Careful parts (~2 days)
- Admin UI (tab, locale picker, per-language edit screen, reset buttons)
- Sanitisation layer (separate functions for short-text vs rich-text, kses allowlist, length caps)
- Nonce + capability + settings API wiring

### Polish parts (~1–2 days)
- Preview iframe (pass strings, render popup, no side effects)
- Default .po/.mo scaffolding (at minimum: extract strings into .pot, ship empty, let translators contribute later)
- Migration from the current hardcoded English path so existing installs don't break

### What makes it harder than it looks
- Rich-text sanitisation is easy to get wrong. Two engineers will write two slightly different `wp_kses` allowlists; one of them will accidentally permit `<img onerror=…>`. Must use a single shared constant and test it with known XSS payloads.
- The preview feature is deceptively tricky — render real user input in an iframe safely.
- Locale fallback chain must be deterministic and tested across visitor-locale/site-locale/admin-override combinations.

### Recommended phasing

**Phase 1 (MVP, ship first)**: editable English only. Admin edits the strings, one language. Proves the whole stack — option storage, sanitisation, wp_localize_script wiring, frontend render, preview. Ships real value to single-language stores who want brand voice / jurisdiction wording. Unblocks the dealbreaker-adjacent case.

**Phase 2 (the actual feature)**: multi-language support. Add language picker, per-locale override storage, locale resolution on frontend. Ship with .pot file for community translation.

**Phase 3 (polish)**: preview iframe, machine-translate helper button, pre-populated translations for top locales (ship a few .po files we translate ourselves or crowdsource).

Phase 1 alone is ~1–2 days and de-risks phase 2 significantly. Phase 2 is another 2–3 days on top. Phase 3 is open-ended.

---

## Stable contract: string keys

Once a string key ships in a stable release, **treat it as a public contract.** Do not rename `btn_accept` to `accept_button` etc. in later versions without a migration.

Why this matters: admin overrides are stored in the `unipixel_consent_strings_i18n` option keyed by string key. If a release silently renames a key, existing overrides become orphaned — still in the DB, but never looked up. Visitors silently revert to the default text without any admin warning.

Adding new keys is fine. Changing default English text is fine (overrides store the user's text, not the default, so they are unaffected). Only renames and removals break the contract.

If a rename is genuinely needed, add a migration step in `unipixel_check_version()` that reads the option, renames the old key to the new key in each locale's overrides, and writes it back.

---

## Update behaviour

| Where translations live | Survives plugin update? |
|---|---|
| Admin UI overrides (`unipixel_consent_strings_i18n` option in DB) | **Yes** — plugin code updates don't touch DB options. |
| `.mo` files we ship in `plugins/unipixel/languages/` | Replaced on update. If we improve bundled translations in a later version, users get the new ones automatically. |
| `.mo` files in `wp-content/languages/plugins/` | **Yes** — WordPress owns that folder. Also where translate.wordpress.org auto-populates community translations after each plugin release. |

The admin warning banner (rendered when a forced locale has neither overrides nor a `.mo` file) points store owners at `wp-content/languages/plugins/` for custom `.mo` placement — NOT the plugin's own `languages/` folder — because the plugin folder gets wiped on auto-update.

---

## Open questions

1. Should category labels (Functional / Performance / Marketing) and their descriptions be translatable, or are they considered technical categories that should stay canonical? **Lean: translatable**, since the display label is separate from the internal category key.
2. Should the language picker show all WP locales (~200) or only the locales the admin has enabled somewhere on the site? **Lean: all WP locales**, let the admin pick freely.
3. Does the admin want a global "cancel all overrides and restore defaults" escape hatch, or only per-field reset? **Lean: both** — per-field for normal use, per-language "restore defaults" for panic.
4. If a string has an admin override AND a .po translation exists, which wins? This doc answers "admin override wins." Worth confirming with a real scenario before shipping.

---

## Related

- Source of requirement: user feedback review (review text captured in session history — item #31 in `release-log.md`).
- Current popup implementation: `public_html/wp-content/plugins/unipixel/js/unipixel-consent-popup.js`, `functions/consent.php`, `admin/page-consent-settings.php`.
- Text domain loading: confirm `load_plugin_textdomain('unipixel', …)` is called on `plugins_loaded` — if not, that's a prerequisite.
