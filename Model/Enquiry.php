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
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_DISMISS = 'dismiss';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /**
     * @var integer
     *
     */
    protected $id;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var stdClass
     */
    protected $about;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @var DateTime
     */
    protected $expiresAt;

    /**
     * @var boolean
     */
    protected $sent;

    /**
     * @var Collection
     */
    protected $responses;

    /**
     *
     */
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
     * @param $user
     * @return Enquiry
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set responses
     *
     * @param  Collection $responses
     * @return Enquiry
     */
    public function setResponses(Collection $responses)
    {
        $this->responses = $responses;

        return $this;
    }

    /**
     * Get responses
     *
     * @return Collection
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Add answer
     * @param Response $response
     */
    public function addResponse(Response $response)
    {
        $this->responses->add($response);
        $response->setEnquiry($this);
    }

    /**
     * Remove an answer
     * @param Response $response
     */
    public function removeResponse(Response $response)
    {
        $this->responses->removeElement($response);
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

        $responses = array();

        foreach ($this->responses as $response) {
            $responses[] = $normalizer->normalize($response, $format);
        }

        $normalized['responses'] = $responses;

        return array('enquiry'=>$normalized);

    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Enquiry
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return \BDK\EnquiryBundle\Model\DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \BDK\EnquiryBundle\Model\DateTime $createdAt
     *
     * @return Enquiry
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \BDK\EnquiryBundle\Model\DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \BDK\EnquiryBundle\Model\DateTime $updatedAt
     *
     * @return Enquiry
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Enquiry
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param \BDK\EnquiryBundle\Model\DateTime $expiresAt
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return \BDK\EnquiryBundle\Model\DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param boolean $sent
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    }

    /**
     * @return boolean
     */
    public function getSent()
    {
        return $this->sent;
    }
}
