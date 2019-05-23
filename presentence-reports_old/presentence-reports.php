<?php
/*
  Plugin Name: Presentence Reports
  Plugin URI: https://childressagency.com
  Author: The Childress Agency
  Version: 1.0
*/

//todo: register pages on activation (register_activation_hook)
//  https://wordpress.stackexchange.com/questions/184014/auto-create-only-1-wp-page-in-activate-custom-plugin
//todo: register acf fields on plugin activation
//  -but how to get group field keys then?


if(!defined('ABSPATH')){
  exit; //exit if accessed directly 
}

function psr_scripts(){
  wp_register_script(
    'psr_script',
    plugin_dir_url(__FILE__) . 'js/psr-scripts.js',
    array('jquery'),
    '',
    true
  );
  wp_register_script(
    'psr_tablefilter',
    plugin_dir_url(__FILE__) . 'js/tablefilter.js',
    array('jquery'),
    '',
    true
  );
  wp_enqueue_script('psr_tablefilter');
  wp_enqueue_script('psr_script');
}
add_action('wp_enqueue_scripts', 'psr_scripts', 100);

function psr_worksheet_style(){
  wp_register_style('psr-worksheet-style', plugin_dir_url(__FILE__) . 'css/psr-style.css');

  wp_enqueue_style('psr-worksheet-style');
}
add_action('wp_enqueue_scripts', 'psr_worksheet_style');

function psr_create_post_type(){
  $defendant_labels = array(
    'name' => 'Defendants',
    'singular_name' => 'Defendant',
    'menu_name' => 'Defendants',
    'add_new_item' => 'Add New Defendant',
    'search_items' => 'Search Defendants'
  );
  $defendant_args = array(
    'labels' => $defendant_labels,
    'public' => true,
    'menu_position' => 5,
    'supports' => array('title', 'author', 'revisions')
  );
  register_post_type('defendants', $defendant_args);
}
add_action('init', 'psr_create_post_type');

function psr_worksheet_shortcode(){
  ob_start();
  psr_get_template('psr-worksheet.php');
  return ob_get_clean();
}
add_shortcode('psr_worksheet', 'psr_worksheet_shortcode');

function psr_dashboard_shortcode(){
  ob_start();
  psr_get_template('psr-dashboard.php');
  return ob_get_clean();
}
add_shortcode('psr_dashboard', 'psr_dashboard_shortcode');

function psr_view_worksheet_shortcode(){
  ob_start();
  psr_get_template('psr-view-worksheet.php');
  return ob_get_clean();
}
add_shortcode('view_psr_worksheet', 'psr_view_worksheet_shortcode');

function psr_get_template($template_name){
  include $template_name;
}

function worksheet_belongs_to_user($worksheet_id, $user_id){
  $worksheet_author = get_post_field('post_author', $worksheet_id);
  if($worksheet_author == $user_id){
    return true;
  }
  else{
    return false;
  }
}

function psr_create_title($post_id){
  $personal_info = get_field('personal_info', $post_id);
  var_dump($personal_info);
  $first_name = $personal_info['first_name'];
  $last_name = $personal_info['last_name'];
  $new_post = array(
    'ID' => $post_id,
    'post_type' => 'defendants',
    'post_title' => $first_name . ' ' . $last_name
  );
  wp_update_post($new_post);
  return $post_id;
}
add_action('acf/save_post', 'psr_create_title', 20);

function psr_add_validate_setting($field){
  acf_render_field_setting($field, array(
    'label' => __('Required for Validation?'),
    'instructions' => '',
    'name' => 'validation_required',
    'type' => 'true_false',
    'ui' => 1
  ), true);
}
add_action('acf/render_field_settings', 'psr_add_validate_setting');

/*
add_action('acf/validate_save_post', 'psr_skip_validation', 10, 0);
function psr_skip_validation(){
  if(isset($_GET['validate_worksheet']) && $_GET['validate_worksheet'] == 'true'){
    if((isset($field['validation_required']) && $field['validation_required'] == 1) && !$value){
      $valid = 'This field is required';
    }
  }
}*/

function psr_skip_validation($valid, $value, $field, $input){
	//var_dump($field);
  if(isset($_GET['validate_worksheet']) && $_GET['validate_worksheet'] == 'true'){

    if((isset($field['validation_required']) && $field['validation_required'] == 1)){
		if($value == '' || $value == null){
	      $valid = 'This field is required';
		}
    }
  }
  return $valid;
}
add_filter('acf/validate_value', 'psr_skip_validation', 10, 4);

function psr_hide_required_field(){
  echo '<style type="text/css">.acf-field-setting-required{ display:none; }</style>';
}
//add_action('acf/field_group/admin_head', 'psr_hide_required_field');
