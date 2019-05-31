<?php
/**
 * main worksheet class
 */
if(!defined('ABSPATH')){ exit; }

if(!class_exists('PPRSUS_Worksheet')){
  class PPRSUS_Worksheet{
    public $form_id;
    public $form_post_type;
    public $step_ids;

    public function __construct(){
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

    public function output_custom_fields($args){
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
      else{
        $inputs[] = '<div class="btn-wrapper"><a href="' . esc_url(home_url('dashboard')) . '">&lt;' . esc_html__('Back to Dashboard', 'pprsus') . '</a></div>';
      }

      $inputs[] = '<input type="button" id="cai-finish-later" name="saveforlater" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Save for Later', 'pprsus') . '" />';
      $inputs[] = '<input type="hidden" id="direction" name="direction" value="" />';

      return implode(' ', $inputs);
    }

    public function display_progress_bar($args){
      $number_of_steps = count($this->step_ids);
      $current_step = $args['step'];
      $percent_complete = ($current_step / $number_of_steps) * 100;

      echo '<div id="progress-bar">';
        echo '<h4>' . sprintf(esc_html('Step %1$d of %2$d', 'pprsus'), $current_step, $number_of_steps) . '</h4>';
        echo '<div class="progress">';
          echo '<div class="progress-bar" role="progressbar" style="width:' . $percent_complete . '%" aria-valuenow="' . $percent_complete . '" aria-valuemin="0" aria-valuemax="100"></div>';
      echo '</div></div>';
    }

    public function get_requested_post_id(){
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

    public function get_requested_step(){
      if(isset($_POST['pprsus-current-step']) && absint($_POST['pprsus-current-step']) <= count($this->step_ids)){
        return absint($_POST['pprsus-current-step']);
      }
      elseif(isset($_GET['step']) && absint($_GET['step']) <= count($this->step_ids)){
        return absint($_GET['step']);
      }

      return 1;
    }

    public function requested_post_is_valid(){
      return (get_post_type((int) $_GET['post_id']) === $this->form_post_type && get_post_status((int) $_GET['post_id']) === 'publish');
    }

    public function can_continue_current_form(){
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

    public function get_defendant_info($post_id){

    }
  }
}