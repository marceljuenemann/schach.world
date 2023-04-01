<?php
  /**
   * In order to improve the mobile experience, we will output the sidebar in between the
   * articles on the frontpage. The responsive design will still show it in the sidebar
   * on large screens. In order to make this responsive design work, we need to set a height
   * of zero on the sidebar, but that only works as a long as the main column is longer than
   * the sidebar, which is usually only the case on the frontpage. Therefore, we ouput the
   * sidebar in the end of the HTML on other pages. This is maybe a bit hacky, but can't really
   * find a better way right now...
   */
  
  // How many posts do we require in order to output the sidebar inline?
  define('INLINE_SIDEBAR_MIN_POSTS', 8);

  // How many posts should be shown before the inlined sidebar?
  define('INLINE_SIDEBAR_POSITION', 2);

  // Whether we'll show an inline sidebar.
  $inline_sidebar = $wp_query->post_count >= INLINE_SIDEBAR_MIN_POSTS;

  get_header();
  echo '<div class="col-12 col-lg-9">';

  // Show an optional term description (used on category pages).
  $term_description = term_description();
  if (!empty($term_description)) {
    echo '<article class="card shadow nsv-card"><div class="card-body">';
    echo '<h5 class="card-title">';
    echo single_cat_title();
    echo '</h5>';
    echo $term_description;
    echo '</div></article>';
  }

  while ( have_posts() ) {
    the_post();
    get_template_part( 'frontpage/post' );
    
    if ($inline_sidebar && $wp_query->current_post == INLINE_SIDEBAR_POSITION - 1) {
      // Output 'Weitere Artikel' card and then rewind post iterator.
      get_template_part( 'frontpage/articlelist' );
      for (rewind_posts(); $wp_query->current_post != INLINE_SIDEBAR_POSITION - 1; the_post());
      
      // Output sidebar with inline-sidebar class.
      echo '</div><div class="col-12 col-lg-3 inline-sidebar" id="nsv-sidebar">';
      get_template_part('sidebar/frontpage-sidebar');
      echo '</div><div class="col-12 col-lg-9">';
    }
  }

  get_template_part('frontpage/pagination');
  get_template_part('frontpage/newsarchiv');

  if (!$inline_sidebar) {
    echo '</div><div class="col-12 col-lg-3" id="nsv-sidebar">';
    get_template_part('sidebar/frontpage-sidebar');
  }

  get_footer(); 
