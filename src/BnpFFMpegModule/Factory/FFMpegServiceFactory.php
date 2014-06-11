<?php

namespace BnpFFMpegModule\Factory;

use FFMpeg\FFMpeg;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FFMpegServiceFactory extends FFMpegFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $this->getConfig($serviceLocator);
        $this->processConfig($config, $serviceLocator);

        return FFMpeg::create($config['configuration'], $config['logger'], $config['ffprobe']);
    }
}