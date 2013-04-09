<?php

namespace BDK\EnquiryBundle\Tests\Model;

use BDK\EnquiryBundle\Model\ResponseNormalizer;

class ResponseNormalizerTest extends \PHPUnit_Framework_TestCase
{

    protected $response;

    public function setUp()
    {
        $this->response = $this->getMock('BDK\EnquiryBundle\Model\Response', array('getKey', 'getValue'));
        $this->response->expects($this->any())
            ->method('getKey')
            ->will($this->returnValue('key'));
        $this->response->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('value'));
    }

    public function testSupportsNormalization()
    {
        $normalizer = new ResponseNormalizer(get_class($this->response), array());

        $support = $normalizer->supportsNormalization($this->response);

        $this->assertTrue($support);
    }

    public function testSupportsNormalizationNoResponseClass()
    {
        $normalizer = new ResponseNormalizer(get_class($this->response), array());

        $support = $normalizer->supportsNormalization(null);

        $this->assertFalse($support);
    }

    public function testSupportsDenormalization()
    {
        $normalizer = new ResponseNormalizer(get_class($this->response), array());

        $support = $normalizer->supportsDenormalization(array(), 'Response');

        $this->assertTrue($support);
    }

    public function testSupportsDenormalizationNoResponseType()
    {
        $normalizer = new ResponseNormalizer(get_class($this->response), array());

        $support = $normalizer->supportsDenormalization(array(), 'wrong');

        $this->assertFalse($support);
    }

    public function testNormalize()
    {
        $normalizer = new ResponseNormalizer(get_class($this->response), array());

        $normalized = $normalizer->normalize($this->response);

        $this->assertInternalType('array', $normalized);
        $this->assertArrayHasKey('key', $normalized);
        $this->assertArrayHasKey('value', $normalized);
        $this->assertEquals($normalized['key'], 'key');
        $this->assertEquals($normalized['value'], 'value');

    }

    public function testNormalizeWithResponseSubclass()
    {

        $normalizer = new ResponseNormalizer('DefaultResponse', array('subclass'=>get_class($this->response)));

        $normalized = $normalizer->normalize($this->response);

        $this->assertInternalType('array', $normalized);
        $this->assertArrayHasKey('key', $normalized);
        $this->assertArrayHasKey('value', $normalized);
        $this->assertEquals($normalized['key'], 'key');
        $this->assertEquals($normalized['value'], 'value');
        $this->assertArrayHasKey('type', $normalized);
        $this->assertEquals($normalized['type'], 'subclass');
    }

    public function testNormalizeWithBadSubclass()
    {
        $this->setExpectedException('Symfony\Component\Serializer\Exception\InvalidArgumentException');

        $normalizer = new ResponseNormalizer('DefaultResponse', array());

        $normalizer->normalize($this->response);
    }

    public function testDenormalize()
    {
        //Use of a mock object to mock the creation of Response classes, the rest remains untouched
        $normalizer = $this->getMock(
            'BDK\EnquiryBundle\Model\ResponseNormalizer',
            array('createResponseClass'),
            array(get_class($this->response), array())
        );

        $response = $this->getMockForAbstractClass('BDK\EnquiryBundle\Model\Response');

        $normalizer->expects($this->any())
            ->method('createResponseClass')
            ->will($this->returnValue($response));

        $data = array('key'=>'key2', 'value'=>'test2');
        $object = $normalizer->denormalize($data, 'Response');

        $this->assertEquals($object->getKey(), 'key2');
        $this->assertEquals($object->getValue(), 'test2');
    }

    public function testDenormalizeWithResponseType()
    {
        //Use of a mock object to mock the creation of Response classes, the rest remains untouched
        //The class names in method parameters are dummy, as they are not used or its construction is mocked
        $normalizer = $this->getMock(
            'BDK\EnquiryBundle\Model\ResponseNormalizer',
            array('createResponseClass'),
            array('DefaultResponse', array('mytype'=>'Dummy'))
        );

        $response = $this->getMockForAbstractClass('BDK\EnquiryBundle\Model\Response');
        $normalizer->expects($this->any())
            ->method('createResponseClass')
            ->will($this->returnValue($response));

        $data = array('type'=>'mytype', 'key'=>'key2', 'value'=>'test2');
        $object = $normalizer->denormalize($data, 'Response');

        $this->assertEquals($object->getKey(), 'key2');
        $this->assertEquals($object->getValue(), 'test2');
    }

    public function testDenormalizeWithWrongType()
    {

        $this->setExpectedException('\Symfony\Component\Serializer\Exception\UnexpectedValueException');

        $normalizer = new ResponseNormalizer('DefaultResponse', array('subclass'=>get_class($this->response)));

        $data = array('type'=>'mytype', 'key'=>'key2', 'value'=>'test2');
        $object = $normalizer->denormalize($data, 'Response');
    }

}
