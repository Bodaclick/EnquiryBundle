<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Tests\Controller;

use BDK\EnquiryBundle\Model\EnquiryManager;
use Symfony\Component\HttpFoundation\Request;

class EnquiryControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $enquiryManager;
    protected $objectManager;
    protected $enquiry;
    protected $json;
    protected $controller;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnCallback(array($this, 'createClassMetadata')));

        $this->enquiry = $this->getMockForAbstractClass('BDK\EnquiryBundle\Model\Enquiry');

        $answer = $this->createAnswer();

        $this->enquiry->setName('test');
        $this->enquiry->addAnswer($answer);

        $this->json = '{"enquiry":{"id":null,"name":"test","answers":[{"answer":{"responses":[{"key":"test","value":"test"}]}}]}}';

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $defaultClass = get_class($answer->getResponses()->first());
        $this->enquiryManager = new EnquiryManager($this->objectManager, $dispatcher, $defaultClass , array() );

        $this->controller = new \BDK\EnquiryBundle\Controller\EnquiryController(
            $this->enquiryManager,
            $this->getMockForAbstractClass('Symfony\Component\Security\Core\SecurityContextInterface')
        );

    }

    public function testGetEnquiry()
    {
        $this->setUpObjectRepository();

        $response = $this->controller->getEnquiryAction(1, Request::create('/enquiry/1', 'GET', array('_format'=>'json')));

        $this->assertJsonStringEqualsJsonString($this->json, $response->getContent());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testGetEnquiryByName()
    {
        $this->setUpObjectRepository();

        $response = $this->controller->getEnquiryByNameAction(
            'name',
            Request::create('/enquiry/by_name/name', 'GET', array('_format'=>'json'))
        );

        $this->assertJsonStringEqualsJsonString($this->json, $response->getContent());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testSaveAnswer()
    {
        $this->setUpObjectRepository();


        $responses = '{"answer":{"responses":[{"key":"test","value":"test"}]}}';

        $response = $this->controller->saveAnswerAction(
            1,
            Request::create(
                '/answer/save/1',
                'POST',
                array('_format'=>'json'),
                array(),
                array(),
                array('CONTENT_TYPE'=> 'application/json'),
                $responses
            )
        );

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEmpty($response->getContent());

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

    public function createClassMetadata($classname)
    {
        $entityClasses = array('BDKEnquiryBundle:Enquiry', 'BDKEnquiryBundle:Answer');

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
