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

use BDK\EnquiryBundle\Form\Type\EnquiryFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use BDK\EnquiryBundle\Model\EnquiryManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use BDK\EnquiryBundle\Form\Handler\EnquiryFormHandler;

/**
 * Controller class
 * @Route("/enquiries")
 */
class EnquiryController extends Controller
{
    /**
     * Action to get a list of enquiries in json or xml format. You can apply a type or about filter.
     *
     * @Route(".{_format}", name="bdk_enquiry_list", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"GET"})
     */
    public function getEnquiriesAction(Request $request)
    {
        $enquiriesArray = new ArrayCollection();
        $serializer = $this->get('serializer');
        $em = $this->container->get('bdk.enquiry.manager');

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
     * Action to get an enquiry by id, in json or xml format
     * @Route("/{id}.{_format}", name="bdk_enquiry_get", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"GET"})
     */
    public function getEnquiryAction(Request $request, $id)
    {
        $serializer = $this->get('serializer');
        $em = $this->container->get('bdk.enquiry.manager');

        $enquiry = $em->getRepository()->findOneById($id);

        if (null === $enquiry) {
            return new Response(null, 404, array('Content-Type' => "application/{$request->getRequestFormat()}"));
        }

        return new Response(
            $serializer->serialize($enquiry, $request->getRequestFormat()),
            200,
            array('Content-Type' => "application/{$request->getRequestFormat()}")
        );
    }


    /**
     * Action to create an enquiry
     * @Route(".{_format}", name="bdk_enquiry_post", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"POST"})
     */
    public function postEnquiryAction(Request $request)
    {
        $serializer = $this->get('serializer');
        $em = $this->container->get('bdk.enquiry.manager');

        $form = $this->createForm(new EnquiryFormType(), $em->create());
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
     * @Route("/{id}.{_format}", name="bdk_enquiry_post", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"PUT"})
     */
    public function putEnquiryAction(Request $request, $id)
    {
        $serializer = $this->get('serializer');
        $em = $this->container->get('bdk.enquiry.manager');

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


    /**
     * Delete an enquiry
     *
     * @Route("/{id}.{_format}", name="bdk_enquiry_delete", requirements={"_format" = "json|xml"},
     *      defaults={"_format" = "json"}
     * )
     * @Method({"DELETE"})
     */
    public function deleteEnquiryAction(Request $request, $id)
    {
        $em = $this->container->get('bdk.enquiry.manager');

        $enquiry = $em->getRepository()->findOneById($id);

        if (null === $enquiry) {
            return new Response(null, 404, array('Content-Type' => "application/{$request->getRequestFormat()}"));
        }

        $em->remove($enquiry);

        return new Response(
            null,
            200,
            array('Content-Type' => "application/{$request->getRequestFormat()}")
        );
    }
}
