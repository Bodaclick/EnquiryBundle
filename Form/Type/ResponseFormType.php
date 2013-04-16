<?php

namespace BDK\EnquiryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ResponseFormType
 *
 * @copyright 2012 Bodaclick S.A.
 * @author    Ignacio Velázquez <ignacio.velazquez@bodaclick.com>
 */
class ResponseFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * buildForm
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', 'text')
            ->add('answer');
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
                'data_class' => $this->class,
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
        return 'response';
    }
}
