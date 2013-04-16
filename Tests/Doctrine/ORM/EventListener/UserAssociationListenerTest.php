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

class UserAssociationListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $entityManager;
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
            ->will($this->returnValue('BDK\EnquiryBundle\Entity\Answer'));

    }

    public function testLoadClassMetadata()
    {
        $event = new \Doctrine\ORM\Event\LoadClassMetadataEventArgs($this->metadata, $this->entityManager);

        $listener = new \BDK\EnquiryBundle\Doctrine\ORM\EventListener\UserAssociationListener('UserClass');

        $this->metadata->expects($this->once())
                       ->method('mapOneToOne');

        $listener->loadClassMetadata($event);
    }

}
