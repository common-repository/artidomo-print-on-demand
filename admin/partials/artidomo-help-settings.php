<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
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
<div class="wrap artidomo-admin-container">
  <h1 class="wp-heading-inline"><?php esc_attr_e('Help', 'artidomo-print-on-demand'); ?></h1>
  <p>
    <?php
    echo sprintf(__('Wenn Sie Hilfe benötigen schauen Sie bitte auf die Anleitungen auf dem Nextcloud-Server sowie die Videos auf <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>. Sie können auch eine Email an <a href="%s">%s</a> senden.'), esc_url('https://www.print-on-demand.academy'), esc_html('https://www.print-on-demand.academy'), esc_url('mailto:support@artidomo.eu'), esc_html('support@artidomo.eu'));
    ?>
  </p>
  <p>
    <?php
    echo sprintf(__('If you need help please have a look at our manual on nextcloud server as on our videos at <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>. You can also send an email to <a href="%s">%s</a>-'), esc_url('https://www.print-on-demand.academy'), esc_html('https://www.print-on-demand.academy'), esc_url('mailto:support@artidomo.eu'), esc_html('support@artidomo.eu'));
    ?>
  </p>
  <address>
    <p>
      <?php
      echo sprintf(__('<strong>Impressum:</strong></br>
      Marina Scheubly</br>
      artidomo</br>
      Wittener Str. 75</br>
      44789 Bochum</br>
      Deutschland'));
      ?>
    </p>
    <p>
      <?php
      echo sprintf(__('<strong>Tel.</strong>: <a href="%s">%s</a></br>
      <strong>Fax</strong>: <a href="%s">%s</a></br>
      <strong>E-Mail</strong>: <a href="%s">%s</a></br>'), esc_url('tel:+023452009829'), esc_html('0234-52009829'), esc_url('tel:+02343888928'), esc_html('0234 – 3 888 928'), esc_url('mailto:info@artidomo.de'), esc_html('info@artidomo.de'));
      ?>
    </p>
  </address>
  <p>
    <?php esc_html_e('Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz: DE304587715'); ?>
  </p>
  <p>
    <?php
    echo sprintf(__('Plattform der EU-Kommission zur Online-Streitbeilegung: <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>'), esc_url('https://ec.europa.eu/odr'), esc_html('https://ec.europa.eu/odr'));
    ?>
  </p>
  <p>
    <?php esc_html_e('Wir sind zur Teilnahme an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle weder verpflichtet noch bereit.'); ?>
  </p>
  <p>
    <?php
    echo sprintf(__('Verantwortlicher i.S.d. § 18 Abs. 2 MStV:</br>
      Marina Scheubly, Wittener Str. 75, 44789 Bochum'));
    ?>
  </p>
</div>