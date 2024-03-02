<?php
  get_header();
  echo '<div class="col-12 col-lg-9">';

  while ( have_posts() ) {
    the_post();
    get_template_part( 'frontpage/post' );
  }

  get_template_part('frontpage/pagination');

  echo '</div><div class="col-12 col-lg-3" id="nsv-sidebar">';
  dynamic_sidebar( 'left' );

  get_footer(); 
