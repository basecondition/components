<?php
/**
 * User: joachimdorr
 * Date: 16.04.20
 * Time: 21:59
 */

namespace BSC\Controller;


use BSC\config;
use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use BSC\Service\EntityManager;
use BSC\Model\MemberRegister;
use BSC\Model\RegisterResend;
use BSC\Model\RegisterVerify;
use BSC\Model\Success;
use OAuth2\Response;
use Psr\Http\Message\ServerRequestInterface;
use rex_extension;
use rex_extension_point;
use rex_sql;
use rex_clang;
use btu_portal;

class Registration extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Joachim Doerr
     */
    public static function createMemberAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            /** @var MemberRegister $member */
            $member = self::deserialize($request->getBody()->getContents(), MemberRegister::class, 'json');
            $member = rex_extension::registerPoint(new rex_extension_point('BSC_API_PRE_CREATE_MEMBER', $member));

            // validate request
            self::processValidation($member);

            $now = new \DateTime();

            $passwordHash = \rex_login::passwordHash($member->getPassword());
            $uniqueId = sha1(uniqid(rand(), true));
            $participant = btu_portal::getParticipantByMail($member->getEmail());

            if(!is_null($participant)) {
                throw new InvalidArgumentException(rex_getString('app_welcome_register_error_accountexists', 'User already exists'), 'user_already_exists');
            }

            $member = EntityManager::persist(
                $member,
                config::get('table.ycom_user'),
                [],
                [],
                [
                    'login' => $member->getEmail(),
                    'status' => 0,
                    'createdate' => $now->format("Y-m-d H:i:s"),
                    'createuser' => 'API',
                    "updateuser" => null,
                    "updatedate" => null,
                    'activation_key' => $uniqueId,
                    'password' => $passwordHash,
                    'language' => ($member->getLanguage() ? $member->getLanguage() : rex_clang::get(rex_clang::getStartId())->getCode()),
                    'state' => (!empty($member->getZipcode())) ? btu_portal::getStateFromZipcode($member->getZipcode()) : null,
                    'termsofuse_accepted' => true],
                true
            );

            $participant = btu_portal::getParticipantByMail($member->getEmail());
    
            if((int)$participant['termsofuse_science_accepted'] == 1) {
                btu_portal::updateLog('API', $participant['id'], $participant['id'], ['type' => 'PARTICIPANT_CREATE', 'reference_id' => $participant['id']]);
            }
            
            rex_extension::registerPoint(new rex_extension_point('BSC_API_POST_CREATE_MEMBER', $member));

            return new Response([], 201);

        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => 'internal_error', 'error_description' => $e->getMessage() . ' ' . $e->getTraceAsString()], 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|Response
     * @author Joachim Doerr
     */
    public static function verifyMemberAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            /** @var RegisterVerify $registerVerify */
            $registerVerify = self::deserialize($request->getBody()->getContents(), RegisterVerify::class, 'json');
            $registerVerify = rex_extension::registerPoint(new rex_extension_point('BSC_API_PRE_VERIFY_MEMBER', $registerVerify));

            // validate request
            self::processValidation($registerVerify);

            // get user data
            $userData = btu_portal::getParticipantByMail($registerVerify->getEmail());
            $now = new \DateTime();

            $valid = false;

            if (isset($userData['verification_key']) && $userData['verification_key'] == $registerVerify->getTan()) {
                $valid = true;

                if (isset($userData['status']) && $userData['status'] == 0) {
                    $password = null;
                    if (!empty($registerVerify->getPassword())) {
                        $password = self::plainPasswordToDbPassword($registerVerify->getPassword());
                    }

                    $sql = rex_sql::factory();
                    $sql->setTable(config::get('table.ycom_user'));
                    $sql->setValue('status', 1);
                    $sql->setValue('updatedate', $now->format(DATE_W3C));
                    $sql->setValue('updateuser', 'API');
                    if (!is_null($password)) $sql->setValue('password', $password['ycom_password']);
                    $sql->setWhere('email = :email', [':email' => $registerVerify->getEmail()]);
                    $sql->update();
    
                    if((int)$userData['termsofuse_science_accepted'] == 1) {
                        btu_portal::updateLog('API', $userData['id'], $userData['id'], ['type' => 'PARTICIPANT_UPDATE', 'reference_id' => $userData['id']]);
                    }

                    if (!is_null($password)) {
                        self::saveOAuth2User(array(
                            'login' => $registerVerify->getEmail(),
                            'password' => $registerVerify->getPassword(),
                            'firstname' => $userData['firstname'],
                            'name' => $userData['name'],
                        ));
                    }

                    rex_extension::registerPoint(new rex_extension_point('BSC_API_POST_VALID_VERIFY_MEMBER', $registerVerify));
                }
            } else {
                rex_extension::registerPoint(new rex_extension_point('BSC_API_POST_INVALID_VERIFY_MEMBER', $registerVerify));
            }

            $response = new Success();
            $response->setResult($valid);
            $response->setSuccess(intval($valid));
            $response->setSuccessDescription(rex_getString("api_register_message_tan-".($valid ? 'valid' : 'invalid')));

            return self::response($response, 200);

        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Joachim Doerr
     */
    public static function resendVerifyTanAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            /** @var RegisterResend $registerResend */
            $registerResend = self::deserialize($request->getBody()->getContents(), RegisterResend::class, 'json');

            self::processValidation($registerResend);
            self::getUserData('email', $registerResend->getEmail(), false);

            rex_extension::registerPoint(new rex_extension_point('BSC_API_MEMBER_REGISTRATION_RESEND_MAIL', $registerResend));
            return new Response([], 204);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
}