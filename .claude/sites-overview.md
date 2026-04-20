# UniPixel / Buildio — Site Reference

Four sites across two product families.

## At a glance

| Site | Local URL | Local folder | GitHub repo | Purpose |
|---|---|---|---|---|
| `unipixelhq.com` | `uphq.local.site` | `C:\xampp\htdocs\uphq` | `rohankleem/uphq` | Marketing site for the UniPixel plugin |
| `dev.unipixelhq.com` | `updev.local.site` | `C:\xampp\htdocs\updev` | `rohankleem/updev` | Plugin dev base + central docs/recording hub |
| `buildio.au` | `bdoau.local.site` | `C:\xampp\htdocs\bdoau` | `rohankleem/bdoau` | **The customer-facing Australian brand** — consultancy helping businesses navigate digital transformation for optimisation. A resignation to focus on Australia, making `.au` the flagship brand |
| `buildio.dev` | `bdodev.local.site` | `C:\xampp\htdocs\bdodev` | `rohankleem/buildio` | Supporting role: forward-facing presentation of the `buildio.au` brand, general WordPress plugin presentation, and backend listener for UniPixel plugin offload services (logging, email sending — may migrate to `unipixelhq.com` later) |

## Hosting

All four live on the same Interserver box (`vda4300.is.cc`, user `buildiod`), managed via DirectAdmin. DNS is delegated to Interserver nameservers (`vda4300a/b.trouble-free.net`) — DNS edits happen in the Interserver panel, not GoDaddy.

**Local dev**: all sites served via Laragon (`C:\laragon\etc\apache2\sites-enabled\`), using XAMPP's `htdocs` as web root. Convention: `<name>.local.site` on port 443 with Laragon's self-signed cert.

## Key relationship

- **`buildio.au`** is the flagship consultancy brand (customer-facing)
- **`buildio.dev`** supports it (brand presentation + plugin backend services)
- **`unipixelhq.com`** markets the UniPixel plugin (product)
- **`dev.unipixelhq.com`** develops the plugin + hosts all plugin knowledge (docs, discoveries, session state, release tracking)

## Status

- `unipixelhq.com` — live
- `dev.unipixelhq.com` — live (local + remote)
- `buildio.au` — live
- `buildio.dev` — live
