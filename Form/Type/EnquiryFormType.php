<?php

namespace BDK\EnquiryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Url;

/**
 * EnquiryFormType
 *
 * @copyright 2012 Bodaclick S.A.
 * @author    Ignacio Velázquez <ignacio.velazquez@bodaclick.com>
 */
class EnquiryFormType extends AbstractType
{
    /**
     * buildForm
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'text')
            ->add('status', 'text')
            ->add(
                'about',
                'text',
                array(
                    'error_bubbling' => true
                )
            )
            ->add(
                'responses',
                'collection',
                array(
                    'type' => 'response',
                    'allow_add'    => true,
                    'allow_delete' => true
                )
            );
    }

    /**
     * setDefaultOptions
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
            )
        );
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'enquiry';
    }
}
