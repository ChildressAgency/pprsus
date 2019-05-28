<?php
/**
 * Worksheet Class
 * 
 * worksheet shortcode and acf multipage functions
 */
if(!defined('ABSPATH')){ exit; }

if(!class_exists('PPRSUS_Worksheet')){
  class PPRSUS_Worksheet Extends PPRSUS_Reports{
    /**
     * the form id, used to identify the specific form being filled out
     * 
     * @var string
     */
    private $form_id;

    /**
     * The name of the post type this for is for
     * The post type comes from the shortcode att
     * 
     * @var string
     */
    private $form_post_type;

    /**
     * List of form groups used as steps
     * Each array item is an acf group id to display each step
     * 
     * @var array;
     */
    private $step_ids;

    public function __construct(){
      $this->load_dependencies();

      $this->form_post_type = $this->get_form_post_type();
      $this->form_id = $this->form_post_type . '_worksheet';
      $this->step_ids = $this->get_form_steps_ids();

      //$this->output_shortcode();
      add_shortcode('pprsus_worksheet', array($this, 'output_shortcode'));

      add_action('acf/validate_save_post', array($this, 'skip_validation'), 10, 0);

      add_action('acf/save_post', array($this, 'process_acf_form'), 20);
    }

    public function load_dependencies(){

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

    public function get_form_post_type(){
      if(isset($_GET['form_type']) && $_GET['form_type'] !== ''){
        $post_type = sanitize_key($_GET['form_type']);

        if(post_type_exists($post_type)){
          $available_post_types = get_post_types(array('_builtin' => false), 'objects');

          foreach($available_post_types as $available_post_type){
            if($available_post_type->name == 'acf-field-group' || $available_post_type->name == 'acf-field' || $available_post_type->name != $post_type){
              continue;
            }
            else{
              return $post_type;
            }
          }
        }
      }

      return false;
    }

    /**
     * skip validation unless the form is finished
     */
    public function skip_validation(){
      if(!isset($_GET['finished']) || (int)$_GET['finished'] !== 1){
        acf_reset_validation_errors();
      }
    }

    /*public function output_shortcode(){
     // ob_start();

      if(!function_exists('acf_form')){ return; }

      if(!$this->current_multistep_form_is_finished()){
        $this->output_acf_form(array('post_type' => $this->form_post_type));
      }
      else{
        wp_safe_redirect(home_url('dashboard'));
        exit();
      }

     // return ob_get_clean();
    }*/

    public function output_shortcode(){

      ob_start();

      if(!function_exists('acf_form')){ return; }

      if(!$this->current_multistep_form_is_finished()){
        $this->output_acf_form(array('post_type' => $this->form_post_type));
      }
      else{
        wp_safe_redirect(home_url('dashboard'));
        exit();
      }

      //return ob_get_clean();
    }

    /**
     * Output the acf frontend form if logged in,
     * otherwise show login/register
     * Requires 'acf_form_head()' in the header of the theme
     */
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

      $submit_label = $args['step'] < count($this->step_ids) ? esc_html__('Save and Continue', 'pprsus') : esc_html__('Review and Finish', 'pprsus');

      //if finished show all groups at once, when submitted will validate and redirect to dashboard.
      if(isset($_GET['finished']) && (int)$_GET['finished'] === 1){
        $current_step_group = $this->step_ids;
        $return = esc_url(home_url('dashboard'));
      }
      else{
        $current_step_group = array(($args['post_id'] !== 'new_post' && $args['step'] > 1) ? $this->step_ids[(int) $args['step'] - 1] : $this->step_ids[0]);
        $return = '';
      }

      //show the progress bar before the form
      $this->display_progress_bar($args);

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
          'html_after_fields' => $this->output_custom_fields($args),
          //'return' => $return
        )
      );
    }

    /**
     * hidden fields and buttons
     * 
     * @param array $args - form arguments passed to acf_form()
     * @return string html hidden input fields
     */
    private function output_custom_fields($args){
      $inputs = array();
      $inputs[] = sprintf('<div class="clearfix"></div><input type="hidden" name="pprsus-form-id" value="%1$s" />', $this->form_id);
      $inputs[] = isset($args['step']) ? sprintf('<input type="hidden" name="pprsus-current-step" value="%1$s" />', $args['step']) : '';

      if($this->get_requested_step() != 1){
        $inputs[] = '<input type="button" id="cai-previous" name="previous" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Previous', 'pprsus') . '" />';
      }

      if($args['step'] < count($this->step_ids)){
        $inputs[] = '<input type="button" id="cai-next" name="next" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Next', 'pprsus') . '" />';
      }
      else{
        $inputs[] = '<input type="button" id="cai-finish" name="finish" class="acf-button button button-primary button-large cai-submit" value="' . esc_html__('Finish', 'pprsus') . '" />';
      }

      //$inputs[] = '<input type="button" id="cai-finish-later" name="saveforlater" class="btn-main cai-submit" value="' . esc_html__('Finish Later', 'pprsus') . '" />';
      $inputs[] = '<input type="hidden" id="direction" name="direction" value="" />';

      return implode(' ', $inputs);
    }

    /**
     * show the progress bar
     * 
     * @param array $args - the arguments passed to acf_form() in $this->output_acf_form()
     */
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

    /**
     * if current $_GET['post_id'] is valid return the id, otherwise see if the user
     * already has another post. if neither create a new post.
     */
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

    /**
     * Get requested step, fallback to 1
     */
    private function get_requested_step(){
      if(isset($_POST['pprsus-current-step']) && absint($_POST['pprsus-current-step']) <= count($this->step_ids)){
        return absint($_POST['pprsus-current-step']);
      }
      elseif(isset($_GET['step']) && absint($_GET['step']) <= count($this->step_ids)){
        return absint($_GET['step']);
      }

      return 1;
    }

    /**
     * si the requested post the right one?
     */
    private function requested_post_is_valid(){
      return (get_post_type((int) $_GET['post_id']) === $this->form_post_type && get_post_status((int) $_GET['post_id']) === 'publish');
    }

    /**
     * is the user allowed to edit this form.
     * check token passed in url matches a post meta so that someone
     * can't pass a random $_GET['post_id'] parameter without its secret token
     * Any logged in verification should be done here
     */
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

    /**
     * process the form
     * post has been created/updated, now update the progress bar
     * and redirect user to the next step or finished form
     */
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

      if(($current_step < count($this->step_ids)) || $_POST['direction'] == 'previous'){
        $query_args = array(
          //'step' => $next_step,
          'post_id' => $post_id,
          'token' => isset($token) ? $token : $_GET['token']
        );

        if(isset($_POST['direction']) && $_POST['direction'] == 'previous'){
          $query_args['step'] = --$current_step;
        }
        else{
          $query_args['step'] = ++$current_step;
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
      //$redirect_url = add_query_arg($query_args, home_url('kick-off-form'));
      wp_safe_redirect($redirect_url);
      exit();
    }
  }//end class
}