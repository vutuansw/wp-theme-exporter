<?php
/**
 * Control Class
 *
 * @class     Control
 * @package   WPThemeExporter
 * @subpackage WPThemeExporter\Field
 * @category  Class
 * @author    WeDesignWeBuild
 * @license   GPLv3
 * @version   1.0
 */
namespace WPThemeExporter\Field;

class Control {

	use ControlVars;

	public function __construct( $args = array() ) {

		$keys = array_keys( get_object_vars( $this ) );

		foreach ( $keys as $key ) {
			if ( isset( $args[$key] ) ) {
				$this->$key = $args[$key];
			}
		}


		if ( !isset( $this->input_attrs['class'] ) || !is_array( $this->input_attrs['class'] ) ) {
			$this->input_attrs['class'] = array();
		}
	}

	/**
	 * Render the custom attributes for the control's input element.
	 */
	public function input_attrs() {

		$this->input_attrs['id'] = $this->name;
		$this->input_attrs['name'] = $this->name;

		if ( is_array( $this->input_attrs['class'] ) ) {

			$default_class = array( 'wdwb-field', 'wdwb-value', 'wdwb-field-type-' . $this->type );

			$this->input_attrs['class'] = array_merge( $this->input_attrs['class'], $default_class );

			$this->input_attrs['class'] = array_unique( $this->input_attrs['class'] );

			$this->input_attrs['class'] = implode( ' ', $this->input_attrs['class'] );
		}

		$atrrs = array();

		foreach ( $this->input_attrs as $attr => $value ) {
			$atrrs[] = $attr . '="' . esc_attr( $value ) . '"';
		}

		return $atrrs;
	}

	public function render() {
		if ( method_exists( $this, "field_$this->type" ) ) {

			return $this->{"field_$this->type"}();
		}
	}

	/**
	 * Field Textfield.
	 *
	 * @since 1.0.0
	 * @return string - html string.
	 */
	private function field_text() {

		$this->input_attrs['class'][] = 'widefat';

		return sprintf( '<input type="text" value="%s" %s/>', htmlspecialchars( $this->value ), implode( ' ', $this->input_attrs() ) );
	}

	/**
	 * Field Textarea
	 *
	 * @since 1.0.0
	 * @return string - html string.
	 */
	private function field_textarea() {
		$this->input_attrs['class'][] = 'widefat';
		return sprintf( '<textarea %s>%s</textarea>', implode( ' ', $this->input_attrs() ), esc_textarea( $this->value ) );
	}

	/**
	 * Field Checkbox
	 * 
	 * @since 1.0.0
	 * @return string - html string.
	 */
	private function field_checkbox() {

		if ( is_array( $this->value ) ) {
			$this->value = implode( ',', $this->value );
		}

		$output = '';

		if ( $this->multiple ) {

			if ( is_array( $this->options ) ) {

				$arr_values = !empty( $this->value ) ? explode( ',', $this->value ) : array();

				$output .= sprintf( '<input type="hidden" value="%s" %s/>', $this->value, implode( ' ', $this->input_attrs() ) );

				$output .= '<ul class="wdwb-field wdwb-checkboxes">';

				foreach ( $this->options as $checkbox_key => $checkbox_value ) {

					$checked = in_array( $checkbox_key, $arr_values ) ? 'checked' : '';

					$output .= sprintf( '<li><label><input %s type="checkbox" value="%s"/><span>%s</span></label></li>', $checked, $checkbox_key, $checkbox_value );
				}

				$output .= '</ul>';
			}
		} else {

			if ( $this->value ) {
				$this->input_attrs['checked'] = 'checked';
			}
			$output .= sprintf( '<input type="checkbox" value="1" class="wdwb-field wdwb-checkbox wdwb_value" %s/>', implode( ' ', $this->input_attrs() ) );
		}

		return $output;
	}

	/**
	 * Field Select
	 * 
	 * @since 1.0.0
	 * @return string - html string.
	 */
	private function field_select() {

		$output = '';

		if ( $this->multiple ) {

			$this->input_attrs['class'][] = 'wdwb-select-multiple';
			$this->input_attrs['multiple'] = 'multiple';
		}

		$output .= sprintf( '<select %s>', implode( ' ', $this->input_attrs() ) );

		if ( is_array( $this->options ) ) {
			foreach ( $this->options as $option_key => $option_value ) {

				if ( is_array( $this->value ) ) {
					$selected = in_array( $option_key, $this->value ) ? 'selected' : '';
				} else {
					$selected = $option_key === $this->value ? 'selected' : '';
				}

				$output .= sprintf( '<option %s value="%s">%s</option>', $selected, $option_key, $option_value );
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Field Radio
	 *
	 * @since 1.0.0
	 * @return string - html string.
	 */
	private function field_radio() {

		$output = '';

		if ( is_array( $this->options ) ) {

			foreach ( $this->input_attrs['class'] as $key => $val ) {
				if ( in_array( $val, array( 'wdwb-field', 'wdwb-field-type-radio' ) ) ) {
					unset( $this->input_attrs['class'][$key] );
				}
			}

			$output .= '<ul class="wdwb-field wdwb-field-type-radio">';

			foreach ( $this->options as $radio_key => $radio_value ) {

				$checked = $radio_key === $this->value ? 'checked' : '';

				$output .= sprintf( '<li><label><input %s %s type="radio" value="%s"/><span>%s</span></label></li>', $checked, implode( ' ', $this->input_attrs() ), $radio_key, $radio_value );
			}

			$output .= '</ul>';
		}

		return $output;
	}

}
