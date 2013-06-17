<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Tests\Doctrine\ODM\EventListener;

use BDK\EnquiryBundle\Doctrine\ODM\MongoDB\EventListener\CollectionNamePrefixListener;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

class CollectionNamePrefixListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $documentManager;
    protected $event;
    protected $metadata;
    protected $listener;
    protected $reflectionClass;

    public function setUp()
    {
        $this->documentManager = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->metadata = $this->getMockBuilder('Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo')
                                ->disableOriginalConstructor()
                                ->setMethods(array('getReflectionClass'))
                                ->getMock();

        $this->metadata->collection ='Test';

        $this->reflectionClass = $this->getMockBuilder('\ReflectionClass')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($this->reflectionClass));

        $this->event = new LoadClassMetadataEventArgs($this->metadata, $this->documentManager);

        $this->listener = new CollectionNamePrefixListener('prefix');

    }

    public function testLoadClassMetadataWithClassInTheNamespace()
    {
        $this->reflectionClass->expects($this->any())
            ->method('getNamespaceName')
            ->will($this->returnValue('BDK\EnquiryBundle\Document'));

        $this->listener->loadClassMetadata($this->event);

        $this->assertEquals($this->metadata->getCollection(), 'prefixTest');
    }

    public function testLoadClassMetadataWithClassNotInTheNamespace()
    {
        $this->reflectionClass->expects($this->any())
            ->method('getNamespaceName')
            ->will($this->returnValue('Test'));

        $this->listener->loadClassMetadata($this->event);

        //The name remains untouched
        $this->assertEquals($this->metadata->getCollection(), 'Test');
    }
}
