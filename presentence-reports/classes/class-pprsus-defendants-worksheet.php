<?php
/**
 * Shortcode worksheet for adding defendant info
 */
if(!defined('ABSPATH')){ exit; }

if(!class_exists('PPRSUS_Defendants_Worksheet')){
  class PPRSUS_Defendants_Worksheet Extends PPRSUS_Worksheet{
    //private $form_id;
    //private $form_post_type;
    //private $step_ids;

    public function __construct(){
      parent::__construct(
        $this->form_post_type = 'defendants',
        $this->form_id = 'defendants_worksheet',
        $this->step_ids = $this->get_form_steps_ids()
      );

      add_shortcode('pprsus_defendants_worksheet', array($this, 'output_shortcode'));
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
        $submit_label = esc_html__('Update', 'pprsus');
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
            //$query_args['saveforlater'] = 1;
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