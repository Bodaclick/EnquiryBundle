<?php

namespace BDK\EnquiryBundle\Form\Handler;

use BDK\EnquiryBundle\Model\Manager\EnquiryManager;
use Symfony\Component\Form\FormInterface;

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
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array                                 $params
     * @param \Doctrine\ORM\EntityManager           $em
     */
    public function __construct(FormInterface $form, array $params, EnquiryManager $em)
    {
        $this->form = $form;
        $this->params = $params;
        $this->em = $em;
    }

    /**
     * Processes the form and updates the Profile data
     *
     * @return bool
     */
    public function process()
    {
        $this->form->bind($this->params);

        if (!$this->form->isValid()) {
            return false;
        }

        $this->em->update($this->form->getData());

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
