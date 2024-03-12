<?php
  get_header();
  echo '<div class="col-12 col-lg-9">';

  while ( have_posts() ) {
    echo '<div class="card shadow nsv-card"><div class="card-body">';
    the_title('<h2 class="card-title">', '</h2>');
    the_post();
    the_content();
    echo '</div></div>';
  }

  echo '</div><div class="col-12 col-lg-3" id="nsv-sidebar">';
  get_template_part('sidebar/navigation-sidebar');
  get_footer(); 
