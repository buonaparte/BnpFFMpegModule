<?php

namespace BnpFFMpegModule\Factory;

use FFMpeg\FFProbe;
use Psr\Log\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FFMpegFactory
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Configuration key holding ffmpeg configuration
     *
     * @var string
     */
    protected $configKey = 'ffmpeg';

    /**
     * Retrieve configuration for ffmpeg services if any
     *
     * @param ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if (null !== $this->config) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config['bnp-ffmpeg-module'][$this->configKey])) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config['bnp-ffmpeg-module'][$this->configKey];
        return $this->config;
    }

    protected function processConfig(&$config, ServiceLocatorInterface $services)
    {
        if (! isset($config['configuration'])) {
            $config['configuration'] = array();
        }

        $configuration = array_merge($this->getDefaultConfig(), $config['configuration']);

        if (isset($config['logger']) && $services->has($config['logger'])) {
            $config['logger'] = $services->get($config['logger']);
            if (!$config['logger'] instanceof LoggerInterface) {
                $config['logger'] = null;
            }
        } else {
            $config['logger'] = null;
        }

        if (isset($configuration['ffmpeg.timeout'])) {
            $configuration['timeout'] = $configuration['ffmpeg.timeout'];
        }

        if (isset($config['ffprobe']) && $services->has($config['ffprobe'])) {
            $config['ffprobe'] = $services->get($config['ffprobe']);
            if (!$config['ffprobe'] instanceof FFProbe) {
                $config['ffprobe'] = null;
            }
        } else {
            $config['ffprobe'] = null;
        }

        $config['configuration'] = $configuration;
    }

    protected function getDefaultConfig()
    {
        return array(
            'ffmpeg.threads' => 4,
            'ffmpeg.timeout' => 300,
            'ffmpeg.binaries' => array('avconv', 'ffmpeg'),
            'ffprobe.timeout' => 30,
            'ffprobe.binaries' => array('avprobe', 'ffprobe'),
        );
    }
}
