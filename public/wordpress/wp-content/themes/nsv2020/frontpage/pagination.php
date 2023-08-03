<?php
  // Taken from https://gist.github.com/mtx-z/f95af6cc6fb562eb1a1540ca715ed928
  $pages = paginate_links( [
      'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
      'format'       => '?paged=%#%',
      'current'      => max( 1, get_query_var( 'paged' ) ),
      'total'        => $wp_query->max_num_pages,
      'type'         => 'array',
      'show_all'     => false,
      'end_size'     => 3,
      'mid_size'     => 1,
      'prev_next'    => true,
      'prev_text'    => __( '«' ),
      'next_text'    => __( '»' ),
      'add_args'     => false,
      'add_fragment' => ''
    ]
  );
  if ( is_array( $pages ) ) {
    echo '<div class="card shadow nsv-card"><div class="card-body">';
    echo '<div class="pagination"><ul class="pagination">';
    foreach ($pages as $page) {
      echo '<li class="page-item' . (strpos($page, 'current') !== false ? ' active' : '') . '"> ';
      echo str_replace('page-numbers', 'page-link', $page) . '</li>';
    }
    echo '</ul></div>';
    echo '</div></div>';
  }
?>
