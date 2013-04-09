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

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Normalizer to serialize/deserialize Response objects
 */
class ResponseNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * Default Response class
     * @var string
     */
    protected $defaultResponseClass;

    /**
     * List of response classes
     * @var array
     */
    protected $responseClasses;

    /**
     * Constructor
     *
     * @param $defaultResponseClass
     * @param $responseClasses
     */
    public function __construct($defaultResponseClass, $responseClasses)
    {
        $this->defaultResponseClass = $defaultResponseClass;
        $this->responseClasses = $responseClasses;
    }

    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param  object $object object to normalize
     * @param  string $format format the normalization result will be encoded as
     * @return array
     */
    public function normalize($object, $format = null)
    {
        $normalized = array('key'=>$object->getKey(), 'value'=>$object->getValue());

        $class = get_class($object);

        if ($class!=$this->defaultResponseClass) {;
            $type = array_search($class, $this->responseClasses);
            if (!$type) {
                throw new InvalidArgumentException(sprintf("Response type not valid: %s",$type));
            }
            $normalized['type'] = $type;
        }

        return $normalized;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param  mixed   $data   Data to normalize.
     * @param  string  $format The format being (de-)serialized from or into.
     * @return Boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Response;
    }

    /**
     * Denormalizes data back into an object of the given class
     *
     * @param  mixed    $data   data to restore
     * @param  string   $class  the expected class to instantiate
     * @param  string   $format format the given data was extracted from
     * @return Response
     */
    public function denormalize($data, $class, $format = null)
    {
        if (!isset($data['type']) || $data['type']=='default') {
            $class = $this->defaultResponseClass;
        } else {
            $type = $data['type'];
            if (!isset($this->responseClasses[$type])) {
                throw new \Symfony\Component\Serializer\Exception\UnexpectedValueException(
                    sprintf('Response type not defined denormalizing response data: %s', $type)
                );
            }
            $class = $this->responseClasses[$type];
        }

        $object = $this->createResponseClass($class);
        if (isset($data['key'])) {
            $object->setKey($data['key']);
        }

        if (isset($data['value'])) {
            $object->setValue($data['value']);
        }

        return $object;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer
     *
     * @param  mixed   $data   Data to denormalize from.
     * @param  string  $type   The class to which the data should be denormalized.
     * @param  string  $format The format being deserialized from.
     * @return Boolean
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        //We use a special type to check support, later in denormalize method is changed to the right class
        return $type=='Response';
    }

    /**
     * Method used to create an instance of the class name given. Used in this way so it can be mocked in testing.
     * @param $class
     * @return mixed
     */
    protected function createResponseClass($class)
    {
        return new $class;
    }
}
