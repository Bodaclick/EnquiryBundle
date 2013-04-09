<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\DependencyInjection;

final class InheritanceTypes
{
    const SINGLE = "single";
    const JOINED = "joined";

    public static function getList()
    {
        return array(self::SINGLE, self::JOINED);
    }
}
