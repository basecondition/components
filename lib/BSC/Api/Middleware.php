<?php

namespace BSC\Api;


use BSC\base;
use BSC\config;
use BSC\dispatcher;
use BSC\OAuth2\Resource;
use BSC\Repository\YComUserRepository;
use BSC\Trait\Authorization;
use OAuth2\Response;
use OAuth2\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rex_ycom_auth;
use rex_ycom_user;

class Middleware
{
    use Authorization;

    const basicAuthSecurityDefinitionKey = 'basicAuth';
    const apiKeySecurityDefinitionKey = 'apiKey';
    const oAuthSecurityDefinitionKey = 'oAuth2';
    private static ?Resource $resource = null;

    public static function process(ServerRequestInterface $request, \FastRoute\Dispatcher $dispatcher): array
    {
        self::$resource = new Resource();
        // in der open api wird die routen security fest gelegt
        // diese wird durch verifyRequest ausgewertet und entsprechend auf den routen call angewendet
        $result = self::verifyRequest($request); // auth verification by openapi yml
        if (is_null($result)) {
            // der dispatcher executed den controller call der in der open api für route und method definiert wurde
            // das routing wird via codegen erzeugt siehe addons/base/resources/codegen.sh
            return $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        }
        // wenn dispatch null liefert ist es ein fehler
        return [0 => 3, 'response' => $result];
    }

    // TODO checken ob ich das noch brauche und wofür genau es gut ist...
    public static function processWithoutRouting($returnResult = false): ?ResponseInterface
    {
//        self::$resource = new Resource();
//
//        $result = self::$resource->verifyRequest();
//        if ($result instanceof Response && $returnResult) return $result;
//
//        try {
//            self::checkTokenUser();
//        } catch (\Exception $e) {
//            \rex_logger::logException($e);
//        }
        return null;
    }

    private static function getRequestConfig(?ServerRequestInterface $request = null): ?array
    {
        if (is_null($request)) return null;
        $method = strtolower($request->getMethod());
        $routPath = str_replace(base::config('resources.openapi.basePath'), '', $request->getUri()->getPath());
        return base::config("resources.openapi.paths.$routPath.$method");
    }

    private static function verifyRequest(ServerRequestInterface $request): ?ResponseInterface
    {
        $requestConfig = self::getRequestConfig($request);
        if (isset($requestConfig['security']) && sizeof($requestConfig['security']) > 0) {
            foreach ($requestConfig['security'] as $item) {
                foreach ($item as $key => $scope) {
                    if (!is_array($scope)) $scope = [$scope];
                    $result = self::executeVerification((string) $key, $scope, $request);
                    if ($result instanceof ResponseInterface) return $result;
                }
            }
        }
        return null;
    }

    private static function executeVerification(string $key, array $scope, ServerRequestInterface $request): ?ResponseInterface
    {
        switch ($key) {
            case self::basicAuthSecurityDefinitionKey:
                return self::$resource->verifyBasicAuth(); // das übernimmt der oauth2 resource server
            case self::apiKeySecurityDefinitionKey:

                // TODO add API Key https://swagger.io/docs/specification/authentication/api-keys/
                //  dafür könnte ycom auth tokens genutzt werden mal sehen ob das bei basic auth passen würde oder sonst wo

                return null;
            case self::oAuthSecurityDefinitionKey:
                $result = null;
                // erstmal generelles zugirffsrecht
                // prüfe zuerst ob ein user eingeloggt ist
                if (!rex_ycom_auth::getUser() instanceof rex_ycom_user) { // wenn nein dann
                    // prüfe den auth token
                    $result = self::$resource->verifyRequest();
                    // ist der token ungültig haben wir ein response obj
                    if (!$result instanceof ResponseInterface) {
                        // jetzt prüfen und laden wir den token user ob der generell darf
                        $result = self::checkTokenUser();
                    }
                }
                // ist bis hier hin alles gut?
                if (!$result instanceof ResponseInterface) {
                    // dann kommt jetzt der permission checken by scope
                    // den user haben wir ja sonst wären wir raus
                    $result = self::checkUserPermission($scope, $request);
                }
                // ist die prüfung schief gibt es ein fehler response obj
                // das wird dann hier ausgespielt oder null
                return $result;
        }
        return null;
    }

    private static function checkTokenUser(): ?ResponseInterface
    {
        if (!empty(base::get('token.user_id'))) {
            $userId = base::get('token.user_id');
            $user = YComUserRepository::findUserByLoginName($userId);
            if ($user instanceof rex_ycom_user) {
                // jetzt loggen wir den api user in ycom auth ein
                rex_ycom_auth::setUser($user);
                // und prüfen die generelle zugriffsberechtigung
                return self::verifyYComUserStatus();
            } else {
                return new Response(['error' => 'account_not_found', 'error_description' => "Account with id {$userId} not found"], 404);
            }
        }
        return null;
    }
    private static function checkUserPermission(?array $scope = null, ?ServerRequestInterface $request = null): ?ResponseInterface
    {
        // default is user permitted check
        $result = self::verifyYComUserStatus(); // wenn der user aktiv ist gehts
        if ($result instanceof ResponseInterface) return $result;

        $user = rex_ycom_auth::getUser();
        // wandele die werte in integer keys um
        $userGroups = array_map('intval', dispatcher::dispatch('BSC_API_PERMISSION_CHECK_YCOM_GROUP_IDS', array_map('intval', $user->getGroups())));

        // filtere ycom_group getGroups array basierend auf den schlüsseln im user groups array
        // ycom_group == rolle -> an der rolle hängt die permission mit ihrem scope
        $groups = dispatcher::dispatch('BSC_API_PERMISSION_CHECK_YCOM_GROUP_LIST', array_intersect_key(\rex_ycom_group::getGroups(), array_flip($userGroups)));
        $path = (!is_null($request)) ? str_replace(base::config('resources.openapi.basePath'), '', $request->getUri()->getPath()) : '';
        $requestConfig = self::getRequestConfig($request);

        // das event ermöglicht es jedem listenern permission prüfungen durchzuführen und je nach scopes zu blocken
        $epPermissionCheckResult = dispatcher::dispatch('BSC_API_PERMISSION_CHECK', [
            'groups' => $groups,
            'scope' => $scope,
            'path'  => $path,
            'requestConfig' => $requestConfig,
        ]);

        // liefert der listener ein response obj wird dieses direkt ausgeliefert
        if ($epPermissionCheckResult instanceof ResponseInterface) return $epPermissionCheckResult; // wir brechen hiermit die iteration, die permission ist ja soweiso nicht gegeben
        return null;
    }
}