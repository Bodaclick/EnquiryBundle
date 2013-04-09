<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Entity;

use Bodaclick\BDKEnquiryBundle\Model\Response as BaseResponse;

/**
 * Response entity
 */
class Response extends BaseResponse
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Answer The answers that this response belongs to
     */
    protected $answer;

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
     * Set Answer
     *
     * @param  Answer   $answer
     * @return Response
     */
    public function setAnswer(Answer $answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get Answer
     *
     * @return Answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
