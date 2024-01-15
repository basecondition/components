<?php
/**
 * User: joachimdorr
 * Date: 14.04.20
 * Time: 09:17
 */

namespace BSC\Trait;


use BSC\Exception\InvalidArgumentException;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

trait Validation
{
    /**
     * @var ValidatorInterface
     */
    protected static $validator;

    /**
     * @param $object
     * @return bool|\Symfony\Component\Validator\ConstraintViolationListInterface
     * @author Joachim Doerr
     */
    protected static function validate($object)
    {
        try {
            return self::getDefaultValidator()->validate($object);
        } catch (AnnotationException $e) {
            \rex_logger::logException($e);
            return false;
        }
    }

    /**
     * @param $object
     * @param array $ignoreProperty
     * @throws InvalidArgumentException
     * @author Joachim Doerr
     */
    protected static function processValidation($object, array $ignoreProperty = array())
    {
        $violations = self::validate($object);

        // TODO add ignore property

        if (\count($violations) > 0) {
            $message = [];
            foreach ($violations as $violation) {
                $message[] = str_replace([PHP_EOL, '   '], '' ,(string) $violation);
            }
            throw new InvalidArgumentException(implode('; ', $message), 'invalid_request');
        }
    }

    /**
     * Get a default validator.
     *
     * By default the usage of annotations to validate object is off. To enable annotation configuration install
     * `doctrine/annotations` and `doctrine/cache`.
     *
     * @return RecursiveValidator|ValidatorInterface
     * @throws AnnotationException
     */
    protected static function getDefaultValidator()
    {
        if (!self::$validator instanceof ValidatorInterface) {
            $loaders = [new StaticMethodLoader()];
            if (class_exists(AnnotationReader::class) && class_exists(ArrayCache::class)) {
                AnnotationRegistry::registerUniqueLoader('class_exists');
                $loaders[] = new AnnotationLoader(new CachedReader(new AnnotationReader(), new ArrayCache()));
            }
            self::$validator = (new ValidatorBuilder())
                ->setMetadataFactory(
                    new LazyLoadingMetadataFactory(
                        new LoaderChain($loaders)
                    )
                )
                ->getValidator();
        }
        return self::$validator;
    }
}