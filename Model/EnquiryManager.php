<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use BDK\EnquiryBundle\Events\Events;
use Symfony\Component\EventDispatcher\Event;
use BDK\EnquiryBundle\Events\EnquiryEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Enquiry manager.
 * Access point to all the bundle's features and factory service to create database objects
 */
class EnquiryManager
{

    /**
     * ObjectManager to access the database, by ORM or ODM
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Dispatcher for the events
     *
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Default Response class, used to build responses if needed.
     * Must be a descendant of Response abstract class in the bundle model.
     *
     * @var string
     */
    protected $defaultResponseClass;

    /**
     * List of response classes defined in configuration and used in serialization/deserialization
     * @var array
     */
    protected $responseClasses;

    /**
     * Optional logger to log service activity
     *
     * @var LoggerInterface $logger
     */
    protected $logger=null;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager        $objectManager
     * @param Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $dispatcher,
        $defaultResponseClass,
        $responseClasses
    )
    {
        $this->objectManager = $objectManager;
        $this->dispatcher = $dispatcher;
        $this->defaultResponseClass = $defaultResponseClass;
        $this->responseClasses = $responseClasses;
    }

    /**
     * Setter for the optional logger parameter
     *
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the last enquiry associated to an object
     *
     * @param AboutInterface $object
     * @param string | null $format Format of the response (json, xml). Optional. If null, return db object or null
     */
    public function getEnquiryFor(AboutInterface $object, $format = null)
    {
        $enquiries = $this->getEnquiriesFor($object);

        if (is_array($enquiries)) {
            $enquiry = array_pop($enquiries);
        } elseif ($enquiries instanceof \Iterator) {
            $enquiries->next();

            $enquiry = $enquiries->current();
        } else {
            throw new \UnexpectedValueException(
                'EnquiryRepository method must return an array or object implementing Iterator interface'
            );
        }

        return $this->getEnquiryFormatted($enquiry, $format);
    }

    /**
     * Get all the enquiries previously associated to an object
     *
     * @param AboutInterface $object
     * @param string | null $format Format of the response (json, xml). Optional. If null, return db object or null
     */
    public function getEnquiriesFor(AboutInterface $object, $format = null)
    {
        //A custom repository is used, so each type of database driver (orm, mongodb,...)
        //can build the most eficient query
        $enquiries = $this->objectManager->getRepository('BDKEnquiryBundle:Enquiry')->getEnquiriesFor($object);

        if ($format!==null) {
            $result = '[';
            foreach ($enquiries as $enquiry) {
                $result .= $this->getEnquiryFormatted($enquiry, $format);
            }
            $result .= ']';
            return $result;
        }

        return $enquiries;
    }

    /**
     * Get an enquiry previously saved with a name
     *
     * @param string $name Name associated to the enquiry
     * @param string | null $format Format of the response (json, xml). Optional. If null, return db object or null
     * @return BDK\EnquiryBundle\Model\Enquiry | null
     */
    public function getEnquiryByName($name, $format = null)
    {
        $enquiry = $this->objectManager->getRepository('BDKEnquiryBundle:Enquiry')->findOneBy(array('name'=>$name));

        return $this->getEnquiryFormatted($enquiry, $format);
    }

    /**
     * Get an enquiry by id
     *
     * @param $id
     * @param string | null $format Format of the response (json, xml). Optional. If null, return db object or null
     * @return BDK\EnquiryBundle\Model\Enquiry | null
     */
    public function getEnquiry($id, $format = null)
    {
        $enquiry = $this->objectManager->getRepository('BDKEnquiryBundle:Enquiry')->find($id);

        return $this->getEnquiryFormatted($enquiry, $format);
    }

    /**
     * Get an enquiry object formatted according to the format specified
     *
     * @param Enquiry $enquiry Enquiry object
     * @param null $format Format, json or xml, or null
     * @return Enquiry|null
     */
    protected function getEnquiryFormatted(Enquiry $enquiry = null, $format = null)
    {
        if ($enquiry!=null && $format!==null) {
            $serializer = $this->getSerializer();
            $enquiry = $serializer->serialize($enquiry, $format);
        }

        return $enquiry;
    }

    /**
     * Create an enquiry (database object, it'll be an entity or a document,
     * depending on the configuration), and return it
     *
     * @param AboutInterface $about The object that the enquiry is associated with. Can be of any type,
     *                     but always an entity or a document implementing AboutInterface
     * @param string $form The name of a form associated to the enquiry. Only used for reference. Optional.
     * @param string $name A name associated to the enquiry. Optional. If specified, must be unique
     *
     * @return BDK\EnquiryBundle\Model\EnquiryInterface The enquiry database object created
     */
    public function saveEnquiry(AboutInterface $about, $form = null, $name = null)
    {

        //Check if the "about" object is persisted (has an identifier value),
        //if not, persist it to get the right id when associated with enquiry
        try {
            $aboutMetadata = $this->objectManager->getClassMetadata(get_class($about));
        } catch (\Exception $e) {
            $msg = 'The about parameter must be a valid entity or a valid document';
            if ($this->logger) {
                $this->logger->crit($msg);
            }
            throw new \InvalidArgumentException($msg);
        }

        $ids = $aboutMetadata->getIdentifierValues($about);

        if (count($ids) == 0) {
            if ($this->logger) {
                $this->logger->debug('About object not saved yet, proceed to save it');
            }
            $this->objectManager->persist($about);
            $this->objectManager->flush();
        }

        //Using the metadata to create a new database object, no matter which db driver is used
        $metadata = $this->objectManager->getClassMetadata('BDKEnquiryBundle:Enquiry');
        $enquiry = $metadata->getReflectionClass()->newInstance();

        //Set the "about" object into the enquiry one
        $enquiry->setAbout($about);

        $enquiry->setName($name);
        $enquiry->setForm($form);

        $event = new EnquiryEvent($enquiry);

        //Dispatch event before persist object, and get the $enquiry object, in case the listener change it
        $this->dispatcher->dispatch(Events::PRE_PERSIST, $event);

        $enquiry = $event->getEnquiry();

        //Persist the enquiry object
        $this->objectManager->persist($enquiry);
        $this->objectManager->flush();

        //Dispatch event to inform object is persisted
        $this->dispatcher->dispatch(Events::POST_PERSIST, $event);

        if ($this->logger) {
            $this->logger->info(
                sprintf(
                    'Enquiry saved with about object of class %s, form value %s and name %s',
                    get_class($about),
                    $form,
                    $name
                )
            );
        }

        return $enquiry;
    }

    /**
     * Delete an enquiry from the database by name or specifying the object itself
     *
     * @param BDK\EnquiryBundle\Model\EnquiryInterface | string The enquiry
     * object or the name of the enquiry that is going to be deleted
     */
    public function deleteEnquiry($enquiry)
    {
        //Get the actual database enquiry object, if name is specified in the param
        $enquiry = $this->resolveEnquiryParam($enquiry);

        $this->objectManager->remove($enquiry);
        $this->objectManager->flush();

        if ($this->logger) {
            $this->logger->info(sprintf('Enquiry %s removed', $enquiry->getName()));
        }
    }

    /**
     * Save the answers to an enquiry to the database.
     * The enquiry can be specified by its database object representation or by name
     * The responses come in an array of Response objects
     *
     * @param BDK\EnquiryBundle\Model\EnquiryInterface | string The enquiry object or the name of the enquiry
     * @param BDK\EnquiryBundle\Model\Answer            $answer An answer object containing the responses given
     * @param \Symfony\Component\Security\Core\User\UserInterface $user  The user that the answers belongs to. Optional.
     */
    public function saveAnswer($enquiry, Answer $answer, UserInterface $user = null)
    {

        //Get the actual database enquiry object, if name is specified in the param
        $enquiry = $this->resolveEnquiryParam($enquiry);

        //Associate the answer to the enquiry
        $enquiry->addAnswer($answer);

        //Associate the user if any
        if ($user!=null) {
            $answer->setUser($user);
        }

        //Dispatch event and get the $enquiry object, in case the listener change it
        $event = new EnquiryEvent($enquiry);

        $this->dispatcher->dispatch(Events::PRE_PERSIST_ANSWER, $event);

        $enquiry = $event->getEnquiry();

        //Save to the database
        $this->objectManager->persist($enquiry);
        $this->objectManager->flush();

        //Dispatch event to inform object has persisted
        $this->dispatcher->dispatch(Events::POST_PERSIST_ANSWER, $event);

        if ($this->logger) {
            if ($user) {
                $msg = sprintf(
                    'Answer from user %s to enquiry %s saved',
                    $user->getUsername(),
                    $enquiry->getName()
                );
            } else {
                $msg = sprintf('Answer from anonymous user to enquiry %s saved', $enquiry->getName());
            }
            $this->logger->info($msg);
        }
    }

    /**
     * Save the responses to an enquiry to the database.
     * The enquiry can be specified by its database object representation or by name
     * The responses come in an array of Response objects
     *
     * @param BDK\EnquiryBundle\Model\EnquiryInterface | string The enquiry object or the name of the enquiry
     * @param array | string $responses Array of Response objects or raw key=>value pair, or string in json format
     * @param \Symfony\Component\Security\Core\User\UserInterface $user  The user that the answers belongs to. Optional.
     */
    public function saveResponses($enquiry, $responses, UserInterface $user=null)
    {
        if (is_array($responses)) {
            //Check the type of the responses, if they are raw key-value pairs, convert into default Response objects
            //Throw and exception if they are objects of classes not descendant of default Response class
            $checkFunction = function(&$value, $key, $defaultResponseClass) {
                if (!is_object($value)) {
                    $response = new $defaultResponseClass;
                    $response->setValue($value);
                    $response->setKey($key);
                    $value = $response;
                } elseif (!($value instanceof Response)) {
                    throw new \Exception();
                }
            };

            try {
                array_walk($responses, $checkFunction, $this->defaultResponseClass);
            } catch (\Exception $e) {
                $msg = 'The responses parameter must contain an array of Response objects or key-value pairs';
                if ($this->logger) {
                    $this->logger->crit($msg);
                }
                throw new \InvalidArgumentException($msg);
            }

            //Create the answer instance object
            $answer = $this->createAnswer();

            //Add the responses to the answer instance
            $answer->setResponses(new ArrayCollection($responses));

        } else {
            //It's a string representation of the responses in json format
            //Deserialize the answer
            $metadata = $this->objectManager->getClassMetadata('BDKEnquiryBundle:Answer');
            $classname = $metadata->getName();
            $serializer = $this->getSerializer();
            $answer = $serializer->deserialize($responses, $classname , 'json');

            //If the answer is null or has no responses, something is bad in the request
            if ($answer == null || $answer->getResponses()->count()==0) {
                throw new InvalidArgumentException('The json request is malformed or not valid');
            }
        }

        //Call the saveAnswer method with the new answer created
        $this->saveAnswer($enquiry, $answer, $user);
    }

    /**
     * Create an empty answer entity or document
     *
     * @return BDK\EnquiryBundle\Model\Answer
     */
    public function createAnswer()
    {
        $metadata = $this->objectManager->getClassMetadata('BDKEnquiryBundle:Answer');
        $answer = $metadata->getReflectionClass()->newInstance();

        return $answer;
    }

    /**
     * Create an empty response entity or document
     *
     * @return BDK\EnquiryBundle\Model\Response
     */
    public function createResponse()
    {
        return new $this->defaultResponseClass;
    }

    /**
     * Helper function used to check the enquiry param in some methods
     * If it's a string, guess it's the enquiry name, if not, the enquiry object itself
     *
     * @param string | BDK\EnquiryBundle\Model\EnquiryInterface $enquiry
     *          The enquiry object or the enquiry's name
     * @return BDK\EnquiryBundle\Model\EnquiryInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolveEnquiryParam($enquiry)
    {

        if (is_string($enquiry)) {
            $name = $enquiry;
            $enquiry = $this->objectManager
                ->getRepository('BDKEnquiryBundle:Enquiry')->findOneBy(array('name'=>$name));
            if ($enquiry===null) {
                throw new \InvalidArgumentException(
                    sprintf("There isn't any enquiry in the database with the name %s", $name)
                );
            }
        } elseif (!($enquiry instanceof EnquiryInterface))
            throw new \InvalidArgumentException(
                sprintf(
                    "The method param must be an object implementing EnquiryInterface
                    or a string containing the name of an enquiry"
                )
            );

        return $enquiry;
    }

    /**
     * Construct a serializer object with the right normalizers and supported encoders
     * Used to parse the request and serialize the response in the format requested.
     * @return \Symfony\Component\Serializer\Serializer
     */
    protected function getSerializer()
    {
        return new Serializer(
            array(new ResponseNormalizer($this->defaultResponseClass, $this->responseClasses), new CustomNormalizer()),
            array(new XmlEncoder(), new JsonEncoder())
        );
    }
}
