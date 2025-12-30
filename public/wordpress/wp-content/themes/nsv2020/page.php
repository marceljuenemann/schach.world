<?php
  get_header();
  echo '<div class="col-12 col-lg-9">';

  while ( have_posts() ) {
    echo '<div class="card shadow nsv-card"><div class="card-body">';
    the_post();
    the_content();
    echo '</div></div>';
  }

  echo '</div><div class="col-12 col-lg-3" id="nsv-sidebar">';
  dynamic_sidebar('frontpage_sidebar');
  get_footer(); 
