<?php

$MISSING = '<span style="color:#700; font-style: oblique;">missing</span>';

if(!defined('ABSPATH')){
  exit; //exit if accessed directly
}

if(!is_user_logged_in()){
  echo 'Please <a href="' .wp_login_url(get_permalink()) . '">Login</a></p>';
}
?>
<div class="dashboard-header">
  <div class="btn_wrapper"><a class="btn" href="<?php echo home_url('dashboard'); ?>">Back to Dashboard</a></div>
</div>

<?php
	
function f_display($s) {
	return $s ? $s : '<span style="color:#700; font-style: oblique;">missing</span>';
}

if(isset($_GET['worksheet_id'])){
  $worksheet_id = $_GET['worksheet_id'];
  $user_id = get_current_user_id();

  global $wpdb;
  if(worksheet_belongs_to_user($worksheet_id, $user_id)){

    $psr_worksheet_keys = $wpdb->get_results("
      SELECT posts.post_name as worksheet_key
      FROM $wpdb->posts AS posts
      LEFT JOIN $wpdb->posts AS parent
        ON posts.post_parent = parent.id
      WHERE posts.post_type = 'acf-field'
        AND parent.post_type = 'acf-field-group'
      ORDER BY posts.menu_order ASC");

    $key_index = 0;
?>
			<h2>Review PSR Information</h2>

<?php
    foreach($psr_worksheet_keys as $psr_worksheet_key){

      $group = get_field_object($psr_worksheet_key->worksheet_key, $worksheet_id);
      $fields = Array();
      foreach($group['sub_fields'] as $field) {
	      $field_name = $field['name'];
	      $fields[$field_name] = Array();
	      $fields[$field_name]['value'] = $field['value'];
	      $fields[$field_name]['label'] = $field['label'];
      }
      
      echo $psr_worksheet_key->worksheet_key . "<br />";
?>

      <?php // Personal Information 
	    if ($psr_worksheet_key->worksheet_key == 'field_5aa6e9777175a') { ?>
      <div class="table-responsive">
			<h3>Personal Information</h3>
      <table class="table" style="border: 0px; margin-bottom:0px;">
        <tr>
          <td style="border: 0px;" scope="row"><?php
	          printf ("<b>Client</b><br />%s, %s<br />", $group['value']['last_name'], $group['value']['first_name']);
	          $s = '';
	          if ($group['value']['current_legal_residence']) {
		          $s .= $group['value']['current_legal_residence'];
	          }
	          if ($group['value']['city'] || $group['value']['state'] || $group['value']['zip_code']) {
		          $s  .= '<br />';
	          }
	          if ($group['value']['city']) {
		          $s .= $group['value']['city'];
		        }
	          if ($group['value']['state']) {
		          if ($group['value']['city']) { $s .= ", "; }
		          $s .= $group['value']['state'];
		        }
	          if ($group['value']['zip_code']) {
		          if ($s) { $s .= " "; }
		          $s .= $group['value']['zip_code'];
		        }
		        if ($s) {
			        echo $s;
			      } else {
              echo '<span style="color:#700; font-style: oblique;">missing address</span>';
			      }
		        f_display($s);
          ?></td>
          <td style="border: 0px;" scope="row"><?php printf ("<b>%s</b><br />%s", $fields['docket_number']['label'],
	                f_display($group['value']['docket_number'])); ?></td>
          <td style="border: 0px;" scope="row"><?php printf ("<b>%s</b><br />%s", $fields['date_of_birth']['label'],
	                f_display($group['value']['date_of_birth'])); ?></td>
          <td style="border: 0px;" scope="row"><?php printf ("<b>Last Updated</b><br />%s", f_display($group['value']['date'])); ?></td>
        </tr>
        <tr>
          <td style="border: 0px;" colspan="4">
	          <b>Residential History</b><br />
	          <?php
		          if ($group['value']['residential_history']) {
			          echo $group['value']['residential_history'];
		          } else {
                echo '<span style="color:#700; font-style: oblique;">no residential history</span>';
		          }
		        ?>
          </td>
        </tr>
      </table>
      <table class="table" style="border: 0px; margin-bottom:0px;">
	      <tr>
		      <td style="border: 0px;" >
		        <table class="table" style="border: 0px; margin-bottom:0px;">
		        <tr>
		          <td style="border: 0px;" scope="row"><?php printf ("<b>Age</b><br />%s", f_display($group['value']['age'])); ?></td>
		          <td style="border: 0px;" scope="row"><?php printf ("<b>Sex</b><br />%s", f_display($group['value']['sex'])); ?></td>
		          <td style="border: 0px;" scope="row"><?php printf ("<b>Race</b><br />%s", f_display($group['value']['race'])); ?></td>
		        </tr>
		        <tr>
		          <td style="border: 0px;" scope="row"><?php printf ("<b>Height</b><br />%s", f_display($group['value']['height'])); ?></td>
		          <td style="border: 0px;" scope="row"><?php printf ("<b>Weight</b><br />%s", f_display($group['value']['weight'])); ?></td>
		          <td style="border: 0px;"></td>
		        </tr>
		        <tr>
		          <td style="border: 0px;" scope="row"><?php printf ("<b>Eye Color</b><br />%s", f_display($group['value']['eye_color'])); ?></td>
		          <td style="border: 0px;" scope="row"><?php printf ("<b>Hair Color</b><br />%s", f_display($group['value']['hair_color'])); ?></td>
		          <td style="border: 0px;"></td>
		        </tr>
		        </table>
		      </td>
		      <td style="border: 0px;">
			      <table class="table" style="border: 0px; margin-bottom:0px;">
			        <tr>
				        <?php
					        $s1 = $group['value']['has_tattoos'][0];
					        if (!$s1) { $s1 = 'None'; }
					        else {
						        $s1 = 'Yes. ' . $group['value']['tattoo_descriptions'];
					        }
					      ?>
				        <?php
					        $s2 = $group['value']['has_scars'][0];
					        if (!$s2) { $s2 = 'None'; }
					        else {
						        $s2 = 'Yes. ' . $group['value']['scars_description'];
					        }
					      ?>
			          <td style="border: 0px;" scope="row">
				          <b>Tattoos</b><br />
			            <?php printf ("%s", $s1); ?>
			            <br /><br />
			            <b>Scars</b><br />
			            <?php printf ("%s", $s2); ?>
			          </td>
			        </tr>
			      </table>
		      </td>
	      </tr>
      </table>
      </div>
      <?php // Marital Status 
      } else if ($psr_worksheet_key->worksheet_key == 'field_5aa6eb3071771') {
      ?>
      <div class="table-responsive">
			<h3>Marital Status</h3>

      <table class="table" style="border: 0px; margin-bottom:0px;">
        <tr>
          <td style="border: 0px;" scope="row"><?php printf ("<b>%s</b><br />%s", $fields['date_of_marriage']['label'],
	                f_display($group['value']['date_of_marriage'])); ?></td>
          <td style="border: 0px;" scope="row"><?php printf ("<b>%s</b><br />%s", $fields['name_of_spouse_or_domestic_partner']['label'],
	                f_display($group['value']['name_of_spouse_or_domestic_partner'])); ?></td>
          <td style="border: 0px;" scope="row"><?php printf ("<b>%s</b><br />%s", $fields['place_of_marriage']['label'],
	                f_display($group['value']['place_of_marriage'])); ?></td>
          <td style="border: 0px;" scope="row"><?php printf ("<b>%s</b><br />%s", $fields['court_where_divorce_was_granted']['label'],
	                f_display($group['value']['court_where_divorce_was_granted'])); ?></td>
        </tr>
      </table>
      
      <table>
            <?php foreach($group['sub_fields'] as $field):
              $field_name = $field['name'];
              $field_value = $group['value'][$field_name];

              if((isset($field['validation_required']) && $field['validation_required'] == 1) && ($field_value == '')){
                $field_value = '<span class="required">No entry - Required Field</span>';
              } ?>
              <tr>
                <th scope="row"><?php echo $field['label']; ?></td>
                <td><?php echo $field_name; ?></td>
                <td><?php echo $field_value; ?></td>
              </tr>
            <?php endforeach; ?>

      </table>

      <?php } else { ?>
      
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th colspan="2"><?php echo $group['label']; ?>&nbsp;<a href="<?php echo esc_url(add_query_arg(array('worksheet_id' => $worksheet_id, 'worksheet_page' => $key_index), home_url('manage-worksheet'))); ?>">[edit]</a></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($group['sub_fields'] as $field):
              $field_name = $field['name'];
              $field_value = $group['value'][$field_name];

              if((isset($field['validation_required']) && $field['validation_required'] == 1) && ($field_value == '')){
                $field_value = '<span class="required">No entry - Required Field</span>';
              } ?>
              <tr>
                <th scope="row"><?php echo $field['label']; ?></td>
                <td><?php echo $field_name; ?></td>
                <td><?php echo $field_value; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php } ?>
    <?php
      $key_index++;
    }
    echo '<a href="' . esc_url(add_query_arg(array('worksheet_id' => $worksheet_id), home_url('test-lawyer-form'))) . '" class="button-primary">Edit Worksheet</a>';
  }
  else{
    return;
  }
}
else{
  return;
}
