<?php

namespace BDK\EnquiryBundle\Tests\Model;

class AnswerTest extends \PHPUnit_Framework_TestCase
{
    protected $answer;

    public function setUp()
    {
        $this->answer = $this->getMockBuilder('BDK\EnquiryBundle\Model\Answer')
                             ->getMockForAbstractClass();

        $user = $this->getMockForAbstractClass('Symfony\Component\Security\Core\User\UserInterface');

        $user->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('test'));

        $this->answer->setUser($user);
        $this->answer->setResponses(new \Doctrine\Common\Collections\ArrayCollection(array('test')));
    }

    public function testNormalize()
    {
        $normalizer = $this->getMock('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $normalizer->expects($this->any())
            ->method('normalize')
            ->will($this->returnValue(array('test')));

        $normalized = $this->answer->normalize($normalizer);

        $this->assertInternalType('array', $normalized);
        $this->assertArrayHasKey('answer', $normalized);
        $this->assertInternalType('array', $normalized['answer']);
        $this->assertArrayHasKey('user', $normalized['answer']);
        $this->assertArrayHasKey('id', $normalized['answer']['user']);
        $this->assertEquals($normalized['answer']['user']['id'], 'none');
        $this->assertArrayHasKey('username', $normalized['answer']['user']);
        $this->assertEquals($normalized['answer']['user']['username'], 'test');
        $this->assertArrayHasKey('responses', $normalized['answer']);
        $this->assertCount(1, $normalized['answer']['responses']);

    }

    public function testDenormalize()
    {
        $denormalizer = $this->getMock('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $denormalizer->expects($this->any())
            ->method('denormalize')
            ->will($this->returnValue($this->getMock('BDK\EnquiryBundle\Model\Response')));

        $data = array('answer'=>array('responses'=>array('response')));
        $this->answer->denormalize($denormalizer, $data);

        //Assert with count 2, because there are another response element set in setUp()
        $this->assertCount(2, $this->answer->getResponses());
    }

}
