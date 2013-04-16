<?php

namespace BDK\EnquiryBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use BDK\EnquiryBundle\Model\Enquiry;

/**
 * Event dispatched in Enquiry Service
 */
class EnquiryEvent extends Event
{
    /**
     * Enquiry associated to the event.
     *
     * @var Enquiry
     */
    protected $enquiry;

    /**
     * Constructor
     *
     * @param Enquiry $enquiry
     */
    public function __construct(Enquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    /**
     * Set the enquiry object associated to the event
     *
     * @param  Enquiry      $enquiry
     * @return EnquiryEvent
     */
    public function setEnquiry(Enquiry $enquiry)
    {
        $this->enquiry = $enquiry;

        return $this;
    }

    /**
     * Get the enquiry object associated to the event
     * @return Enquiry
     */
    public function getEnquiry()
    {
        return $this->enquiry;
    }
}
