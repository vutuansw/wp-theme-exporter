<?php
/**
 * Plugin Settings
 *
 * @class     Settings
 * @package   WPThemeExporter
 * @subpackage WPThemeExporter\Admin
 * @category  Class
 * @author    WeDesignWeBuild
 * @license   GPLv3
 * @version   1.0
 */
namespace WPThemeExporter\Admin;

class Settings {

	private $id;
	private $title;
	private $fields;

	/**
	 * @access private
	 * @var string Container of the field in markup HTML
	 */
	private $field_wrapper;

	/**
	 * @access private
	 * @var array Group fields
	 */
	private $group_fields = array();
	
	private $values = array();

	public function __construct( $args = array() ) {

		$defaults = array(
			'id' => 'wdwb_settings',
			'title' => esc_html__( 'Settings', 'wp-theme-exporter' ),
			'fields' => array(
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$keys = array_keys( get_object_vars( $this ) );

		foreach ( $keys as $key ) {
			if ( isset( $args[$key] ) ) {
				$this->$key = $args[$key];
			}
		}

		$this->values = get_option( 'wp_theme_exporter_settings', array() );

		$this->field_wrapper = '<div class="wdwb_form_row" %3$s><div class="col-label">%1$s</div><div class="col-field">%2$s</div></div>';
	}

	/**
	 * Process field output
	 * 
	 * @global Object $post
	 * @param array $args
	 * @return string Html
	 */
	public function render() {

		$output = '';

		$output .= sprintf( '<div class="wdwb-settings wdwb-settings_%s">', $this->id );

		$output .= sprintf( '<input type="hidden" name="%s_nonce" value="%s" />', $this->id, wp_create_nonce( $this->id ) );

		foreach ( $this->fields as $field ) {
			/**
			 * Field value
			 */
			$value = '';

			if ( isset( $this->values[$field['name']] ) ) {
				$value = $this->values[$field['name']];
			} elseif ( !empty( $field['value'] ) ) {
				$value = $field['value'];
			}

			$field['value'] = $value;

			/**
			 * Before render field type
			 */
			do_action( 'WPThemeExporter\Field\before_render_' . $field['type'], $field );

			/**
			 * Render field
			 */
			$field_output = $this->field_render( $field );

			/**
			 * Add field to group
			 */
			if ( $field_output != '' ) {
				$group = !empty( $field['group'] ) ? $field['group'] : '';
				if ( empty( $this->group_fields[$group] ) ) {
					$this->group_fields[$group] = array();
				}
				$this->group_fields[$group][] = $field_output;
			}
		}

		if ( count( $this->group_fields ) == 1 && !key( $this->group_fields ) ) {
			$output .= implode( '', $this->group_fields[''] );
		} else {
			$nav = '';
			$content = '';
			$index = 0;
			foreach ( $this->group_fields as $name => $fields ) {

				$name = empty( $name ) ? esc_html__( 'General', 'wp-theme-exporter' ) : $name;
				$index++;
				$active = $index == 1 ? 'active' : '';
				$id = $this->id . '-group_' . $index;
				$nav .= sprintf( '<li><a href="#%s" class="%s">%s</a></li>', $id, $active, $name );
				$content .= sprintf( '<div id="%s" class="group_item %s">%s</div>', $id, $active, implode( '', $fields ) );
			}

			$output .= '<div class="wdwb_group">';
			$output .= '<ul class="group_nav">' . $nav . '</ul>';
			$output .= '<div class="group_panel_container"><div class="group_panel">' . $content . '</div>';
			$output .= '</div></div>';
		}

		$output .= '</div>';

		print $output;
	}

	/**
	 * Process field
	 * @access private
	 * @return string Field Html
	 */
	private function field_render( $field ) {

		$control = new Field( $field );

		$required = $control->required ? '<span>*</span>' : '';

		$lable = !empty( $control->title ) ? sprintf( '<label for="%1$s">%2$s %3$s</label>', $control->name, $control->title, $required ) : '';

		$desc = !empty( $control->description ) ? sprintf( '<p class="description">%s</p>', $control->description ) : '';

		$attrs = sprintf( 'data-param_name="%s" ', $control->name );

		$attrs .= !empty( $field['dependency'] ) && is_array( $field['dependency'] ) ? 'data-dependency="' . esc_attr( json_encode( $field['dependency'] ) ) . '"' : '';

		if ( isset( $control->sanitize_callback ) && function_exists( $control->sanitize_callback ) ) {
			$control->value = call_user_func( $control->sanitize_callback, $control->value );
		}

		$field_output = $control->render() . $desc;

		$field_output = sprintf( $this->field_wrapper, $lable, $field_output, $attrs );

		return $field_output;
	}
	
}
