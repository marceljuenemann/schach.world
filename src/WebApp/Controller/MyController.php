<?php

namespace Nsv\WebApp\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyController extends AbstractController {

  #[Route('/v3/blog', name: 'blog_list')]
  public function test(): Response {
    return new Response(
      sprintf("Hello World!")
    );
  }

  #[Route('/v3/hello/{name}', name: 'hello')]
  public function hello(string $name): Response {
    return new Response(
      sprintf("Hello $name!")
    );
  }

  #[Route('v3/bye/{name}', name: 'bye')]
  public function bye(string $name): Response {
    return $this->render('hello-world.html.twig', [
      'user_first_name' => $name
    ]);
  }
}
