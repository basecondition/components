<?php

namespace BSC\Controller;


use BSC\config;
use BSC\Model\Success;
use BSC\Model\TokenOrder;
use DateTime;
use OAuth2\Response;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response\EmptyResponse;
use rex_logger;
use rex_sql;

class Token extends AbstractController
{
    public static function tokenAction(ServerRequestInterface $request, array $args = array()): Response|JsonResponse|TextResponse|EmptyResponse
    {
        try {
            /** @var TokenOrder $tokenOrder */
            $tokenOrder = self::deserialize($request->getBody()->getContents(), TokenOrder::class, 'json');
            // map to post parameter
            $_POST['grant_type'] = $tokenOrder->getGrantType();
            $_POST['client_id'] = $tokenOrder->getClientId();
            $_POST['client_secret'] = $tokenOrder->getClientSecret();

            if (!empty($tokenOrder->getUsername()))
                $_POST['username'] = $tokenOrder->getUsername();
            if (!empty($tokenOrder->getPassword()))
                $_POST['password'] = $tokenOrder->getPassword();
            if (!empty($tokenOrder->getRefreshToken()))
                $_POST['refresh_token'] = $tokenOrder->getRefreshToken();
        } catch (\Exception $e) {
            rex_logger::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }

        // we wrap the token service on this point
        // on the service we dont have something to do
        // the auth server will handel all by the php $_REQUEST global variable

        $token = new \BSC\OAuth2\Token();
        $result = $token->handleToken();

        /** @var Response $response */
        $response = $result['response'];
        $userData = $result['user'];

        // in case of success we add the user status
        if ($response->getStatusCode() == 200 && sizeof($userData) > 0) {
            // delete pw reset key if existing
            if(isset($userData['password_reset_key']) && !is_null($userData['password_reset_key'])) {
                // TODO prüfen ob das benötigt wird und so!
                // bsc::resetParticipantPasswordReset($userData['id']);
            }

            $response = new Response(array_merge(
                $response->getParameters(),
                ['type' => (!empty($userData['ycom_groups'])) ? intval($userData['ycom_groups']) : 2]
            ));
        }

        return $response;
    }

    public static function logoutAction(ServerRequestInterface $request, array $args = array()): Response|JsonResponse|TextResponse|EmptyResponse
    {
        try {
            $bearer = self::getBearer();
            $yUser = self::getYComAuthUser();
            $now = new DateTime();

            // token
            $sql = rex_sql::factory();
            $sql->setTable(config::get('table.token'));
            $sql->setValue('expires', $now->format("Y-m-d H:i:s"));
            $sql->setWhere('access_token = :token', ['token' => $bearer]);
            $sql->update();

            // refresh token
            $sql = rex_sql::factory();
            $sql->setTable(config::get('table.refresh_token'));
            $sql->setValue('expires', $now->format("Y-m-d H:i:s"));
            $sql->setWhere('user_id = :mail and expires > NOW()', ['mail' => $yUser->getValue('email')]);
            $sql->update();

            $success = new Success();
            $success->setResult(true)
                ->setSuccess('successful_logged_out')
                ->setSuccessDescription('Your token has been expired');

            return self::response($success, 200);

        } catch (\Exception $e) {
            rex_logger::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|Response
     * @author Joachim Doerr
     */
    public static function isloggedInAction(ServerRequestInterface $request, array $args = array())
    {
        try {
            $success = new Success();
            $success->setResult(true)
                ->setSuccess('token_valid')
                ->setSuccessDescription('Your token is valid');

            return self::response($success, 200);

        } catch (\Exception $e) {
            rex_logger::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
}