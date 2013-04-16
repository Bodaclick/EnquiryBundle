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

class TableNamePrefixListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $entityManager;
    protected $event;
    protected $metadata;
    protected $listener;
    protected $reflectionClass;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
                                ->disableOriginalConstructor()
                                ->setMethods(array('getReflectionClass'))
                                ->getMock();

        $this->metadata->table = array('name'=>'Test');

        $this->reflectionClass = $this->getMockBuilder('\ReflectionClass')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($this->reflectionClass));

        $this->event = new \Doctrine\ORM\Event\LoadClassMetadataEventArgs($this->metadata, $this->entityManager);

        $this->listener = new \BDK\EnquiryBundle\Doctrine\ORM\EventListener\TableNamePrefixListener('prefix');

    }

    public function testLoadClassMetadataWithClassInTheNamespace()
    {
        $this->reflectionClass->expects($this->any())
            ->method('getNamespaceName')
            ->will($this->returnValue('BDK\EnquiryBundle\Entity'));

        $this->listener->loadClassMetadata($this->event);

        $this->assertEquals($this->metadata->getTableName(), 'prefixTest');
    }

    public function testLoadClassMetadataWithClassNotInTheNamespace()
    {
        $this->reflectionClass->expects($this->any())
            ->method('getNamespaceName')
            ->will($this->returnValue('Test'));

        $this->listener->loadClassMetadata($this->event);

        //The name remains untouched
        $this->assertEquals($this->metadata->getTableName(), 'Test');
    }

}
