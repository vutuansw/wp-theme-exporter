<?php
/**
 * Plugin Name:     WP Theme Exporter
 * Plugin URI:      https://wordpress.org/plugins/wp-theme-exporter
 * Description:     WP Theme Exporter is a amazing tool helps you export theme and plugin easily.    
 * Version:         1.2    
 * Author:          WeDesignWeBuild
 * Author URI:      https://profiles.wordpress.org/wedesignwebuild
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Requires at least: 4.5
 * Tested up to: 4.9
 * Text Domain: wp-theme-exporter
 * Domain Path: /languages/
 *
 * @package    WPThemeExporter
 */

define( 'WP_THEME_EXPORTER_VER', '1.2' );
define( 'WP_THEME_EXPORTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_THEME_EXPORTER_URL', plugin_dir_url( __FILE__ ) );

/**
 * First, we need autoload via Composer to make everything works.
 */
require WP_THEME_EXPORTER_DIR . 'vendor/autoload.php';
require WP_THEME_EXPORTER_DIR . 'includes/helpers.php';

/**
 *  Make WPThemeExporter\WPThemeExporter as WPThemeExporter alias.
 */
class_alias( 'WPThemeExporter\\WPThemeExporter', 'WPThemeExporter' );
class_alias( 'WPThemeExporter\\Admin\\Settings', 'WPThemeExporterSettings' );

/**
 * Run
 */
$GLOBALS['wp_theme_exporter'] = new WPThemeExporter;
