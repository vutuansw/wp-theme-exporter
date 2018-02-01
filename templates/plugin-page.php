<div class="wrap">

	<h2 class="wp-heading-inline"></h2>

	<form method="POST" class="wp_theme_exporter_form">

		<div class="wp_theme_exporter_heading">
			<h1><?php echo esc_html__( 'WP Theme Exporter', 'wp-theme-exporter' ) ?></h1>
		</div>

		<?php
		$themes = wp_get_themes();
		$plugins = get_plugins();
		$plugin_opts = array();

		foreach ( $plugins as $key => $obj ) {
			$key = explode( '/', $key );
			$plugin_opts[$key[0]] = $obj['Name'] . ' (v' . $obj['Version'] . ')';
		}

		$currentTheme = wp_get_theme()->get( 'Name' );

		$theme_opts = array( '' => esc_html__( '-- Select theme --', 'wp-theme-exporter' ) );

		foreach ( $themes as $key => $obj ) {
			$current = '';
			if ( $currentTheme == $obj->get( 'Name' ) ) {
				$current = esc_attr__( '(current theme)', 'wp-theme-exporter' );
			}
			$theme_opts[$key] = $obj->get( 'Name' ) . $current;
		}

		$plugin_ex = implode( ', ', wp_theme_exporter_get_ignore_exts() );

		$settings = new WPThemeExporterSettings( array(
			'id' => 'wp_theme_exporter',
			'title' => esc_html__( 'Settings', 'wp-theme-exporter' ),
			'fields' => array(
				array(
					'title' => esc_html__( 'Export plugins', 'wp-theme-exporter' ),
					'name' => 'export_plugin',
					'type' => 'select',
					'value' => '',
					'group' => esc_html__( 'Export', 'wp-theme-exporter' ),
					'description' => wp_kses_post( sprintf( __( 'Select plugins to export (Note: Select a theme bellow to push these plugins as zipped file in to %s in the theme.', 'wp-theme-exporter' ), '<code>/plugins</code>' )),
					'options' => $plugin_opts,
					'multiple' => true,
					'input_attrs' => array( 'placeholder' => esc_html__( 'Select plugins', 'wp-theme-exporter' ) )
				),
				array(
					'title' => esc_html__( 'Export theme', 'wp-theme-exporter' ),
					'name' => 'export_theme',
					'type' => 'select',
					'value' => '',
					'group' => esc_html__( 'Export', 'wp-theme-exporter' ),
					'description' => esc_html__( 'Select theme to export', 'wp-theme-exporter' ),
					'options' => $theme_opts
				),
				array(
					'title' => esc_html__( 'Plugin ignore extensions', 'wp-theme-exporter' ),
					'name' => 'plugin_ignore_extensions',
					'type' => 'textarea',
					'value' => $plugin_ex,
					'description' => esc_html__( 'Enter ignore extensions (Note: Devide a ignore extension with a comma).', 'wp-theme-exporter' ),
					'group' => esc_html__( 'Settings', 'wp-theme-exporter' ),
				),
				array(
					'title' => esc_html__( 'Plugin ignore folder', 'wp-theme-exporter' ),
					'name' => 'plugin_ignore_folder',
					'type' => 'textarea',
					'value' => 'node_modules',
					'description' => esc_html__( 'Enter ignore folder (Note: Divide a ignore folder with a comma).', 'wp-theme-exporter' ),
					'group' => esc_html__( 'Settings', 'wp-theme-exporter' ),
				),
				array(
					'title' => esc_html__( 'Theme ignore extensions', 'wp-theme-exporter' ),
					'name' => 'theme_ignore_extensions',
					'type' => 'textarea',
					'value' => $plugin_ex,
					'description' => esc_html__( 'Enter ignore extensions (Note: Divide a ignore extension with a comma).', 'wp-theme-exporter' ),
					'group' => esc_html__( 'Settings', 'wp-theme-exporter' ),
				),
				array(
					'title' => esc_html__( 'Theme ignore folder', 'wp-theme-exporter' ),
					'name' => 'theme_ignore_folder',
					'type' => 'textarea',
					'value' => 'document, node_modules',
					'description' => esc_html__( 'Enter ignore folder (Note: Divide a ignore folder with a comma).', 'wp-theme-exporter' ),
					'group' => esc_html__( 'Settings', 'wp-theme-exporter' ),
				),
				array(
					'title' => esc_html__( 'Plugin folder in theme', 'wp-theme-exporter' ),
					'name' => 'theme_plugin_folder',
					'type' => 'text',
					'value' => 'plugins',
					'description' => esc_html__( 'Enter a folder name (ex: ../wp-content/themes/yourtheme/<code>plugins</code>/plugin-toolkit.zip).', 'wp-theme-exporter' ),
					'group' => esc_html__( 'Settings', 'wp-theme-exporter' ),
				),
				array(
					'title' => esc_html__( 'Clear plugins folder in theme', 'wp-theme-exporter' ),
					'name' => 'theme_plugin_folder_clear',
					'type' => 'checkbox',
					'value' => 0,
					'description' => esc_html__( 'Remove all file in (../wp-content/themes/yourtheme/<code>plugins</code>/) before export.', 'wp-theme-exporter' ),
					'group' => esc_html__( 'Settings', 'wp-theme-exporter' ),
				), )
				) );

		$settings->render();
		?>

		<div class="wp_theme_exporter_footer">
			<span class="warning"></span>
			<div class="buttons">
				<button type="button" id="restore_default" class="wpef-button wpef-button-secondary"><?php echo esc_html__( 'Reset', 'wp-theme-exporter' ) ?></button>
				<button type="button" id="save_change" class="wpef-button wpef-button-primary"><?php echo esc_html__( 'Save changes', 'wp-theme-exporter' ) ?></button>
				<button type="button" id="export" class="wpef-button wpef-button-primary"><i class="dashicons dashicons-download"></i><span><?php echo esc_html__( 'Export now', 'wp-theme-exporter' ) ?></span></button>
			</div>
		</div>
	</form>
</div>

<div class="wp_theme_exporter_modal">
	<div class="wp_theme_exporter_modal__layout"></div>
	<div class="wp_theme_exporter_modal__container">
		<div class="wp_theme_exporter_modal__header">
			<h3><?php echo esc_html__( 'Exported files','wp-theme-exporter' ) ?></h3>
			<a class="modal_close" href="#"><span class="dashicons dashicons-no-alt"></span></a>
		</div>
		<div class="wp_theme_exporter_modal__body">
		</div>
	</div>
</div>