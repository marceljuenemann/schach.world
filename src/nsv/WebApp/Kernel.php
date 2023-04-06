<?php

namespace Nsv\WebApp;

use Nsv\WebApp\Controller\MyController;
use Nsv\WebApp\Core\WordPress\TwigExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\DependencyInjection\Extension\AbstractExtension;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new TwigBundle()
        ];
    }

    protected function configureContainer(ContainerConfigurator $containerConfigurator): void
    {
        // PHP equivalent of config/packages/framework.yaml
        // TODO: set secret. Not sure it's needed any time soon, but better safe than sorry
        $containerConfigurator->extension('framework', [
            'secret' => wp_salt()
        ]);

        // Register all controllers
        $containerConfigurator->services()
            ->load('Nsv\\WebApp\\Controller\\', __DIR__.'/Controller/*')
            ->autowire()
            ->autoconfigure()
        ;

        // Register twig extensions.
        $containerConfigurator->services()
            ->set(null, TwigExtension::class)
            ->tag('twig.extension');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
      $routes->import(
        ['path' => __DIR__ . '/Controller', 'namespace' => 'Nsv\\WebApp\\Controller'],
        'attribute',
      );
    }
}
