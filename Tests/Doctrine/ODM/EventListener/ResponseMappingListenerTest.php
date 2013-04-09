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

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

class ResponseMappingListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $documentManager;
    protected $event;
    protected $metadata;

    public function setUp()
    {
        $this->documentManager = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->metadata = $this->getMockBuilder('Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo')
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->metadata->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Bodaclick\BDKEnquiryBundle\Document\Answer'));

        $this->event = new LoadClassMetadataEventArgs($this->metadata, $this->documentManager);

    }

    public function testLoadClassMetadataWithMultipleResponses()
    {
        $listener = new \Bodaclick\BDKEnquiryBundle\Doctrine\ODM\MongoDB\EventListener\ResponseMappingListener(
            'DefaultResponse', array('other'=>'OtherResponse')
        );

        $map = array(
            'name'=>'responses',
            'discriminatorMap'=>array('default'=>'DefaultResponse', 'other'=>'OtherResponse'),
            'strategy'=>'pushAll',
            'discriminatorField'=>'type'
        );

        $this->metadata->expects($this->once())
            ->method('mapManyEmbedded')
            ->with($this->equalTo($map));

        $listener->loadClassMetadata($this->event);

    }

    public function testLoadClassMetadataWithEmptyResponses()
    {
        $listener = new \Bodaclick\BDKEnquiryBundle\Doctrine\ODM\MongoDB\EventListener\ResponseMappingListener(
            'DefaultResponse', array()
        );

        $this->metadata->expects($this->never())
            ->method('mapManyEmbedded');

        $listener->loadClassMetadata($this->event);
    }

}
