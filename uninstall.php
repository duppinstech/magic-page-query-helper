<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package MPQH
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_site_option( 'mpqh_github_release' );
