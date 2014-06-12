<?php

namespace BnpFFMpegModule\Factory;

use Doctrine\Common\Cache\Cache;
use FFMpeg\FFProbe;
use Psr\Log\LoggerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FFProbeServiceFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $configKey = 'ffprobe';

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

        return FFProbe::create($config['configuration'], $config['logger'], $config['cache']);
    }

    protected function getConfig(ServiceLocatorInterface $services) {
        if (null !== $this->config) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            return $this->config = array();
        }

        $config = $services->get('Config');
        return $this->config = isset($config['bnp-ffmpeg-module'][$this->configKey])
            ? (array) $config['bnp-ffmpeg-module'][$this->configKey]
            : array();
    }

    protected function processConfig(&$config, ServiceLocatorInterface $services)
    {
        if (! isset($config['configuration'])) {
            $config['configuration'] = array();
        }

        $config['configuration'] = array_merge($this->getDefaultConfig(), $config['configuration']);

        if (isset($config['logger']) && $services->has($config['logger'])) {
            $config['logger'] = $services->get($config['logger']);
            if (!$config['logger'] instanceof LoggerInterface) {
                $config['logger'] = null;
            }
        } else {
            $config['logger'] = null;
        }

        if (isset($config['cache']) && $services->has($config['cache'])) {
            $config['cache'] = $services->get($config['cache']);
            if (!$config['cache'] instanceof Cache) {
                $config['cache'] = null;
            }
        } else {
            $config['cache'] = null;
        }
    }

    protected function getDefaultConfig()
    {
        return array(
            'timeout' => 30,
            'ffprobe.binaries' => array('avprobe', 'ffprobe'),
        );
    }
}
