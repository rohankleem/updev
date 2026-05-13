# Writing Style Guide — Blog & Docs

Voice and structural rules for content published at `unipixelhq.com`. Distinct from `positioning.md` (which sets *what* we say); this file is *how* we say it. Apply to every blog post, docs article, syndicated piece, and email reply.

For content inventory and where things live, see `unipixelhq-content.md`. For positioning rules (comparative claims, no cost-as-value, etc.) see `positioning.md`. For the catalogue of article hook formulas (I-statement question, vs/alternatives, watch-out warning, hidden-cost gotcha, universal fear, category capture, X without Y, how-to) and how to pick one deliberately for each article, see `article-hook-patterns.md`.

---

## The voice in one line

**Direct. Technical credibility without jargon. Conversational without being chatty. Anti-hype.**

Sounds like a knowledgeable peer explaining something at the pub, not a marketing department or a developer talking down.

---

## Universal voice rules (apply to blog AND docs)

### 1. Second-person dominant
Talk *to* the reader, not *about* them.
- ✅ "You've probably come across PixelYourSite already."
- ❌ "Many WordPress users encounter PixelYourSite when researching tracking solutions."

### 2. Direct, declarative claims
Skip the qualifying preamble. Say the thing.
- ✅ "Stape sells hosting for infrastructure WordPress doesn't need."
- ❌ "It could be argued that, in some cases, Stape's hosting may not be strictly required for WordPress users."

### 3. Rhetorical setup → punchline pattern
A reliable structural move when introducing a key insight:
- "But WordPress? WordPress already runs PHP. It can call APIs directly."
- "Your ad data is wrong. Here's why."
- "Most WordPress users don't realise this: their server can already do the thing they're paying $100/mo to outsource."

The setup creates a beat; the punchline lands harder for it.

### 4. Specificity over generality
Name the platform, the version, the price, the number.
- ✅ "Stape's basic plan is $20/mo, plus your GTM expertise hours."
- ❌ "There are ongoing costs."
- ✅ "iOS 14.5 dropped reported Meta conversions by 30-40% for many advertisers."
- ❌ "Privacy changes have affected reporting."

### 5. Comparative, not absolute (from `positioning.md`)
- ✅ "More accurate" / "more conversions reported" / "fewer gaps"
- ❌ "100% conversions" / "complete data" / "perfect tracking"
- ❌ "Your numbers will match WooCommerce" — this is unwinnable; never imply it.

### 6. Anti-hype vocabulary
Words to avoid: *supercharge, revolutionary, leading, premium, cutting-edge, world-class, unleash, unlock, game-changing, blazingly fast, AI-powered (unless literally true)*. Marketing-speak undermines the credibility we earn through specificity.

### 7. No instructions in marketing copy
Setup steps belong in docs, not blog/landing copy.
- ✅ Blog: "UniPixel handles Meta Conversions API setup inside WordPress."
- ❌ Blog: "Install the plugin, paste your Pixel ID, then go to Setup > Meta and..."

(The reverse: docs should NOT lean on marketing claims. Just tell them how.)

### 8. No em dashes. Anywhere.
**Em dashes (—) are not used in any UniPixel content.** Not in blog. Not in docs. Not in plugin admin copy. Not in ads, not in readme, not in social posts, not in this doc going forward.

Em dashes read as AI-generated in 2026. Even one in an article telegraphs "this was written by an LLM" to a reader who's developed the eye for it, and that perception is now widespread enough to undermine credibility.

Use periods, commas, colons, semicolons, or parentheses instead. A comma-style aside is almost always clearer than a dash-style aside. A full stop is almost always cleaner than a continuation dash.

Hyphens in compound words (`send-mode`, `fire-once-per-session`, `post-redirect`, `lead-gen`) are fine. Those are not em dashes — they're hyphens, and they belong inside multi-word adjectives and compound terms.

En dashes (–) for ranges (`$149–228`, `2013–2026`) are also fine. They're a different mark with a different purpose.

**Style checklist enforcement:** the published-piece checklist below has been updated. Em dash count target: 0.

### 9. Comparative tables for "vs" claims
Whenever the article makes a comparison, include a tight table. Readers scan tables before reading prose. Make the table support the article's thesis without needing the prose.

### 10. UK spelling
`organise`, `optimise`, `colour`, `behaviour`, `analyse`, `realise`. Buildio is Australian; the WordPress plugin ecosystem leans US but our voice stays UK-Australian.

### 11. No time estimates
Don't tell users how long setup, configuration, or any process will take. Leave the duration off entirely.

- ❌ "~5 minutes"
- ❌ "Setup takes about 10 minutes"
- ❌ "In just 30 seconds..."
- ❌ "This usually only takes a couple of minutes"
- ✅ "By the end, server-side events will fire from your site directly to Meta."
- ✅ "Once this is done, your pixel will be live on every page."

Estimates set expectations that get broken. A user who hits a snag and takes 20 minutes feels like they did something wrong. Platform UIs differ across regions, account types, and recent redesigns, so any time we give is a per-user guess. The downside (user mistrusts us when their experience differs) outweighs the upside (small reassurance).

Applies to: plugin admin copy, wizards, docs, blog, ads, social, readme, support replies. Internal scoping (effort columns in `release-log.md`, project planning docs) is team-facing not user-facing, so exempt.

---

## Blog voice — additional rules

### Length
**2,000-2,400 words for comparison articles.** Not because length is virtue — because credible "vs" / "alternatives" articles need real depth. Skim-thin content gets ignored by both readers and Google.

Awareness articles can be shorter (1,200-1,800 words) when the point doesn't need a comparison table.

### Structure (comparison articles)
1. **Hook** (1 paragraph) — name the competitor + the pain or question that brought the reader here
2. **What [competitor] does** (1-2 paragraphs) — fair, accurate description; resist the urge to attack
3. **Why WordPress changes things** (the core thesis) — sets up the divergence
4. **Comparison table** — side-by-side
5. **The real cost / hidden cost** (where applicable) — concrete numbers
6. **Other alternatives worth considering** — name them honestly, don't pretend they don't exist
7. **Why UniPixel wins** — numbered list of 4-6 reasons
8. **What it looks like to switch** — practical
9. **Bottom line + CTA** — single sentence framing + "Try UniPixel — [benefit]"

### Closing CTA
Always: **"Try UniPixel — [outcome-framed benefit]."** Linked to wp.org plugin URL. Don't link the home page from the blog CTA — wp.org is the conversion surface.

Examples:
- "Try UniPixel — server-side tracking from the server you already have."
- "Try UniPixel — Meta Conversions API in WordPress, free, no GTM."

### Don't bash competitors
Honest critique > attack. "PYS is the market leader for a reason — and here's where it falls short for WordPress users on a budget" lands. "PYS sucks" doesn't.

### One main argument per article
Comparison articles fail when they try to be the comprehensive guide *and* the alternative case *and* the troubleshooting article. Pick the angle. Cross-link to the others.

---

## Docs voice — additional rules

### Length
**~2,000 words per article** (similar to blog). Setup guides on a platform are substantial because the platform's own UI is where people get lost.

### Structure (setup guides)
1. **Quick "what you'll need" list** — let the reader assess if they have everything before starting
2. **"Already have these?"** — branch for users who've done parts before
3. **Numbered steps** — 5-8 numbered tasks; each one is a sub-section
4. **"What to ignore"** — call out platform UI elements that are misleading or irrelevant. The platforms push their own products; we steer past.
5. **Q&A-style FAQ inside the article** — questions in the same shape readers actually ask ("Wait — does this replace my tracking codes?")
6. **"After you set up UniPixel"** — what happens next, what to expect

### Tone — instructional but warm
- ✅ "Stick with it — this part is fiddly because Meta's UI changed in 2024."
- ✅ "Look, no hands. UniPixel handles it from here."
- ✅ "(That's where most people get stuck — Meta puts it three menus deep.)"
- ❌ "The user should now navigate to..."
- ❌ "Click the button labelled 'Save' to persist your changes."

Reassurance phrases that fit the voice:
- "That's it."
- "Stick with it."
- "(This is the one most people are missing.)"
- "Look, no hands."
- "Don't worry about [X] — UniPixel handles it."

### No screenshots (current convention)
The existing docs don't use screenshots. Pros: never go stale when platforms redesign. Cons: harder to follow for visual learners. **Open question** — worth revisiting once we have stable docs writing capacity. Until then, write *as if* the screenshots aren't coming.

### Code / commands
Docs almost never need code. The plugin abstracts it. If a code block IS needed:
- Triple-fenced markdown
- Language identifier (` ```php `, ` ```bash `)
- One-line description above
- Don't make readers guess what to copy

### Stable URLs
Docs URLs are referenced from the plugin code (help icons, Need-help links). **Never change a published docs URL** — at minimum set up a 301 redirect from the old URL. The plugin file change to update the link is one thing; broken links from already-installed plugins out there is much bigger.

---

## Email / support replies — additional rules

Direct customer-support replies: feedback-widget responses, email back to a user, forum DMs, anything one-to-one. Universal voice rules above still apply, but the register is more casual and the structure is conversational, not article-shaped.

### 1. Open with the answer
First line is the verdict, not a preamble.
- ✅ "It does look like you've done the right thing."
- ✅ "That's a known limitation, here's the workaround."
- ✅ "No, that's not the issue, here's what's actually happening."
- ❌ "Thanks for reaching out", "I appreciate the feedback", "Great question, let me help with that".

Politeness rituals burn the reader's first second. Warmth comes from the answer being useful, not from a soft entrance.

### 2. Affirm what they did, honestly
If the user's path was correct but felt uncertain, say so plainly. Don't gaslight them by implying they did something unnecessary if they didn't. "You had to do it that way, and you did it right" beats "you didn't need to do that" when they actually did. The reassurance has to be true to land.

### 3. Acknowledge the context that confused them
If the platform UI or our own UX is the real source of confusion, name it. "Meta used to hide that part behind the scenes, but more recently they've made it visible." Validates the user's experience without throwing anyone under the bus.

### 4. Always give a verification path
Most support questions are really "did I do it right?". Include a way the user can verify the outcome themselves before signing off. Prefer in-plugin paths (Stored Event Logs, an admin page) over external ones. Add a platform-side cross-check as a secondary option when possible.

### 5. Label failure modes by symptom
Give them a self-diagnosis ladder. "Code 200 = good. 401/403 = token issue. 400 = payload issue, send a screenshot." Beats vague "if it doesn't work, let me know."

### 6. Offer concrete help, not vague availability
- ✅ "Send me a screenshot if you see that and I'll have a look."
- ❌ "Please don't hesitate to reach out if you have any further questions."

### 7. Casual register, contractions, UK/AU vocabulary
"you're sorted", "have a look", "flick it on", "give it a go", "let me know what you see", "have a dig". Avoid: "kindly", "please advise", "at your earliest convenience", "as per", "per my last email", "going forward".

### 8. Don't upsell
Support replies are not marketing. Don't end with "Try our Pro tier" or "Have you considered...". If they don't ask, don't offer.

### 9. Mention upcoming improvements lightly, framed as benefit-to-them
If their feedback exposed a real gap that's going to get fixed, mention it briefly. Frame it as "for the next person who lands on it" rather than "we're working on improving the product". Don't promise dates. Don't apologise for the gap; acknowledging it is enough.

### 10. Don't be defensive about real gaps
When our UX caused the confusion, say so without bowing. "Worth a UX improvement on our end" lands better than either "by design" (false) or "I'm so sorry for the confusion" (over-apologetic). Customers can smell both.

### 11. Short closing
"Cheers, Rohan" is the closing. Not "Best regards", not "Kind regards", not "Thank you for being a valued UniPixel user".

### 12. Zero em dashes (reiterated, this surface is where it matters most)
Customer support replies are the surface where AI-detection is sharpest. A reader who suspects "this was written by ChatGPT" disengages instantly. Em dashes are the single biggest tell. Stay zero, even more strictly than blog/docs.

### Structural template (rough)
1. **Verdict line** (1 sentence): the answer.
2. **Affirmation + context** (1-2 short paragraphs): what they did, why it felt confusing.
3. **Verification path** (numbered or flowing prose): how they confirm it themselves.
4. **Failure-mode ladder** (1 short paragraph or bullets): what each error means.
5. **Optional cross-check** (1 paragraph): the secondary verification.
6. **Our-side note** (1 sentence): improvements coming, framed as benefit-to-them.
7. **Open-door close** (1 sentence): concrete offer to help if they get stuck.
8. **Cheers, Rohan.**

Not every reply needs all eight. A two-line answer is fine when the question's a two-line question. The order matters more than the completeness.

---

## Examples — voice in action

### Hook examples (blog)
- "You've probably come across Stape if you've spent any time researching server-side tracking for WordPress."
- "Meta keeps telling you about Conversions API. Your match score is 'medium'. Now what?"
- "Here's what nobody tells you about Conversios' pricing page: the plugin is the cheap part."

### Setup-guide opener examples (docs)
- "Setting up UniPixel with Meta has two parts: things you find inside Meta, and things you paste into UniPixel. Most of the work is the first one."
- "Pinterest's Conversions API setup is buried three menus deep. Once you know where to click, it's about ten minutes."

### Closing line examples (blog)
- "Try UniPixel — server-side tracking from the server you already have."
- "Try UniPixel — five platforms, one plugin, zero GTM."

### Email opener examples (support replies)
- "It does look like you've done the right thing."
- "Quick answer: yes, that's a known limitation. Here's the workaround."
- "No, that's not the issue. What's actually happening is..."
- "You're nearly there, one piece is missing."

### Email closer examples (support replies)
- "Give the check above a go and let me know what you see. Happy to help interpret anything that looks weird."
- "Send me a screenshot if it still looks off and I'll have a dig."
- "Let me know how that goes."

### What to NOT write
- ❌ "Discover how UniPixel revolutionises your tracking workflow."
- ❌ "The most powerful WordPress tracking solution available."
- ❌ "UniPixel: making tracking effortless for everyone."
- ❌ "Synergise your conversion data across the marketing stack."

---

## Style checklist before publishing

- [ ] Reader is "you", not "users" or "the customer"
- [ ] At least one specific number, version, or platform name in the first 200 words
- [ ] No words from the anti-hype list
- [ ] Comparative claims (not absolute)
- [ ] Zero em dashes (—). Count them. The target is 0. Use periods, commas, colons, semicolons, or parentheses.
- [ ] No time estimates ("~5 minutes", "in just 30 seconds", "takes about 10 minutes"). Leave durations off.
- [ ] Comparison table if it's a "vs" / "alternatives" piece
- [ ] CTA is "Try UniPixel — [benefit]" linked to wp.org
- [ ] UK spelling consistent throughout
- [ ] No setup instructions in blog articles; no marketing claims in docs articles
- [ ] Internal links to relevant docs and other blog posts
- [ ] Word count: 2,000-2,400 (comparison) / 1,200-1,800 (awareness) / ~2,000 (docs setup)
