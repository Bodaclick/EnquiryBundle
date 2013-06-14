<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Tests\Model;

use BDK\EnquiryBundle\DependencyInjection\BDKEnquiryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class BDKEnquiryExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;

    public function setUp()
    {
        $this->configuration = new ContainerBuilder();
    }

    public function testThrowsExceptionUnlessDatabaseDriverSet()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $loader = new BDKEnquiryExtension();
        $config = $this->getEmptyConfig();
        unset($config['db_driver']);
        $loader->load(array($config), $this->configuration);
    }

    public function testLoadThrowsExceptionUnlessDatabaseDriverIsValid()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $loader = new BDKEnquiryExtension();
        $config = $this->getEmptyConfig();
        $config['db_driver'] = 'wrong';
        $loader->load(array($config), $this->configuration);
    }

    public function testThrowsExceptionIfUnknownParameterSet()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $loader = new BDKEnquiryExtension();
        $config = $this->getEmptyConfig();
        $config['unknown'] = 'test';
        $loader->load(array($config), $this->configuration);
    }

    public function testThrowsExceptionIfUnknownInheritanceSet()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $loader = new BDKEnquiryExtension();
        $config = $this->getFullConfig();
        $config['responses']['mapping']['inheritance'] = 'unknown';
        $loader->load(array($config), new ContainerBuilder());
    }

    public function testThrowsExceptionIfResponseTypeNotSet()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $loader = new BDKEnquiryExtension();
        $config = $this->getFullConfig();
        unset($config['responses']['mapping'][0]['type']);
        $loader->load(array($config), $this->configuration);
    }

    public function testThrowsExceptionIfResponseClassNotSet()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $loader = new BDKEnquiryExtension();
        $config = $this->getFullConfig();
        unset($config['responses']['mapping'][0]['class']);
        $loader->load(array($config), $this->configuration);
    }

    public function testThrowsExceptionIfMappingAlreadySetResponseType()
    {
        $this->setExpectedException('\LogicException');
        $loader = new BDKEnquiryExtension();
        $config = $this->getFullConfig();
        $config['responses']['mapping'][]= array('type'=>'default','class'=>'Response');
        $loader->load(array($config), $this->configuration);
    }

    public function testListenerEnabledIfPrefixSet()
    {
        $loader = new BDKEnquiryExtension();
        $config = $this->getFullConfig();
        $loader->load(array($config), $this->configuration);

        $def = $this->configuration->getDefinition('bdk.db_prefix.listener');

        $this->assertTrue($def->hasTag('doctrine.event_listener'));
        $this->assertContains($config['db_prefix'], $def->getArguments());

    }

    public function testListenerEnabledIfPrefixSetMongo()
    {
        $loader = new BDKEnquiryExtension();
        $config = $this->getEmptyConfig();
        $config['db_prefix'] = 'test';
        $loader->load(array($config), $this->configuration);

        $def = $this->configuration->getDefinition('bdk.db_prefix.listener');

        $this->assertTrue($def->hasTag('doctrine_mongodb.odm.event_listener'));
        $this->assertContains($config['db_prefix'], $def->getArguments());

    }

    public function testListenerEnabledIfResponseMappingSet()
    {
        $loader = new BDKEnquiryExtension();
        $config = $this->getFullConfig();
        $loader->load(array($config), $this->configuration);

        $def = $this->configuration->getDefinition('bdk.response_mapping.listener');

        $this->assertTrue($def->hasTag('doctrine.event_listener'));

    }

    public function testLoggerSet()
    {
        $loader = new BDKEnquiryExtension();
        $config = $this->getFullConfig();
        $loader->load(array($config), $this->configuration);

        $def = $this->configuration->getDefinition('bdk.enquiry.manager');

        $this->assertTrue($def->hasMethodCall('setLogger'));

    }

    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
db_driver: mongodb
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    protected function getFullConfig()
    {
        $yaml = <<<EOF
db_driver: orm
db_prefix: test
responses:
    mapping:
        - { type: test , class: TestResponseClass }
    inheritance: single
logger: logger

EOF;
        $parser = new Parser();

        return  $parser->parse($yaml);
    }
}
