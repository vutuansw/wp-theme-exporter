<?php
/**
 * ControlVars Class
 *
 * @class     ControlVars
 * @package   WPThemeExporter
 * @subpackage WPThemeExporter\Field
 * @category  Class
 * @author    WeDesignWeBuild
 * @license   GPLv3
 * @version   1.0
 */
namespace WPThemeExporter\Field;

trait ControlVars{
	public $type;
	public $name;
	public $title;
	public $value;
	public $required = false;
	public $description;
	public $options = array();
	public $multiple = false;
	public $input_attrs = array();
	public $sanitize_callback;
	public $placeholder;
}