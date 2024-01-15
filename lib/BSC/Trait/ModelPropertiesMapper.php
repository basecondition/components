<?php
/**
 * User: joachimdorr
 * Date: 08.04.20
 * Time: 09:13
 */

namespace BSC\Trait;


use BSC\DTO\NamePropertiesDTO;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Annotation\SerializedName;
use ReflectionClass;

trait ModelPropertiesMapper
{
    protected static function modelPropertyToSerializerName($name, $model)
    {

    }

    /**
     * @param $model
     * @return NamePropertiesDTO[]
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @author Joachim Doerr
     */
    protected static function modelPropertiesToSerializerNames($model)
    {
        $reflectionClass = new ReflectionClass(get_class($model));
        $reader = new AnnotationReader();

        $properties = array();

        foreach ($reflectionClass->getProperties() as $property) {
            $annotations = $reader->getPropertyAnnotations($property);
            $columnName = '';

            foreach ($annotations as $annotation) {
                if ($annotation instanceof SerializedName) {
                    $columnName = $annotation->name;
                }
            }

            foreach (array('is', 'get') as $prefix) {
                if ($reflectionClass->hasMethod($prefix . ucfirst($property->getName()))) {
                    $getter = $prefix . ucfirst($property->getName());
                    $value = $model->$getter();
                    $properties[$columnName] = new NamePropertiesDTO(
                        $columnName,
                        $property->getName(),
                        $value,
                        $getter,
                        $model
                    );
                    continue;
                }
            }

        }
        return $properties;
    }

    protected static function serializerNameToModelProperty($name, $value, $model)
    {

    }

    protected static function serializerNamesToModelProperties($values, $model)
    {

    }
}