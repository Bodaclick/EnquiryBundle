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
 * Doctrine ODM Listener to add a prefix to all the bundle's collection names
 */
class CollectionNamePrefixListener
{
    protected $prefix;

    /**
     * Constructor
     *
     * @param string $prefix Prefix to add to the collection names
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Method called when event loadClassMetadata is launched
     *
     * @param Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        //Only set the prefix to the bundle entities
        if ($classMetadata->getReflectionClass()->getNamespaceName()!== 'BDK\EnquiryBundle\Document') {
            return;
        }

        $classMetadata->setCollection($this->prefix.$classMetadata->getCollection());

    }
}
