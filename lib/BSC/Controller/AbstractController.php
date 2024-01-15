<?php
/**
 * User: joachimdorr
 * Date: 13.04.20
 * Time: 18:05
 */

namespace BSC\Controller;


use BSC\Trait\Authorization;
use BSC\Trait\Provider;
use BSC\Trait\Serializer;
use BSC\Trait\Validation;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use OAuth2\Response;

abstract class AbstractController
{
    use Serializer;
    use Authorization;
    use Validation;
    use Provider;

    /**
     * @param $context
     * @param int $code
     * @param bool $serializeNull
     * @return TextResponse|JsonResponse|EmptyResponse|Response
     * @author Joachim Doerr
     */
    protected static function response($context, int $code = 200, $serializeNull = false)
    {
        if ($context instanceof Response) {
            if ($context->getStatusCode() != $code) $context->setStatusCode($code);
            return $context;
        }
        if (is_array($context))
            foreach ($context as $item)
                if (is_object($item))
                    return new TextResponse(self::serialize($context, 'json', $serializeNull), $code);
        if (is_array($context)) return new JsonResponse($context, $code);
        if (is_object($context)) return new TextResponse(self::serialize($context, 'json', $serializeNull), $code);
        return new EmptyResponse($code);
    }
}