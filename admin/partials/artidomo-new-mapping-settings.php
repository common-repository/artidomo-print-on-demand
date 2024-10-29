<?php
if ( ! defined( 'ABSPATH' ) ) exit;
//child product-cat
$product_cat = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));

//main product listing
$apidata = get_option('_artidomo_api_details');
$api_key = isset($apidata['artidomo_api_key']) ? $apidata['artidomo_api_key'] : '';
$secret_key = isset($apidata['artidomo_secret_key']) ? $apidata['artidomo_secret_key'] : '';
$result = array();

//main product category listing
$cat_url = ARTIDOMO_API_SERVER_URL . "/wp-json/artidomo-category/v1/product-categories";
$cat_response = wp_remote_get(esc_url($cat_url), array(
  'timeout'     => 120,
  'headers'     => [
    'Content-Type' => 'application/json',
    'Authorization' => 'Bearer ' . base64_encode(esc_attr($secret_key) . ':' . esc_attr($api_key)),
  ],
));
$cat_responseBody = wp_remote_retrieve_body($cat_response);
$cat_result = json_decode($cat_responseBody);
$cat_result = $cat_result->data;

// Current user data
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$user_login = $current_user->user_login;
$user_pass = $current_user->user_pass;
$user_nicename = $current_user->user_nicename;
$user_email = $current_user->user_email;
$user_url = $current_user->user_url;
$user_registered = $current_user->user_registered;
$display_name = $current_user->display_name;


//submit form
if (isset($_POST['submit']) && isset($_POST['artidomo_map_settings_nonce']) && current_user_can( 'edit_posts' ) && wp_verify_nonce( $_POST['artidomo_map_settings_nonce'], 'artidomo_map_settings' )) {

  if (isset($_POST['product']) && $_POST['product']) {

    $post_id = isset($_POST['product']) ? (int)sanitize_text_field($_POST['product']) : '';

    update_post_meta($post_id, 'artidomo_shop_product', sanitize_text_field($_POST['product']));

    if (isset($_POST['product_cat']) && $_POST['product_cat']) {
      update_post_meta($post_id, 'artidomo_shop_product_cat', sanitize_text_field($_POST['product_cat']));
    }

    if (isset($_POST['status']) && $_POST['status']) {
      update_post_meta($post_id, 'artidomo_shop_status', sanitize_text_field($_POST['status']));
    }

    if (isset($_POST['file_handling']) && $_POST['file_handling']) {
      update_post_meta($post_id, 'artidomo_file_handling_method', sanitize_text_field($_POST['file_handling']));
    }

    if (isset($_POST['artidomo_product_cat']) && $_POST['artidomo_product_cat']) {
      update_post_meta($post_id, 'artidomo_product_cat', sanitize_text_field($_POST['artidomo_product_cat']));
    }
    if (isset($_POST['artidomo_product_small_length']) && $_POST['artidomo_product_small_length']) {
      update_post_meta($post_id, 'artidomo_product_small_length', sanitize_text_field($_POST['artidomo_product_small_length']));
    }
    if (isset($_POST['artidomo_product_large_length']) && $_POST['artidomo_product_large_length']) {
      update_post_meta($post_id, 'artidomo_product_large_length', sanitize_text_field($_POST['artidomo_product_large_length']));
    }

    if (isset($_POST['artidomo_product']) && $_POST['artidomo_product']) {
      update_post_meta($post_id, 'artidomo_product', sanitize_text_field($_POST['artidomo_product']));
    }

    if (isset($_POST['variations']) && $_POST['variations']) {
      update_post_meta($post_id, 'artidomo_shop_variation', sanitize_text_field($_POST['variations']));
    }

    if (isset($_POST['artidomo_main_variant']) && !empty($_POST['artidomo_main_variant'])) {
      update_post_meta($post_id, 'artidomo_main_variant', sanitize_text_field($_POST['artidomo_main_variant']));
    }
    if (isset($_POST['ac_var_id'])) {
      $mapped_variations = array();
      for ($i = 0; $i < count($_POST['ac_var_id']); $i++) {
        if ($_POST['ac_var_id'][$i] && $_POST['ar_pr_id'][$i]) {
          $arti_var_name = ($_POST['ar_var_name'][$i]) ? sanitize_text_field($_POST['ar_var_name'][$i]) : "";
          $arti_var_size = ($_POST['ar_var_size'][$i]) ? sanitize_text_field($_POST['ar_var_size'][$i]) : "";
          $mapped_variations[] = array(
            'var_id' => sanitize_text_field($_POST['ac_var_id'][$i]),
            'ar_pr_id' => sanitize_text_field($_POST['ar_pr_id'][$i]),
            'ar_var_id' => (isset($_POST['ar_var_id'][$i]) && !empty($_POST['ar_var_id'][$i])) ? sanitize_text_field($_POST['ar_var_id'][$i]) : '',
            'ar_var_name' => $arti_var_name,
            'ar_var_size' => $arti_var_size
          );
        }
      }
      if ($mapped_variations) {
        update_post_meta($post_id, 'ac_product_mapped_variations', $mapped_variations);
      }
    } else
      update_post_meta($post_id, 'ac_product_mapped_variations', array());

    if (isset($_FILES['static_file']) && $_FILES['static_file']['name'] !== '') {

      require_once ABSPATH . 'wp-admin/includes/image.php';
      require_once ABSPATH . 'wp-admin/includes/file.php';
      require_once ABSPATH . 'wp-admin/includes/media.php';

      $attach_id = media_handle_upload('static_file', 0);
      update_post_meta($post_id, 'artidomo_static_file_id', sanitize_text_field($attach_id));
    }

    $class = 'updated notice';
    $message = esc_html('Product link with "ARTIDOMO" product successfully.');
  } else {
    $class = 'error notice';
    $message = esc_html('Please select product.');
  }
}
$product_cat_id = $shop_variation = $product_variation = $variation_name = $fullfill_status = $artidomo_product = $artidomo_product_cat = $artidomo_main_variant = $file_handling = $image_id = $variation_html = "";
$main_productData = $artidomo_product_small_length = $artidomo_product_large_length = $mapped_variations = array();

$productData = array();
if (isset($_GET['edit']) && $_GET['edit']) {
  $post_ID = intval(sanitize_text_field($_GET['edit']));

  $product_cat_id = get_post_meta($post_ID, 'artidomo_shop_product_cat', true);
  $shop_variation = get_post_meta($post_ID, 'artidomo_shop_variation', true);
  $product_variation = new WC_Product_Variation($shop_variation);
  $variation_name = $product_variation->get_name();
  $fullfill_status = get_post_meta($post_ID, 'artidomo_shop_status', true);

  $artidomo_product = get_post_meta($post_ID, 'artidomo_product', true);
  $artidomo_product_cat = get_post_meta($post_ID, 'artidomo_product_cat', true);
  $artidomo_product_small_length = get_post_meta($post_ID, 'artidomo_product_small_length', true);
  $artidomo_product_large_length = get_post_meta($post_ID, 'artidomo_product_large_length', true);
  $artidomo_main_variant = get_post_meta($post_ID, 'artidomo_main_variant', true);
  $file_handling = get_post_meta($post_ID, 'artidomo_file_handling_method', true);
  $image_id = get_post_meta($post_ID, 'artidomo_static_file_id', true);
  if ($artidomo_product) $main_productData = ac_get_main_product($artidomo_product);
  $mapped_variations = get_post_meta($post_ID, 'ac_product_mapped_variations', true);
  $productData = wc_get_product($post_ID);
  $result = ac_get_main_product_of_category($artidomo_product_cat, $artidomo_product_small_length, $artidomo_product_large_length);
  $html = '';
  if ($productData && $productData->is_type('variable')) {
    $args = array(
      'post_type'     => 'product_variation',
      'post_status'   => 'publish',
      'numberposts'   => -1,
      'post_parent'   => $post_ID,
    );
    $variations = get_posts($args);
    if (!empty($variations)) {
      $html .= '<option value="">' . esc_html__('- Choose Variation -', 'artidomo-print-on-demand') . '</option>';
      foreach ($variations as $variation) {

        $variation_ID = $variation->ID;

        $product_variation = new WC_Product_Variation($variation_ID);

        $variation_name = $product_variation->get_name();
        $product_name  = $product_variation->get_name();
        $product_sku   = $product_variation->get_sku();
        $product_price = $product_variation->get_price();
        if ($product_price)
          $product_price = wc_price($product_price);
        $attributes = $product_variation->get_attributes();
        $atts = "";
        if ($attributes) {
          $attributename = array();
          foreach ($attributes as $attribute) {
            if ($attribute)
              $attributename[] = $attribute;
          }
          $atts = implode("-", $attributename);
        }
        $variation_html .= '<option value="' . esc_attr($variation_ID) . '" price="' . esc_attr($product_price) . '" sku="' . esc_attr($product_sku) . '" name="' . esc_attr($product_name) . '">' . esc_html($atts) . '</option>';
      }
    }
  }
}
?>

<div class="wrap artidomo-admin-container">
  <?php
  if (!empty($message)) { ?>
    <div class="<?php echo esc_attr($class); ?>">
      <p><?php echo esc_html($message); ?></p>
    </div>
  <?php
  }
  ?>
  <div class="artidomo-img">
    <img src="<?php echo esc_url(ARTIDOMO_PRINT_URL . 'admin/img/artidomo-logo.jpg'); ?>">
  </div>

  <?php $tabs = array('settings' => esc_html('Settings'), 'products_mapping_list' => esc_html('Products Mapping list'), 'new_mapping' => esc_html('New Mapping'), 'artidomo_help' => esc_html('Help')); ?>
  <div id="icon-themes" class="icon32"><br></div>
  <h2 class="nav-tab-wrapper">
    <?php
    foreach ($tabs as $tab => $name) {
      $class = ($tab == $_GET['page']) ? ' nav-tab-active' : '';
      echo "<a class='nav-tab " . esc_attr($class) . "' href='" . esc_url('?page=' . $tab) . "'>" . esc_attr($name) . "</a>";
    } ?>
  </h2>

  <h1 class="wp-heading-inline"><?php esc_html_e('New Mapping', 'artidomo-print-on-demand'); ?></h1>
  <form method="post" novalidate="novalidate" enctype="multipart/form-data">
    <?php wp_nonce_field( 'artidomo_map_settings', 'artidomo_map_settings_nonce' ); ?>
    <div class="choose-variation-section-container">
      <div class="dont-show product-filter-loader">
        <img src="<?php echo esc_url(ARTIDOMO_PRINT_URL . 'admin/img/loader.gif'); ?>" alt="filter loader" />
      </div>
      <table class="form-table product-maping-table" role="presentation">
        <h2 class="title"><?php esc_html_e('Choose Your Shop Product', 'artidomo-print-on-demand'); ?></h2>
        <tr>
          <td class="map-product">
            <table class="form-table map-product-table" role="presentation">
              <tbody>
                <tr>
                  <th><label><?php esc_html_e('Choose Category', 'artidomo-print-on-demand'); ?></label></th>
                  <td>
                    <select name="product_cat" id="artidomo_product_cat">
                      <option value=""><?php esc_html_e('- Choose Category -', 'artidomo-print-on-demand'); ?></option>
                      <?php
                      if (!empty($product_cat)) :
                        foreach ($product_cat as $category) { ?>
                          <option value="<?php esc_attr_e($category->term_id); ?>" <?php echo ($category->term_id == $product_cat_id) ? 'selected="selected"' : ''; ?>><?php esc_html_e($category->name); ?></option>
                      <?php }
                      endif;
                      ?>
                    </select>
                    <p class="description"><?php esc_html_e('Choose category to filter products.', 'artidomo-print-on-demand'); ?></p>
                  </td>
                  <th><label><?php esc_html_e('Artidomo category', 'artidomo-print-on-demand'); ?></label></th>
                  <td>
                    <div class="artidomo-main-cat">
                      <select name="artidomo_product_cat" class="first-timer" id="artidomo_main_product_category">
                        <option value=""><?php esc_html_e('- Choose artidomo product category -', 'artidomo-print-on-demand'); ?></option>
                        <?php
                        if (is_array($cat_result->categories) && !is_wp_error($cat_result->categories)) {
                          foreach ($cat_result->categories as $arti_category) { ?>
                            <option value="<?php echo esc_attr($arti_category); ?>" <?php echo ($artidomo_product_cat == $arti_category) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($arti_category); ?></option>
                        <?php
                          }
                        }
                        ?>
                      </select>
                      <p class="description"><?php esc_html_e('Choose Artidomo product category', 'artidomo-print-on-demand'); ?></p>
                    </div>
                    <div class="artidomo-size-filter">
                      <label><?php esc_html_e('Filter by size', 'artidomo-print-on-demand'); ?></label>
                      <input type="radio" name="artiproductsize" class="artidomo-product-size" value="yes" checked> Yes
                      <input type="radio" name="artiproductsize" class="artidomo-product-size" value="no"> No
                    </div>
                    <div class="artidomo-main-length-div">
                      <div class="artidomo-small-product">
                        <select name="artidomo_product_small_length" id="artidomo_main_product_small_length">
                          <option value=""><?php esc_html_e('- Choose small length -', 'artidomo-print-on-demand'); ?></option>
                          <?php
                          if (is_array($cat_result->smalllengths) && !is_wp_error($cat_result->smalllengths)) {
                            foreach ($cat_result->smalllengths as $arti_small_length) { ?>
                              <option value="<?php echo esc_attr($arti_small_length); ?>" <?php echo ($artidomo_product_small_length == $arti_small_length) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($arti_small_length); ?></option>
                          <?php
                            }
                          }
                          ?>
                        </select>
                        <p class="description"><?php esc_html_e('Choose small length', 'artidomo-print-on-demand'); ?></p>
                      </div>
                      <div class="artidomo-large-product">
                        <select name="artidomo_product_large_length" id="artidomo_main_product_large_length">
                          <option value=""><?php esc_html_e('- Choose large length -', 'artidomo-print-on-demand'); ?></option>
                          <?php
                          if (is_array($cat_result->largelengths) && !is_wp_error($cat_result->largelengths)) {
                            foreach ($cat_result->largelengths as $arti_lagelength) { ?>
                              <option value="<?php echo esc_attr($arti_lagelength); ?>" <?php echo ($artidomo_product_large_length == $arti_lagelength) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($arti_lagelength); ?></option>
                          <?php
                            }
                          }
                          ?>
                        </select>
                        <p class="description"><?php esc_html_e('Choose large length', 'artidomo-print-on-demand'); ?></p>
                      </div>
                    </div>
                  </td>
                </tr>

                <tr>
                  <th><label><?php esc_html_e('Map Products', 'artidomo-print-on-demand'); ?></label></th>
                  <td>
                    <select name="product" id="artidomo_product">
                      <option value=""><?php esc_html_e('- Choose your product -', 'artidomo-print-on-demand'); ?></option>
                      <?php if ($post_ID) { ?>
                        <option value="<?php echo esc_attr($post_ID); ?>" selected="selected"><?php esc_html_e('#' . esc_html($post_ID) . ' ' . get_the_title($post_ID)); ?></option>
                      <?php } ?>
                    </select>
                    <p class="description"><?php esc_html_e('Choose your product which you want to link.', 'artidomo-print-on-demand'); ?></p>
                  </td>
                  <th><label><?php esc_html_e('Artidomo product', 'artidomo-print-on-demand'); ?></label></th>
                  <td>
                    <select name="artidomo_product" id="artidomo_main_product">
                      <option value=""><?php esc_html_e('- Choose Artidomo Product -', 'artidomo-print-on-demand'); ?></option>
                      <?php
                      if ($_GET['edit']) {
                        if (is_array($result) && !is_wp_error($result)) {
                          foreach ($result as $product) {
                            $price = (empty($product['option_1'])) ? $product['price_netto'] : "";
                      ?>
                            <option value="<?php echo esc_attr($product['product_id']); ?>" <?php echo ($artidomo_product == $product['id']) ? 'selected="selected"' : ''; ?> price="<?php echo esc_attr($price); ?>" size="<?php echo esc_attr($product['small_length']) ?>x<?php echo esc_attr($product['large_length']); ?>"><?php echo esc_attr($product['description']); ?></option>
                      <?php
                          }
                        }
                      }
                      ?>
                    </select>
                    <p class="description"><?php esc_html_e('Choose Artidomo product to link.', 'artidomo-print-on-demand'); ?></p>
                    <p class="artidomo-no-var-product-price"></p>
                  </td>
                </tr>
                <tr>
                  <th><label><?php esc_html_e('Choose Variant', 'artidomo-print-on-demand'); ?></label></th>
                  <td>
                    <select name="variations" id="artidomo_variations" <?php if (empty($variation_html)) : ?> disabled <?php endif; ?>>
                      <option value=""><?php esc_html_e('- Choose Our Variant -', 'artidomo-print-on-demand'); ?></option>
                      <?php
                      echo wp_kses($variation_html, array(
                        'option' => array(
                          'value' => array(),
                          'price' => array(),
                          'sku' => array(),
                          'name' => array(),
                        )
                      )); ?>
                    </select>
                    <p class="description"><?php esc_html_e('Choose your product variant which you want to link.', 'artidomo-print-on-demand'); ?></p>
                  </td>
                  <th><label><?php esc_html_e('Artidomo variant', 'artidomo-print-on-demand'); ?></label></th>
                  <td>
                    <div class="ac-variations">
                      <select name="artidomo_main_variant" id="artidomo_main_variant">
                        <option value=""><?php esc_html_e('- Choose Artidomo Variant -', 'artidomo-print-on-demand'); ?></option>
                        <?php
                        $variants = ac_get_main_product_variants($main_productData);
                        if ($variants) {
                          foreach ($variants as $variant) {
                            $selected = '';
                            if ($variant == $artidomo_main_variant) {
                              $selected = "selected";
                            }
                            echo '<option value="' . esc_attr($variant) . '" ' . esc_attr($selected) . '>' . esc_html($variant) . '</option>';
                          }
                        } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Choose Artidomo product variant to link with our product variation.', 'artidomo-print-on-demand'); ?></p>
                      <p class="artidomo-product-price"></p>
                    </div>
                    <button type="button" id="ac_add_variation" class="button btn-primary" style="display: none;"><?php esc_html_e('Add', 'artidomo-print-on-demand'); ?></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
          <td valign="top" class="shop-product">
            <?php
            if (isset($_GET['edit']) && $_GET['edit']) {
              $product = wc_get_product(intval(esc_attr($_GET['edit'])));
              $product_id    = $product->get_id();
              $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'single-post-thumbnail');
              $product_name  = $product->get_name();
              $product_sku   = $product->get_sku();
              $product_price = $product->get_price();

              echo $product_html = '<img src="' . esc_url($image[0]) . '" widtth="150" height="150">
              <h2>' . esc_html($product_name) . ' - ' . wc_price($product_price) . '</h2>
              <h4>SKU - ' . esc_html($product_sku) . '</h4>
              <a href="' . esc_url($product->get_permalink()) . '" class="button btn-primary">' . esc_html('View Product') . '</a>';
            }
            ?>
          </td>
        </tr>
      </table>
    </div>
    <div class="ac-added-variation-sec" <?php if (empty($mapped_variations)) : ?>style="display: none;" <?php endif; ?>>
      <h3><?php esc_html_e('Product Mapped Variations', 'artidomo-print-on-demand'); ?></h3>
      <ul class="ac-added-variation">
        <?php
        if ($mapped_variations) {
          foreach ($mapped_variations as $mapped_variation) {
            $map_product = wc_get_product($mapped_variation['var_id']);
            $main_product = wc_get_product($map_product->get_parent_id());
            if ($main_product) {
              $product_variation = new WC_Product_Variation($mapped_variation['var_id']);
              $mapp_attributes = $product_variation->get_attributes();
              $map_atts = "";
              if ($mapp_attributes) {
                $map_attributename = array();
                foreach ($mapp_attributes as $attribute) {
                  if ($attribute)
                    $map_attributename[] = esc_html($attribute);
                }
                $map_atts = implode("-", $map_attributename);
              }
            }
            $our_variant_name = '#' . $mapped_variation['var_id'] . ' ' . get_the_title($mapped_variation['var_id']);
            $arti_variant = $mapped_variation['ar_var_id'];
            $arti_size = $mapped_variation['ar_var_size'];
            $arti_variant_name = ($mapped_variation['ar_var_name']) ?: $arti_variant;
            $arti_pr_id = isset($mapped_variation['ar_pr_id']) ? esc_html($mapped_variation['ar_pr_id']) : 0;
            $arti_var_id = isset($mapped_variation['var_id']) ? esc_attr($mapped_variation['var_id']) : 0;
            echo '<li><span class="ac-p-var"><label>Child Variation</label>' . esc_html($our_variant_name) . ' ' . /* $map_atts . */ '</span><span class="ac-a-var"><label>Main Variation</label>' . esc_html($arti_variant_name) . '-' . esc_html($arti_size) . '</span><span class="var-remove">-</span><input type="hidden" class="ac_var_id" name="ac_var_id[]" value="' . $arti_var_id . '"><input  class="ar_var_id" type="hidden" name="ar_var_id[]" value="' . esc_html($arti_variant) . '"><input  class="ar_pr_id" type="hidden" name="ar_pr_id[]" value="' . $arti_pr_id . '"><input class="ar_var_name" type="hidden" name="ar_var_name[]" value="' . esc_html($arti_variant_name) . '" ><input class="ar_var_name" type="hidden" name="ar_var_size[]" value="' . esc_html($arti_size) . '" ></li>';
          }
        }
        ?>
      </ul>
    </div>
    <table class="form-table" role="presentation">
      <h2 class="title"><?php esc_html_e('Choose Order Process', 'artidomo-print-on-demand'); ?></h2>
      <tr>
        <td>
          <table class="form-table" role="presentation">
            <tbody>
              <tr>
                <th><label><?php esc_html_e('File Handling Method', 'artidomo-print-on-demand'); ?></label></th>
                <td>
                  <div class="ac-file-handling-wrap">
                    <select name="file_handling" id="artidomo_file_handling">
                      <option value="static" <?php echo ($file_handling == 'static') ? 'selected="selcetd"' : ''; ?>>
                        <?php esc_html_e('Upload static method', 'artidomo-print-on-demand'); ?>
                      </option>
                      <option value="manually" <?php echo ($file_handling == 'manually') ? 'selected="selcetd"' : ''; ?>>
                        <?php esc_html_e('Upload manually after order', 'artidomo-print-on-demand'); ?>
                      </option>
                      <option value="enduser" <?php echo ($file_handling == 'enduser') ? 'selected="selcetd"' : ''; ?>>
                        <?php esc_html_e('Upload by end-user', 'artidomo-print-on-demand'); ?>
                      </option>
                    </select>
                    <?php $css = ($file_handling == 'static' || empty($file_handling)) ? 'style=display:block' : 'style=display:none'; ?>
                    <div class="ac-static-file-sec" <?php echo esc_attr($css); ?>>
                      <input type="file" name="static_file" id="static_file_method" <?php echo esc_attr($css); ?>>
                      <?php if ($image_id) { ?>
                        <span class="ac-file-info" <?php echo esc_attr($css); ?>><?php echo esc_html(basename(wp_get_attachment_url(esc_attr($image_id)))); ?></span>
                      <?php } ?>
                    </div>
                  </div>
                  <p class="description"><?php esc_html_e('Choose file handling method.', 'artidomo-print-on-demand'); ?></p>
                </td>
              </tr>
              <tr>
                <th><label><?php esc_html_e('Fullfill at status', 'artidomo-print-on-demand'); ?></label></th>
                <td>
                  <select name="status" id="artidomo_status">
                    <option value="Open" <?php echo ($fullfill_status == 'Open') ? 'selected="selcetd"' : ''; ?>><?php esc_html_e('Open', 'artidomo-print-on-demand'); ?></option>
                    <option value="Paid" <?php echo ($fullfill_status == 'Paid') ? 'selected="selcetd"' : ''; ?>><?php esc_html_e('Paid', 'artidomo-print-on-demand'); ?></option>
                    <option value="Manually" <?php echo ($fullfill_status == 'Manually') ? 'selected="selcetd"' : ''; ?>><?php esc_html_e('Manually', 'artidomo-print-on-demand'); ?></option>
                  </select>
                  <p class="description"><?php esc_html_e('Choose product ordder fullfill status.', 'artidomo-print-on-demand'); ?></p>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
        <td valign="top" class="artidomo-product">
          <?php
          if (isset($_GET['edit']) && !empty($products)) {
            $product_id = get_post_meta($post_ID, 'artidomo_product', true);
            foreach ($products as $product) {
              if ($product_id == $product->id) {
                echo $product_html = '<img src="' . esc_url($product->images[0]->src) . '" width="150" height="150">
                                                  <h2>' . esc_html($product->name) . ' - ' . esc_html($product->price_html) . '</h2>
                                                  <h4>SKU - ' . esc_html($product->sku) . '</h4>
                                                  <a href="' . esc_url($product->permalink) . '" class="button btn-primary">' . esc_html('View Product') . '</a>';
              }
            }
          }
          ?>
        </td>
      </tr>
    </table>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Save Link', 'artidomo-print-on-demand'); ?>"></p>
  </form>
</div>