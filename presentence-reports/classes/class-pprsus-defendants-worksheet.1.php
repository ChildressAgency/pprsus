<?php
/**
 * Shortcode worksheet for adding defendant info
 */
if(!defined('ABSPATH')){ exit; }

if(!class_exists('PPRSUS_Defendants_Worksheet')){
  class PPRSUS_Defendants_Worksheet{
    private $form_id;
    private $form_post_type;
    private $step_ids;

    public function __construct(){
      $this->form_post_type = 'defendants';
      $this->form_id = 'defendants_worksheet';
      $this->step_ids = $this->get_form_steps_ids();

      add_shortcode('pprsus_defendants_worksheet', array($this, 'output_shortcode'));

      add_action('acf/validate_save_post', array($this, 'skip_validation'), 10, 0);
      add_action('acf/save_post', array($this, 'process_acf_form'), 20);
    } 
    
    public function get_form_steps_ids(){
      $form_steps = array();

      global $wpdb;
      $groups = $wpdb->get_results($wpdb->prepare("
        SELECT post_name
        FROM {$wpdb->prefix}posts
        WHERE post_type = %s
          AND post_content LIKE '%%%s%%'
          AND post_status NOT LIKE %s
        ORDER BY menu_order ASC", 'acf-field-group', $this->form_post_type, 'acf-disabled'));

      $g = 0;
      foreach($groups as $group){
        //var_dump($group);
        $form_steps[$g] = $group->post_name;
        $g++;
      }

      return $form_steps;
    }

    public function skip_validation(){
      if(!isset($_GET['finished']) || (int)$_GET['finished'] !== 1){
        acf_reset_validation_errors();
      }
      elseif(isset($_POST['direction'])){
        if($_POST['direction'] == 'previous' || $_POST['direction'] == 'saveforlater'){
          acf_reset_validation_errors();
        }
      }
    }

    public function output_shortcode(){

      ob_start();

      if(!function_exists('acf_form')){ return; }

      $this->output_acf_form();

      return ob_get_clean();
    }

    public function output_acf_form($args = []){
      //if step 1 create new post, otherwise get post_id from url
      $requested_post_id = $this->get_requested_post_id();

      //get the current step we are in
      $requested_step = $this->get_requested_step();

      $args = wp_parse_args(
        $args,
        array(
          'post_id' => $requested_post_id,
          'step' => 'new_post' === $requested_post_id ? 1 : $requested_step,
          'post_type' => $this->form_post_type,
          'post_status' => 'publish',
        )
      );

      if($this->current_multistep_form_is_finished()){
        $current_step_group = $this->step_ids;
        $submit_label = esc_html__('Finish', 'pprsus');
        $submit_button = '<input type="submit" class="acf-button button button-primary button-large" value="%s" />';
      }
      else{
        $current_step_group = array(($args['post_id'] !== 'new_post' && $args['step'] > 1) ? $this->step_ids[(int) $args['step'] - 1] : $this->step_ids[0]);
        $submit_label = $args['step'] < count($this->step_ids) ? esc_html__('Save and Continue', 'pprsus') : esc_html__('Review and Finish', 'pprsus');
        $submit_button = '<input type="submit" class="acf-button button button-primary button-large acf-hidden" value="%s" />';
      }

      //show the progress bar before the form
      if(!$this->current_multistep_form_is_finished()){
        $this->display_progress_bar($args);
      }

      /**
       * display the form with acf_form()
       */
      acf_form(
        array(
          'id' => $this->form_id,
          'post_id' => $args['post_id'],
          'new_post' => array(
            'post_type' => $args['post_type'],
            'post_status' => $args['post_status']
          ),
          'field_groups' => $current_step_group,
          'submit_value' => $submit_label,
          'html_submit_button' => $submit_button,
          'html_after_fields' => $this->output_custom_fields($args),
          //'return' => $return
        )
      );
    }

    private function output_custom_fields($args){
      $inputs = array();
      $inputs[] = sprintf('<div class="clearfix"></div><input type="hidden" name="pprsus-form-id" value="%1$s" />', $this->form_id);
      $inputs[] = isset($args['step']) ? sprintf('<input type="hidden" name="pprsus-current-step" value="%1$s" />', $args['step']) : '';

      if(!$this->current_multistep_form_is_finished()){
        if(($this->get_requested_step() != 1)){
          $inputs[] = '<input type="button" id="cai-previous" name="previous" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Previous', 'pprsus') . '" />';
        }

        if($args['step'] < count($this->step_ids)){
          $inputs[] = '<input type="button" id="cai-next" name="next" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Next', 'pprsus') . '" />';
        }

        $inputs[] = '<input type="button" id="cai-finish" name="finish" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Review and Finish', 'pprsus') . '" />';
      }

      $inputs[] = '<input type="button" id="cai-finish-later" name="saveforlater" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Save for Later', 'pprsus') . '" />';
      $inputs[] = '<input type="hidden" id="direction" name="direction" value="" />';

      return implode(' ', $inputs);
    }

    private function display_progress_bar($args){
      $number_of_steps = count($this->step_ids);
      $current_step = $args['step'];
      $percent_complete = ($current_step / $number_of_steps) * 100;

      echo '<div id="progress-bar">';
        echo '<h4>' . sprintf(esc_html('Step %1$d of %2$d', 'pprsus'), $current_step, $number_of_steps) . '</h4>';
        echo '<div class="progress">';
          echo '<div class="progress-bar" role="progressbar" style="width:' . $percent_complete . '%" aria-valuenow="' . $percent_complete . '" aria-valuemin="0" aria-valuemax="100"></div>';
      echo '</div></div>';
    }

    private function get_requested_post_id(){
      //if(isset($_GET['post_id']) && $this->requested_post_is_valid() && $this->can_continue_current_form()){
      //  return (int) $_GET['post_id'];
      //}

      if(isset($_GET['post_id'])){
        if($this->requested_post_is_valid() && $this->can_continue_current_form()){
          return (int) $_GET['post_id'];
        }
      }

      return 'new_post';
    }

    private function get_requested_step(){
      if(isset($_POST['pprsus-current-step']) && absint($_POST['pprsus-current-step']) <= count($this->step_ids)){
        return absint($_POST['pprsus-current-step']);
      }
      elseif(isset($_GET['step']) && absint($_GET['step']) <= count($this->step_ids)){
        return absint($_GET['step']);
      }

      return 1;
    }

    private function requested_post_is_valid(){
      return (get_post_type((int) $_GET['post_id']) === $this->form_post_type && get_post_status((int) $_GET['post_id']) === 'publish');
    }

    private function can_continue_current_form(){
      if(!isset($_GET['token'])){ return false; }
      //if(!is_user_logged_in()){ return false; }

      //check token
      $token_from_url = sanitize_text_field($_GET['token']);
      $token_from_post_meta = get_post_meta((int) $_GET['post_id'], 'secret_token', true);

      if($token_from_url === $token_from_post_meta){
        return true;
      }

      return false;
    }

    public function current_multistep_form_is_finished(){
      return (isset($_GET['finished']) && 1 === (int) $_GET['finished']);
    }

    private function get_defendant_name($post_id){
      $defendant_id = get_field('defendant_name', $post_id, false);
      if($defendant_id){
        $defendant_name = get_the_title($defendant_id[0]);
      }
      else{
        $defendant_fname = get_field('first_name', $post_id);
        $defendant_lname = get_field('last_name', $post_id);

        $defendant_name = $defendant_fname . ' ' . $defendant_lname;
      }

      return $defendant_name;
    }

    public function process_acf_form($post_id){
      //don't do anything if in admin or working on different front end acf form
      if(is_admin() || !isset($_POST['pprsus-form-id']) || $_POST['pprsus-form-id'] !== $this->form_id){
        return;
      }

      $current_step = $this->get_requested_step();

      //if it was a new post create a title and security token for it
      if($current_step == 1 && !isset($_GET['post_id'])){
        //$company_name = get_field('company_name', $post_id);
        $defendant_name = $this->get_defendant_name($post_id);

        wp_update_post(array(
          'ID' => $post_id,
          'post_type' => $this->form_post_type,
          'post_title' => esc_html($defendant_name)
        ));

        $token = wp_generate_password(rand(10,20), false, false);
        update_post_meta((int)$post_id, 'secret_token', $token);
      }

      //if not done with the form put post_id and step number in the url

      if(($current_step < count($this->step_ids)) || $_POST['direction'] == 'previous' || $_POST['direction'] == 'saveforlater'){
        $query_args = array(
          //'step' => $next_step,
          'post_id' => $post_id,
          'token' => isset($token) ? $token : $_GET['token']
        );

        if(isset($_POST['direction'])){
          if($_POST['direction'] == 'previous'){
            $query_args['step'] = --$current_step;
          }
          elseif($_POST['direction'] == 'next'){
            $query_args['step'] = ++$current_step;
          }
          elseif($_POST['direction'] == 'saveforlater'){
            $query_args['step'] = $current_step;
            $query_args['saveforlater'] = 1;
          }
        }
      }
      else{
        //we are done so put finished in the url
        $query_args = array('finished' => 1);

        //maybe send an email to someone here
        //$this->email_completed_form($post_id);
        //$email_form = new CAI_Email_Form($post_id, $this->step_ids);
      }

      $redirect_url = add_query_arg($query_args, wp_get_referer());
      wp_safe_redirect($redirect_url);
      exit();
    }
  }//end class
}