<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$args = array(
  'post_type'       => 'product',
  'post_status'     => 'publish',
  'posts_per_page'  => -1,
  'meta_query' => array(
    array(
      'key' => 'artidomo_shop_product',
      'compare' => 'EXISTS'
    ),
  ),
);

$the_query = new WP_Query($args);

$url = ARTIDOMO_API_SERVER_URL . "/wp-json/artidomo-products/v1/list";
$apidata = get_option('_artidomo_api_details');
$api_key = isset($apidata['artidomo_api_key']) ? $apidata['artidomo_api_key'] : '';
$secret_key = isset($apidata['artidomo_secret_key']) ? $apidata['artidomo_secret_key'] : '';
$response = wp_remote_get(esc_url($url), array(
  'timeout' => 120,
  'headers'     => [
    'Content-Type' => 'application/json',
    'Authorization' => 'Bearer ' . base64_encode(esc_attr($secret_key) . ':' . esc_attr($api_key)),
  ],
));
$responseBody = wp_remote_retrieve_body($response);
$result = json_decode($responseBody);
?>

<div class="wrap artidomo-admin-container">
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


  <h1 class="wp-heading-inline" style="margin-bottom:10px;"><?php esc_html_e('Products Mapping list', 'artidomo-print-on-demand'); ?></h1>
  <table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
      <tr>
        <th id="shop_ID" class="manage-column column-cb check-column"><?php esc_html_e('ID', 'artidomo-print-on-demand'); ?></th>
        <th scope="col" id="shop_products" class="manage-column column-primary"><?php esc_html_e('Shop products', 'artidomo-print-on-demand'); ?></th>
        <th scope="col" id="artidomo_products" class="manage-column"><?php esc_html_e('Artidomo products', 'artidomo-print-on-demand'); ?></th>
        <th scope="col" id="actions" class="manage-column column-categories"><?php esc_html_e('Actions', 'artidomo-print-on-demand'); ?></th>
      </tr>
    </thead>

    <tbody id="the-list">
      <?php if ($the_query->have_posts()) {
        $i = 0;
        while ($the_query->have_posts()) {
          $the_query->the_post();
          $i++;
          $product_cat = get_post_meta(get_the_ID(), 'artidomo_shop_product_cat', true);
          $term = get_term_by('id', esc_attr($product_cat), 'product_cat');
          $shop_variation = get_post_meta(get_the_ID(), 'artidomo_shop_variation', true);

          $product_variation = new WC_Product_Variation($shop_variation);
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
          $variation_name = ($atts) ?: "";
          $fullfill_status = get_post_meta(get_the_ID(), 'artidomo_shop_status', true);

          $artidomo_product = get_post_meta(get_the_ID(), 'artidomo_product', true);
          $artidomo_product_cat = get_post_meta(get_the_ID(), 'artidomo_product_cat', true);
          $file_handling = get_post_meta(get_the_ID(), 'artidomo_file_handling_method', true);
          if ($file_handling == 'static') {
            $file_handling = esc_html__('Upload static method', 'artidomo-print-on-demand');
          } else if ($file_handling == 'manually') {
            $file_handling = esc_html__('Upload manually after order', 'artidomo-print-on-demand');
          } else if ($file_handling == 'enduser') {
            $file_handling = esc_html__('Upload by end-user', 'artidomo-print-on-demand');
          }
      ?>
          <tr id="post-<?php echo esc_attr(get_the_ID()); ?>" class="iedit author-self level-0 post-<?php echo esc_attr(get_the_ID()); ?> type-post status-publish format-standard hentry entry">
            <th><?php echo esc_html($i); ?></th>
            <td class="title column-title column-primary page-title" data-colname="shop_products">
              <p><strong><a class="row-title" href="<?php echo esc_url(the_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a></strong></p>
              <p><label><strong><?php esc_html_e('Product category : ', 'artidomo-print-on-demand'); ?></strong><?php echo  esc_html($term->name); ?></label></p>
              <?php if ($variation_name) { ?>
                <p><label><strong><?php esc_html_e('Product variation : ', 'artidomo-print-on-demand'); ?></strong><?php echo esc_html($variation_name); ?></label></p>
              <?php } ?>
              <p><label><strong><?php esc_html_e('Fullfill status at : ', 'artidomo-print-on-demand'); ?></strong><?php echo esc_html($fullfill_status); ?></label></p>
            </td>
            <td data-colname="artidomo_products">
              <?php
              if (!empty($artidomo_product_cat)) { ?>
                <p><strong><?php esc_html_e('Artidomo product category', 'artidomo-print-on-demand'); ?> : </strong><?php $artidomo_product_cat; ?></p>
                <?php }
              if (is_array($result) && !is_wp_error($result)) {
                foreach ($result as $product) {
                  if ($product->product_id == $artidomo_product) { ?>
                    <p><strong><?php esc_html_e('Artidomo product :', 'artidomo-print-on-demand'); ?> </strong><?php echo esc_html($product->description); ?></p>
              <?php }
                }
              }
              ?>
              <p><label><strong><?php esc_html_e('File handling Method : ', 'artidomo-print-on-demand'); ?></strong><?php echo esc_html($file_handling); ?></label></p>

            </td>
            <td class="author column-author" data-colname="actions">
              <a href="<?php echo esc_url('?page=new_mapping&edit=' . esc_attr(get_the_ID())); ?>" title="<?php echo esc_attr('Edit mapping of this product'); ?>"><span class="dashicons dashicons-edit"></span></a>
              <a href="<?php echo esc_url('?page=products_mapping_list&delete=' . esc_attr(get_the_ID())); ?>" title="<?php echo esc_attr('All mapping will be removed of this product.'); ?>"><span class="dashicons dashicons-trash"></span></a>
            </td>
          </tr>
        <?php  }
      } else { ?>
        <tr class="iedit author-self level-0 type-post status-publish format-standard hentry entry">
          <th></th>
          <td class="title column-title column-primary page-title" data-colname="shop_products"><?php esc_html_e('No Artidomo product found!', 'artidomo-print-on-demand'); ?></td>
          <td data-colname="artidomo_products"></td>
          <td class="author column-author" data-colname="actions"></td>
        </tr>
      <?php }

      wp_reset_postdata(); ?>
    </tbody>

    <tfoot>
      <tr>
        <th class="manage-column column-cb check-column"><?php esc_html_e('ID', 'artidomo-print-on-demand'); ?></th>
        <th scope="col" class="manage-column column-primary"><?php esc_html_e('Shop products', 'artidomo-print-on-demand'); ?></th>
        <th scope="col" class="manage-column column-author"><?php esc_html_e('Artidomo products', 'artidomo-print-on-demand'); ?></th>
        <th scope="col" class="manage-column column-categories"><?php esc_html_e('Actions', 'artidomo-print-on-demand'); ?></th>
      </tr>
    </tfoot>

  </table>
</div>