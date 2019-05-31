<?php
/**
 * worksheet for defendant medical history
 */
if(!defined('ABSPATH')){ exit; }

if(!class_exists('PPRSUS_Medical_History_Worksheet')){
  class PPRSUS_Medical_History_Worksheet Extends PPRSUS_Worksheet{
    private $defendant_info = array();

    public function __construct(){
      parent::__construct(
        $this->form_post_type = 'medical_history',
        $this->form_id = 'medical_history_worksheet',
        $this->step_ids = $this->get_form_steps_ids()
      );

      $this->defendant_info = $this->get_defendant_info();
      add_shortcode('pprsus_medical_history_worksheet', array($this, 'output_shortcode'));
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

  }//end class
}