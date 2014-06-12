<?php

namespace BnpFFMpegModuleTest\Factory;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use FFMpeg\FFProbe;
use Monolog\Logger;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class FFProbeServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    protected function setUp()
    {
        $this->services = new ServiceManager(new Config(array(
            'factories' => array(
                'ffprobe' => 'BnpFFMpegModule\Factory\FFProbeServiceFactory'
            )
        )));
    }

    public function testWithoutConfig()
    {
        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');

        $this->assertInstanceOf('FFMpeg\FFProbe', $ffprobe);
        $this->assertEquals(30, $ffprobe->getFFProbeDriver()->getProcessBuilderFactory()->getTimeout());
    }

    public function testWithBasicConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'configuration' => array(
                        'timeout' => 45
                    )
                )
            )
        ));

        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');

        $this->assertInstanceOf('FFMpeg\FFProbe', $ffprobe);
        $this->assertEquals(45, $ffprobe->getFFProbeDriver()->getProcessBuilderFactory()->getTimeout());
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    public function testWithFFProbeBinaryConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'configuration' => array(
                        'ffprobe.binaries' => '/path/to/ffprobe'
                    )
                )
            )
        ));

        try {
            $this->services->get('ffprobe');
        } catch (\Exception $e) {
            $this->assertInstanceOf('FFMpeg\Exception\ExecutableNotFoundException', $e->getPrevious());
        }

        $this->services->get('ffprobe');
    }

    public function testWithLoggerConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'logger' => 'dummy_logger'
                )
            ),
            'log' => array()
        ));
        $this->services->setService('dummy_logger', new Logger('dummy'));

        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');
        $this->assertInstanceOf('Monolog\Logger', $ffprobe->getFFProbeDriver()->getProcessRunner()->getLogger());

        /** @var $logger Logger */
        $logger = $ffprobe->getFFProbeDriver()->getProcessRunner()->getLogger();
        $this->assertEquals('dummy', $logger->getName());
    }

    public function testSilentPassesInvalidLoggerConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'logger' => 'invalid_logger'
                )
            ),
            'log' => array()
        ));
        $this->services->setService('invalid_logger', new \stdClass());

        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');
        $this->assertInstanceOf('Monolog\Logger', $ffprobe->getFFProbeDriver()->getProcessRunner()->getLogger());
    }

    public function testSilentPassesNotExistingLoggerConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'logger' => 'unknown_logger'
                )
            )
        ));

        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');
        $this->assertInstanceOf('Monolog\Logger', $ffprobe->getFFProbeDriver()->getProcessRunner()->getLogger());
    }

    public function testWithCacheConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'cache' => 'dummy_cache'
                )
            )
        ));
        $cache = new ArrayCache();
        $cache->save('dummy_key', 'dummy_data');
        $this->services->setService('dummy_cache', $cache);

        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');
        $this->assertInstanceOf('Doctrine\Common\Cache\Cache', $ffprobe->getCache());

        /** @var $ffprobeCache Cache */
        $ffprobeCache = $ffprobe->getCache();
        $this->assertTrue($ffprobeCache->contains('dummy_key'));
        $this->assertEquals('dummy_data', $ffprobeCache->fetch('dummy_key'));

        $this->assertSame($cache, $ffprobeCache);
    }

    public function testSilentPassesInvalidCacheConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'cache' => 'invalid_cache'
                )
            )
        ));
        $this->services->setService('invalid_cache', new \stdClass());

        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');
        $this->assertInstanceOf('Doctrine\Common\Cache\Cache', $ffprobe->getCache());
    }

    public function testSilentPassesNotExistingCacheConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffprobe' => array(
                    'cache' => 'unknown_cache'
                )
            )
        ));

        /** @var $ffprobe FFProbe */
        $ffprobe = $this->services->get('ffprobe');
        $this->assertInstanceOf('Doctrine\Common\Cache\Cache', $ffprobe->getCache());
    }
}
