<?php acf_form_head(); ?>

<?php get_header(); ?>
<section class="blog-section">
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-xs-12">
        <article id="post-<?php echo get_the_ID(); ?>" class="post" data-wow-delay=".4s">
          <div class="post-content">
            <div class="entry-content">
              <div class="btn-wrapper">
                <a class="btn" href="<?php echo esc_html(home_url('dashboard')); ?>">&lt; Back to Dashboard</a>
              </div>

              <?php
                //$form_type = '';
                //if(isset($_GET['form_type'])){
                //  $form_type = esc_html($_GET['form_type']);
                //}

                //echo do_shortcode('[pprsus_worksheet form_post_type="' . $form_type . '"]');
                echo do_shortcode('[pprsus_worksheet]');
              ?>
            </div>
          </div>
        </article>
      </div>
      <?php get_sidebar(); ?>
    </div>
  </div>
</section>
<?php get_footer();