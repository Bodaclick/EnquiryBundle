<?php

namespace BDK\EnquiryBundle\Form\Handler;

use BDK\EnquiryBundle\Events\EnquiryEvent;
use BDK\EnquiryBundle\Events\Events;
use BDK\EnquiryBundle\Model\Manager\EnquiryManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * EnquiryFormHandler
 *
 * @author    Ignacio Velázquez <ignacio.velazquez@bodaclick.com>
 * @copyright 2012 Bodaclick S.A.
 */
class EnquiryFormHandler
{
    /**
     * @var \Symfony\Component\Form\FormInterface $form
     */
    protected $form;

    /**
     * @var array $params
     */
    protected $params;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param FormInterface            $form
     * @param Request                  $request
     * @param EnquiryManager           $em
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        EnquiryManager $em,
        EventDispatcherInterface $dispatcher
    ) {
        $this->form = $form;
        $this->params = $request->request->all();
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Processes the form and updates the Profile data
     *
     * @return bool
     */
    public function process()
    {
        $this->form->submit($this->params);

        if (!$this->form->isValid()) {
            return false;
        }

        $event = new EnquiryEvent($this->form->getData());

        $this->dispatcher->dispatch(Events::PRE_PERSIST, $event);

        $this->em->update($this->form->getData());

        $this->dispatcher->dispatch(Events::POST_PERSIST, $event);

        return true;
    }

    /**
     * @return string
     */
    public function getErrorsArray()
    {
        $messages = [];

        foreach ($this->form->getErrors() as $error) {
            $params = $error->getMessageParameters();
            if (array_key_exists('{{ extra_fields }}', $params)) {
                $formattedParams = explode(', ', str_replace("\"", "", $params['{{ extra_fields }}']));
                $messages[] = "This form should not contain extra fields: " . implode(', ', $formattedParams);
                continue;
            }

            $messages[] = $error->getMessage();
        }

        return implode(', ', $messages);
    }
}
