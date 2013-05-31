<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use BDK\EnquiryBundle\Model\AboutInterface;
use BDK\EnquiryBundle\Model\EnquiryRepositoryInterface;

/**
 * Enquiry repository
 */
class EnquiryRepository extends EntityRepository implements EnquiryRepositoryInterface
{

    /**
     * Gets all the enquiries associated with a list of users
     *
     * @param  array $users
     * @return mixed
     */
    public function getEnquiriesForUsers(array $users)
    {
        $qb=$this->createQueryBuilder('e')
            ->where('e.user IN (:users)')
            ->setParameter('users', $users);

        return $qb->getQuery()->execute();
    }
}
