<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Doctrine\Common\Collections\Collection;

abstract class Enquiry implements EnquiryInterface, NormalizableInterface
{
    /**
     * @var integer
     *
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Collection
     */
    protected $answers;

    /**
     * @var string
     */
    protected $form;

    /**
     * @var stdClass
     */
    protected $about;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return Enquiry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set answers
     *
     * @param  Collection $answers
     * @return Enquiry
     */
    public function setAnswers(Collection $answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * Get answers
     *
     * @return Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Add answer
     * @param Answer $answer
     */
    public function addAnswer(Answer $answer)
    {
        $this->answers->add($answer);
        $answer->setEnquiry($this);
    }

    /**
     * Set form
     *
     * @param  string  $form
     * @return Enquiry
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set about
     *
     * @param  mixed   $about Can be an string representation or a AboutInterface object
     * @return Enquiry
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return stdClass
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Normalize function used to convert the object in an array of fields
     *
     * @param  \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer
     * @param  string|null                                                  $format
     * @return array|\Symfony\Component\Serializer\Normalizer\scalar
     */
    public function normalize(NormalizerInterface $normalizer, $format = null)
    {
        $normalized = array();

        $normalized['id'] = $this->id;

        if ($this->form!=null) {
            $normalized['form'] = $this->form;
        }

        if ($this->name!=null) {
            $normalized['name'] = $this->name;
        }

        $answers = array();

        foreach ($this->answers as $answer) {
            $answers[] = $normalizer->normalize($answer,$format);
        }

        $normalized['answers'] = $answers;

        return array('enquiry'=>$normalized);

    }
}
