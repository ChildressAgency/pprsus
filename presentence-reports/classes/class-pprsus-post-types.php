<?php
if(!defined('ABSPATH')){ exit; }

if(!class_exists('PPRSUS_Post_Types')){
  class PPRSUS_Post_Types{
    public function __construct(){
      add_action('init', array($this, 'init'));
    }

    public function init(){
      $this->create_post_types();
    }

    public function create_post_types(){
      $defendant_labels = array(
        'name' => esc_html_x('Defendants', 'post type general name', 'pprsus'),
        'singular_name' => esc_html_x('Defendant', 'post type singular name', 'pprsus'),
        'menu_name' => esc_html_x('Defendants', 'post type menu name', 'pprsus'),
        'add_new_item' => esc_html__('Add New Defendant', 'pprsus'),
        'search_items' => esc_html__('Search Defendants', 'pprsus'),
        'edit_item' => esc_html__('Edit Defendant', 'pprsus'),
        'view_item' => esc_html__('view Defendant', 'pprsus'),
        'all_items' => esc_html__('All Defendants', 'pprsus'),
        'new_item' => esc_html__('New Defendant', 'pprsus'),
        'not_found' => esc_html__('No Defendants Found', 'pprsus')
      );

      $defendant_args = array(
        'labels' => $defendant_labels,
        'capability_type' => 'post',
        'public' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-businessperson',
        'query_var' => 'defendant',
        'has_archive' => false,
        'show_in_rest' => true,
        'supports' => array(
          'title',
          'custom-fields',
          'revisions',
          'author'
        )
      );
      register_post_type('defendants', $defendant_args);

      $medical_history_labels = array(
        'name' => esc_html_x('Medical Histories', 'post type general name', 'pprsus'),
        'singular_name' => esc_html_x('Medical History', 'post type singular name' , 'pprsus'),
        'menu_name' => esc_html_x('Medical Histories', 'post type menu name' , 'pprsus'),
        'add_new_item' => esc_html__('Add New Medical History', 'pprsus'),
        'search_items' => esc_html__('Search Medical Histories', 'pprsus'),
        'edit_item' => esc_html__('Edit Medical History', 'pprsus'),
        'view_item' => esc_html__('View Medical History', 'pprsus'),
        'all_items' => esc_html__('All Medical Histories', 'pprsus'),
        'new_item' => esc_html__('New Medical History', 'pprsus'),
        'not_found' => esc_html__('Medical History Not Found', 'pprsus')
      );

      $medical_history_args = array(
        'labels' => $medical_history_labels,
        'capability_type' => 'post',
        'public' => true,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-universal-access',
        'query_var' => 'medical_history',
        'has_archive' => false,
        'show_in_rest' => true,
        'supports' => array(
          'title',
          'custom-fields',
          'revisions',
          'author'
        )
      );
      register_post_type('medical_history', $medical_history_args);
    }
  }
}