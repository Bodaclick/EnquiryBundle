<?php

namespace BDK\EnquiryBundle\Tests\Model;

use BDK\EnquiryBundle\Model\EnquiryManager;

class EnquiryManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $enquiryManager;
    protected $objectManager;
    protected $about;
    protected $enquiry;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnCallback(array($this, 'createClassMetadata')));

        $this->about = $this->getMock('BDK\EnquiryBundle\Model\AboutInterface');

        $this->enquiry = $this->getMockForAbstractClass('BDK\EnquiryBundle\Model\Enquiry');

        $answer = $this->createAnswer();

        $this->enquiry->setName('test');
        $this->enquiry->addAnswer($answer);

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $defaultClass = get_class($answer->getResponses()->first());
        $this->enquiryManager = $this->createEnquiryManager($this->objectManager, $dispatcher, $defaultClass );
        $this->enquiryManager->setLogger($this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface'));
    }

    public function testSaveEnquiry()
    {
        $form = 'testForm';
        $name = 'testName';

        $this->objectManager->expects($this->exactly(2))->method('persist');

        $enquiry = $this->enquiryManager->saveEnquiry($this->about, $form, $name);

        $this->assertInstanceOf('BDK\EnquiryBundle\Model\Enquiry', $enquiry);
        $this->assertEquals($form, $enquiry->getForm());
        $this->assertEquals($name, $enquiry->getName());
    }

    public function testGetEnquiryFor()
    {
        $this->setUpEnquiryRepository();

        $enquiry = $this->enquiryManager->getEnquiryFor($this->about);

        $this->assertEquals($enquiry->getName(), 'test');
    }

    public function testGetEnquiriesFor()
    {
        $this->setUpEnquiryRepository();

        $enquiries = $this->enquiryManager->getEnquiriesFor($this->about);

        $this->assertCount(1, $enquiries);
    }

    public function testGetEnquiriesForFormatted()
    {
        $this->setUpEnquiryRepository();

        $enquiries = $this->enquiryManager->getEnquiriesFor($this->about, 'json');

        $test = json_decode($enquiries);

        $this->assertEquals(json_last_error(), JSON_ERROR_NONE);
        $this->assertCount(1, $test);
        $this->assertEquals($test[0]->enquiry->name, 'test');

    }

    public function testGetEnquiryByName()
    {
        $this->setUpObjectRepository();

        $enquiry = $this->enquiryManager->getEnquiryByName('test');

        $this->assertEquals($enquiry->getName(), 'test');
    }

    public function testGetEnquiryById()
    {
        $this->setUpObjectRepository();

        $enquiry = $this->enquiryManager->getEnquiry('test');

        $this->assertEquals($enquiry->getName(), 'test');
    }

    public function testDeleteEnquiry()
    {
        $this->objectManager->expects($this->once())->method('remove')->with($this->equalTo($this->enquiry));

        $this->enquiryManager->deleteEnquiry($this->enquiry);
    }

    public function testSaveAnswer()
    {
        $user = $this->getMockForAbstractClass('Symfony\Component\Security\Core\User\UserInterface');
        $answer = $this->createAnswer();

        $this->objectManager->expects($this->once())->method('persist')->with($this->equalTo($this->enquiry));

        $this->enquiryManager->saveAnswer($this->enquiry, $answer, $user);

        $this->assertEquals($user, $answer->getUser());
        $this->assertCount(2, $this->enquiry->getAnswers());
        $this->assertEquals($answer, $this->enquiry->getAnswers()->offsetGet(1));
    }

    public function testSaveAnswerUsingName()
    {
        $this->setUpObjectRepository();

        $user = $this->getMockForAbstractClass('Symfony\Component\Security\Core\User\UserInterface');
        $answer = $this->createAnswer();

        $this->objectManager->expects($this->once())->method('persist')->with($this->equalTo($this->enquiry));

        $this->enquiryManager->saveAnswer('testEnquiry', $answer, $user);

        $this->assertEquals($user, $answer->getUser());
        $this->assertCount(2, $this->enquiry->getAnswers());
        $this->assertEquals($answer, $this->enquiry->getAnswers()->offsetGet(1));
    }

    public function testSaveAnswerWithBadEnquiry()
    {

        $this->setExpectedException('\InvalidArgumentException');

        $this->enquiryManager->saveAnswer($this->createAnswer(), $this->createAnswer());

    }

    public function testSaveResponsesWithArrayOfResponseObjects()
    {
        $responses = array($this->createResponse(), $this->createResponse());

        $this->objectManager->expects($this->once())->method('persist')->with($this->equalTo($this->enquiry));

        $this->enquiryManager->saveResponses($this->enquiry, $responses);

        $this->assertCount(2, $this->enquiry->getAnswers());
        $this->assertCount(2, $this->enquiry->getAnswers()->last()->getResponses());

    }

    public function testSaveResponsesWithArrayOfKeyValue()
    {
        $responses = array('key'=>'value', 'key2'=>'value2');

        $this->objectManager->expects($this->once())->method('persist')->with($this->equalTo($this->enquiry));

        $this->enquiryManager->saveResponses($this->enquiry, $responses);

        $this->assertCount(2, $this->enquiry->getAnswers());
        $this->assertCount(2, $this->enquiry->getAnswers()->last()->getResponses());

    }

    public function testSaveResponsesInJsonFormat()
    {
        $responses = '{"answer":{"responses":[{"key":"test","value":"test"}]}}';

        $this->objectManager->expects($this->once())->method('persist')->with($this->equalTo($this->enquiry));

        $this->enquiryManager->saveResponses($this->enquiry, $responses);

        $this->assertCount(2, $this->enquiry->getAnswers());
        $this->assertCount(1, $this->enquiry->getAnswers()->last()->getResponses());
    }

    public function testSaveResponsesInBadJsonFormat()
    {
        $responses = '{}';

        $this->setExpectedException('Symfony\Component\Serializer\Exception\InvalidArgumentException');

        $this->enquiryManager->saveResponses($this->enquiry, $responses);

    }

    public function testSaveResponsesWithAnyArray()
    {
        $responses = array(new \stdClass());

        $this->setExpectedException('\InvalidArgumentException');

        $this->enquiryManager->saveResponses($this->enquiry, $responses);

    }

    protected function createAnswer()
    {
        $answer = $this->getMockForAbstractClass('BDK\EnquiryBundle\Model\Answer');

        $answer->addResponse($this->createResponse());

        return $answer;
    }

    protected function createResponse()
    {
        $response = $this->getMockForAbstractClass('BDK\EnquiryBundle\Model\Response');

        $response->setKey('test');
        $response->setValue('test');

        return $response;
    }

    protected function setUpEnquiryRepository()
    {
        $repository = $this->getMock('BDK\EnquiryBundle\Model\EnquiryRepositoryInterface');
        $repository->expects($this->any())
            ->method('getEnquiriesFor')
            ->will($this->returnValue(array($this->enquiry)));

        $this->objectManager->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('BDKEnquiryBundle:Enquiry'))
            ->will($this->returnValue($repository));
    }

    protected function setUpObjectRepository()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($this->enquiry));

        $repository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->enquiry));

        $this->objectManager->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('BDKEnquiryBundle:Enquiry'))
            ->will($this->returnValue($repository));
    }

    protected function createEnquiryManager($objectManager, $dispatcher, $defaultClass)
    {
        return new EnquiryManager($objectManager, $dispatcher, $defaultClass , array() );
    }

    public function createClassMetadata($classname)
    {
        $entityClasses = array('BDKEnquiryBundle:Enquiry', 'BDKEnquiryBundle:Answer', get_class($this->about));

        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        if ($classname == 'BDKEnquiryBundle:Enquiry') {
            $class->expects($this->any())
                ->method('getReflectionClass')
                ->will($this->returnValue(new \ReflectionClass($this->enquiry)));
        }

        if ($classname == 'BDKEnquiryBundle:Answer') {
            $answer = $this->createAnswer();
            $class->expects($this->any())
                ->method('getReflectionClass')
                ->will($this->returnValue(new \ReflectionClass($answer)));
            $class->expects($this->any())
                ->method('getName')
                ->will($this->returnValue(get_class($answer)));
        }

        if (in_array($classname, $entityClasses)) {
            return $class;
        } else {
            throw new \Exception('Not an entity class');
        }
    }
}
