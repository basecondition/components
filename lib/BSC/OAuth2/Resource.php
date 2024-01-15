<?php

namespace BSC\OAuth2;


use BSC\config;
use OAuth2\Response;
use OAuth2\Storage\ClientInterface;

class Resource extends Server
{
    protected ?array $token = null;

    public function verifyRequest()
    {
        foreach (['bearer', 'token'] as $item) {
            if (empty($this->request->headers['AUTHORIZATION']) && isset($this->request->query[$item])) {
                $this->request->headers['AUTHORIZATION'] = 'Bearer ' . str_replace('Bearer ', '', $this->request->query[$item]);
            }
        }
        if (!$this->server->verifyResourceRequest($this->request)) {
            return $this->server->getResponse();
        }
        $this->token = $this->server->getResourceController()->getToken();
        config::set('token', $this->token);
        return null;
    }

    public function verifyBasicAuth(): ?Response
    {
        if (empty($this->request->headers['AUTHORIZATION']) && isset($this->request->query['basic'])) {
            $this->request->headers['AUTHORIZATION'] = 'Basic ' . str_replace('Basic ', '', $this->request->query['basic']);
        }

        /** @var ClientInterface $clientStorage */
        $clientStorage = $this->server->getStorage('client');
        $authorizationHeader = $this->request->headers['AUTHORIZATION'];

        $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
        $client = array();
        $response = new Response();

        if (count($exploded) == 2) {
            list($client['client_id'], $client['client_secret']) = $exploded;
        }
        if (!isset($client['client_id'])) {
            $response->setError(400, 'invalid_client', "No client id supplied");
            return $response;
        }
        if (!$client['details'] = $clientStorage->getClientDetails($client['client_id'])) {
            $response->setError(401, 'invalid_client', 'The client id supplied is invalid');
            return $response;
        }
        if ($client['client_secret'] != $client['details']['client_secret']) {
            $response->setError(401, 'invalid_client', 'The client credentials are invalid');
            return $response;
        }
        return null;
    }

    public function getToken(): ?array
    {
        return $this->token;
    }
}