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
use Bodaclick\BDKEnquiryBundle\Model\AboutInterface;
use Bodaclick\BDKEnquiryBundle\Model\EnquiryRepositoryInterface;

/**
 * Enquiry repository
 */
class EnquiryRepository extends DocumentRepository implements EnquiryRepositoryInterface
{
    /**
     * Gets all the enquiries associated with an object
     *
     * @param  AboutInterface                                                       $object
     * @return array|bool|\Doctrine\MongoDB\ArrayIterator|\Doctrine\MongoDB\Cursor|
     *          \Doctrine\MongoDB\EagerCursor|int|mixed|\MongoCursor|null
     */
    public function getEnquiriesFor(AboutInterface $object)
    {
        //Order from newer to older, so if we get the first one, we get the last enquiry saved
        $qb=$this->createQueryBuilder()
            ->field('about.$id')->equals(new \MongoId($object->getId()))
            ->sort('id','DESC');

        //Disable check for indexes, because about reference cannot be indexed and throw an error otherwise
        $qb->requireIndexes(false);

        return $qb->getQuery()->execute();
    }
}
