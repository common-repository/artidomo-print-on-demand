<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if (isset($_POST['submit']) && isset($_POST['artidomo_api_settings_nonce']) && current_user_can( 'edit_posts' ) && wp_verify_nonce( $_POST['artidomo_api_settings_nonce'], 'artidomo_api_settings' )) {
   $data = $_POST;
   unset($data['submit']);
   $data = array_map("sanitize_text_field", $data);
   update_option('_artidomo_api_details', $data);
   $api_key = isset($_POST['artidomo_api_key']) ? sanitize_text_field($_POST['artidomo_api_key']) : '';
   $secret_key = isset($_POST['artidomo_secret_key']) ? sanitize_text_field($_POST['artidomo_secret_key']) : '';
   $apidata = array('api_key' => esc_html($api_key), 'secret_key' => esc_html($secret_key));
   $postdata = json_encode($apidata);
   $url = ARTIDOMO_API_SERVER_URL . "/wp-json/artidomo/verify-user/";
   $response = wp_remote_post(esc_url($url), array(
      'timeout' => 120,
      'sslverify' => false,
      'body'    => $postdata,
      'headers' => array(
         'Content-Type' => 'application/json',
         'Authorization' => 'Bearer ' . base64_encode(esc_attr($secret_key) . ':' . esc_attr($api_key)),
      ),
   ));
   $class = '';
   $message = '';
   if (!is_wp_error($response)) {
      $response = json_decode(wp_remote_retrieve_body($response));
      if (isset($response->data->access) && $response->data->access == 1) {
         update_user_meta(get_current_user_id(), 'artidomo-connected-to-parent', true);
         update_option('artidomo-connected-to-parent', true);
      } else {
         update_option('artidomo-connected-to-parent', false);
      }
   }
}
$connection_status = get_option('artidomo-connected-to-parent');

$apidata = get_option('_artidomo_api_details');
$artiApikey = isset($apidata['artidomo_api_key']) ? $apidata['artidomo_api_key'] : '';
$artiSecretkey = isset($apidata['artidomo_secret_key']) ? $apidata['artidomo_secret_key'] : '';
$post_order = isset($apidata['post_order']) ? $apidata['post_order'] : '';
$order_status = isset($apidata['order_status']) ? $apidata['order_status'] : '';

$current_user = wp_get_current_user();
$email = $current_user->user_email;
$firstname = $current_user->user_firstname;
$lastname = $current_user->user_lastname;


?>

<style>

</style>
<div class="wrap artidomo-admin-container">
   <div class="artidomo-img">
      <img class="asdasd" src="<?php echo esc_url(ARTIDOMO_PRINT_URL . 'admin/img/artidomo-logo.jpg');  ?>">
   </div>
   <?php $tabs = array('settings' => esc_attr('Settings'), 'products_mapping_list' => esc_attr('Products Mapping list'), 'new_mapping' => esc_attr('New Mapping'), 'artidomo_help' => esc_attr('Help')); ?>
   <div id="icon-themes" class="icon32"><br></div>
   <h2 class="nav-tab-wrapper">
      <?php
      foreach ($tabs as $tab => $name) {
         $class = ($tab == $_GET['page']) ? ' nav-tab-active' : '';
         echo "<a class='nav-tab " . esc_attr($class) . "' href='" . esc_url('?page=' . $tab) . "'>" . esc_attr($name) . "</a>";
      } ?>
   </h2>


   <h1 class="wp-heading-inline"><?php esc_attr_e('artidomo print settings', 'artidomo-print-on-demand'); ?></h1>
   <form method="post" novalidate="novalidate">
      <?php wp_nonce_field( 'artidomo_api_settings', 'artidomo_api_settings_nonce' ); ?>
      <table class="form-table" role="presentation">
         <h2 class="title"><?php esc_attr_e('Artidomo API Key', 'artidomo-print-on-demand'); ?></h2>
         <tbody>

            <tr>
               <th><label><?php esc_attr_e('Status', 'artidomo-print-on-demand'); ?></label></th>
               <td>
                  <?php
                  if ($connection_status)
                     echo '<p class="conncted success">' . esc_html("Connected") . '</p>';
                  else
                     echo '<p class="not-connected error">' . esc_html("Not Connected") . '</p>';
                  ?>
               </td>
            </tr>

            <tr>
               <th><label><?php esc_html_e('Secret key', 'artidomo-print-on-demand'); ?></label></th>
               <td><input type="text" class="regular-text" id="artidomo_secret_key" name="artidomo_secret_key" value="<?php esc_attr_e($artiSecretkey); ?>"></td>
            </tr>
            <tr>
               <th><label><?php esc_html_e('API key', 'artidomo-print-on-demand'); ?></label></th>
               <td><input type="text" name="artidomo_api_key" class="regular-text" id="artidomo_api_key" value="<?php esc_attr_e($artiApikey); ?>"></td>
            </tr>
            <tr>
               <td class="text-info" colspan="2">
                  <p><?php echo sprintf('<strong>Note</strong> : If you don\'t have API keys then please request for API key there. <a href="%s" target="_blank" class="request-api-btn">Click Here</a>', add_query_arg(array(
                        'firstname' => esc_attr($firstname),
                        'lastname' => esc_attr($lastname),
                        'email' => esc_attr($email),
                     ), esc_url(ARTIDOMO_API_SERVER_URL . "/api-registration/"))); ?></p>
               </td>
            </tr>

         </tbody>
      </table>
      <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr("Save Changes"); ?>"></p>
   </form>

</div>