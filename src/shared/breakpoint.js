/**
 * Shared mobile-breakpoint detection for GameStuff Core block
 * frontend scripts.
 *
 * Centralizes logic that would otherwise be duplicated identically
 * across every block whose mobile behavior depends on the same
 * breakpoint (starting with Accordion; other blocks will reuse this
 * as they're migrated).
 */
import { MOBILE_BREAKPOINT } from './constants';

/**
 * Whether the current viewport is at or below the mobile breakpoint.
 *
 * @return {boolean} True when the viewport is mobile-width.
 */
export const isMobileViewport = () => window.innerWidth <= MOBILE_BREAKPOINT;

/**
 * Registers a `resize` listener that only invokes `onBreakpointChange`
 * when the viewport actually crosses the mobile/desktop boundary --
 * not on every `resize` event.
 *
 * Without this guard, "resize" events that mobile browsers fire for
 * non-breakpoint reasons (e.g. the address bar hiding/showing when
 * content height changes after a toggle) would re-run the callback
 * and undo state the user just changed.
 *
 * @param {Function} onBreakpointChange Called with the new `isMobile` boolean, only when it changes.
 */
export const watchMobileBreakpoint = ( onBreakpointChange ) => {
	let wasMobile = isMobileViewport();

	const handleResize = () => {
		const mobile = isMobileViewport();

		if ( mobile === wasMobile ) {
			return;
		}

		wasMobile = mobile;
		onBreakpointChange( mobile );
	};

	window.addEventListener( 'resize', handleResize, { passive: true } );
};
