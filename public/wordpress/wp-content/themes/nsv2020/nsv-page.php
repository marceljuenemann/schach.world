<?php
  // Create the page object responsible for this route.
  $router = \NSV\Core\Router::getInstance();
  $nsv_page = $router->getPage();

  // Allow the page to preprocess the request and redirect to other pages.
  // Show preprocess errors on a nice error page.
  try {
    do {
      $result = $nsv_page->preprocess();
    } while ($result instanceof \NSV\Core\Page && $nsv_page = $result);
  } catch (\Exception $e) {
    $nsv_page = new \NSV\Core\ErrorPage($e);
  }

  // TODO: We shouldn't even be here if the theme won't be shown
  if ($nsv_page->showTheme()) {
    // Set custom title tag.
    add_filter('document_title_parts', function($title) use ($nsv_page) {
      $title['title'] = $nsv_page->getTitle();
      return $title;
    });

    // Output the header and navbar in the requested theme.
    // Theme here refers to the NSV 2020 theme, rather than the WordPress theme. 
    get_header(null, array('nsv2020theme' => $nsv_page->getTheme()));

    // Layout.
    $sidebar = $nsv_page->showSidebar();
    if ($sidebar) {
      echo '<div class="col-12 col-lg-9">';
    } else {
      echo '<div class="col-12">';
    }

    // Main content.
    echo '<div class="card shadow nsv-card"><div class="card-body">';
    $nsv_page->printPageTitle();
    $nsv_page->printInfoMessages();
    $nsv_page->printPage();
    echo '</div></div>';

    // Sidebar.
    if ($sidebar) {
      // TODO: Enable custom sidebar content or widgets?
      echo '</div><div class="col-12 col-lg-3" id="nsv-sidebar">';
      //get_template_part('sidebar/navigation-sidebar');
      dynamic_sidebar('frontpage_sidebar');
    }

    get_footer(); 
  } else {
    $nsv_page->printPage();
  }
