<?php
/**
 * Clean up on uninstall.
 *
 * @package Karnisha_PowerBy
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_site_transient( 'kpb_github_release' );
