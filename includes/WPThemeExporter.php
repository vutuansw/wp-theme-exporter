<?php

/**
 * WPThemeExporter Class
 *
 * @class     WPThemeExporter
 * @package   WPThemeExporter
 * @category  Class
 * @author    WeDesignWeBuild
 * @license   GPLv3
 * @version   1.0
 */

namespace WPThemeExporter;

class WPThemeExporter {

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'registerPage' ) );

		add_action( 'wp_ajax_wp_theme_exporter_dump', array( $this, 'ajaxDump' ) );
		add_action( 'wp_ajax_wp_theme_exporter_export', array( $this, 'ajaxExport' ) );
		add_action( 'wp_ajax_wp_theme_exporter_save_settings', array( $this, 'ajaxSaveSettings' ) );
		add_action( 'wp_ajax_wp_theme_exporter_reset_settings', array( $this, 'ajaxResetSettings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'adminScripts' ) );
		
	}

	/**
	 * Enqueue script in admin
	 */
	public function adminScripts( $hook ) {

		if ( $hook == 'tools_page_wp-theme-exporter' ) {

			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'selectize', WP_THEME_EXPORTER_URL . 'assets/vendors/selectize/selectize.js', array( 'jquery' ), WP_THEME_EXPORTER_VER );
			wp_enqueue_style( 'selectize', WP_THEME_EXPORTER_URL . 'assets/vendors/selectize/selectize.css', null, WP_THEME_EXPORTER_VER );
			wp_enqueue_style( 'selectize-skin', WP_THEME_EXPORTER_URL . 'assets/vendors/selectize/selectize.default.css', null, WP_THEME_EXPORTER_VER );

			wp_enqueue_style( 'wp_theme_exporter', WP_THEME_EXPORTER_URL . 'assets/css/admin.css', array(), WP_THEME_EXPORTER_VER );
			wp_enqueue_script( 'wp_theme_exporter', WP_THEME_EXPORTER_URL . 'assets/js/admin.js', array( 'jquery' ), WP_THEME_EXPORTER_VER, true );

			wp_localize_script( 'wp_theme_exporter', 'wp_theme_exporter', array(
				'form' => array(
					'plswait' => esc_html__( 'Please wait...', 'wp-theme-exporter' ),
					'export' => esc_html__( 'Export now', 'wp-theme-exporter' ),
					'zipinfo' => esc_html__( 'will have been in', 'wp-theme-exporter' ),
					'savechange' => esc_html__( 'Save changes', 'wp-theme-exporter' ),
					'reset' => esc_html__( 'Reset', 'wp-theme-exporter' )
				)
			) );
		}
	}

	/**
	 * Export file
	 */
	public function ajaxExport() {

		if ( !empty( $_POST['wp_theme_exporter_nonce'] ) && wp_verify_nonce( $_POST['wp_theme_exporter_nonce'], 'wp_theme_exporter' ) ) {
			
			/**
			 * Check user permission
			 */
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to do this action.', 'wp-theme-exporter' ) );
			}
			
			if( !class_exists( '\ZipArchive')){
				wp_send_json_error( esc_html__( 'ZipArchive extension was not installed for php.', 'wp-theme-exporter' ) );
			}

			$theme = !empty( $_POST['export_theme'] ) ? sanitize_key( $_POST['export_theme'] ) : false;
			$plugins = !empty( $_POST['export_plugin'] ) ? wp_theme_exporter_sanitize_array( $_POST['export_plugin'] ) : false;
			
			$upload = wp_upload_dir();
			$uploadDir = $upload['basedir'] . '/wp-theme-exporter/';
			$uploadUrl = $upload['baseurl'] . '/wp-theme-exporter/';

			$pluginDir = $uploadDir . 'plugins/';
			$themeDir = $uploadDir . 'themes/';

			$pluginUrl = $uploadUrl . 'plugins/';
			$themeUrl = $uploadUrl . 'themes/';


			$themesource = WP_CONTENT_DIR . '/themes/' . $theme;


			if ( !is_dir( $pluginDir ) ) {
				mkdir( $pluginDir, 0777, true );
			}

			if ( !is_dir( $themeDir ) ) {
				mkdir( $themeDir, 0777, true );
			}

			$results = array();
			$zip = new Zip( $_POST );

			if ( $theme && $plugins ) {

				/**
				 * Change path into theme
				 */
				$pluginDir = $themesource . '/plugins/';

				if ( !is_dir( $pluginDir ) ) {
					mkdir( $pluginDir, 0777, true );
				}

				/**
				 * Remove all plugin files in theme before export
				 */
				if ( isset( $_POST['theme_plugin_folder_clear'] ) && is_dir( $pluginDir ) ) {
					
					$files = scandir( $pluginDir );

					if ( $files ) {
						foreach ( $files as $file ) {
							if ( $file != '.' && $file != '..' ) {
								unlink( trailingslashit( $pluginDir ) . $file );
							}
						}
					}
				}

				/**
				 * Zip plugin into theme
				 */
				foreach ( $plugins as $plugin ) {

					$plugin = sanitize_key( $plugin );
					$pluginsource = trailingslashit( WP_PLUGIN_DIR ) . $plugin;
					$pluginDest = $pluginDir . $plugin . '.zip';
					$pluginDestUrl = $pluginUrl . $plugin . '.zip';

					$zip->setType( 'plugin' );
					$zip->zipDir( $pluginsource, $pluginDest );
					$this->saveFile( $plugin, 'plugin', $pluginDest, $pluginDestUrl );
				}

				/**
				 * Zip theme
				 */
				$themeDest = $themeDir . $theme . '.zip';
				$themeDestUrl = $themeUrl . $theme . '.zip';

				$zip->setType( 'theme' );
				$zip->zipDir( $themesource, $themeDest );

				$results[] = $this->saveFile( $theme, 'theme', $themeDest, $themeDestUrl );
			} elseif ( $theme ) {

				$themeDest = $themeDir . $theme . '.zip';
				$themeDestUrl = $themeUrl . $theme . '.zip';

				$zip->setType( 'theme' );
				$zip->zipDir( $themesource, $themeDest );
				$results[] = $this->saveFile( $theme, 'theme', $themeDest, $themeDestUrl );
				
			} elseif ( $plugins ) {

				foreach ( $plugins as $plugin ) {

					$plugin = sanitize_key( $plugin );

					$pluginsource = trailingslashit( WP_PLUGIN_DIR ) . $plugin;
					$pluginDest = $pluginDir . $plugin . '.zip';
					$pluginDestUrl = $pluginUrl . $plugin . '.zip';

					$zip->setType( 'plugin' );
					$zip->zipDir( $pluginsource, $pluginDest );

					$results[] = $this->saveFile( $plugin, 'plugin', $pluginDest, $pluginDestUrl );
				}
			} else {
				wp_send_json_error( esc_html__( 'Please, select a theme or plugin to export.', 'wp-theme-exporter' ) );
			}

			if ( $results ) {
				$html = '<ul>';

				$nonce_url = wp_create_nonce( 'wp_theme_exporter_download' );

				foreach ( $results as $value ) {
					$download_link = add_query_arg( 'check', $nonce_url, admin_url( 'admin-ajax.php?action=wp_theme_exporter_dump&file=' . $value['md5'] ) );
					$html .= sprintf( '<li><span>%s.zip - %s</span><a class="wpef-button" href="%s" target="_blank">%s</a></li>', $value['name'], size_format( $value['size'], 2 ), $download_link, esc_html__( 'Download', 'wp-theme-exporter' ) );
				}
				$html .= '</ul>';

				wp_send_json_success( $html );
			}

			wp_send_json_error( esc_html__( 'Cannot zip file.', 'wp-theme-exporter' ) );
		}

		wp_send_json_error( esc_html__( 'Verify code is invalid.', 'wp-theme-exporter' ) );
	}

	/**
	 * Dump file
	 */
	public function ajaxDump() {

		if ( isset( $_GET['file'] ) ) {
			
			/**
			 * Check is valid download
			 */
			check_admin_referer( 'wp_theme_exporter_download', 'check' );
			
			/**
			 * Check user permission
			 */
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-theme-exporter' ) );
			}

			$key = sanitize_text_field( $_GET['file'] );

			if ( $arr = get_option( 'wp_theme_exporter_list' ) ) {

				if ( isset( $arr[$key] ) ) {
					$arr = $arr[$key];

					if ( file_exists( $arr['file'] ) ) {
						$zip = new Zip();
						$zip->dump( $arr['url'] );
					} else {
						echo esc_html__( 'Cannot find the file.', 'wp-theme-exporter' );
					}
				}
			} else {
				echo esc_html__( 'Invalid donwload link.', 'wp-theme-exporter' );
			}

			exit;
		}
	}

	public function ajaxSaveSettings() {

		if ( !empty( $_POST['wp_theme_exporter_nonce'] ) && wp_verify_nonce( $_POST['wp_theme_exporter_nonce'], 'wp_theme_exporter' ) ) {
			
			/**
			 * Check user permission
			 */
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to do this action.', 'wp-theme-exporter' ) );
			}

			$settings = get_option( 'wp_theme_exporter_settings', array() );
			$fields = array( 'plugin_ignore_extensions', 'plugin_ignore_folder', 'theme_ignore_extensions', 'theme_ignore_folder', 'theme_plugin_folder', 'theme_plugin_folder_clear' );

			if ( isset( $_POST['theme_plugin_folder_clear'] ) ) {
				$_POST['theme_plugin_folder_clear'] = 1;
			} else {
				$_POST['theme_plugin_folder_clear'] = 0;
			}

			foreach ( $fields as $value ) {
				if ( isset( $_POST[$value] ) ) {
					$settings[$value] = stripslashes( sanitize_textarea_field( $_POST[$value] ) );
				}
			}

			update_option( 'wp_theme_exporter_settings', $settings );
			wp_send_json_success( esc_html__( 'Update setting successfully.', 'wp-theme-exporter' ) );
		}
	}

	public function ajaxResetSettings() {
		if ( !empty( $_POST['wp_theme_exporter_nonce'] ) && wp_verify_nonce( $_POST['wp_theme_exporter_nonce'], 'wp_theme_exporter' ) ) {
			
			/**
			 * Check user permission
			 */
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to do this action.', 'wp-theme-exporter' ) );
			}

			delete_option( 'wp_theme_exporter_settings' );
			wp_send_json_success( esc_html__( 'Reset settings successfully.', 'wp-theme-exporter' ) );
		}
	}

	/**
	 * Register plugin page
	 */
	public function registerPage() {
		add_submenu_page( 'tools.php', esc_html__( 'WP Theme Exporter', 'wp-theme-exporter' ), esc_html__( 'WP Theme Exporter', 'wp-theme-exporter' ), 'manage_options', 'wp-theme-exporter', array( $this, 'settingPage' ) );
	}

	/**
	 * Save file info
	 */
	public function saveFile( $name, $type, $file, $url ) {

		$md5 = md5( $file );

		$files = get_option( 'wp_theme_exporter_list', array() );

		$files[$md5] = array(
			'name' => $name,
			'file' => $file,
			'url' => $url,
			'type' => $type,
			'time' => date( "Y-m-d H:i:s" ),
			'size' => filesize( $file ),
			'md5' => $md5
		);

		update_option( 'wp_theme_exporter_list', $files );

		return $files[$md5];
	}

	public function settingPage() {
		include WP_THEME_EXPORTER_DIR . 'templates/plugin-page.php';
	}

}
