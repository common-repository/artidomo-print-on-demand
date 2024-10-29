<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.artidomo.eu
 * @since      1.0.0
 *
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/admin
 * @author     Artidomo Team <cmitexperts@gmail.com>
 */
class Artidomo_Print_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Artidomo_Print_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Artidomo_Print_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/artidomo-print-admin.css', array(), $this->version, 'all');
		wp_enqueue_style('arti-select2min', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Artidomo_Print_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Artidomo_Print_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script('arti-select2min', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/artidomo-print-admin.js', array('jquery'), $this->version, true);
		wp_localize_script(
			$this->plugin_name,
			'adminajax',
			array(
				'ajaxurl' => admin_url('admin-ajax.php')
			)
		);
	}


	public function artidomo_plugin_settings_link($links)
	{
		$_link = '<a href="' . esc_url(admin_url("admin.php?page=artidomo_settings")) . '">' . esc_html__('Settings', 'artidomo-print-on-demand') . '</a>';
		$links[] = $_link;
		return $links;
	}

	public function artidomo_print_register_settings()
	{
		add_menu_page(

			esc_html__('artidomo print settings', 'artidomo-print-on-demand'),
			esc_html__('artidomo print fulfilment', 'artidomo-print-on-demand'),
			'manage_options',
			'artidomo_settings',
			array($this, 'artidomo_plugin_page'),
			'dashicons-printer',
			75
		);

		add_submenu_page(
			'artidomo_settings',
			esc_html__('Settings', 'artidomo-print-on-demand'),
			esc_html__('Settings', 'artidomo-print-on-demand'),
			'manage_options',
			'settings',
			array($this, 'artidomo_settings_callback')
		);
		add_submenu_page(
			'artidomo_settings',
			esc_html__('Products Mapping list', 'artidomo-print-on-demand'),
			esc_html__('Products Mapping list', 'artidomo-print-on-demand'),
			'manage_options',
			'products_mapping_list',
			array($this, 'products_mapping_list_callback')
		);
		add_submenu_page(
			'artidomo_settings',
			esc_html__('New Mapping', 'artidomo-print-on-demand'),
			esc_html__('New Mapping', 'artidomo-print-on-demand'),
			'manage_options',
			'new_mapping',
			array($this, 'artidomo_new_mapping_callback')
		);
		add_submenu_page(
			'artidomo_settings',
			esc_html__('Help', 'artidomo-print-on-demand'),
			esc_html__('Help', 'artidomo-print-on-demand'),
			'manage_options',
			'artidomo_help',
			array($this, 'artidomo_help_callback')
		);

		remove_submenu_page('artidomo_settings', 'artidomo_settings');
	}



	public function artidomo_settings_callback()
	{
		require_once plugin_dir_path(__FILE__) . '/partials/artidomo-api-settings.php';
	}

	public function products_mapping_list_callback()
	{
		require_once plugin_dir_path(__FILE__) . '/partials/artidomo-product-mapping-settings.php';
	}

	public function artidomo_new_mapping_callback()
	{
		require_once plugin_dir_path(__FILE__) . '/partials/artidomo-new-mapping-settings.php';
	}

	public function artidomo_help_callback()
	{
		require_once plugin_dir_path(__FILE__) . '/partials/artidomo-help-settings.php';
	}

	public function artidomo_new_product_tab($tabs)
	{

		$tabs['artidomo_relation'] = array(
			'label'		=> esc_html__('Artidomo Relation', 'woocommerce'),
			'target'	=> 'artidomo_relation_options',
			'class'		=> array('show_if_simple'),
		);

		return $tabs;
	}

	public function artidomo_get_product_select()
	{
		$product_cat = (int)sanitize_key($_POST['product_cat']);
		$html = '';
		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query' => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $product_cat,
				),
			),
		);
		$the_query = new WP_Query($args);
		if ($the_query->have_posts()) {
			$html .= '<option value="">' . esc_html__('- Choose Product -', 'artidomo-print-on-demand') . '</option>';
			while ($the_query->have_posts()) {
				$the_query->the_post();
				$html .= '<option value="' . esc_attr(get_the_ID()) . '">#' . esc_html(get_the_ID()) . ' ' . esc_html(get_the_title()) . '</option>';
			}
		} else {
			$html .= '<option value="">' . esc_html__('No prodct found!', 'artidomo-print-on-demand') . '</option>';
		}

		wp_reset_postdata();

		wp_send_json_success(array('html' => wp_kses($html, array(
			'option' => array(
				'value' => array(),
				'selected' => array(),
			)
		))));
		exit();
	}

	public function artidomo_get_variation_select()
	{
		$product_id = (int)sanitize_key($_POST['product_id']);
		$product_html = '';
		$html = '';
		$is_variable = false;
		$product = wc_get_product($product_id);

		$product_id    = $product->get_id();
		$image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'single-post-thumbnail');
		$product_name  = $product->get_name();
		$product_sku   = $product->get_sku();
		$product_price = $product->get_price();

		$product_html .= '<div class="product-info-sec"><img src="' . esc_url($image[0]) . '" height="150">
		<div class="product-meta-sec">
        <h2>' . esc_html($product_name) . ' - ' . wc_price($product_price) . '</h2>
        <h4>SKU - ' . esc_html($product_sku) . '</h4>
        <a target="_blank" href="' . esc_url($product->get_permalink()) . '" class="button btn-primary">' . esc_html('View Product') . '</a></div></div>';




		if ($product->is_type('variable')) {
			$args = array(
				'post_type'     => 'product_variation',
				'post_status'   => 'publish',
				'numberposts'   => -1,
				'post_parent'   => $product_id,
			);
			$variations = get_posts($args);

			if (!empty($variations)) {
				$is_variable = true;
				$html .= '<option value="">' . esc_html__('- Choose Variation -', 'artidomo-print-on-demand') . '</option>';
				foreach ($variations as $variation) {

					$variation_ID = $variation->ID;

					$product_variation = new WC_Product_Variation($variation_ID);

					$variation_name = $product_variation->get_name();
					$attributes = $product_variation->get_attributes();
					$name  = $product_variation->get_name();
					$sku   = $product_variation->get_sku();
					$price = $product_variation->get_price();
					if ($price)
						$price = wc_price($price);
					$atts = "";
					if ($attributes) {
						$attributename = array();
						foreach ($attributes as $attribute) {
							if ($attribute)
								$attributename[] = $attribute;
						}
						$atts = implode("-", $attributename);
					}
					$html .= '<option data-name="' . esc_attr($variation_name) . '" value="' . esc_attr($variation_ID) . '" price="' . esc_attr($price) . '" name="' . esc_attr($name) . '" sku="' . esc_attr($sku) . '">' . esc_html($atts) . '</option>';
				}
			} else {
				$html .= '<option value="">' . esc_html__('No variation found!', 'artidomo-print-on-demand') . '</option>';
			}
		}
		$html = wp_kses($html, array(
			'option' => array(
				'value' => array(),
				'price' => array(),
				'sku' => array(),
				'name' => array(),
				'data-name' => array(),
			)
		));
		wp_send_json_success(array('product_html' => wp_kses_post($product_html), 'html' => $html, 'product_id' => esc_html($product_id), 'is_variable' => (bool)esc_html($is_variable)));
		exit();
	}



	public function product_link_delete_redirect()
	{

		if ((isset($_GET['delete']) && $_GET['page'] == 'products_mapping_list') && isset($_GET['delete']) && $_GET['delete']) {
			$post_id = intval(sanitize_text_field($_GET['delete']));
			$artidomo_setting = get_option('_artidomo_api_details');
			$artidomo_secret_key = $artidomo_api_key = "";
			if (!empty($artidomo_setting['artidomo_secret_key']) && !empty($artidomo_setting['artidomo_api_key'])) {
				$artidomo_secret_key = $artidomo_setting['artidomo_secret_key'];
				$artidomo_api_key = $artidomo_setting['artidomo_api_key'];
			}

			$url = ARTIDOMO_API_SERVER_URL . "/wp-json/artidomo-delete/v1/delete?id=" . $post_id;

			wp_remote_request(esc_url($url), array(
				'timeout' => 120,
				'sslverify' => false,
				'method' => 'DELETE',
				'headers' => array(
					"Accept: application/json",
					'Authorization' => 'Basic ' . base64_encode(esc_attr($artidomo_secret_key) . ':' . esc_attr($artidomo_api_key)),
				),
			));

			delete_post_meta($post_id, 'artidomo_shop_product');
			delete_post_meta($post_id, 'artidomo_shop_product_cat');
			delete_post_meta($post_id, 'artidomo_shop_variation');
			delete_post_meta($post_id, 'artidomo_shop_status');

			delete_post_meta($post_id, 'artidomo_product');
			delete_post_meta($post_id, 'artidomo_product_cat');
			delete_post_meta($post_id, 'artidomo_static_file_id');
			delete_post_meta($post_id, 'artidomo_file_handling_method');
			delete_post_meta($post_id, 'artidomo_variations');

			wp_redirect(esc_url(admin_url('/admin.php?page=products_mapping_list')));
		}
	}

	public function artidomo_get_main_product_variation()
	{
		if (!isset($_POST['id'])) {
			echo json_encode(array('res' => 'error', 'msg' => esc_html__("Invalid Product ID!", "artidomo-print")));
			exit();
		}
		$id = sanitize_text_field($_POST['id']);

		$product = ac_get_main_product($id);
		$price = $product->price_netto;
		$html = '<option value="">' . esc_html("No variation found!") . '</option>';
		if ($product && isset($product->id)) {
			$html = '<option value="">' . esc_html__('- Choose Variant -', 'artidomo-print-on-demand') . '</option>';
			$variants = ac_get_main_product_variants($product);
			if ($variants) {
				foreach ($variants as $variant) {
					$html .= '<option price="' . esc_attr($price) . '" value="' . esc_attr($variant) . '">' . esc_html($variant) . '</option>';
				}
			}
		}
		echo json_encode(array('res' => 'success', 'html' => wp_kses($html, array(
			'option' => array(
				'value' => array(),
				'price' => array(),
			)
		))));
		exit();
	}

	public function artidomo_get_main_product_products()
	{
		if (!isset($_POST['id']) ||  empty($_POST['id'])) {
			echo json_encode(array('res' => 'error', 'msg' => esc_html__("Invalid Product Category!", "artidomo-print")));
			exit();
		}
		$category = sanitize_text_field($_POST['id']);
		$smlength = ($_POST['smlength']) ? intval($_POST['smlength']) : "";
		$lglength = ($_POST['lglength']) ? intval($_POST['lglength']) : "";
		$checksize = ($_POST['checksize']) ? sanitize_text_field($_POST['checksize']) : "";
		$products = array();
		if ($category)
			$products = ac_get_main_product_of_category($category, $smlength, $lglength, $checksize);

		$html = '<option value="">' . esc_html('No product found!') . '</option>';
		if (!empty($products) && !isset($products['message'])) {
			$html = '<option value="">' . esc_html__('- Choose Artidomo Product -', 'artidomo-print-on-demand') . '</option>';
			if ($products) {
				foreach ($products as $product) {
					$price = (empty($product['option_1'])) ? $product['price_netto'] : "";
					$html .= '<option value="' . esc_attr($product['product_id']) . '" price="' . esc_attr($price) . '" size="' . esc_attr($product["small_length"]) . 'x' . esc_attr($product["large_length"]) . '">' . esc_html($product['description']) . '</option>';
				}
			}
		}
		echo json_encode(array('res' => 'success', 'html' => wp_kses($html, array(
			'option' => array(
				'value' => array(),
				'price' => array(),
				'size' => array(),
			)
		))));
		exit();
	}

	public function ac_hide_order_item_meta($arr)
	{

		$arr = array('_ac_uploaded_file', '_ac_main_product', '_ac_main_variant', '_ac_file_handling', '_ac_fullfill_status', '_ac_main_product_name', '_ac_main_product_cat', '_ac_main_product_subcat', '_ac_main_product_price', '_ac_main_order_id', '_ac_shop_id', '_ac_file_upload_status');
		return $arr;
	}
	public function ac_add_metabox_to_orders()
	{
		add_meta_box('ac-metabox', esc_html__('Artidomo Settings', 'artidomo-print-on-demand'), array($this, 'ac_show_metabox_to_orders'), 'shop_order', 'advanced', 'high');
	}

	public function ac_show_metabox_to_orders($post)
	{
		$order_id = $post->ID;
		$order_status = get_post_meta($order_id, '_ac_order_status', true);
		$order = wc_get_order($order_id);
?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label><?php esc_html_e("Status") ?>:</label></th>
					<td>
						<input id="fulfilled" type="radio" name="none" <?php if ($order_status == 1)	echo "checked"; ?> value="1" disabled>
						<label for="fulfilled"><?php esc_html_e("Full Filled") ?></label>
					</td>
					<td>
						<input id="cancelled" type="radio" name="none" <?php if ($order_status == 2)	echo "checked"; ?> value="2" disabled>
						<label for="cancelled"><?php esc_html_e("Cancelled") ?></label>
					</td>
					<td>
						<input id="billed" type="radio" name="none" <?php if ($order_status == 3)	echo "checked"; ?> value="3" disabled>
						<label for="billed"><?php esc_html_e("Billed") ?></label>
					</td>

				</tr>
				<?php
				foreach ($order->get_items() as $item_id => $item) {

					$main_product = $item->get_meta('_ac_main_product', true);
					if (empty($main_product))	continue;
					$main_variant = $item->get_meta('_ac_main_variant', true);
					$file = $item->get_meta('_ac_uploaded_file', true);
					$item->get_meta('_ac_file_handling', true);
					$status = $item->get_meta('_ac_fullfill_status', true);
				?>
					<tr>
						<th><?php esc_html_e("Item", "artidomo-print"); ?></th>
						<td><?php echo esc_html($item->get_name() . ' x ' . $item->get_quantity()); ?></td>
						<td><?php echo esc_html($main_variant); ?></td>
						<td>
							<?php
							if ($file) {

								echo '<a target="_blank" href="' . esc_url(basename(wp_get_attachment_url(esc_attr($file)))) . '">' . esc_html(basename(wp_get_attachment_url(esc_attr($file)))) . '</a>';
							} else {

								echo '<input type="file" name="ac_manual_file[' . esc_attr($item_id) . ']" id="ac_manual_file" >';
							}
							?>
						</td>
					</tr>

				<?php
				}
				?>
			</tbody>
		</table>
<?php
	}

	public function ac_update_order_data($order_id, $post)
	{
		if ($post->post_type != 'shop_order') {
			return;
		}
		if (!is_admin())	return;

		if (isset($_POST['ac_order_status'])) {
			update_post_meta($order_id, '_ac_order_status',  sanitize_text_field($_POST['ac_order_status']));
		}
		$items = array();

		if (isset($_FILES) && isset($_FILES['ac_manual_file'])) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			$files = $_FILES['ac_manual_file'];

			//aa($files);
			foreach ($files['name'] as $key => $value) {
				if ($files['name'][$key]) {
					$file = array(
						'name' => sanitize_text_field($files['name'][$key]),
						'type' => sanitize_text_field($files['type'][$key]),
						'tmp_name' => sanitize_text_field($files['tmp_name'][$key]),
						'error' => sanitize_text_field($files['error'][$key]),
						'size' => sanitize_text_field($files['size'][$key])
					);

					$_FILES = array("ac_manual_file" => $file);
					$attach_id = media_handle_upload('ac_manual_file', 0);
					$items[$key] = $attach_id;
				}
			}
		}

		$order = wc_get_order($order_id);
		foreach ($order->get_items() as $item_id => $item) {
			if ($items && isset($items[$item_id]) && $items[$item_id]) {
				$item->add_meta_data('_ac_uploaded_file', sanitize_text_field($items[$item_id]));
			}
		}
		$order->save();

		if (isset($_POST['ac_order_status']) && !empty($_POST['ac_order_status'])) {
			$apidata = get_option('_artidomo_api_details');
			$api_key = isset($apidata['artidomo_api_key']) ? $apidata['artidomo_api_key'] : '';
			$secret_key = isset($apidata['artidomo_secret_key']) ? $apidata['artidomo_secret_key'] : '';

			$data['fulfilled'] = ($_POST['ac_order_status'] == 1) ? 1 : '';
			$data['canceld'] = ($_POST['ac_order_status'] == 2) ? 1 : '';
			$data['billed'] = ($_POST['ac_order_status'] == 3) ? 1 : '';

			$url = ARTIDOMO_API_SERVER_URL . '/wp-json/artidomo-orders/v1/update/' . $order_id;

			wp_remote_post(
				esc_url($url),
				array(
					'timeout'     => 120,
					'method' 	  => 'PUT',
					'headers'     => [
						'Content-Type' => 'application/json',
						'Authorization' => 'Bearer ' . base64_encode(esc_attr($secret_key) . ':' . esc_attr($api_key)),
					],
					'body'        => json_encode($data)
				)
			);
		}
		$sent_to_cloud = get_post_meta($order_id, '_send_to_nextcloud', true);
		if (empty($sent_to_cloud) && ac_can_send_to_cloud($order)) {
			ac_send_data_to_cloud($order);
		}
	}

	public function ac_post_edit_form_tag($post)
	{
		if ($post->post_type != 'shop_order') {
			return;
		}
		echo ' enctype="multipart/form-data"';
	}
}

?>