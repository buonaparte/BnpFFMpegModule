<?php

namespace BnpFFMpegModuleTest;

use BnpFFMpegModule\Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Module
     */
    protected $module;

    protected function setUp()
    {
        $this->module = new Module();
    }

    public function testAutoloadReturnsValidAutoloadConfigArray()
    {
        $config = $this->module->getAutoloaderConfig();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('Zend\Loader\StandardAutoloader', $config);
        $this->assertInternalType('array', $config['Zend\Loader\StandardAutoloader']);
        $this->assertArrayHasKey('namespaces', $config['Zend\Loader\StandardAutoloader']);
    }

    /**
     * @depends BnpFFMpegModuleTest\ModuleTest::testAutoloadReturnsValidAutoloadConfigArray
     */
    public function testAutoloadReturnsNamespaceAccordingToDirectory()
    {
        $config = $this->module->getAutoloaderConfig();

        $this->assertNotEmpty($config['Zend\Loader\StandardAutoloader']['namespaces']);

        $autoloadConfig = $config['Zend\Loader\StandardAutoloader']['namespaces'];
        $this->assertArrayHasKey('BnpFFMpegModule', $autoloadConfig);
        $this->assertEquals(
            basename(__DIR__.'/../../src/BnpFFMpegModule'),
            basename($autoloadConfig['BnpFFMpegModule']));
    }
}
