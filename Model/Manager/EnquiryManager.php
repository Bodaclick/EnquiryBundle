<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Model\Manager;

use BDK\EnquiryBundle\Model\Enquiry;
use BDK\EnquiryBundle\Events\EnquiryEvent;
use BDK\EnquiryBundle\Events\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class EnquiryManager
 * @package BDK\EnquiryBundle\Model\Manager
 */
class EnquiryManager
{
    protected $om;
    protected $repository;
    protected $class;
    protected $dispatcher;

    /**
     * @param ObjectManager $om
     * @param               $class
     */
    public function __construct(ObjectManager $om, EventDispatcher $dispatcher, $class)
    {
        $this->om = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->name;

        $this->dispatcher = $dispatcher;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param $user
     * @param $about
     * @param $type
     * @param $expDate
     * @return mixed
     */
    public function create($user, $about, $type, $expDate)
    {
        $enquiry = new $this->class();



        $enquiry->setCreatedAt(new \DateTime());
        $enquiry->setExpiresAt($expDate);

        $enquiry->setStatus(Enquiry::STATUS_NEW);
        $enquiry->setUser($user);
        $enquiry->setType($type);
        $enquiry->setAbout($about);
        $enquiry->setSent(false);

        $event = new EnquiryEvent($enquiry);
        $this->dispatcher->dispatch(Events::CREATE, $event);

        return $event->getEnquiry();
    }

    /**
     * @param      $element
     * @param bool $andFlush
     */
    public function update($element, $andFlush = true)
    {
        $this->om->persist($element);

        if (true === $andFlush) {
            $this->om->flush();
        }
    }

    /**
     * @param $element
     */
    public function remove($element, $andFlush = true)
    {
        $this->om->remove($element);

        if (true === $andFlush) {
            $this->om->flush();
        }
    }

    /**
     * Flush the data
     */
    public function flush()
    {
        $this->om->flush();
    }
}
