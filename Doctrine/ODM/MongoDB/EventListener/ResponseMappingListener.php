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
 * Doctrine ODM Listener to map the Responses as Embedded documents in Answer,
 * with targetDocument or with discriminatorMap, depending on configuration
 */
class ResponseMappingListener
{
    protected $responseClasses;
    protected $defaultResponse;

    /**
     * Constructor
     *
     * @param $defaultResponse Default Response class
     * @param array $responseClasses Array with data mapping from configuration
     */
    public function __construct($defaultResponse, $responseClasses)
    {
        $this->defaultResponse = $defaultResponse;
        $this->responseClasses = $responseClasses;
    }

    /**
     * Method called when event loadClassMetadata is launched
     *
     * @param Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        //The discriminatorMap is set in Answer class
        if ($classMetadata->getName()!=='Bodaclick\BDKEnquiryBundle\Document\Answer') {
            return;
        }

        //If only one default Response class and no subclasses given in configuration,
        //no changes to the mapping are made
        if (empty($this->responseClasses)) {
            return;
        }

        //If there are more than one default Response class or in configuration, override the default mapping
        $map = array('default'=>$this->defaultResponse);

        foreach ($this->responseClasses as $type=>$class) {
            $map[$type]=$class;
        }

        $classMetadata->mapManyEmbedded(
            array('name'=>'responses','discriminatorMap'=>$map,'strategy'=>'pushAll','discriminatorField'=>'type')
        );

    }
}
