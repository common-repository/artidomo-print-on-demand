<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.artidomo.eu
 * @since      1.0.0
 *
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/includes
 * @author     Artidomo Team <cmitexperts@gmail.com>
 */
class Artidomo_Print
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Artidomo_Print_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('ARTIDOMO_PRINT_VERSION')) {
			$this->version = ARTIDOMO_PRINT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'artidomo-print';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Artidomo_Print_Loader. Orchestrates the hooks of the plugin.
	 * - Artidomo_Print_i18n. Defines internationalization functionality.
	 * - Artidomo_Print_Admin. Defines all hooks for the admin area.
	 * - Artidomo_Print_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-artidomo-print-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-artidomo-print-i18n.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/artidomo-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-artidomo-print-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-artidomo-print-public.php';

		$this->loader = new Artidomo_Print_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Artidomo_Print_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Artidomo_Print_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Artidomo_Print_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_filter('plugin_action_links_' . plugin_basename(__FILE__), $plugin_admin, 'artidomo_plugin_settings_link');
		$this->loader->add_action('admin_menu', $plugin_admin, 'artidomo_print_register_settings');
		$this->loader->add_filter('woocommerce_product_data_tabs', $plugin_admin, 'artidomo_new_product_tab', 999999);
		$this->loader->add_action("wp_ajax_artidomo_get_product", $plugin_admin, "artidomo_get_product_select");
		$this->loader->add_action("wp_ajax_artidomo_get_variation", $plugin_admin, "artidomo_get_variation_select");

		$this->loader->add_action("admin_init", $plugin_admin, "product_link_delete_redirect");

		//get main product variations
		$this->loader->add_action('wp_ajax_artidomo_get_main_product_variation', $plugin_admin, 'artidomo_get_main_product_variation');
		$this->loader->add_action("wp_ajax_nopriv_artidomo_get_main_product_variation", $plugin_admin, "artidomo_get_main_product_variation");
		$this->loader->add_action('wp_ajax_artidomo_get_main_product_products', $plugin_admin, 'artidomo_get_main_product_products');
		$this->loader->add_action("wp_ajax_nopriv_artidomo_get_main_product_products", $plugin_admin, "artidomo_get_main_product_products");

		//Add metabox to orders
		$this->loader->add_action("add_meta_boxes", $plugin_admin, "ac_add_metabox_to_orders");
		$this->loader->add_filter("woocommerce_hidden_order_itemmeta", $plugin_admin, "ac_hide_order_item_meta");
		$this->loader->add_action("save_post", $plugin_admin, "ac_update_order_data", 10, 2);
		$this->loader->add_action("post_edit_form_tag", $plugin_admin, "ac_post_edit_form_tag");
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Artidomo_Print_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('woocommerce_thankyou', $plugin_public, 'ac_order_created');
		$this->loader->add_action('woocommerce_before_add_to_cart_button', $plugin_public, 'ac_uploads_section');
		$this->loader->add_filter('woocommerce_add_cart_item_data', $plugin_public, 'ac_add_cart_item_data', 10, 2);
		$this->loader->add_filter('woocommerce_get_cart_item_from_session', $plugin_public, 'ac_get_cart_item_from_session', 10, 2);
		$this->loader->add_filter('woocommerce_get_item_data', $plugin_public, 'ac_get_item_data', 10, 2);
		$this->loader->add_action('woocommerce_checkout_create_order_line_item', $plugin_public, 'ac_add_item_meta_url', 10, 4);
		$this->loader->add_action('woocommerce_cart_item_removed', $plugin_public, 'ac_remove_cart_action', 10, 2);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Artidomo_Print_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
