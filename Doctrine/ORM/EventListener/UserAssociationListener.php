<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Doctrine\ORM\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Doctrine ORM Listener updating the Answer entity to set a one to one
 * relationship with the user class defined in configuration
 */
class UserAssociationListener
{
    protected $userClassname;

    /**
     * @param string $user_class
     */
    public function __construct($userClassname)
    {
        $this->userClassname = $userClassname;
    }

    /**
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        if ($classMetadata->getName()!=='Bodaclick\BDKEnquiryBundle\Entity\Answer') {
            return;
        }

        //Setting the one to one relationship
        $builder = new ClassMetadataBuilder($args->getClassMetadata());

        $builder->addOwningOneToOne('user', $this->userClassname);
    }
}
