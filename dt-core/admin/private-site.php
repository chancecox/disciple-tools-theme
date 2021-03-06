<?php
/**
 * TODO: This feature is present and force enabled in the D.T. theme. In the plugin it should be an option checkbox to enable.
 */

/**
 * Handles the private site and private feed features of the plugin.  If private site is
 * selected in the plugin settings, the plugin will redirect all non-logged-in users to the
 * login page.  If private feed is selected, all content is blocked from feeds from the site.
 *
 * @package    Members
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2009 - 2016, Justin Tadlock
 * @link       http://themehybrid.com/plugins/members
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

# Redirects users to the login page.
add_action( 'template_redirect', 'disciple_tools_please_log_in', 0 );

# Disable content in feeds if the feed should be private.
add_filter( 'the_content_feed', 'disciple_tools_private_feed', 95 );
add_filter( 'the_excerpt_rss', 'disciple_tools_private_feed', 95 );
add_filter( 'comment_text_rss', 'disciple_tools_private_feed', 95 );

/**
 * Conditional tag to see if we have a private blog.
 *
 * @since  0.1.0
 * @access public
 * @return bool
 */
function disciple_tools_is_private_blog() {
    return true;
}

/**
 * Conditional tag to see if we have a private feed.
 *
 * @since  0.1.0
 * @access public
 * @return bool
 */
function disciple_tools_is_private_feed() {
    return true;
}

/**
 * Redirects users that are not logged in to the 'wp-login.php' page.
 *
 * @since  0.1.0.0
 * @access public
 * @return void
 */
function disciple_tools_please_log_in() {

    // Check if the private blog feature is active and if the user is not logged in.
    if ( disciple_tools_is_private_blog() && !is_user_logged_in() ) {

        // If using BuddyPress and on the register/activate page, don't do anything.
        if ( function_exists( 'bp_is_current_component' ) && ( bp_is_current_component( 'register' ) || bp_is_current_component( 'activate' ) ) ) {
            return;
        }

        // Redirect to the login page.
        auth_redirect();
        exit;
    }
}

/**
 * Blocks feed items if the user has selected the private feed feature.
 *
 * @since  0.2.0
 * @access public
 *
 * @param  string $content
 *
 * @return string
 */
function disciple_tools_private_feed( $content ) {

    return disciple_tools_is_private_feed() ? disciple_tools_get_private_feed_message() : $content;
}

/**
 * Returns the private feed error message.
 *
 * @since  0.1.0
 * @access public
 * @return string
 */
function disciple_tools_get_private_feed_message() {

    return apply_filters( 'disciple_tools_feed_error_message', 'Restricted Feed' );
}
