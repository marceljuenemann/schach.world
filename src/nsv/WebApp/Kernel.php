<?php

namespace Nsv\WebApp;

use Nsv\WebApp\Controller\MyController;
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

// TODO: Load a config file first
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
    
    protected function build(ContainerBuilder $containerBuilder)
    {
//        $containerBuilder->registerExtension(new AbstractExtension());
    }

    protected function configureContainer(ContainerConfigurator $containerConfigurator): void
    {
        // PHP equivalent of config/packages/framework.yaml
        // TODO: set secret. Not sure it's needed any time soon, but better safe than sorry
        $containerConfigurator->extension('framework', [
            'secret' => 'S0ME_SECRET'
        ]);

        // Register all controllers
        $containerConfigurator->services()
            ->load('Nsv\\WebApp\\Controller\\', __DIR__.'/Controller/*')
            ->autowire()
            ->autoconfigure()
        ;
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
      $routes->import(
        ['path' => __DIR__ . '/Controller', 'namespace' => 'Nsv\\WebApp\\Controller'],
        'attribute',
      );
    }
}
