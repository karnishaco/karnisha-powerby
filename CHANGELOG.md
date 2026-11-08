# Changelog

## 1.0.3 — 2026-11-08

- Update packages are signed (Ed25519) by GitHub Actions and verified before installing; unsigned or tampered files are refused.
- Brand link gets `rel="nofollow"` by default (Elementor link option and `nofollow` shortcode attribute).

## 1.0.2 — 2026-11-08

- Updates now come from Karnisha's download host (cityseo.ir) instead of GitHub, fixing update timeouts on hosts with slow access to GitHub.
- All texts, including the brand name, now use the site's default font (previously KalamehWeb).

## 1.0.1 — 2026-11-08

- Box colours matched to the new logo: blue gradient background (#4f48ed → #0e21a2), yellow brand name (#ffbe1a), blue-tinted shadow.
- Slogan set to 14px so it fits on one line.

## 1.0.0 — 2026-11-08

- First release: the footer badge previously loaded from an external host is now a self-contained plugin.
- Elementor widget with content, behaviour and style controls; `[karnisha_powerby]` shortcode.
- Assets load only where the badge is used; vanilla JS, deferred.
- Fixed: `power_opened` was never removed from `body`.
- Fixed: only the first badge on a page worked.
- Fixed: hidden box caused horizontal scroll on mobile; replaced the fixed `left: 80px` mobile offset with automatic viewport fitting.
- Self-updater from GitHub Releases: updates show in the WordPress dashboard, background auto-update on by default (`KPB_AUTO_UPDATE` to opt out), works even when WordPress.org is unreachable, optional `KPB_GITHUB_TOKEN` for a private repo, "Check for updates" link.
- New optimized SVG logo bundled (1.9 KB, square canvas).
- Added: Esc to close, keyboard access, `aria-expanded`, logo `width`/`height` to avoid layout shift.
