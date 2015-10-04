<?php
/*
Plugin Name: QR - Login
Version: 0.1.6
Plugin URI: https://github.com/jellmaier/qrlogin
Description: Login via QR - Code
Author: Jakob Ellmaier
Author URI: http://medienbausatz.at
GitHub Plugin URI: https://github.com/jellmaier/qrlogin
GitHub Branch:     master
Licence: GP2
*/



function script_enqueue($hook_suffix) {
  wp_enqueue_script('qrl_edit_script', plugin_dir_url(__FILE__) . 'qr-login.js', array('jquery') );
 }
 add_action('admin_enqueue_scripts', 'script_enqueue');


function qrl_user_page(){
  $page_title = __('QR - Login', 'qrl');
  $menu_title = __('QR - Login', 'qrl');
  $capability = 'edit_posts';
  $menu_slug = 'qrl_user_login_options';
  $function = 'qrl_user_login_render';

  add_users_page( $page_title, $menu_title, $capability, $menu_slug, $function);
}
add_action('admin_menu', 'qrl_user_page');

function qrl_user_login_render() {
  ?>
  <div class="wrap">
    <h2>QR - Login Einstellungen<h2>
    <?php settings_errors(); ?>
    <form method="post" action="">
      <p><label>Login Token:   </label><input type="text" disabled value="<?= get_user_meta(get_current_user_id(), 'qrl-token', true) ?>"/>
      <input type="submit" id="reset-token" value="Renew Token"></p>
      <img src="https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=<?= bloginfo('url'); ?>/?token=<?= get_user_meta(get_current_user_id(), 'qrl-token', true)?>">
    </form>      
  </div>
  <?php
  
}


function reset_token() {
  $token = wp_generate_password(16, false);
  update_user_meta( get_current_user_id(), 'qrl-token', $token );
  die();
}
add_action( 'wp_ajax_reset_token', 'reset_token' );


//--------------------------------------------------------
// Login via url
//--------------------------------------------------------
function qrl_login() {
  if(isset( $_GET['token'] ) ){
    $token = sanitize_key( $_GET['token'] );
    $args = array(
      'meta_key'     => 'qrl-token',
      'meta_value'   => $token,
    );
    $user_query = new WP_User_Query($args);
    // Get the results
    $users = $user_query->get_results();
    // Check for results
    if (count($users) == 1 && !empty($token)) {
      foreach ($users as $user)
      {
        $user_id = $user->ID;
        // from codex : https://codex.wordpress.org/Function_Reference/wp_set_current_user
        $user = get_user_by( 'id', $user_id ); 
        if( $user ) {
            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login );
            //wp_redirect( bloginfo('url') . '/wordpress/get-ticket/' ); exit;
        }
      }
    } 
  } 
}
add_action( 'init', 'qrl_login' );

?>