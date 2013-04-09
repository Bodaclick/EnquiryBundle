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

use BDK\EnquiryBundle\Model\Answer as BaseAnswer;

/**
 * Answer entity
 */
class Answer extends BaseAnswer
{
    /**
     * Add a response
     * @param Response $response
     */
    public function addResponse(\BDK\EnquiryBundle\Model\Response $response)
    {
        $this->responses->add($response);
        $response->setAnswer($this);
    }
}
