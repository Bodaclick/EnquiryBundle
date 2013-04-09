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

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Doctrine ORM Listener to manage the relationship between the enquiry and the about object in ORM
 * The about object is of an unknown class, so we save a definition (className and id) in JSON Format
 * when the enquiry object is persisted, and convert again in the actual object when an enquiry object
 * is loaded from the database or after persisted in the database (so getAbout always return an object
 * and not a definition)
 */
class LinkAboutListener
{
    public function prePersist(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof \Bodaclick\BDKEnquiryBundle\Entity\Enquiry) {
            $enquiry = $event->getEntity();
            $about = $enquiry->getAbout();

            //The class metadata is used to get the identifiers, that could be compound
            $metadata = $event->getEntityManager()->getClassMetadata(get_class($about));

            //And used to generate a definition/semi-serialization of the about object in JSON format
            $definition = json_encode(array("className"=>$metadata->getName(), "ids"=>$metadata->getIdentifierValues($about)));

            //Change the about object by its definition
            $enquiry->setAbout($definition);
        }
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        if ($event->getEntity() instanceof \Bodaclick\BDKEnquiryBundle\Entity\Enquiry) {
            //The about object is inmutable, so undo changes to that field in updates
            $event->setNewValue('about', $event->getOldValue('about'));
        }

    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $this->regenerateAboutField($event);
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->regenerateAboutField($event);
    }

    protected function regenerateAboutField(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof \Bodaclick\BDKEnquiryBundle\Entity\Enquiry) {
            $enquiry = $event->getEntity();
            $definition = $enquiry->getAbout();

            //The definition consist in an array with the names of the variables and their value in JSON format
            $object = json_decode($definition, true);

            $className = $object['className'];
            $ids = $object['ids'];

            //Only a reference (proxy) to the about object is set, so no query to the database is needed
            //until one of the about object's field is accessed.
            $about=$event->getEntityManager()->getReference($className, $ids);

            $enquiry->setAbout($about);
        }
    }
}
