<?php
/**
 * Plugin Name: Presentence Reports
 * Description: Presentence Reports Plugin
 * Author: The Childress Agency
 * Author URI: https://childressagency.com
 * Version: 2.0
 * Text Domain: pprsus
 */
if(!defined('ABSPATH')){ exit; }


/**
 * Define global constants
 */
if(!defined('PPRSUS_PLUGIN_DIR')){
  define('PPRSUS_PLUGIN_DIR', dirname(__FILE__));
}

if(!defined('PPRSUS_PLUGIN_URL')){
  define('PPRSUS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if(!defined('PPRSUS_VERSION')){
  define('PPRSUS_VERSION', '2.0.0');
}

if(!class_exists('PPRSUS_Reports')){
  class PPRSUS_Reports{
    public function __construct(){
      $this->load_dependencies();

      $this->init();
    }

    public function load_dependencies(){
      if(!class_exists('acf')){
        require_once PPRSUS_PLUGIN_DIR . '/vendors/advanced-custom-fields-pro/acf.php';
        add_filter('acf/settings/path', array($this, 'acf_settings_path'));
        add_filter('acf/settings/dir', array($this, 'acf_settings_dir'));
      }

      require_once PPRSUS_PLUGIN_DIR . '/classes/class-pprsus-post-types.php';
      require_once PPRSUS_PLUGIN_DIR . '/classes/class-pprsus-worksheet.php';
      require_once PPRSUS_PLUGIN_DIR . '/classes/class-pprsus-defendants-worksheet.php';
      require_once PPRSUS_PLUGIN_DIR . '/classes/class-pprsus-medical-history-worksheet.php';
      require_once PPRSUS_PLUGIN_DIR . '/classes/class-pprsus-security-worksheet.php';
    }

    public function init(){
      add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
      add_action('init', array($this, 'load_textdomain'));

      add_filter('template_include', array($this, 'load_template'), 99);

      $post_types = new PPRSUS_Post_Types();

      add_action('init', array($this, 'create_worksheets'));
    }

    public function create_worksheets(){
      new PPRSUS_Defendants_Worksheet();
      new PPRSUS_Medical_History_Worksheet();
      new PPRSUS_Security_Worksheet();
    }

    public function load_textdomain(){
      load_plugin_textdomain('pprsus', false, basename(dirname(__FILE__)) . '/languages');
    }

    public function enqueue_scripts(){
      wp_register_script(
        'pprsus-script',
        PPRSUS_PLUGIN_URL . 'js/pprsus-scripts.js',
        array('jquery'),
        '',
        true
      );

      wp_register_script(
        'tablefilter',
        PPRSUS_PLUGIN_URL . 'vendors/tablefilter/tablefilter.js',
        array('jquery'),
        '',
        true
      );

      wp_enqueue_script('tablefilter');
      wp_enqueue_script('pprsus-script');


      //styles
      wp_register_style(
        'pprsus-style',
        PPRSUS_PLUGIN_URL . 'css/pprsus-style.css'
      );

      wp_register_style(
        'tablefilter-style',
        PPRSUS_PLUGIN_URL . 'vendors/tablefilter/style/tablefilter.css'
      );

      wp_enqueue_style('pprsus-style');
      wp_enqueue_style('tablefilter-style');
    }//end enqueue scripts

    public function acf_settings_path($path){
      $path = plugin_dir_path(__FILE__) . 'vendors/advanced-custom-fields-pro';
      return $path;
    }

    public function acf_settings_dir($dir){
      $dir = plugin_dir_url(__FILE__) . 'vendors/advanced-custom-fields-pro';
      return $dir;
    }

    public function load_template($template){
      $template_name = '';

      if(is_page('worksheet')){
        $template_name = 'page-worksheet.php';
      }

      if($template_name != ''){
        return $this->find_template($template_name);
      }

      return $template;
    }

    public function find_template($template_name){
      $template_path = get_stylesheet_directory_uri() . '/pprsus-templates/';

      $template = locate_template(array(
        $template_path . $template_name,
        $template_name
      ), TRUE);

      if(!$template){
        $template = PPRSUS_PLUGIN_DIR . '/templates/' . $template_name;
      }

      return $template;
    }
  }//end class
}

new PPRSUS_Reports();
