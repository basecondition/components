<?php
/**
 * User: joachimdorr
 * Date: 20.04.20
 * Time: 12:32
 */

namespace BSC\Controller;


use OAuth2\Response;
use Psr\Http\Message\ServerRequestInterface;

class Heartbeat extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Joachim Doerr
     */
    public static function getHeartbeatAction(ServerRequestInterface $request, array $args = []) {
        return new Response([], 204);
    }
}