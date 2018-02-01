<?php

/**
 * Helper functions
 *
 * @package   WPThemeExporter
 * @category  functions
 * @author    WeDesignWeBuild
 * @license   GPLv3
 * @version   1.0
 */

/**
 * Get ignore extensions
 * 
 * @return array
 */
function wp_theme_exporter_get_ignore_exts() {
	return apply_filters( 'wp_theme_exporter_get_ignore_exts', array( '.DS_Store', '.git', 'desktop.ini', 'humbs.db', '.dropbox', '.dropbox.attr', 'icon\r', '.log', '.cfg', '.config', '.md', 'package-lock.json', 'package.json', 'webpack.mix.js' ) );
}

/**
 * Sanitize plugin list
 * 
 * @return array | bool
 */
function wp_theme_exporter_sanitize_array( $value ) {

	if ( empty( $value ) ) {
		return false;
	}

	if ( is_string( $value ) ) {
		$value = array( $value );
	}

	return array_map( function($val) {
		return sanitize_key( $val );
	}, $value );
}
