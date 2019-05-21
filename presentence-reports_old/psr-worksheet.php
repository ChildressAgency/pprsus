<?php

  if(!defined('ABSPATH')){
    exit; //exit if accessed directly
  }

  if(!is_user_logged_in()){
    echo '<p>Please <a href="' . wp_login_url(get_permalink()) . '">Login</a></p>';
  }

  $dashboard_page_slug = 'dashboard';
  $form_page_slug = 'manage-worksheet';
  $user_id = get_current_user_id();

  global $wpdb;
  global $wp;
  $psr_worksheet_keys = $wpdb->get_results("
    SELECT posts.post_name as worksheet_key, posts.post_title as worksheet_section_title, posts.post_excerpt as worksheet_section_slug
    FROM $wpdb->posts AS posts
    LEFT JOIN $wpdb->posts AS parent
      ON posts.post_parent = parent.id
    WHERE posts.post_type = 'acf-field'
      AND parent.post_type = 'acf-field-group'
    ORDER BY posts.menu_order ASC");

  if(isset($_GET['worksheet_id'])){
    $worksheet_id = $_GET['worksheet_id'];
    if(!worksheet_belongs_to_user($worksheet_id, $user_id)){
      $worksheet_id = 0;
    }
  }
  else{
    $worksheet_id = 0;
  }

  if(isset($_GET['worksheet_page'])){
    $worksheet_page = $_GET['worksheet_page'];
  }
  else{
    $worksheet_page = 0;
  }

?>

  <div class="btn_wrapper"><a class="btn" href="<?php echo home_url('dashboard'); ?>" class="button-primary">&lt; Back to Dashboard</a></div>
  <div class="worksheet-nav">
    <ul class="nav-tabs">
      <?php
        for ($worksheet_label_counter = 0; $worksheet_label_counter < 10; $worksheet_label_counter++) {
  	      $worksheet_selected_class = '';
  	      if ($worksheet_page == $worksheet_label_counter) {
    	      $worksheet_selected_class = 'class="worksheet_nav_selected" ';
  	      }
          echo '<li><a ' . $worksheet_selected_class . ' href="' . home_url(add_query_arg(array('worksheet_page' => $worksheet_label_counter, 'worksheet_id' => $worksheet_id), $wp->request)) . '" class="button-primary">M' . ($worksheet_label_counter+1) . '</a></li>';
        }
        reset($psr_worksheet_keys);
      ?>
    </ul>
  </div>

<?php

  if(isset($_GET['validate_worksheet']) && $_GET['validate_worksheet'] == 'true') {

    $form_args['post_id'] = $worksheet_id;
    $form_args['submit_value'] = 'Finish';
    $form_args['return'] = home_url($dashboard_page_slug);

    acf_form($form_args);
  }
  else{

    $psr_worksheet_last_page = count($psr_worksheet_keys) - 1;

    if ($worksheet_page < $psr_worksheet_last_page) {
      $submit_value = 'Save and Continue';
    }
    else{
      $submit_value = 'Save draft';
    }

    if($worksheet_id == 0){
      $form_args['post_id'] = 'new_post';
      $form_args['post_title'] = false;
      $form_args['post_content'] = false;
      $form_args['new_post'] = array('post_type' => 'defendants', 'post_status' => 'draft');
      $form_args['submit_value'] = $submit_value;
      $form_args['fields'] = Array($psr_worksheet_keys[$worksheet_page]->worksheet_key);
      $form_args['return'] = home_url(add_query_arg(array('worksheet_page' => $worksheet_page + 1, 'worksheet_id' => '%post_id%'), $wp->request));
    }
    else{
      $return_url = ($worksheet_page < $psr_worksheet_last_page) ?
        home_url(add_query_arg(array('worksheet_page' => $worksheet_page + 1, 'worksheet_id' => '%post_id%'), $wp->request)) : home_url($dashboard_page_slug);

      $form_args['post_id'] = $worksheet_id;
      $form_args['post_title'] = false;
      $form_args['post_content'] = false;
      $form_args['submit_value'] = $submit_value;
      $form_args['fields'] = Array($psr_worksheet_keys[$worksheet_page]->worksheet_key); // $psr_worksheet_keys[$worksheet_page];
      $form_args['return'] = $return_url;
    }
    // echo "<pre>"; var_dump($form_args); echo "</pre>";
    acf_form($form_args);

    if($worksheet_page > 0){
      echo '<p><a href="' . home_url(add_query_arg(array('worksheet_page' => $worksheet_page - 1, 'worksheet_id' => $worksheet_id), $wp->request)) . '">Go Back</a></p>';
    }
    if($worksheet_page == $psr_worksheet_last_page){
      echo '<p><a href="' . esc_url(add_query_arg(array('worksheet_id' => $worksheet_id, 'validate_worksheet' => 'true'), home_url($form_page_slug))) . '">Review and Finish</a></p>';
    }
  }
