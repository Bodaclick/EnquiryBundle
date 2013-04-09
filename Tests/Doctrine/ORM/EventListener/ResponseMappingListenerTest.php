<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Tests\Doctrine\ORM\EventListener;

use Bodaclick\BDKEnquiryBundle\DependencyInjection\InheritanceTypes;

class ResponseMappingListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $entityManager;
    protected $event;
    protected $metadata;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->metadata->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('DefaultResponse'));

        $reflectionClass = $this->getMockBuilder('\ReflectionClass')
                                ->disableOriginalConstructor()
                                ->getMock();

        $reflectionClass->expects($this->any())
            ->method('isSubclassOf')
            ->with($this->equalTo('DefaultResponse'))
            ->will($this->returnValue(false));

        $this->metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($reflectionClass));

        $this->event = new \Doctrine\ORM\Event\LoadClassMetadataEventArgs($this->metadata, $this->entityManager);

    }

    public function testLoadClassMetadataWithMultipleResponses()
    {
        $listener = new \Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener\ResponseMappingListener(
            'DefaultResponse', array('other'=>'OtherResponse'), InheritanceTypes::SINGLE
        );

        $this->metadata->expects($this->once())
                ->method('setInheritanceType')
                ->with($this->equalTo(\Doctrine\ORM\Mapping\ClassMetadata::INHERITANCE_TYPE_SINGLE_TABLE));

        $this->metadata->expects($this->once())
            ->method('addDiscriminatorMapClass')
            ->with($this->equalTo('other'), $this->equalTo('OtherResponse'));

        $listener->loadClassMetadata($this->event);

    }

    public function testLoadClassMetadataWithNoDefaultClass()
    {
        $listener = new \Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener\ResponseMappingListener(
            'OtherDefaultResponse', array('other'=>'OtherResponse'), InheritanceTypes::SINGLE
        );

        $this->metadata->expects($this->never())
            ->method('setInheritanceType');

        $listener->loadClassMetadata($this->event);
    }

    public function testLoadClassMetadataWithEmptyResponses()
    {
        $listener = new \Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener\ResponseMappingListener(
            'DefaultResponse', array(), InheritanceTypes::SINGLE
        );

        $this->metadata->expects($this->never())
            ->method('setInheritanceType');

        $listener->loadClassMetadata($this->event);
    }

    public function testLoadClassMetadataWithBadResponse()
    {
        $this->setExpectedException('\LogicException');

        //The response list is wrong
        $listener = new \Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener\ResponseMappingListener(
            'DefaultResponse', array('other'=>'DefaultResponse'), InheritanceTypes::SINGLE
        );

        $this->metadata->expects($this->never())
            ->method('setInheritanceType');

        $listener->loadClassMetadata($this->event);
    }

}
