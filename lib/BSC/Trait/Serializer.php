<?php
/**
 * User: joachimdorr
 * Date: 13.04.20
 * Time: 16:55
 */

namespace BSC\Trait;


use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;

trait Serializer
{
    /**
     * @var \Symfony\Component\Serializer\SerializerInterface|\JMS\Serializer\Serializer
     */
    protected static $serializer;

    /**
     * @param $object
     * @param string $format
     * @param bool $serializeNull
     * @return string
     * @author Joachim Doerr
     */
    protected static function serialize($object, $format = 'json', $serializeNull = false)
    {
        self::instanceSerializer('JMS', $serializeNull);
        return self::$serializer->serialize($object, 'json');
    }

    // TODO work with SF serializer
    // $serializer = new \Symfony\Component\Serializer\Serializer([new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())], [new JsonEncoder()]);
    // $content = $serializer->serialize($context, 'json', ['skip_null_values' => true]);

    /**
     * @param $data
     * @param $type
     * @param string $format
     * @return array|mixed|object
     * @author Joachim Doerr
     */
    protected static function deserialize($data, $type, $format = 'json')
    {
        self::instanceSerializer();
        return self::$serializer->deserialize($data, $type, $format);
    }

    /**
     * @param string $serializer
     * @param bool $serializeNull
     * @return \JMS\Serializer\Serializer|\JMS\Serializer\SerializerInterface|\Symfony\Component\Serializer\SerializerInterface
     * @author Joachim Doerr
     */
    private static function instanceSerializer($serializer = 'JMS', $serializeNull = false)
    {
        if (!self::$serializer instanceof \JMS\Serializer\Serializer)

            if ($serializeNull) {
                self::$serializer = SerializerBuilder::create()
                    ->setSerializationContextFactory(function () {
                        return SerializationContext::create()->setSerializeNull(true);
                    })
                    ->build();
            } else {
                self::$serializer = SerializerBuilder::create()
                    ->setSerializationContextFactory(function () {
                        return SerializationContext::create()->setSerializeNull(false);
                    })
                    ->build();
            }

        return self::$serializer;
    }
}