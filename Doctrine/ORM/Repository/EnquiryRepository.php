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
use Bodaclick\BDKEnquiryBundle\Model\AboutInterface;
use Bodaclick\BDKEnquiryBundle\Model\EnquiryRepositoryInterface;

/**
 * Enquiry repository
 */
class EnquiryRepository extends EntityRepository implements EnquiryRepositoryInterface
{

    /**
     * Gets all the enquiries associated with an object
     *
     * @param  mixed $object
     * @return mixed
     */
    public function getEnquiriesFor(AboutInterface $object)
    {
        //Generate the definition and find using the string generated
        $metadata = $this->getEntityManager()->getClassMetadata(get_class($object));
        $definition = json_encode(array("className"=>$metadata->getName(), "ids"=>$metadata->getIdentifierValues($object)));

        //Order from newer to older, so if we get the first one, we get the last enquiry saved
        $qb=$this->createQueryBuilder('e')
            ->where('e.about = :definition')
            ->setParameter('definition', $definition)
            ->orderBy('e.id','DESC');

        return $qb->getQuery()->execute();
    }
}
