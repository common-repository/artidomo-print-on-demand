<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.artidomo.eu
 * @since             1.0.0
 * @package           Artidomo_Print
 *
 * @wordpress-plugin
 * Plugin Name:       artidomo print on-demand
 * Plugin URI:        https://www.artidomo.eu/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Artidomo Team
 * Author URI:        https://www.artidomo.eu/impressum/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       artidomo-print-on-demand
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('ARTIDOMO_PRINT_VERSION', '1.0.0');
define('ARTIDOMO_PRINT_PATH', plugin_dir_path(__FILE__));
define('ARTIDOMO_API_SERVER_URL', 'https://www.artidomo.eu');
define('ARTIDOMO_PRINT_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-artidomo-print-activator.php
 */
function activate_artidomo_print()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-artidomo-print-activator.php';
    Artidomo_Print_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-artidomo-print-deactivator.php
 */
function deactivate_artidomo_print()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-artidomo-print-deactivator.php';
    Artidomo_Print_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_artidomo_print');
register_deactivation_hook(__FILE__, 'deactivate_artidomo_print');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-artidomo-print.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_artidomo_print()
{

    $plugin = new Artidomo_Print();
    $plugin->run();
}
run_artidomo_print();
