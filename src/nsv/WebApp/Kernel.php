<?php

namespace Nsv\WebApp;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

// TODO: Load a config file first
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $containerConfigurator): void
    {
        // PHP equivalent of config/packages/framework.yaml
        // TODO: set secret. Not sure it's needed any time soon, but better safe than sorry
        /*
        $containerConfigurator->extension('framework', [
            'secret' => 'S0ME_SECRET'
        ]);
        */
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
      $routes->import(
        ['path' => '../../nsv-wp/', 'namespace' => 'NsvWp'],
        'attribute',
      );
    }
}
