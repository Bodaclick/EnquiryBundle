<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Doctrine\ODM\MongoDB\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use BDK\EnquiryBundle\Model\AboutInterface;
use BDK\EnquiryBundle\Model\EnquiryRepositoryInterface;

/**
 * Enquiry repository
 */
class EnquiryRepository extends DocumentRepository implements EnquiryRepositoryInterface
{
    /**
     * Gets all the enquiries associated with a list of users
     *
     * @param  array $users
     * @return array|bool|\Doctrine\MongoDB\ArrayIterator|\Doctrine\MongoDB\Cursor|
     *          \Doctrine\MongoDB\EagerCursor|int|mixed|\MongoCursor|null
     */
    public function getEnquiriesForUsers(array $users)
    {
        $qb=$this->createQueryBuilder()
            ->field('user')->in($users);

        return $qb->getQuery()->execute()->toArray();
    }
}
