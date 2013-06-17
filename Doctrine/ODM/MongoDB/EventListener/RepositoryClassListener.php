<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Doctrine\ODM\MongoDB\EventListener;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

/**
 * Doctrine ODM Listener to map the RepositoryClass
 */
class RepositoryClassListener
{
    protected $repClass;

    /**
     * __construct
     *
     * @param string $repClass
     */
    public function __construct($repClass)
    {
        $this->repClass = $repClass;
    }

    /**
     * @param Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();
        $classMetadata->setCustomRepositoryClass($this->repClass);
    }
}
