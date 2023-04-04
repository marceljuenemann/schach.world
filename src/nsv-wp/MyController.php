<?php

namespace NsvWp;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyController {

  #[Route('/v3/blog', name: 'blog_list')]
  public function test(): Response {
    return new Response(
      sprintf("Hello World!")
    );
  }

  #[Route('/hello/{name}', name: 'hello')]
  public function hello(string $name): Response {
    return new Response(
      sprintf("Hello $name!")
    );
  }

  #[Route('v3/bye/{name}', name: 'hello')]
  public function bye(string $name): Response {
    //        return $this->render('user/notifications.html.twig', [

  }

}