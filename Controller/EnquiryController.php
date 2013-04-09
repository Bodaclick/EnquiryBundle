<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Controller;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Bodaclick\BDKEnquiryBundle\Model\EnquiryManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Controller class
 */
class EnquiryController
{
    //Enquiry Manager
    protected $em;

    //Security Context
    protected $sc;

    /**
     * Constructor
     *
     * @param \Bodaclick\BDKEnquiryBundle\Model\EnquiryManager $em
     */
    public function __construct(EnquiryManager $em, SecurityContextInterface $sc)
    {
        $this->em = $em;
        $this->sc = $sc;
    }

    /**
     * Action to get an enquiry by id, in json or xml format
     *
     * @param $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws
     */
    public function getEnquiryAction($id, Request $request)
    {

        //Get the enquiry in the format requested (json or xml)
        $enquiry = $this->em->getEnquiry($id, $request->getRequestFormat());

        //If not found, return a 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        return new Response($enquiry, 200, array('Content-Type'=>'application/json'));
    }

    /**
     * Action to get an enquiry by name, in json or xml format
     *
     * @param $name
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getEnquiryByNameAction($name, Request $request)
    {

        //Get the enquiry in the format requested (json or xml)
        $enquiry = $this->em->getEnquiryByName($name, $request->getRequestFormat());

        //If not found, return a 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        return new Response($enquiry, 200, array('Content-Type'=>'application/json'));
    }

    /**
     * Save an user response/s to an existing enquiry specified by its id
     * The responses are given in the content of a POST request, in the following format:
     * "answer": { "responses": [ {"key"="examplekey","value"="examplevalue",...}, {...},...]}
     *
     * @param $enquiryId
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveAnswerAction($enquiryId, Request $request)
    {

        //Get the responses in json format from the body of the POST request
        $content = $request->getContent();
        $content_type = $request->headers->get('Content-Type');

        //Check that it's the right content type and the content is not empty
        if (strpos($content_type,'application/json')===false || empty($content)) {
            throw new HttpException(405);
        }

        //Get the enquiry by id, using the service
        $enquiry = $this->em->getEnquiry($enquiryId);

        //If not found, return 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        //Save the answer, using the service
        $this->em->saveResponses($enquiry, $content, $this->getUser());

        //Return an empty response
        return new Response('', 200, array('Content-Type'=>'application/json'));
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * @param string    $message  A message
     * @param \Exception $previous The previous exception
     *
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }


    /**
     * Get a user from the Security Context
     *
     * @return mixed
     *
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    public function getUser()
    {

        if (null === $token = $this->sc->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
