<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function ac_send_data_to_cloud($order)
{
	if (!is_dir(ARTIDOMO_PRINT_PATH . 'temp')) {
		mkdir(ARTIDOMO_PRINT_PATH . 'temp');
	}
	$path = ARTIDOMO_PRINT_PATH . 'temp/';
	$customerID = $order->get_customer_id();
	$customername = "";
	if (!empty($customerID)) {
		$customer = get_user_by('id', (int)$customerID);
		$customername = $customer->user_login;
	}
	if (empty($customerID))	$customername = 'Guest';
	$orderId = $order->get_id();
	$createdon = $order->get_date_created();
	$createdon = date('d-m-Y H:i:s', strtotime($createdon));
	$shop_id = 0;
	foreach ($order->get_items() as $item_id => $item) {
		$shop_id = $item->get_meta('_ac_shop_id', true);
	}
	$xml = new SimpleXMLElement('<xml/>');
	$orderxml = $xml->addChild('Order');
	$orderxml->addChild('CustomerID', esc_attr($shop_id));
	$orderxml->addChild('OrderID', esc_attr($orderId));
	$orderxml->addChild('Date', esc_attr($createdon));
	$items = $orderxml->addChild('items');

	// Get and Loop Over Order Items
	$i = 1;
	$files = array();
	$upload_to_ftp = false;
	$items_count = count($order->get_items());
	foreach ($order->get_items() as $item_id => $item) {
		$ProductID = $item->get_meta('_ac_main_product', true);
		$ProductDesciption = $item->get_meta('_ac_main_product_name', true);
		$ProductCategory = $item->get_meta('_ac_main_product_cat', true);
		$ProductSubCategory = $item->get_meta('_ac_main_product_subcat', true);
		$ProductSmallLength = $item->get_meta('_ac_main_product_small_length', true);
		$ProductLargeLength = $item->get_meta('_ac_main_product_large_length', true);
		$ProductQm = $item->get_meta('_ac_main_product_qm', true);
		$ProductWeight = $item->get_meta('_ac_main_product_weight', true);
		$PriceNetto = $item->get_meta('_ac_main_product_price', true);
		$ProductOption = $item->get_meta('_ac_main_variant', true);
		$shop_id = $item->get_meta('_ac_shop_id', true);
		$ftp_upload = $item->get_meta('_ac_ftp_upload', true);

		$itemRow = $items->addChild("ProductDesciption", sanitize_text_field($ProductDesciption));
		$items->addChild("ProductID", sanitize_text_field($ProductID));
		$items->addChild("ProductCategory", sanitize_text_field($ProductCategory));
		$items->addChild("ProductSubCategory", $ProductSubCategory);
		$items->addChild("ProductQty", sanitize_text_field($item->get_quantity()));
		$items->addChild("ProductSmallLength", sanitize_text_field($ProductSmallLength));
		$items->addChild("ProductLargeLength", sanitize_text_field($ProductLargeLength));
		$items->addChild("ProductQm", sanitize_text_field($ProductQm));
		$items->addChild("ProductWeight", sanitize_text_field($ProductWeight));
		$items->addChild("ProductOption", sanitize_text_field($ProductOption));
		$items->addChild("PriceNetto", sanitize_text_field($PriceNetto));

		$file = $item->get_meta('_ac_uploaded_file', true);
		$file_prefix = "";
		if ($file) {
			$filetype = wp_check_filetype(basename(wp_get_attachment_url(esc_attr($file))));
			$ext = isset($filetype['ext']) ? $filetype['ext'] : '';

			$file_prefix = $shop_id . '_' . $ProductDesciption . '_' . $ProductID . '_' . $orderId . '_' . $customername . '_' . $ProductSmallLength . '_' . $ProductLargeLength . '_' . $i . '_' . $items_count . '.' . $ext;
			$file_prefix = str_replace(' ', '-', strip_tags($file_prefix));
			$code = ac_send_file_to_cloud($file, $file_prefix);
			$item->add_meta_data('_ac_file_upload_status', $code);
		}
		if ($ftp_upload && !empty($file_prefix)) {
			$image_uploaded = arti_file_upload_to_ftp(wp_get_attachment_url(esc_attr($file)), esc_attr($file_prefix));
			$item->add_meta_data('_ac_image_upload_ftp_status', sanitize_text_field($image_uploaded));
			$upload_to_ftp = true;
		}
		$items->addChild("PrintFile", esc_html($file_prefix));
		$i++;
	}
	$order_billing_address = str_replace("<br/>", ", ", $order->get_formatted_billing_address());
	$order_shipping_address = str_replace("<br/>", ", ", $order->get_formatted_shipping_address());

	$shop = $items->addChild('shop');
	$shop->addChild('ShopID', esc_url(site_url()));
	$shop->addChild('ShopName', sanitize_text_field(get_bloginfo('name')));
	$shop->addChild('ShopEmail', sanitize_text_field(get_option('admin_email')));


	$EndCustomer = $items->addChild('EndCustomer');
	$EndCustomer->addChild('CustomerID', esc_attr($order->get_customer_id()));
	$BillingAddress = $EndCustomer->addChild('BillingAddress');
	$ShippingAddress = $EndCustomer->addChild('ShippingAddress');

	// Get Order User, Billing & Shipping Addresses
	$BillingAddress->addChild('billing_first_name', sanitize_text_field($order->get_billing_first_name()));
	$BillingAddress->addChild('billing_last_name', sanitize_text_field($order->get_billing_last_name()));
	$BillingAddress->addChild('billing_company', sanitize_text_field($order->get_billing_company()));
	$BillingAddress->addChild('billing_address_1', sanitize_text_field($order->get_billing_address_1()));
	$BillingAddress->addChild('billing_address_2', sanitize_text_field($order->get_billing_address_2()));
	$BillingAddress->addChild('billing_city', sanitize_text_field($order->get_billing_city()));
	$BillingAddress->addChild('billing_state', sanitize_text_field($order->get_billing_state()));
	$BillingAddress->addChild('billing_postcode', sanitize_text_field($order->get_billing_postcode()));
	$BillingAddress->addChild('billing_email', sanitize_text_field($order->get_billing_email()));
	$BillingAddress->addChild('billing_phone', sanitize_text_field($order->get_billing_phone()));
	$BillingAddress->addChild('formatted_billing_full_name', sanitize_text_field($order->get_formatted_billing_full_name()));
	$BillingAddress->addChild('formatted_billing_address', sanitize_text_field($order_billing_address));


	$ShippingAddress->addChild('shipping_first_name', $order->get_shipping_first_name() ? sanitize_text_field($order->get_shipping_first_name()) : sanitize_text_field($order->get_billing_first_name()));
	$ShippingAddress->addChild('shipping_last_name', $order->get_shipping_last_name() ? sanitize_text_field($order->get_shipping_last_name()) : sanitize_text_field($order->get_billing_last_name()));
	$ShippingAddress->addChild('shipping_company', $order->get_shipping_company() ? sanitize_text_field($order->get_shipping_company()) : sanitize_text_field($order->get_billing_company()));
	$ShippingAddress->addChild('shipping_address_1', $order->get_shipping_address_1() ? sanitize_text_field($order->get_shipping_address_1()) : sanitize_text_field($order->get_billing_address_1()));
	$ShippingAddress->addChild('shipping_address_2', $order->get_shipping_address_2() ? sanitize_text_field($order->get_shipping_address_2()) : sanitize_text_field($order->get_billing_address_2()));
	$ShippingAddress->addChild('shipping_city', $order->get_shipping_city() ? sanitize_text_field($order->get_shipping_city()) : sanitize_text_field($order->get_billing_city()));
	$ShippingAddress->addChild('shipping_state', $order->get_shipping_state() ? sanitize_text_field($order->get_shipping_state()) : sanitize_text_field($order->get_billing_state()));
	$ShippingAddress->addChild('shipping_postcode', $order->get_shipping_postcode() ? sanitize_text_field($order->get_shipping_postcode()) : sanitize_text_field($order->get_billing_postcode()));
	$ShippingAddress->addChild('formatted_shipping_full_name', $order->get_formatted_shipping_full_name() ? sanitize_text_field($order->get_formatted_shipping_full_name()) : sanitize_text_field($order->get_formatted_billing_full_name()));
	$ShippingAddress->addChild('formatted_shipping_address', $order_shipping_address ? sanitize_text_field($order_shipping_address) : sanitize_text_field($order_billing_address));

	$xmlFileData  = $xml->asXML();
	// Header('Content-type: text/xml');
	$nextcloud_details = artidomo_get_nextcloud_details();
	if (!empty($nextcloud_details)) {
		$login = esc_attr($nextcloud_details['username']);
		$password = esc_attr($nextcloud_details['password']);

		$xml_name = $shop_id . '_' . $orderId . '_' . $customername . '_' . $ProductSmallLength . '_' . $ProductLargeLength . '.xml';
		$xml_name = str_replace(' ', '-', strip_tags($xml_name));

		$url = esc_url_raw($nextcloud_details['url']) . '/' . sanitize_text_field($xml_name);

		$response = wp_remote_request(esc_url($url), array(
			'timeout' => 120,
			'sslverify' => false,
			'method' => 'PUT',
			'body'    => $xmlFileData,
			'headers' => array(
				'OCS-APIRequest' => 'true',
				'Authorization' => 'Basic ' . base64_encode(esc_attr($login) . ':' . esc_attr($password)),
			),
		));
		if (!is_wp_error($response)) {
			$order->update_meta_data('_send_to_nextcloud', true);
		}
	}
	if ($upload_to_ftp) {
		if (!is_file($path . $xml_name)) {
			$xmlDom = dom_import_simplexml($xml)->ownerDocument;
			$xmlDom->formatOutput = true;
			$xmlDom->save($path . $xml_name);
		}
		$xml_uploaded = arti_file_upload_to_ftp($path . $xml_name, $xml_name);
		$order->update_meta_data('_upload_ftp_status', sanitize_text_field($xml_uploaded));
		if (is_dir($path)) {
			deleteDirectory(ARTIDOMO_PRINT_PATH . 'temp');
		}
	}
	$order->save();
}

function deleteDirectory($dir)
{
	if (!file_exists($dir)) {
		return true;
	}

	if (!is_dir($dir)) {
		return unlink($dir);
	}

	foreach (scandir($dir) as $item) {
		if ($item == '.' || $item == '..') {
			continue;
		}

		if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
			return false;
		}
	}

	return rmdir($dir);
}

function ac_send_file_to_cloud($file, $filename)
{
	$nextcloud_details = artidomo_get_nextcloud_details();
	if (!empty($nextcloud_details)) {
		if (empty($file))	return;
		$login = esc_attr($nextcloud_details['username']);
		$password = esc_attr($nextcloud_details['password']);
		$gestor = fopen(get_attached_file($file), "r");
		$contenido = fread($gestor, filesize(get_attached_file($file)));
		fclose($gestor);

		$url = esc_url_raw($nextcloud_details['url']) . '/' . $filename;

		$response = wp_remote_request(esc_url($url), array(
			'timeout' => 120,
			'sslverify' => false,
			'method' => 'PUT',
			'body'    => $contenido,
			'headers' => array(
				'OCS-APIRequest' => 'true',
				'Authorization' => 'Basic ' . base64_encode(esc_attr($login) . ':' . esc_attr($password)),
			),
		));
		if (!is_wp_error($response)) {
			$response = json_decode(wp_remote_retrieve_body($response), true);
			$httpcode = 201;
		}
		return $httpcode;
	}
}

function ac_can_send_to_cloud($order)
{
	$send = true;
	foreach ($order->get_items() as $item_id => $item) {
		$status = $item->get_meta('_ac_fullfill_status', true);
		if (is_admin() && ($status == 'Open' || $status == "Paid")) {
			$send = false;
		} elseif ($status == "Paid" && $order->is_paid() == 1) {
			$send = true;
		} else if (!is_admin() && ($status == "Paid" || $status == "Manually")) {
			$send = false;
		}
	}
	return $send;
}

if (!function_exists('artidomo_get_nextcloud_details')) {
	function artidomo_get_nextcloud_details()
	{
		$artidomo_setting = get_option('_artidomo_api_details');
		if (!empty($artidomo_setting['artidomo_secret_key']) && !empty($artidomo_setting['artidomo_api_key'])) {
			$artidomo_secret_key = $artidomo_setting['artidomo_secret_key'];
			$artidomo_api_key = $artidomo_setting['artidomo_api_key'];
			$apidata = array('api_key' => esc_attr($artidomo_api_key), 'secret_key' => esc_attr($artidomo_secret_key), 'neednextcloud' => "yes");
			$postdata = json_encode($apidata);
			$url = ARTIDOMO_API_SERVER_URL . "/wp-json/artidomo/verify-user/";
			$response = wp_remote_post(esc_url($url), array(
				'timeout' => 120,
				'sslverify' => false,
				'body'    => $postdata,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . base64_encode(esc_attr($artidomo_secret_key) . ':' . esc_attr($artidomo_api_key)),
				),
			));
			if (!is_wp_error($response)) {
				$response = json_decode(wp_remote_retrieve_body($response), true);
				if ($response['data']['nextcloud_details'])
					return $response['data']['nextcloud_details'];
				exit;
			}
		}
	}
}

if (!function_exists('artidomo_get_ftp_details')) {
	function artidomo_get_ftp_details()
	{
		$artidomo_setting = get_option('_artidomo_api_details');
		if (!empty($artidomo_setting['artidomo_secret_key']) && !empty($artidomo_setting['artidomo_api_key'])) {
			$artidomo_secret_key = $artidomo_setting['artidomo_secret_key'];
			$artidomo_api_key = $artidomo_setting['artidomo_api_key'];
			$apidata = array('api_key' => esc_attr($artidomo_api_key), 'secret_key' => esc_attr($artidomo_secret_key), 'needftp' => "yes");
			$postdata = json_encode($apidata);
			$url = ARTIDOMO_API_SERVER_URL . "/wp-json/artidomo/verify-user/";
			$response = wp_remote_post(esc_url($url), array(
				'timeout' => 120,
				'sslverify' => false,
				'body'    => $postdata,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . base64_encode(esc_attr($artidomo_secret_key) . ':' . esc_attr($artidomo_api_key)),
				),
			));
			if (!is_wp_error($response)) {
				$response = json_decode(wp_remote_retrieve_body($response), true);
				if ($response['data']['ftp_details'])
					return $response['data']['ftp_details'];
				exit;
			}
		}
	}
}


function arti_file_upload_to_ftp($file, $filename)
{
	$status = 200;
	$ftp_details = artidomo_get_ftp_details();
	$host = (isset($ftp_details['host'])) ? esc_attr($ftp_details['host']) : "";
	$user = (isset($ftp_details['username'])) ? esc_attr($ftp_details['username']) : "";
	$pass = (isset($ftp_details['password'])) ? esc_attr($ftp_details['password']) : "";
	$path = (isset($ftp_details['url'])) ? esc_attr($ftp_details['url']) : "";
	$dest_file = $path . $filename;
	$source_file = $file;
	$ftp = ftp_connect($host);
	ftp_login($ftp, $user, $pass);
	$ret = ftp_nb_put($ftp, esc_attr($dest_file), $source_file, FTP_BINARY, FTP_AUTORESUME);
	while (FTP_MOREDATA == $ret) {
		$ret = ftp_nb_continue($ftp);
	}
	if ($ret != FTP_FINISHED) {
		$status = 400;
	}
	ftp_close($ftp);
	return $status;
}

function ac_get_main_product($id)
{
	$apidata = get_option('_artidomo_api_details');
	$api_key = isset($apidata['artidomo_api_key']) ? $apidata['artidomo_api_key'] : '';
	$secret_key = isset($apidata['artidomo_secret_key']) ? $apidata['artidomo_secret_key'] : '';
	$url = ARTIDOMO_API_SERVER_URL . '/wp-json/artidomo-products/v1/product/product=' . $id;
	$response = wp_remote_get(
		esc_url($url),
		array(
			'timeout'     => 120,
			'headers'     => [
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . base64_encode(esc_attr($secret_key) . ':' . esc_attr($api_key)),
			]
		)
	);

	$response = wp_remote_retrieve_body($response);
	$response = json_decode($response);
	if (isset($response->data->id))  return $response->data;
	return $response;
}

if (!function_exists('ac_get_main_product_of_category')) {
	function ac_get_main_product_of_category($category, $small_length = "", $large_length = "", $checksize = "")
	{
		$apidata = get_option('_artidomo_api_details');
		$api_key = isset($apidata['artidomo_api_key']) ? $apidata['artidomo_api_key'] : '';
		$secret_key = isset($apidata['artidomo_secret_key']) ? $apidata['artidomo_secret_key'] : '';

		$sendata = array('category' => esc_html($category));
		if ($small_length)
			$sendata['small_length'] = esc_html($small_length);
		if ($large_length)
			$sendata['large_length'] = esc_html($large_length);
		if ($checksize)
			$sendata['checksize'] = esc_html($checksize);

		$postdata = json_encode($sendata);
		$url = ARTIDOMO_API_SERVER_URL . '/wp-json/artidomo-category/v1/products-of';
		$response = wp_remote_post(esc_url($url), array(
			'timeout' => 120,
			'sslverify' => false,
			'body'    => $postdata,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . base64_encode(esc_attr($secret_key) . ':' . esc_attr($api_key)),
			),
		));
		if (!is_wp_error($response)) {
			$response = json_decode(wp_remote_retrieve_body($response), true);
			if (count($response['data']) > 0)
				return $response['data'];
			exit;
		}
	}
}



function ac_get_main_product_variants($product)
{
	$variants = array();
	if ($product && isset($product->id)) {
		if ($product->option_1)  $variants[] = $product->option_1;
		if ($product->option_2)  $variants[] = $product->option_2;
		if ($product->option_3)  $variants[] = $product->option_3;
		if ($product->option_4)  $variants[] = $product->option_4;
		if ($product->option_5)  $variants[] = $product->option_5;
	}
	return $variants;
}
