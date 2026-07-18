( function () {
	'use strict';

	/**
	 * Initializes a single GameStuff TOC instance.
	 *
	 * @param {HTMLElement} toc Root element, .gamestuff-toc.
	 */
	function initToc( toc ) {
		initToggle( toc );

		var links = toc.querySelectorAll( '.gamestuff-toc__link' );
		var smoothScroll = toc.getAttribute( 'data-smooth-scroll' ) === 'true';
		var scrollOffset = parseInt( toc.getAttribute( 'data-scroll-offset' ), 10 ) || 0;
		var highlightActive =
			toc.getAttribute( 'data-highlight-active' ) === 'true' && 'IntersectionObserver' in window;

		var setActiveLink = highlightActive ? initHighlight( links, scrollOffset ) : null;

		if ( smoothScroll ) {
			initSmoothScroll( links, scrollOffset, setActiveLink );
		}

		// Reload/direct-open scenario where the URL already has a hash
		// (e.g. #Introduction): treat it the same as a click (lock it in),
		// since the user is explicitly heading to that section.
		if ( setActiveLink ) {
			applyInitialHash( links, setActiveLink );
		}

		if ( toc.getAttribute( 'data-collapse-subheading' ) === 'true' ) {
			initCollapse( toc );
		}

		initStickyFallback( toc );
	}

	/**
	 * Marks the active link based on window.location.hash on the page's
	 * first load (reload or direct open with a hash in the URL).
	 *
	 * @param {NodeListOf<HTMLElement>} links         TOC links.
	 * @param {Function}                setActiveLink Function from initHighlight().
	 */
	function applyInitialHash( links, setActiveLink ) {
		var hash = window.location.hash;

		if ( ! hash ) {
			return;
		}

		var i;

		for ( i = 0; i < links.length; i++ ) {
			if ( links[ i ].getAttribute( 'href' ) === hash ) {
				setActiveLink( links[ i ] );
				return;
			}
		}
	}

	/**
	 * Show/hide button for the entire TOC content.
	 *
	 * @param {HTMLElement} toc Root element, .gamestuff-toc.
	 */
	function initToggle( toc ) {
		var toggleButton = toc.querySelector( '.gamestuff-toc__toggle' );
		var content = toc.querySelector( '.gamestuff-toc__content' );

		if ( ! toggleButton || ! content ) {
			return;
		}

		toggleButton.addEventListener( 'click', function () {
			var expanded = toggleButton.getAttribute( 'aria-expanded' ) === 'true';

			toggleButton.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );

			if ( expanded ) {
				content.setAttribute( 'hidden', '' );
			} else {
				content.removeAttribute( 'hidden' );
			}
		} );
	}

	/**
	 * Smooth scrolls to the target heading when a TOC link is clicked.
	 *
	 * @param {NodeListOf<HTMLElement>} links         TOC links.
	 * @param {number}                  scrollOffset  Scroll offset (px).
	 * @param {Function|null}           setActiveLink Function from initHighlight() used to
	 *                                                mark the active link immediately on
	 *                                                click (without waiting for the observer).
	 */
	function initSmoothScroll( links, scrollOffset, setActiveLink ) {
		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				var targetId = link.getAttribute( 'href' ).slice( 1 );
				var target = document.getElementById( targetId );

				if ( ! target ) {
					return;
				}

				event.preventDefault();

				var targetPosition =
					target.getBoundingClientRect().top + window.pageYOffset - scrollOffset;

				window.scrollTo( { top: targetPosition, behavior: 'smooth' } );

				if ( window.history && window.history.pushState ) {
					window.history.pushState( null, '', '#' + targetId );
				} else {
					window.location.hash = targetId;
				}

				// Move focus to the target heading so keyboard and screen
				// reader users still get proper navigation context,
				// replacing the browser's default behavior which was
				// preventDefault()'d above.
				target.setAttribute( 'tabindex', '-1' );
				target.focus( { preventScroll: true } );

				// A click is an explicit signal from the user. Lock the
				// highlight to this link (see initHighlight /
				// setActiveLinkFromClick) - manual scrolling after this no
				// longer changes it.
				if ( setActiveLink ) {
					setActiveLink( link );
				}
			} );
		} );
	}

	/**
	 * Sets up tracking of the heading currently active in the viewport,
	 * and returns the setActiveLink() function so other callers (a TOC
	 * click) can mark the active link directly.
	 *
	 * Before any click: the highlight follows natural scrolling
	 * (scroll-spy) via IntersectionObserver. Once a link is clicked, the
	 * highlight is LOCKED to that link - the observer permanently stops
	 * changing the active state until another link is clicked (a
	 * deliberate product decision).
	 *
	 * @param {NodeListOf<HTMLElement>} links        TOC links.
	 * @param {number}                  scrollOffset Scroll offset (px).
	 * @return {Function} setActiveLink( link ) - HTMLElement|null.
	 */
	function initHighlight( links, scrollOffset ) {
		var linkById = {};

		links.forEach( function ( link ) {
			var id = link.getAttribute( 'href' ).slice( 1 );
			linkById[ id ] = link;
		} );

		var activeLink = null;
		var locked = false;

		/**
		 * Marks a link as active. Used both by the observer (natural
		 * scroll, only before locked) and called externally through a
		 * TOC click.
		 *
		 * @param {HTMLElement|null} link Link to mark as active.
		 */
		function applyActiveLink( link ) {
			if ( ! link || link === activeLink ) {
				return;
			}

			if ( activeLink ) {
				activeLink.classList.remove( 'gamestuff-toc__link--active' );
			}

			link.classList.add( 'gamestuff-toc__link--active' );
			activeLink = link;
		}

		var headings = Object.keys( linkById )
			.map( function ( id ) {
				return document.getElementById( id );
			} )
			.filter( Boolean );

		/**
		 * Called from a TOC click (or the initial hash in the URL):
		 * permanently locks the highlight to this link - manual
		 * scrolling after this no longer changes the active state, until
		 * another link is clicked.
		 *
		 * @param {HTMLElement} link Link that was clicked.
		 */
		function setActiveLinkFromClick( link ) {
			locked = true;
			applyActiveLink( link );
		}

		if ( ! headings.length ) {
			return setActiveLinkFromClick;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				// Once locked (a click has happened), manual scrolling may
				// no longer change the active state at all.
				if ( locked ) {
					return;
				}

				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}

					applyActiveLink( linkById[ entry.target.id ] );
				} );
			},
			{
				rootMargin: '-' + ( scrollOffset + 8 ) + 'px 0px -70% 0px',
				threshold: 0,
			}
		);

		headings.forEach( function ( heading ) {
			observer.observe( heading );
		} );

		return setActiveLinkFromClick;
	}

	/**
	 * Enables collapse/expand for subheading branches.
	 *
	 * The toggle button is injected via JS (progressive enhancement):
	 * without JS, the full list still displays and remains accessible.
	 *
	 * @param {HTMLElement} toc Root element, .gamestuff-toc.
	 */
	function initCollapse( toc ) {
		var items = toc.querySelectorAll( '.gamestuff-toc__item' );

		items.forEach( function ( item ) {
			var nestedList = item.querySelector( ':scope > .gamestuff-toc__list' );

			if ( ! nestedList ) {
				return;
			}

			var link = item.querySelector( ':scope > .gamestuff-toc__link' );
			var label = link ? link.textContent.trim() : '';

			var toggle = document.createElement( 'button' );

			toggle.type = 'button';
			toggle.className = 'gamestuff-toc__collapse-toggle';
			toggle.setAttribute( 'aria-expanded', 'false' );

			if ( label ) {
				toggle.setAttribute( 'aria-label', label );
			}

			nestedList.setAttribute( 'hidden', '' );
			item.classList.add( 'gamestuff-toc__item--collapsible' );
			item.insertBefore( toggle, nestedList );

			toggle.addEventListener( 'click', function () {
				var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';

				toggle.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );

				if ( expanded ) {
					nestedList.setAttribute( 'hidden', '' );
				} else {
					nestedList.removeAttribute( 'hidden' );
				}
			} );
		} );
	}

	/**
	 * Determines whether sticky positioning should be active at the
	 * current viewport width, and how much extra gap applies -
	 * replicating the exact same breakpoints as the @media rules in
	 * style.scss, since JS can't read a CSS @media state directly.
	 *
	 * @param {HTMLElement} toc Root element, .gamestuff-toc.
	 * @return {{active: boolean, gap: number}}
	 */
	function getStickyConfig( toc ) {
		var width = window.innerWidth;

		if ( width >= 782 && toc.classList.contains( 'gamestuff-toc--sticky-desktop' ) ) {
			return { active: true, gap: 8 };
		}

		if ( width >= 600 && width <= 781 && toc.classList.contains( 'gamestuff-toc--sticky-tablet' ) ) {
			return { active: true, gap: 8 };
		}

		if ( width <= 599 && toc.classList.contains( 'gamestuff-toc--sticky-mobile' ) ) {
			return { active: true, gap: 0 };
		}

		return { active: false, gap: 0 };
	}

	/**
	 * JS fallback for Sticky TOC when CSS position:sticky is guaranteed
	 * to fail (some ancestor has an overflow other than visible - an
	 * inherent CSS spec limitation, not something this plugin's CSS
	 * alone can fix). Only activates when a failure is actually
	 * detected, so most pages (where CSS sticky works normally) don't
	 * carry any scroll-listener cost at all - staying "lightweight" per
	 * the original principle.
	 *
	 * @param {HTMLElement} toc Root element, .gamestuff-toc.
	 */
	function initStickyFallback( toc ) {
		var hasAnySticky =
			toc.classList.contains( 'gamestuff-toc--sticky-desktop' ) ||
			toc.classList.contains( 'gamestuff-toc--sticky-tablet' ) ||
			toc.classList.contains( 'gamestuff-toc--sticky-mobile' );

		if ( ! hasAnySticky ) {
			return;
		}

		if ( ! ancestorBreaksSticky( toc ) ) {
			return;
		}

		var baseOffset = parseInt(
			window.getComputedStyle( toc ).getPropertyValue( '--gamestuff-toc-sticky-offset' ),
			10
		) || 0;

		var placeholder = document.createElement( 'div' );
		var isFixed = false;
		var originalTop = null;
		var ticking = false;

		function measureNaturalTop() {
			var rect = toc.getBoundingClientRect();
			return rect.top + window.pageYOffset;
		}

		function stick( offset ) {
			var rect = toc.getBoundingClientRect();

			placeholder.style.width = rect.width + 'px';
			placeholder.style.height = rect.height + 'px';
			toc.parentNode.insertBefore( placeholder, toc );

			toc.style.position = 'fixed';
			toc.style.top = offset + 'px';
			toc.style.width = rect.width + 'px';

			isFixed = true;
		}

		function unstick() {
			toc.style.position = '';
			toc.style.top = '';
			toc.style.width = '';

			if ( placeholder.parentNode ) {
				placeholder.parentNode.removeChild( placeholder );
			}

			isFixed = false;
		}

		function update() {
			var config = getStickyConfig( toc );

			if ( ! config.active ) {
				if ( isFixed ) {
					unstick();
				}

				originalTop = null;
				return;
			}

			if ( null === originalTop ) {
				originalTop = measureNaturalTop();
			}

			var offset = baseOffset + config.gap;
			var shouldStick = window.pageYOffset + offset >= originalTop;

			if ( shouldStick && ! isFixed ) {
				stick( offset );
			} else if ( ! shouldStick && isFixed ) {
				unstick();
			}
		}

		window.addEventListener(
			'scroll',
			function () {
				if ( ticking ) {
					return;
				}

				ticking = true;

				window.requestAnimationFrame( function () {
					update();
					ticking = false;
				} );
			},
			{ passive: true }
		);

		window.addEventListener(
			'resize',
			function () {
				// Size changed (screen rotation, breakpoint crossed, etc.) -
				// the natural position must be recalculated from scratch,
				// not read from a stale cached value.
				if ( isFixed ) {
					unstick();
				}

				originalTop = null;
				update();
			},
			{ passive: true }
		);

		update();
	}

	/**
	 * Checks whether any ancestor (up to html/body) has an overflow that
	 * isn't "visible" - if so, native CSS position:sticky is GUARANTEED
	 * to fail for this element (a CSS spec limitation, not a bug).
	 *
	 * @param {HTMLElement} el Element whose ancestors are checked.
	 * @return {boolean}
	 */
	function ancestorBreaksSticky( el ) {
		var node = el.parentElement;

		while ( node ) {
			var style = window.getComputedStyle( node );

			if (
				'visible' !== style.overflow ||
				'visible' !== style.overflowX ||
				'visible' !== style.overflowY
			) {
				return true;
			}

			if ( node === document.body ) {
				break;
			}

			node = node.parentElement;
		}

		return false;
	}

	function init() {
		document.querySelectorAll( '.gamestuff-toc' ).forEach( initToc );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
