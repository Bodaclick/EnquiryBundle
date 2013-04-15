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

/**
 * Class Response
 *
 * @package BDK\EnquiryBundle\Model
 */
abstract class Response
{
    /**
     * @var integer
     *
     */
    protected $id;

    /**
     * @var string Question to identify the response
     */
    protected $question;

    /**
     * @var string Answer of the response.
     */
    protected $answer;

    /**
     * @var Enquiry $enquiry
     */
    protected $enquiry;

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
     * Set question
     *
     * @param  string   $question
     * @return Response
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set answer
     *
     * @param  string   $answer
     * @return Response
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
        return $this;
    }

    /**
     * Get answer
     *
     * @return string $answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return \BDK\EnquiryBundle\Model\Enquiry
     */
    public function getEnquiry()
    {
        return $this->enquiry;
    }

    /**
     * @param \BDK\EnquiryBundle\Model\Enquiry $enquiry
     *
     * @return Response
     */
    public function setEnquiry($enquiry)
    {
        $this->enquiry = $enquiry;
        return $this;
    }
}
