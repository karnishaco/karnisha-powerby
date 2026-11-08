# Changelog

## 1.0.0 — 2026-11-08

- First release: the footer badge previously loaded from an external host is now a self-contained plugin.
- Elementor widget with content, behaviour and style controls; `[karnisha_powerby]` shortcode.
- Assets load only where the badge is used; vanilla JS, deferred.
- Fixed: `power_opened` was never removed from `body`.
- Fixed: only the first badge on a page worked.
- Fixed: hidden box caused horizontal scroll on mobile; replaced the fixed `left: 80px` mobile offset with automatic viewport fitting.
- Added: Esc to close, keyboard access, `aria-expanded`, logo `width`/`height` to avoid layout shift.
