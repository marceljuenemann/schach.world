<?php

namespace NsvWp;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Loader\Psr4DirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// TODO: Set up dependency injection stuff.
// TODO: Enable prod mode? Otherwise maybe slow?

class Prototype {

  function __construct() {
  }

  // TODO: only run the whole thing from theme/nsv2020/symfony.php if we are on a
  // whitelisted route.
  function run() {
    $dispatcher = new EventDispatcher();
    $controllerResolver = new ControllerResolver();
    $argumentResolver = new ArgumentResolver();

    $kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);


    $loader = new DelegatingLoader(
      new LoaderResolver([
          new Psr4DirectoryLoader(
              new FileLocator()
          ),
          new class() extends AnnotationClassLoader {
              protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot) {
                  $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
              }
          }
      ])
    );
    $routes = $loader->load([
      'path' => __DIR__, // . '/../src/App/Controller',
      'namespace' => 'NsvWp'
    ], 'attribute');


  
  //$routes = $loader->load(['path' => __DIR__ . '/../src/App/Controller', 'namespace' => 'App\Controller'], 'attribute');
  //$routes = new RouteCollection();
    /*
    $routes->add('hello', new Route('/hello/{name}', [
        '_controller' => function (Request $request) {
            return new Response(
                sprintf("Hello %s", $request->get('name'))
            );
        }]
    ));
    */
    /*
    $routes->add('hello', new Route('/hello/{name}', [
      '_controller' => [MyController::class, 'test']
    ]));
    */

    //$routes->add('hello', '/hello/{name}')->controller([MyController::class, 'test']);

    $request = Request::createFromGlobals();

    $matcher = new UrlMatcher($routes, new RequestContext());

    $dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));



    $response = $kernel->handle($request);
    $response->send();

    $kernel->terminate($request, $response);
  }
}
