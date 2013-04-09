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

class LinkAboutListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $entityManager;
    protected $listener;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
                         ->disableOriginalConstructor()
                         ->getMock();

        $metadata->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('About'));

        $metadata->expects($this->any())
                ->method('getIdentifierValues')
                ->will($this->returnValue(array('id'=>1)));

        $this->entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadata));

        $aboutMock = $this->getMock('Bodaclick\BDKEnquiryBundle\Model\AboutInterface');

        $this->entityManager->expects($this->any())
            ->method('getReference')
            ->with($this->equalTo('About'),$this->equalTo(array('id'=>1)))
            ->will($this->returnValue($aboutMock));

        $this->listener = new \Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener\LinkAboutListener();
    }

    public function testPrePersist()
    {

        $entity = new \Bodaclick\BDKEnquiryBundle\Entity\Enquiry();

        $event = new \Doctrine\ORM\Event\LifecycleEventArgs($entity, $this->entityManager);

        $this->listener->prePersist($event);

        $about = $event->getEntity()->getAbout();
        $expected = json_encode(array("className"=>'About', "ids"=>array('id'=>1)));

        $this->assertJsonStringEqualsJsonString($about, $expected);

    }

    public function testPreUpdate()
    {
        $entity = new \Bodaclick\BDKEnquiryBundle\Entity\Enquiry();

        $changeSet = array('about'=>array('old', 'new'));

        $event = new \Doctrine\ORM\Event\PreUpdateEventArgs($entity, $this->entityManager, $changeSet);

        $this->listener->preUpdate($event);

        $this->assertEquals($changeSet['about'][1], 'old');

    }

    public function testRegenerateAboutField()
    {
        $entity = new \Bodaclick\BDKEnquiryBundle\Entity\Enquiry();
        $entity->setAbout(json_encode(array("className"=>'About', "ids"=>array('id'=>1))));

        $event = new \Doctrine\ORM\Event\LifecycleEventArgs($entity, $this->entityManager);

        //Use the postLoad method that call protected method regenerateAboutField
        $this->listener->postLoad($event);

        $this->assertInstanceof('Bodaclick\BDKEnquiryBundle\Model\AboutInterface', $entity->getAbout());
    }
}
