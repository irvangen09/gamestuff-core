/**
 * GameStuff Accordion
 *
 * Frontend only.
 */
import { isMobileViewport, watchMobileBreakpoint } from '../shared/breakpoint';

document.addEventListener( 'DOMContentLoaded', () => {
	const items = document.querySelectorAll( '.gamestuff-accordion-item' );

	const updateState = () => {
		const mobile = isMobileViewport();

		items.forEach( ( item ) => {
			const trigger = item.querySelector( '.gamestuff-accordion-item__trigger' );

			if ( ! trigger ) {
				return;
			}

			item.classList.toggle( 'is-open', ! mobile );
			trigger.setAttribute( 'aria-expanded', ! mobile );
		} );
	};

	updateState();

	// Track the last known breakpoint state so we only reset items
	// when actually crossing the mobile/desktop boundary -- handled by
	// watchMobileBreakpoint(), see src/shared/breakpoint.js for why.
	watchMobileBreakpoint( updateState );

	items.forEach( ( item ) => {
		const trigger = item.querySelector( '.gamestuff-accordion-item__trigger' );

		if ( ! trigger ) {
			return;
		}

		trigger.addEventListener( 'click', () => {
			if ( ! isMobileViewport() ) {
				return;
			}

			const opened = item.classList.toggle( 'is-open' );

			trigger.setAttribute( 'aria-expanded', opened );
		} );
	} );
} );
