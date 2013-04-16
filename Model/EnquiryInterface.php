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
 * Class EnquiryInterface
 *
 * @package BDK\EnquiryBundle\Model
 */
interface EnquiryInterface
{
    public function getResponses();

    public function getType();

    public function getAbout();
}
