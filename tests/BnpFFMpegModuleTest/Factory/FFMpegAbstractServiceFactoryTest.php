<?php

namespace BnpFFMpegModuleTest\Factory;

use FFMpeg\FFMpeg;
use Monolog\Logger;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class FFMpegAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    public function setUp()
    {
        $this->services = new ServiceManager(new Config(array(
            'abstract_factories' => array('BnpFFMpegModule\Factory\FFMpegAbstractServiceFactory')
        )));

        $this->services->setService('Config', array(
            'bnp-ffmpeg-module' => array(
                'ffmpeg' => array(
                    'FFMpeg\MultiThreaded' => array(
                        'configuration' => array(
                            'ffmpeg.threads' => 2,
                        )
                    ),
                    'FFMpeg\SingleThreaded' => array(
                        'configuration' => array(
                            'ffmpeg.threads' => 1,
                        )
                    ),
                )
            )
        ));
    }

    /**
     * @return array
     */
    public function providerValidFFMpegService()
    {
        return array(
            array('FFMpeg\SingleThreaded'),
            array('FFMpeg\MultiThreaded'),
        );
    }

    /**
     * @return array
     */
    public function providerInvalidFFMpegService()
    {
        return array(
            array('Application\Unknown'),
            array('Application\SingleThreaded'),
            array('FFMpeg\MultiThreaded\FFMpeg'),
        );
    }

    public function testAllowsMultipleInstancesWithDifferentConfig()
    {
        /** @var $single FFMpeg */
        $single = $this->services->get('FFMpeg\SingleThreaded');
        /** @var $multi FFMpeg */
        $multi = $this->services->get('FFMpeg\MultiThreaded');

        $this->assertInstanceOf('FFMpeg\FFMpeg', $single);
        $this->assertInstanceOf('FFMpeg\FFMpeg', $multi);

        $this->assertEquals(1, $single->getFFMpegDriver()->getConfiguration()->get('ffmpeg.threads'));
        $this->assertEquals(2, $multi->getFFMpegDriver()->getConfiguration()->get('ffmpeg.threads'));
    }

    public function testAllowsMultipleInstancesWithSameLoggerConfig()
    {
        $allowOverride = $this->services->getAllowOverride();
        $this->services->setAllowOverride(true);
        $this->services->setService('Config', array_merge_recursive(
            $this->services->get('Config'),
            array(
                'bnp-ffmpeg-module' => array(
                    'ffmpeg' => array(
                        'FFMpeg\MultiThreaded' => array(
                            'logger' => 'dummy_logger'
                        ),
                        'FFMpeg\SingleThreaded' => array(
                            'logger' => 'dummy_logger'
                        ),
                    )
                )
            )
        ));
        $this->services->setAllowOverride($allowOverride);

        $this->services->setService('dummy_logger', $logger = new Logger('dummy'));

        /** @var $single FFMpeg */
        $single = $this->services->get('FFMpeg\SingleThreaded');
        /** @var $multi FFMpeg */
        $multi = $this->services->get('FFMpeg\MultiThreaded');

        /** @var $firstLogger Logger */
        $firstLogger = $single->getFFMpegDriver()->getProcessRunner()->getLogger();
        /** @var $secondLogger Logger */
        $secondLogger = $multi->getFFMpegDriver()->getProcessRunner()->getLogger();

        $this->assertEquals('dummy', $firstLogger->getName());
        $this->assertEquals('dummy', $secondLogger->getName());

        $this->assertSame($logger, $firstLogger);
        $this->assertSame($logger, $secondLogger);
    }

    public function testAllowsMultipleInstancesWithSameFFProbeConfig()
    {
        $allowOverride = $this->services->getAllowOverride();
        $this->services->setAllowOverride(true);
        $this->services->setService('Config', array_merge_recursive(
            $this->services->get('Config'),
            array(
                'bnp-ffmpeg-module' => array(
                    'ffmpeg' => array(
                        'FFMpeg\MultiThreaded' => array(
                            'ffprobe' => 'ffprobe_service'
                        ),
                        'FFMpeg\SingleThreaded' => array(
                            'ffprobe' => 'ffprobe_service'
                        ),
                    ),
                    'ffprobe' => array(
                        'configuration' => array(
                            'timeout' => 4242
                        )
                    )
                )
            )
        ));
        $this->services->setAllowOverride($allowOverride);

        $this->services->setFactory('ffprobe_service', 'BnpFFMpegModule\Factory\FFProbeServiceFactory');


        /** @var $single FFMpeg */
        $single = $this->services->get('FFMpeg\SingleThreaded');
        /** @var $multi FFMpeg */
        $multi = $this->services->get('FFMpeg\MultiThreaded');

        $this->assertEquals(4242, $single->getFFProbe()->getFFProbeDriver()->getProcessBuilderFactory()->getTimeout());
        $this->assertEquals(4242, $multi->getFFProbe()->getFFProbeDriver()->getProcessBuilderFactory()->getTimeout());
        $this->assertSame($single->getFFProbe(), $multi->getFFProbe());
    }

    /**
     * @param string $service
     * @dataProvider providerValidFFMpegService
     */
    public function testValidFFMpegService($service)
    {
        $actual = $this->services->get($service);
        $this->assertInstanceOf('FFMpeg\FFMpeg', $actual);
    }

    /**
     * @param string $service
     * @dataProvider providerInvalidFFMpegService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testInvalidFFMpegService($service)
    {
        $this->services->get($service);
    }
}