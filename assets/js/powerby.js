/*! Karnisha Power By — https://github.com/karnishaco/karnisha-powerby */
(function () {
	'use strict';

	var ROOT = '.krn__powerby';
	var ANCHOR = '.karnisha';
	var BOX = '.karnisha__power_box';
	var TOGGLE = '.karnisha__toggle';
	var MARGIN = 8; // min distance from viewport edges (px)
	var openRoot = null;

	function closest(el, selector) {
		return el && el.closest ? el.closest(selector) : null;
	}

	function isHover(root) {
		return root.classList.contains('kpb--hover');
	}

	/**
	 * Compute, from the logo's position, where the (still hidden) box will
	 * land and shift it so it always stays inside the viewport. Done before
	 * showing the box so it never overflows, not even for one frame.
	 */
	function fit(root) {
		var box = root.querySelector(BOX);
		var anchor = root.querySelector(ANCHOR);
		if (!box || !anchor) return;

		var a = anchor.getBoundingClientRect();
		var vw = document.documentElement.clientWidth || window.innerWidth;
		var w = Math.min(parseFloat(window.getComputedStyle(box).width) || 200, vw - 2 * MARGIN);
		var rtl = window.getComputedStyle(root).direction === 'rtl';
		var align = root.classList.contains('kpb--align-start') ? 'start'
			: root.classList.contains('kpb--align-end') ? 'end' : 'center';

		var left;
		if (align === 'center') {
			left = a.left + a.width / 2 - w / 2;
		} else if ((align === 'start') !== rtl) {
			left = a.left; // LTR start / RTL end
		} else {
			left = a.right - w; // RTL start / LTR end
		}

		var shift = 0;
		if (left < MARGIN) {
			shift = MARGIN - left;
		} else if (left + w > vw - MARGIN) {
			shift = vw - MARGIN - (left + w);
		}
		box.style.setProperty('--kpb-shift', shift.toFixed(1) + 'px');
	}

	function open(root) {
		if (openRoot && openRoot !== root) close(openRoot);
		var box = root.querySelector(BOX);
		var toggle = root.querySelector(TOGGLE);
		if (!box) return;
		fit(root);
		box.classList.add('active');
		if (toggle) toggle.setAttribute('aria-expanded', 'true');
		document.body.classList.add('power_opened');
		openRoot = root;
	}

	function close(root) {
		var box = root.querySelector(BOX);
		var toggle = root.querySelector(TOGGLE);
		if (box) box.classList.remove('active');
		if (toggle) toggle.setAttribute('aria-expanded', 'false');
		if (openRoot === root) {
			openRoot = null;
			document.body.classList.remove('power_opened');
		}
	}

	// Delegated listeners: one set handles every badge on the page (and in the Elementor editor).
	document.addEventListener('click', function (e) {
		var toggle = closest(e.target, TOGGLE);
		var root = toggle ? closest(toggle, ROOT) : null;

		if (root) {
			e.preventDefault();
			var box = root.querySelector(BOX);
			var isOpen = box && box.classList.contains('active');
			// In hover mode a tap/click only opens (touch devices fire mouseover first).
			if (isOpen && !isHover(root)) {
				close(root);
			} else if (!isOpen) {
				open(root);
			}
			return;
		}

		if (openRoot && !openRoot.contains(e.target)) {
			close(openRoot);
		}
	});

	document.addEventListener('keydown', function (e) {
		if ((e.key === 'Escape' || e.key === 'Esc') && openRoot) {
			var toggle = openRoot.querySelector(TOGGLE);
			close(openRoot);
			if (toggle) toggle.focus();
		}
	});

	// Hover mode (only the logo + its box count, not the whole widget row).
	// A short close delay lets the pointer travel from the logo to the box.
	var HOVER_DELAY = 250;
	var hoverTimer = null;

	function cancelHoverClose() {
		if (hoverTimer) {
			clearTimeout(hoverTimer);
			hoverTimer = null;
		}
	}

	// Pointer events with pointerType "mouse" only: touch devices open with a tap
	// (click) and close by tapping elsewhere, so synthetic mouse events are ignored.
	document.addEventListener('pointerover', function (e) {
		if (e.pointerType !== 'mouse') return;
		var anchor = closest(e.target, ROOT + ' ' + ANCHOR);
		var root = anchor ? closest(anchor, ROOT) : null;
		if (!root || !isHover(root)) return;
		cancelHoverClose();
		if (openRoot !== root) open(root);
	});

	document.addEventListener('pointerout', function (e) {
		if (e.pointerType !== 'mouse') return;
		if (!openRoot || !isHover(openRoot)) return;
		var anchor = openRoot.querySelector(ANCHOR);
		if (!anchor || !anchor.contains(e.target)) return;
		var to = e.relatedTarget;
		if (to && anchor.contains(to)) return;
		var root = openRoot;
		cancelHoverClose();
		hoverTimer = setTimeout(function () {
			hoverTimer = null;
			if (openRoot === root) close(root);
		}, HOVER_DELAY);
	});

	document.addEventListener('focusin', function (e) {
		var root = closest(e.target, ROOT);
		if (root && isHover(root) && openRoot !== root) {
			open(root);
		} else if (!root && openRoot && isHover(openRoot)) {
			close(openRoot);
		}
	});

	window.addEventListener('resize', function () {
		if (openRoot) fit(openRoot);
	});
})();
