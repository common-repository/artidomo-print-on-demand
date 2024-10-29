<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.artidomo.eu
 * @since      1.0.0
 *
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Artidomo_Print
 * @subpackage Artidomo_Print/public
 * @author     Artidomo Team <cmitexperts@gmail.com>
 */
class Artidomo_Print_Public
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/artidomo-print-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/artidomo-print-public.js', array('jquery'), $this->version, false);
	}

	/**
	 * create order on main site
	 *
	 * @since    1.0.0
	 */

	public function ac_order_created($order_id)
	{

		if (!$order_id)
			return;

		if (!get_post_meta($order_id, '_thankyou_action_done', true)) {
			$order = wc_get_order($order_id);

			$data = $itemData = array();

			foreach ($order->get_items() as $item_id => $item) {


				$product_id = (int)$item->get_product_id();
				$main_product = get_post_meta($product_id, 'artidomo_product', true);
				$static_file = get_post_meta($product_id, 'artidomo_static_file_id', true);
				$main_variant = '';
				if ($main_product) {
					$product = wc_get_product($product_id);

					if ($product->is_type('variable')) {
						$mapped_variations = get_post_meta($product_id, 'ac_product_mapped_variations', true);
						if (is_array($mapped_variations)) {
							foreach ($mapped_variations as $mapped_variation) {
								if ($mapped_variation['var_id'] == $item->get_variation_id()) {
									$main_variant = isset($mapped_variation['ar_var_id']) ? $mapped_variation['ar_var_id'] : 0;
									if (isset($mapped_variation['ar_pr_id']) && $mapped_variation['ar_pr_id'])
										$main_product = $mapped_variation['ar_pr_id'];
								}
							}
						}
					} else {
						$main_variant = get_post_meta($product_id, 'artidomo_main_variant', true);
					}
					$itemData[$item_id] = array(
						"ProductID" => $product_id,
						"VariationID" => $item->get_variation_id(),
						"ProductName" => $item->get_name(),
						"ProductMeta" => $item->get_meta_data(),
						"ProductQty" => $item->get_quantity(),
						"ProductSubtotal" => $item->get_subtotal(),
						"ProductTotal" => $item->get_total(),
						"ProductType" => $item->get_type(),
						'MainProduct' => $main_product,
						"MainVariant" => $main_variant
					);
				}
				$file_handling = get_post_meta($product_id, 'artidomo_file_handling_method', true);
				$fullfill_status = get_post_meta($product_id, 'artidomo_shop_status', true);
				$file = $item->get_meta('_ac_uploaded_file', true);
				$item->add_meta_data('_ac_main_product', $main_product);
				$item->add_meta_data('_ac_main_variant', $main_variant);
				$item->add_meta_data('_ac_file_handling', $file_handling);
				$item->add_meta_data('_ac_fullfill_status', $fullfill_status);
				if (empty($file) && !empty($static_file) && $file_handling == 'static') {
					$item->add_meta_data('_ac_uploaded_file', $static_file);
				}
			}
			$data['order_id'] = $order_id;
			$data['items'] = $itemData;
			$data['order_date'] = $order->get_date_created()->date("Y-m-d H:i:s");
			$data['customer_id'] =  $order->get_customer_id();
			$data['customer_name'] =  $order->get_billing_first_name();
			$data['customer_surname'] =  $order->get_billing_last_name();
			$data['customer_company_name'] =  $order->get_billing_company();
			$data['customer_address'] =  rtrim($order->get_billing_address_1() . ', ' . $order->get_billing_address_2(), ', ');
			$data['customer_city'] =  $order->get_billing_city();
			$data['customer_zip'] =  $order->get_billing_postcode();
			$data['customer_country'] =  $order->get_billing_country();
			$data['customer_email'] =  $order->get_billing_email();
			$data['customer_phone'] =  $order->get_billing_phone();

			if (!empty($data['items']) && is_array($data['items'])) {
				$apidata = get_option('_artidomo_api_details');
				$api_key = isset($apidata['artidomo_api_key']) ? $apidata['artidomo_api_key'] : '';
				$secret_key = isset($apidata['artidomo_secret_key']) ? $apidata['artidomo_secret_key'] : '';

				$url = ARTIDOMO_API_SERVER_URL . '/wp-json/artidomo-orders/v1/create';
				$response = wp_remote_post(
					esc_url($url),
					array(
						'timeout'     => 120,
						'headers'     => [
							'Content-Type' => 'application/json',
							'Authorization' => 'Bearer ' . base64_encode(esc_attr($secret_key) . ':' . esc_attr($api_key)),
						],
						'body'        => json_encode($data)
					)
				);
				$order->update_meta_data('_ac_order_log', $response);
				$response = wp_remote_retrieve_body($response);
				$response = json_decode($response);
				if ($response && isset($response->success)) {
					$this->ac_update_item_meta($order, $response->data);
					if (ac_can_send_to_cloud($order)) {
						ac_send_data_to_cloud($order);
					}
				}
			}
			$order->update_meta_data('_thankyou_action_done', true);
			$order->save();
		}
	}

	public function ac_update_item_meta($order, $data)
	{
		foreach ($order->get_items() as $item_id => $item) {
			$productData = isset($data->$item_id->product) ? $data->$item_id->product : array();
			if (empty($productData))	continue;
			$item->add_meta_data('_ac_main_product_name', $productData->description);
			$item->add_meta_data('_ac_main_product_cat', $productData->category);
			$item->add_meta_data('_ac_main_product_subcat', $productData->subcategory);
			$item->add_meta_data('_ac_main_product_small_length', $productData->small_length);
			$item->add_meta_data('_ac_main_product_large_length', $productData->large_length);
			$item->add_meta_data('_ac_main_product_qm', $productData->qm);
			$item->add_meta_data('_ac_main_product_weight', $productData->weight);
			$item->add_meta_data('_ac_main_product_price', $productData->price_netto);
			$item->add_meta_data('_ac_main_order_id', $data->$item_id->order_id);
			$item->add_meta_data('_ac_shop_id', $data->$item_id->shop_id);
			$item->add_meta_data('_ac_ftp_upload', $productData->ftp_upload);
		}
		$order->save();
	}

	public function ac_uploads_section()
	{
		global $product;
		$file_handling = get_post_meta($product->get_id(), 'artidomo_file_handling_method', true);
		if ($file_handling == 'enduser') {
			$max_upload_size = wp_max_upload_size();
			$upload_size_text = sprintf(__('Maximum upload file size is: %s.', 'artidomo-print-on-demand'), esc_html(size_format($max_upload_size)));
			$upload_label = esc_html__('Upload File: ', 'artidomo-print-on-demand');
			$file_upload =
				'<div class="ac-file-upload-sec">
					<label for="ac_file_upload">' . esc_html($upload_label) . '</label>
					<input type="file" name="ac_file_upload" max_upload_size="' . esc_attr($max_upload_size) . '" id="ac_file_upload" accept="image/*" class="ac_file_upload" />
					<p class="upload-limit-text">' . esc_html($upload_size_text) . '</p>
				</div>';
			echo $file_upload;
		}
	}

	public function ac_add_cart_item_data($cart_item_meta, $product_id)
	{

		$file_id = array();

		if (isset($_FILES) && isset($_FILES['ac_file_upload']) && $_FILES['ac_file_upload']['name'] !== '') {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			$attach_id = media_handle_upload('ac_file_upload', 0);

			$file_id['media_id'] = $attach_id;
			$file_id['media_url'] = wp_get_attachment_url(esc_attr($attach_id));
			$cart_item_meta['ac_file_ids'][] = $file_id;
		}

		return $cart_item_meta;
	}

	public function ac_get_cart_item_from_session($cart_item, $values)
	{

		if (isset($values['ac_file_ids'])) {
			$cart_item['ac_file_ids'] = $values['ac_file_ids'];
		}
		return $cart_item;
	}

	public function ac_get_item_data($other_data, $cart_item)
	{
		if (isset($cart_item['ac_file_ids'])) {
			foreach ($cart_item['ac_file_ids'] as $ac_file_id) {
				$name    = esc_html__('Uploaded File', 'artidomo-print-on-demand');
				$display = $ac_file_id['media_id'];

				$other_data[] = array(
					'name'    => $name,
					'display' => basename(get_attached_file($display))
				);
			}
		}

		return $other_data;
	}

	public function ac_add_item_meta_url($item, $cart_item_key, $values, $order)
	{

		if (empty($values['ac_file_ids'])) {
			return;
		}

		foreach ($values['ac_file_ids'] as $key => $ac_file_id) {
			$item->add_meta_data('_ac_uploaded_file', $ac_file_id['media_id']);
		}
	}

	public function ac_remove_cart_action($cart_item_key, $cart)
	{
		$removed_item = $cart->removed_cart_contents[$cart_item_key];

		if (
			isset($removed_item['ac_file_ids']) && isset($removed_item['ac_file_ids'][0]) &&
			isset($removed_item['ac_file_ids'][0]['media_id']) && $removed_item['ac_file_ids'][0]['media_id'] !== ''
		) {

			$media_id = $removed_item['ac_file_ids'][0]['media_id'];

			$delete_status = wp_delete_attachment($media_id, true);
		}
	}
}
