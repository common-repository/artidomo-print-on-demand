<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.artidomo.eu
 * @since      1.0.0
 *
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/includes
 * @author     Artidomo Team <cmitexperts@gmail.com>
 */
class Artidomo_Print_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'artidomo-print-on-demand',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
