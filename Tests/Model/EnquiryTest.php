<?php

namespace BDK\EnquiryBundle\Tests\Model;

class EnquiryTest extends \PHPUnit_Framework_TestCase
{
    protected $enquiry;
    protected $serializer;

    public function setUp()
    {
        $this->enquiry = $this->getMockBuilder('Bodaclick\BDKEnquiryBundle\Model\Enquiry')
                             ->getMockForAbstractClass();

        $this->enquiry->setForm('form');
        $this->enquiry->setName('name');
        $this->enquiry->setAnswers(new \Doctrine\Common\Collections\ArrayCollection(array('test')));

        $this->serializer = $this->getMock('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->serializer->expects($this->any())
             ->method('normalize')
             ->will($this->returnValue(array('test')));
    }

    public function testNormalize()
    {
        $normalized = $this->enquiry->normalize($this->serializer);

        $this->assertInternalType('array', $normalized);
        $this->assertArrayHasKey('enquiry', $normalized);
        $this->assertInternalType('array', $normalized['enquiry']);
        $this->assertArrayHasKey('form', $normalized['enquiry']);
        $this->assertEquals($normalized['enquiry']['form'], 'form');
        $this->assertArrayHasKey('id', $normalized['enquiry']);
        $this->assertNull($normalized['enquiry']['id']);
        $this->assertArrayHasKey('name', $normalized['enquiry']);
        $this->assertEquals($normalized['enquiry']['name'], 'name');
        $this->assertArrayHasKey('answers', $normalized['enquiry']);
        $this->assertCount(1, $normalized['enquiry']['answers']);

    }

}
