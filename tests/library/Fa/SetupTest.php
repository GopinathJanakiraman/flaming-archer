<?php

namespace Fa;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-11-20 at 06:56:07.
 */
class SetupTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var  vfsStreamDirectory
     */
    private $root;
    
    /**
     * Mocked webroot directory
     *
     * @var string Mocked webroot directory
     */
    private $webroot;
    
    /**
     * @var \Composer\Composer
     */
    private $composerMock;
    
    /**
     * @var \Composer\Config
     */
    private $config;
    
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $outputMock;
    
    /**
     * @var \Composer\Script\Event
     */
    private $event;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->root = \vfsStream::setup('dev.flaming-archer');
        $this->webroot = \vfsStream::url('dev.flaming-archer');
        \vfsStream::create(array('config-dist.php' => 'config without secure data'), $this->root);
        \vfsStream::create(array('vendor' => array()), $this->root);
        
        // Replacing default vendor directory with mocked filesystem
        $this->config = new \Composer\Config();
        $this->config->merge(array('config' => array('vendor-dir' => $this->webroot . '/vendor')));
        
        // Setting up Composer environment
        $this->composerMock = $this->getMock('Composer\Composer');
        $inputMock = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $this->outputMock = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $helperMock = $this->getMock('Symfony\Component\Console\Helper\HelperSet');
        $consoleIO = new \Composer\IO\ConsoleIO($inputMock, $this->outputMock, $helperMock);
        $this->event = new \Composer\Script\Event('pre-install-command', $this->composerMock, $consoleIO, true);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers Fa\Setup::createConfig
     */
    public function testCreateConfigConfigNotFound()
    {
        // Confirm mock filesystem doesn't contain config.php
        $this->assertFalse($this->root->hasChild('config.php'));

        $output = array(
            'Reviewing your Flaming Archer environment . . .',
            'Creating config.php by copying config-dist.php . . .',
            "Done! Please edit config.php and add your Flickr API key to 'flickr.api.key' and change 'cookie['secret']'."
        );

        // Configure expectations
        foreach ($output as $index => $message) {
            $this->outputMock->expects($this->at($index))
                    ->method('write')
                    ->with($this->equalTo($message), $this->equalTo(true));
        }

        $this->composerMock->expects($this->once())
                ->method('getConfig')
                ->will($this->returnValue($this->config));

        $result = Setup::createConfig($this->event);
        $this->assertTrue($result);
        $this->assertTrue($this->root->hasChild('config.php'));
    }
    
    /**
     * @covers Fa\Setup::createConfig
     */
    public function testCreateConfigConfigFound()
    {
        // Confirm mock filesystem contains config.php
        \vfsStream::create(array('config.php' => 'config without secure data'), $this->root);
        $this->assertTrue($this->root->hasChild('config.php'));

        $output = array(
            'Reviewing your Flaming Archer environment . . .',
            'Found config.php.'
        );

        // Configure expectations
        foreach ($output as $index => $message) {
            $this->outputMock->expects($this->at($index))
                    ->method('write')
                    ->with($this->equalTo($message), $this->equalTo(true));
        }

        $this->composerMock->expects($this->once())
                ->method('getConfig')
                ->will($this->returnValue($this->config));

        $result = Setup::createConfig($this->event);
        $this->assertTrue($result);
    }

    /**
     * @covers Fa\Setup::prepareDatabase
     * @todo   Implement testPrepareDatabase().
     */
    public function testPrepareDatabase()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
