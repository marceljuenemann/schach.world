<article class="card shadow nsv-card">
  <div class="card-body">
    <a name="post-<?php the_ID(); ?>" class="anchor"></a>
    
    <?php
      if ( is_singular() ) :
        the_title('<h5 class="card-title">', '</h5>');
      else :
        the_title(
          sprintf('<h5 class="card-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())),
          '</a></h5>'
        );
      endif;
    ?>
 
    <h6 class="card-subtitle mb-2 text-muted">
      <?php echo get_the_date(); ?> von <a href="/vorstand/"><?php the_author(); ?></a>
    </h6>

    <?php
      the_content('Weiterlesen...');
    ?>
  </div>
</article>
