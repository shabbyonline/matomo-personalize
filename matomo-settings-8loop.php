<?php 
  /**
   *  Plugin Name: Matomo Settings (8loop)
   *  Plugin URI: https://8thloop.com/
   *  Description: This plugin contains settings related to matomo analytics
   *  Version: 1.0.0
   *  Author: Waqar Haider
   *  Author URI: https://8thloop.com/
   *  Text Domain: matomo-settings-8loop
   */
  
  /**
   * Security Measure
   */
  if(!defined('ABSPATH')) exit;
  
  define('TEXT_DOMAIN', 'matomo-settings-8loop');
  define('PLUGIN_PATH', plugin_dir_path(__FILE__));

  // AWS SDK
  require 'vendor/autoload.php';

  /*
  * @author: Ammar Ali <ammar.ali@va8ivedigital.com>
  * Enqueue Styles
  */
  add_action( 'wp_enqueue_scripts', 'load_styles' );
  function load_styles() {
      wp_enqueue_style( 'matomo-settings-8loop', plugin_dir_url( __FILE__ ).'matomo-settings-8loop-styles.css' );
      // wp_enqueue_script( 'matomo-settings-8loop', plugin_dir_url( __FILE__ ).'matomo-settings-8loop-scripts.js', '', true );
  }

  /**
  * @author: Ammar Ali <ammar.ali@va8ivedigital.com>
  * Description: Add Menu in WP Admin Panel Sidebar
  */
  function matomo_settings_8loop_init(){
    add_menu_page(
      __( 'Matomo (8loop)', 'matomo-settings-8loop' ),
      'Matomo (8loop)',
      'manage_options',
      'matomo-settings-8loop',
      'matomo_settings_8loop_settings_page',
      plugin_dir_url( __FILE__ ).'matomo_logo.png'
    );    
  }
  add_action('admin_menu', 'matomo_settings_8loop_init');

  /**
    * @author: Ammar Ali <ammar.ali@va8ivedigital.com>
    * Description: Matomo Settings Page Templates
    */
  function matomo_settings_8loop_settings_page(){
    require_once(dirname(__FILE__) . '/settings/matomo-settings.php');
    // if( get_option('auth-token-matomo') != "" || !empty(get_option('auth-token-matomo')) ){
    //     require_once(plugin_dir_path( __FILE__ ).'settings/matomo-api-request.php');
    // }
  }

  //Overriding order-details.php template
  add_filter( 'woocommerce_locate_template', 'woo_adon_plugin_template', 1, 3 );
function woo_adon_plugin_template( $template, $template_name, $template_path ) {
    global $woocommerce;
    $_template = $template;
    if ( ! $template_path ) 
       $template_path = $woocommerce->template_url;

    $plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/template/woocommerce/';

   // Look within passed path within the theme - this is priority
   $template = locate_template(
   array(
     $template_path . $template_name,
     $template_name
   )
  );

  if( ! $template && file_exists( $plugin_path . $template_name ) )
   $template = $plugin_path . $template_name;

  if ( ! $template )
   $template = $_template;

  return $template;
}

  add_action( 'after_setup_theme', function(){
    require plugin_dir_path( __FILE__ ).'settings/matomo-notices.php';
  });

// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
//Remove the existing one to show the custom title with product id
// function add_custom_text_after_product_title(){
//     global $product;
//     $prod_id = $product->get_id();
//     // the_title( '<h3 class="product_title entry-title">', '<span class=product_id>'.$prod_id.'.</span><span class=user_id>'.get_current_user_id().'</span></h3>' );
//     the_title( '<h1 class="title_for_matomo">', '<span class=product_id>'.$prod_id.'</span></h1>' );
// }
// add_action( 'woocommerce_single_product_summary', 'add_custom_text_after_product_title', 5);
 
function get_loggedinuser_id() { ?>
    <script type="text/javascript">
        var user_id = <?php echo get_current_user_id(); ?>;
        var current_user_id = 0;
        
        function setCookie(cname, cvalue, exdays) {
          const d = new Date();
          d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
          let expires = "expires="+d.toUTCString();
          document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        function getCookie(cname) {
          let name = cname + "=";
          let ca = document.cookie.split(';');
          for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
              c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
              return c.substring(name.length, c.length);
            }
          }
          return "";
        }

        function uniqueid() {
            var ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            var ID_LENGTH = 8;
            var rtn = '';
            for (var i = 0; i < ID_LENGTH; i++) {
              rtn += ALPHABET.charAt(Math.floor(Math.random() * ALPHABET.length));
            }
            return rtn;
        }

        function generateCookie() {
          current_user_id = getCookie("guest");
          if (current_user_id != "") {
            return current_user_id;
          } else {
              setCookie("guest", ("guest_"+uniqueid()), 1);
          }
        }
        
        if( user_id == 0 ){
          current_user_id = generateCookie();
        } else {
          setCookie("guest", "", null , null , null, 14);
          current_user_id = <?php echo get_current_user_id(); ?>;
        }
    </script>
    <?php
     if ( is_product() ){
      global $product;
      if($product != NULL){
        $prod_id = $product->get_id();
    ?>
    <meta name="mtm-product-id" value="<?php echo $prod_id; ?>">
<?php
    }
  }
}
add_action('wp_head', 'get_loggedinuser_id');

if( get_option('tracking-code-matomo') != "" ){
    function setMatomoCode(){
      echo str_replace("\\","",get_option('tracking-code-matomo'));
    }
    add_action('wp_head', 'setMatomoCode');
}
