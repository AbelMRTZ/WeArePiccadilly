# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**WeArePiccadilly** is the website for a Spanish audiovisual production company based in Yecla, Murcia. It is a static HTML/CSS/JS frontend backed by PHP APIs and a MySQL database. There is no build step — files are served directly by a PHP-capable web server (Apache/Nginx).

## Running Locally

Serve with any PHP-enabled local server. Example using PHP's built-in server from the project root:

```bash
php -S localhost:8000
```

The backend APIs require a MySQL database. Connection details live in [api/conexion.php](api/conexion.php):
- Host: `localhost`, DB: `wapDB`, User: `wapUser`, Pass: `wapPass_6769`

The Node.js entry point ([index.js](index.js)) is a minimal Express wrapper that exposes `sync-endpoint` at `/api`. Run it with:

```bash
node index.js
```

## Architecture

### Frontend
Two HTML pages:
- [index.html](index.html) — main single-page site with anchor-based navigation (Home, Proyectos, Servicios, Nosotros, Contacto, Clientes, Radio sections)
- [radio.html](radio.html) — dedicated page listing radio show episodes

JS files are plain vanilla (no framework):
- [js/main.js](js/main.js) — on load: fetches projects from `api/proyectos.php` and renders them into `#seccion_proyectos`; manages the responsive services carousel (triple-card mode above 850px, single-card below); handles contact form submission to `api/send_email.php`
- [js/radio.js](js/radio.js) — fetches episodes from `api/radio.php` and renders them into `#radio_container` in reverse-chronological order (newest first)
- [js/toggle.js](js/toggle.js) — mobile hamburger menu toggle

Icons come from a self-hosted Fontello icon font ([css/fontello.css](css/fontello.css), configured via [config.json](config.json)).

### PHP API (`api/`)
All endpoints include [api/conexion.php](api/conexion.php) for the DB connection:
- `api/proyectos.php` — `SELECT * FROM PROYECTOS`, returns JSON array
- `api/radio.php` — `SELECT * FROM RADIO`, returns JSON array
- `api/send_email.php` — receives POST from the contact form, sends via PHPMailer + Gmail SMTP (`smtp.gmail.com:587`)

PHPMailer is vendored in [src/](src/) and also in [api/](api/).

### Database Tables
- **PROYECTOS** — columns include `titulo`, `subtitulo`, `foto_rojo`, `enlace` (used by the projects section)
- **RADIO** — columns include `titulo`, `enlace` (radio show episodes)

### Podcast Sync
[sync-cron.php](sync-cron.php) is a standalone PHP CLI script scheduled weekly via cron. It fetches the iVoox RSS feed, parses episodes (episode number ≥ 26), and inserts new ones into the `RADIO` table. Run manually with:

```bash
php sync-cron.php
```

The Node.js equivalent lives at [api/sync-podcast.php](api/sync-podcast.php) and is exposed by [index.js](index.js).

### Assets
- [resources/](resources/) — media files (videos, images, logos used in the site)
- [imgBD/](imgBD/) — project portfolio images; each project has a red-tinted variant named `<N>_red.png` used as `foto_rojo` in the DB

## Key Conventions
- The services carousel reads card data from the static HTML at init time, then rebuilds the DOM — edits to card content go in [index.html](index.html) (the `.card` divs inside `.carousel-track`), not in JS.
- All PHP API endpoints set `Content-Type: application/json` and return plain JSON — no authentication or CORS headers are set (intended for same-origin requests only).
- The contact form's Gmail app password is hardcoded in [api/send_email.php](api/send_email.php) — do not commit changes that expose or rotate this credential publicly.
