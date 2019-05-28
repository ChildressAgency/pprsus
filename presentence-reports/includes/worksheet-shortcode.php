<?php

function worksheet_shortcode(){
  ob_start();

  echo $this->form_post_type;

  return ob_get_clean();
}