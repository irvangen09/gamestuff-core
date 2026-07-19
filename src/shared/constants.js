/**
 * Shared constants for GameStuff Core block frontend scripts.
 *
 * Single source of truth for values used by more than one block, so
 * they don't get hardcoded independently in each block's view.js.
 */

/**
 * Viewport width, in pixels, at and below which GameStuff blocks
 * switch to their mobile layout.
 *
 * Must stay in sync with the `$gamestuff-mobile-breakpoint` SCSS
 * variable in src/shared/_tokens.scss (SCSS can't import a JS
 * constant, so that value needs to be updated by hand if this one
 * ever changes).
 */
export const MOBILE_BREAKPOINT = 781;
