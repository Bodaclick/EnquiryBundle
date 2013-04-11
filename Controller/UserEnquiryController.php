<?php

namespace BDK\EnquiryBundle\Controller;

use BDK\EnquiryBundle\Form\Handler\EnquiryFormHandler;
use BDK\EnquiryBundle\Form\Type\EnquiryFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @author    Ignacio Velazquez <ignacio.velazquez@bodaclick.com>
 * @copyright 2012 Bodaclick S.A.
 * @Route("/user")
 */
class UserEnquiryController extends Controller
{
    /**
     * Finds the profile's address.
     *
     * @param string $username Username or Email
     *
     * @Route("/{username}/enquiries.{_format}", name="bdk_enquiry_user_list", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"GET"})
     */
    public function getEnquiriesAction(Request $request, $username)
    {
        $enquiriesArray = new ArrayCollection();
        $serializer = $this->get('serializer');
        $token = $this->get('security.context')->getToken();
        $em = $this->container->get('bdk.enquiry.manager');

        if (
            null === $token ||
            ($token->getUser()->getUsername() !== $username &&
            !$token->getUser()->hasRole('ROLE_ADMIN'))
        ) {
            return new Response(
                null,
                403,
                array('Content-Type' => "application/{$request->getRequestFormat()}")
            );
        }

        $enquiries = $em->getRepository()->findByUser($username);

        foreach ($enquiries as $enquiry) {
            $enquiriesArray->add($enquiry);
        }

        // Query parameters
        $type = $request->query->get('type');
        $about = $request->query->get('about');

        $enquiries = $em->getRepository()->findAll();

        foreach ($enquiries as $enquiry) {
            $enquiriesArray->add($enquiry);
        }

        // Applying filters
        if (null !== $type) {
            $enquiriesArray = $enquiriesArray->filter(
                function ($enquiry) use ($type) {
                    return $type === $enquiry->getType();
                }
            );
        }

        if (null !== $about) {
            $enquiriesArray = $enquiriesArray->filter(
                function ($enquiry) use ($about) {
                    return $about === $enquiry->getAbout();
                }
            );
        }

        return new Response(
            $serializer->serialize($enquiriesArray, $request->getRequestFormat()),
            200,
            array('Content-Type' => "application/{$request->getRequestFormat()}")
        );
    }

    /**
     * Action to create an enquiry
     * @Route("/{username}/enquiries.{_format}", name="bdk_enquiry_user_post", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"POST"})
     */
    public function postEnquiryAction(Request $request, $username)
    {
        $serializer = $this->get('serializer');
        $token = $this->get('security.context')->getToken();
        $em = $this->container->get('bdk.enquiry.manager');

        if (
            null === $token ||
            ($token->getUser()->getUsername() !== $username &&
            !$token->getUser()->hasRole('ROLE_ADMIN'))
        ) {
            return new Response(
                null,
                403,
                array('Content-Type' => "application/{$request->getRequestFormat()}")
            );
        }

        $enquiry = $em->create();
        $enquiry->setUser($token->getUser());

        $form = $this->createForm(new EnquiryFormType(), $enquiry);
        $formHandler = new EnquiryFormHandler($form, $request->request->all(), $em);

        if ($formHandler->process()) {
            return new Response(
                $serializer->serialize($form->getData(), $request->getRequestFormat()),
                201,
                array('Content-Type' => "application/{$request->getRequestFormat()}")
            );
        }

        return new Response(
            $serializer->serialize($formHandler->getErrorsArray(), $request->getRequestFormat()),
            400,
            array('Content-Type' => "application/{$request->getRequestFormat()}")
        );
    }


    /**
     * Action to create an enquiry
     * @Route("/{username}/enquiries/{id}.{_format}", name="bdk_enquiry_user_put", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"PUT"})
     */
    public function putEnquiryAction(Request $request, $username, $id)
    {
        $serializer = $this->get('serializer');
        $token = $this->get('security.context')->getToken();
        $em = $this->container->get('bdk.enquiry.manager');

        if (
            null === $token ||
            ($token->getUser()->getUsername() !== $username &&
            !$token->getUser()->hasRole('ROLE_ADMIN'))
        ) {
            return new Response(
                null,
                403,
                array('Content-Type' => "application/{$request->getRequestFormat()}")
            );
        }

        $enquiry = $em->getRepository()->findOneById($id);

        if (null === $enquiry) {
            return new Response(null, 404, array('Content-Type' => "application/{$request->getRequestFormat()}"));
        }

        $form = $this->createForm(new EnquiryFormType(), $enquiry);
        $formHandler = new EnquiryFormHandler($form, $request->request->all(), $em);

        if ($formHandler->process()) {
            return new Response(
                $serializer->serialize($form->getData(), $request->getRequestFormat()),
                201,
                array('Content-Type' => "application/{$request->getRequestFormat()}")
            );
        }

        return new Response(
            $serializer->serialize($formHandler->getErrorsArray(), $request->getRequestFormat()),
            400,
            array('Content-Type' => "application/{$request->getRequestFormat()}")
        );
    }
}
