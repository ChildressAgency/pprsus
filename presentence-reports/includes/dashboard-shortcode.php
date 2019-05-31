<?php 
if(!defined('ABSPATH')){ exit; }

if(!is_user_logged_in()){
  echo '<h3>' . esc_html__('PPRSUS Match Login', 'pprsus') . '</h3>';
  echo '<p>' . esc_html__('Log in to create new or edit existing client profiles.', 'pprsus') . '</p>';
  echo '<p>' . sprintf(esc_html__('Please <a href="%s">Login</a>', 'pprsus'), array('a' => array('href' => array())), wp_login_url(get_permalink())) . '</p>';
  return;
}
else{
  echo '<h3>' . esc_html__('PPRSUS Prison Match', 'pprsus') . '</h3>';
  $announcement = get_option('options_special_announcement');
  if($announcement){
    echo '<div class="announcement">';
    echo apply_filters('the_content', wp_kses_post($announcement));
    echo '</div>';
  }
  echo '<p>' . esc_html__('Create or edit your client profiles.', 'pprsus') . '</p>';
}
?>

<div class="dashboard-header">
  <div class="btn_wrapper">
    <a class="btn" href="<?php echo esc_url(add_query_arg(array('form_type' => 'defendant'), home_url('worksheet'))); ?>"><?php echo esc_html__('+ Create New Profile', 'pprsus'); ?></a>
  </div>

  <div class="dashboard">
    <?php
      $user_id = get_current_user_id();

      $defendants_query_args = array(
        'post_type' => 'defendants',
        'posts_per_page' => -1,
        'post_status' => array('draft', 'publish')
      )

      if(current_user_can('edit_others_pages')){
        $defendants_query_args['author'] = $user_id;
      }

      $defendants = new WP_Query($defendants_query_args);
    ?>

    <div class="table-responsive">
      <table id="psr-table" class="table table-striped psr-table">
        <thead>
          <tr>
            <th><?php echo esc_html__('User Name', 'pprsus'); ?></th>
            <th><?php echo esc_html__('Defendant', 'pprsus'); ?></th>
            <th><?php echo esc_html__('Medical History', 'pprsus'); ?></th>
            <th><?php echo esc_html__('Security', 'pprsus'); ?></th>
            <th><?php echo esc_html__('Date Created', 'pprsus'); ?></th>
          </tr>
          <?php if($defendants->have_posts()): while($defendants->have_posts()): $defendants->the_post(); ?>
            <tr>
              <td><?php echo get_the_author_meta('display_name'); ?></td>
              <td><?php the_title(); ?></td>
            </tr>
          <?php endwhile; endif; wp_reset_postdata(); ?>
        </thead>
      </table>
    </div>
  </div>
</div>