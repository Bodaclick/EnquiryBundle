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

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

abstract class Answer implements NormalizableInterface, DenormalizableInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;

    /**
     * @var BDK\EnquiryBundle\Model\EnquiryInterface
     */
    protected $enquiry;

    /**
     * @var Collection
     */
    protected $responses;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
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
    * Set user
    *
    * @param Symfony\Component\Security\Core\User\UserInterface $user
    * @return Answer
    */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set enquiry
     *
     * @param  BDK\EnquiryBundle\Model\EnquiryInterface $enquiry
     * @return Answer
     */
    public function setEnquiry(EnquiryInterface $enquiry)
    {
        $this->enquiry = $enquiry;

        return $this;
    }

    /**
     * Get enquiry
     *
     * @return BDK\EnquiryBundle\Model\EnquiryInterface $enquiry
     */
    public function getEnquiry()
    {
        return $this->enquiry;
    }

    /**
     * Set responses
     *
     * @param  Collection $responses
     * @return Answer
     */
    public function setResponses(Collection $responses)
    {
        $this->responses = $responses;

        return $this;
    }

    /**
     * Get responses
     *
     * @return Collection $responses
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Add a response
     * @param Response $response
     */
    public function addResponse(Response $response)
    {
        $this->responses->add($response);
    }

    /**
     * Normalize function used to convert the object in an array of fields
     *
     * @param  \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer
     * @param  string| null                                                 $format
     * @return array|\Symfony\Component\Serializer\Normalizer\scalar
     */
    public function normalize(NormalizerInterface $normalizer, $format = null)
    {
        $normalized = array();

        if ($this->user!=null) {
            $id = method_exists($this->user, 'getId')? $this->user->getId() : 'none';
            $normalized['user'] = array('id'=>$id, 'username'=>$this->user->getUsername());
        }

        $normalized['responses'] = array();

        foreach ($this->responses as $response) {
            $normalized['responses'][] = $normalizer->normalize($response, $format);
        }

        return array('answer'=>$normalized);
    }

    /**
     * Denormalize function used to fills the object's fields from an array of key-value pairs
     *
     * @param \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer
     * @param array|\Symfony\Component\Serializer\Normalizer\scalar          $data
     * @param string|null                                                    $format
     */
    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null)
    {
        if (!isset($data['answer'])) {
            return null;
        }

        $answer = $data['answer'];

        if (isset($answer['responses'])) {
            foreach ($answer['responses'] as $response) {
                //The ResponseNormalizer class knows which Response class to use, based on informed type
                //but we have to use a special 'Response' type to have the Response normalizer treat this
                $object = $denormalizer->denormalize($response, 'Response', $format);
                $this->addResponse($object);
            }
        }
    }
}
