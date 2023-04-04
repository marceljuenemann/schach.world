<?php

namespace NsvWp; // TODO: move somewhere sensible

// TODO: Probably not chess specific, so maybe not in NSV namespace?
abstract class NsvController {

/*
  protected function renderView(string $view, array $parameters = []): string {
    if (!$this->container->has('twig')) {
        throw new \LogicException('You cannot use the "renderView" method if the Twig Bundle is not available. Try running "composer require symfony/twig-bundle".');
    }

    foreach ($parameters as $k => $v) {
        if ($v instanceof FormInterface) {
            $parameters[$k] = $v->createView();
        }
    }

    return $this->container->get('twig')->render($view, $parameters);
  }

  /**
   * Renders a view.
   *
   * If an invalid form is found in the list of parameters, a 422 status code is returned.
   * Forms found in parameters are auto-cast to form views.
   */
/*  
  protected function render(string $view, array $parameters = [], Response $response = null): Response
  {
  $content = $this->renderView($view, $parameters);
  $response ??= new Response();

  if (200 === $response->getStatusCode()) {
    foreach ($parameters as $v) {
        if ($v instanceof FormInterface && $v->isSubmitted() && !$v->isValid()) {
            $response->setStatusCode(422);
            break;
        }
    }
  }

  $response->setContent($content);

  return $response;
  }
*/
}
