<?php

namespace BnpFFMpegModuleTest\Factory;

use FFMpeg\FFMpeg;
use Monolog\Logger;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class FFMpegServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    public function setUp()
    {
        $this->services = new ServiceManager(new Config(array(
            'factories' => array(
                'ffmpeg' => 'BnpFFMpegModule\Factory\FFMpegServiceFactory'
            )
        )));
    }

    public function testWithoutConfig()
    {
        /** @var $ffmpeg FFMpeg */
        $ffmpeg = $this->services->get('ffmpeg');

        $this->assertInstanceOf('FFMpeg\FFMpeg', $ffmpeg);
        $this->assertInstanceOf('FFMpeg\FFProbe', $ffmpeg->getFFProbe());

        $this->assertEquals(4, $ffmpeg->getFFMpegDriver()->getConfiguration()->get('ffmpeg.threads'));
        $this->assertEquals(300, $ffmpeg->getFFMpegDriver()->getProcessBuilderFactory()->getTimeout());
    }

    public function testBasicWithConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffmpeg' => array(
                    'configuration' => array(
                        'ffmpeg.threads' => 12,
                        'ffmpeg.timeout' => 10666,
                    )
                )
            )
        ));

        /** @var $ffmpeg FFMpeg */
        $ffmpeg = $this->services->get('ffmpeg');

        $this->assertInstanceOf('FFMpeg\FFMpeg', $ffmpeg);
        $this->assertInstanceOf('FFMpeg\FFProbe', $ffmpeg->getFFProbe());

        $this->assertEquals(12, $ffmpeg->getFFMpegDriver()->getConfiguration()->get('ffmpeg.threads'));
        $this->assertEquals(10666, $ffmpeg->getFFMpegDriver()->getProcessBuilderFactory()->getTimeout());
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    public function testWithFFMpegBinaryConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffmpeg' => array(
                    'configuration' => array(
                        'ffmpeg.binaries' => '/path/to/ffmpeg',
                    )
                )
            )
        ));

        try {
            $this->services->get('ffmpeg');
        } catch (\Exception $e) {
            $this->assertInstanceOf('FFMpeg\Exception\ExecutableNotFoundException', $e->getPrevious());
        }

        $this->services->get('ffmpeg');
    }

    public function testWithLoggerConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffmpeg' => array(
                    'logger' => 'dummy_logger'
                )
            ),
            'log' => array()
        ));
        $this->services->setService('dummy_logger', new Logger('dummy'));

        /** @var $ffmpeg FFMpeg */
        $ffmpeg = $this->services->get('ffmpeg');
        $this->assertInstanceOf('Monolog\Logger', $ffmpeg->getFFMpegDriver()->getProcessRunner()->getLogger());

        /** @var $logger Logger */
        $logger = $ffmpeg->getFFMpegDriver()->getProcessRunner()->getLogger();
        $this->assertEquals('dummy', $logger->getName());
    }

    public function testSilentPassesInvalidLoggerConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffmpeg' => array(
                    'logger' => 'invalid_logger'
                )
            ),
            'log' => array()
        ));
        $this->services->setService('invalid_logger', new \stdClass());

        /** @var $ffmpeg FFMpeg */
        $ffmpeg = $this->services->get('ffmpeg');
        $this->assertInstanceOf('Monolog\Logger', $ffmpeg->getFFMpegDriver()->getProcessRunner()->getLogger());
    }

    public function testSilentPassesInvalidFFProbeConfig()
    {
        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffmpeg' => array(
                    'ffprobe' => 'invalid_ffprobe'
                )
            ),
            'log' => array()
        ));
        $this->services->setService('invalid_ffprobe', new \stdClass());

        /** @var $ffmpeg FFMpeg */
        $ffmpeg = $this->services->get('ffmpeg');
        $this->assertInstanceOf('FFMpeg\FFProbe', $ffmpeg->getFFProbe());
    }
}