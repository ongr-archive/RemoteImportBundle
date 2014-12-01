<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
            new ONGR\ConnectionsBundle\ONGRConnectionsBundle(),
            new ONGR\RemoteImportBundle\ONGRRemoteImportBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
        $loader->load(__DIR__ . '/config/services.yml');
    }
}
