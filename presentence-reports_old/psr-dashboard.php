<?php 

if(!defined('ABSPATH')){
  exit; //exit if accessed directly
}

if(!is_user_logged_in()){
  //auth_redirect();
  echo '<h3>PPRSUS Match Login</h3>';
  echo '<p>Log in to create new or edit existing client profiles.</p>';
  echo '<p>Please <a href="' . wp_login_url(get_permalink()) . '">Login</a></p>';
  return;
} else {
  echo '<h3>PPRSUS Prison Match</h3>';
  echo '<div class="announcement"><b>Special Announcement Box Here</b>';
  echo '<p>This is where you can post a short paragraph that will be seen by all users when they log in. This could ether be like a tip of the day, or maybe telling users about a referral promotion.</p>';
  echo '</div>';
  echo '<p>Create or edit your client profiles.</p>';
}
?>

<div class="dashboard-header">
  <!--<h1>Dashboard</h1>-->
  <?php //$worksheet_url = home_url('manage-worksheet'); ?>
  <div class="btn_wrapper"><a class="btn" href="<?php echo esc_url(add_query_arg(array('worksheet_id' => '0', 'worksheet_page' => '0'), home_url('manage-worksheet'))); ?>">+ Create New Profile</a></div>
  <!--<p><a href="<?php echo home_url('manage-worksheet') . '?worksheet_id=0&worksheet_page=0'; ?>">Add New PSR Medical History Worksheet</a></p>-->
</div>
<div class="dashboard">
  <?php
    $user_id = get_current_user_id();
    if(current_user_can('edit_others_pages')){
      $defendants_query_args = array(
        'post_type' => 'defendants',
        'posts_per_page' => -1,
        'post_status' => array('draft', 'publish')
      );
    }
    else{
      $defendants_query_args = array(
        'post_type' => 'defendants',
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => array('draft', 'publish')
      );
    }
    $defendants = new WP_Query($defendants_query_args);

    if($defendants->have_posts()): ?>
      <div class="table-responsive">
        <table id="psr-table" class="table table-striped psr-table">
          <thead>
            <tr>
              <th>User Name</th>
              <th>Defendant</th>
              <th>&nbsp;</th>
              <th>&nbsp;</th>
              <th>Date Created</th>
            </tr>
          </thead>
          <?php while($defendants->have_posts()): $defendants->the_post(); ?>
            <tr>
              <td style="width:30%;"><?php echo get_the_author_meta('display_name'); ?></td>
              <?php $personal_info = get_field('personal_info'); ?>
              <td style="width:30%;"><?php echo $personal_info['first_name'] . ' ' . $personal_info['last_name']; ?></td>
              <td style="width:10%; text-align:center;"><a href="<?php echo esc_url(add_query_arg(array('worksheet_id' => get_the_ID(), 'worksheet_page' => '0'), home_url('manage-worksheet'))); ?>" class="" title="Edit Worksheet"><span class="dashicons dashicons-welcome-write-blog btn-worksheet<?php echo (get_post_status() == 'publish') ? ' validated-worksheet' : ' draft-worksheet'; ?>"></span></a></td>
              <td style="width:10%; text-align:center;"><a href="<?php echo esc_url(add_query_arg(array('worksheet_id' => get_the_ID()), home_url('view-worksheet'))); ?>" class="" title="View Worksheet"><span class="dashicons dashicons-visibility btn-worksheet"></span></a></td>
              <td style="width:20%;"><?php echo $personal_info['date'] ? $personal_info['date'] : get_the_date('F j, Y'); ?></td>
            </tr>
          <?php endwhile; ?>
        </table>      
      </div>
  <?php endif; ?>
</div>