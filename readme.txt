=== Karnisha Power By ===
Contributors: karnisha
Tags: footer, credit, elementor, badge
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Karnisha "designed & developed by" footer badge as an Elementor widget and a shortcode. All assets are local.

== Description ==

Shows the Karnisha logo in the footer; clicking (or hovering) it opens a small box with "designed & developed with pride by Karnisha".

* Elementor widget (category "Karnisha") and `[karnisha_powerby]` shortcode
* CSS/JS loaded only on pages that contain the badge; no jQuery, deferred script
* Click or hover trigger, top/bottom position, start/center/end alignment (RTL aware)
* Box is kept inside the viewport on small screens and never causes horizontal scroll
* Keyboard accessible, closes on outside click or Esc, multiple badges per page
* Updates itself from Karnisha's download host (auto-update on by default; define KPB_AUTO_UPDATE false to opt out)

== Installation ==

1. Upload the ZIP via Plugins > Add New > Upload Plugin and activate.
2. Add the "Karnisha Power By" widget to your footer, or use `[karnisha_powerby]`.

== Changelog ==

= 1.0.3 =
* Updates are signed (Ed25519) and verified before installing.
* Brand link gets rel="nofollow" by default.

= 1.0.2 =
* Updates now come from Karnisha's download host instead of GitHub (fixes update timeouts).
* All texts use the site's default font.

= 1.0.1 =
* Box colours matched to the new Karnisha logo (blue gradient, yellow brand name); slogan sized to fit on one line.

= 1.0.0 =
* First release: converted the externally hosted badge into a self-contained plugin.
