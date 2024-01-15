<?php
/**
 * User: joachimdorr
 * Date: 13.04.20
 * Time: 13:09
 */

namespace BSC\Controller;


use BSC\base;
use BSC\config;
use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use BSC\Model\MemberPassword;
use BSC\Model\MemberPatch;
use BSC\Model\MilestonePatch;
use BSC\Service\EntityManager;
use Laminas\Diactoros\Response\JsonResponse;
use OAuth2\Response;
use Psr\Http\Message\ServerRequestInterface;
use rex_extension;
use rex_extension_point;
use rex_request;
use rex_ycom_user;
use rex_yform_validate_password_policy;
use rex_password_policy;
use btu_portal;

class Member extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Joachim Doerr
     */
    public static function deleteMemberByEmailAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $email = rex_request::get('email', 'string', null);
            $user = btu_portal::getParticipantByMail($email);
            $yUser = rex_ycom_user::get($user['id']);

            rex_extension::registerPoint(new rex_extension_point('BSC_API_DELETE_MEMBER', $yUser));

            return new Response([], 204);

        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Joachim Doerr
     */
    public static function deleteMemberAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            rex_extension::registerPoint(new rex_extension_point('BSC_API_DELETE_MEMBER', $yUser));

            return new Response([], 204);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Joachim Doerr
     */
    public static function updateMemberByTokenAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            /** @var MemberPatch $memberPatch */
            $memberPatch = self::deserialize($request->getBody()->getContents(), MemberPatch::class, 'json');
            $memberPatch = rex_extension::registerPoint(new rex_extension_point('BSC_API_PRE_UPDATE_MEMBER', $memberPatch));
            $yUser = self::getYComAuthUser();

            $now = new \DateTime();

            // validate request
            self::processValidation($memberPatch);

//            $password = self::plainPasswordToDbPassword($memberPatch->getPassword());
            $overwrite = array(
                'updatedate' => $now->format(DATE_W3C),
                'updateuser' => 'API'
            );

//            if (!is_null($password)) {
//                $overwrite['password'] = $password['ycom_password'];
//            }
            if (!is_null($memberPatch->getEmail()) && $yUser->getValue('email') != $memberPatch->getEmail()) {
                try {
                    self::getUserData('email', $memberPatch->getEmail());
                    throw new InvalidArgumentException(rex_getString("app_error_email_exists"), 'email_already_exist');
                } catch (NotFoundException $e) {
                    $overwrite['login'] = $memberPatch->getEmail();
                }
            }

            EntityManager::persist(
                $memberPatch,
                base::get('table.ycom_user'),
                [],
                [],
                array_filter(array_merge($overwrite, ['last_action_time' => $now->format("Y-m-d H:i:s"), 'id' => $yUser->getId()])),
                true
            );
    
            $participant = btu_portal::factory()->getParticipant($yUser->getId());
            
            if((int)$participant['termsofuse_science_accepted'] == 1) {
                btu_portal::updateLog('API', $yUser->getId(), $yUser->getId(), ['type' => 'PARTICIPANT_UPDATE', 'reference_id' => $yUser->getId()]);
            }

            rex_extension::registerPoint(new rex_extension_point('BSC_API_POST_UPDATE_MEMBER', ['member_patch' => $memberPatch, 'y_user' => $yUser, 'token' => self::getBearer()]));

            return new Response([], 204);

        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 403);
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Joachim Doerr
     */
    public static function getMemberByTokenAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            /** @var \BSC\Model\Member $member */
            $member = self::getUserData('id', $yUser->getId(), true, ['password', 'username', 'termsofuse_accepted', 'verification_key', 'new_password_required', 'activation_key']);

//            $qr = btu_portal::generateParticipantQR($yUser->getId(), $yUser->getValue("birthday"));
//            $qr = preg_replace("@^participant_(.+)\.png$@", "$1", $qr);
//            $member->setQr($yUser->getId().'-'.$qr);

            $member = rex_extension::registerPoint(new rex_extension_point('BSC_API_FIND_MEMBER', $member));

            return self::response($member, 200, true);

        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function memberTypesAction(ServerRequestInterface $request, array $args = []) {
        $typesRaw = btu_portal::factory()->getArray("
            SELECT * FROM rex_ycom_group WHERE hidden = 0 ORDER BY extended_list ASC, priority ASC
        ");

        $types = array();

        foreach($typesRaw as &$type) {
            $type['extendedList'] = ($type['extended_list'] === '1');
            $type['hidden'] = ($type['hidden'] === '1');
            $types[] = $type;
        }

        return new Response($types, 200);
    }

    /**
     * set new password
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function setPasswordAction(ServerRequestInterface $request, array $args = []) {
        try {
            $setPassword = self::deserialize($request->getBody()->getContents(), MemberPassword::class, 'json');
            $yUser = self::getYComAuthUser();

            if (!is_null($yUser)) {
                // check if password is set
                if ($setPassword->getPassword() != "") {
                    // check if password matches requirements
                    $rules = json_decode(rex_yform_validate_password_policy::PASSWORD_POLICY_DEFAULT_RULES, true);
                    $policy = new rex_password_policy($rules);
                    $result = $policy->check($setPassword->getPassword());

                    if($result === true) {
                        $success = btu_portal::updateParticipantPassword($yUser->getId(), $setPassword->getPassword(), 'API');

                        if (!$success) {
                            throw new Exception(rex_getString("app_password_update_error", "password_save_error"), "password_save_error");
                        }

                        return new Response([], 204);
                    } else {
                        throw new InvalidArgumentException(rex_getString("app_password_policy_error"), "unmatched_password_policy");
                    }
                } else {
                    throw new InvalidArgumentException(rex_getString("app_password_update_error_empty", "Empty password"), "empty_password");
                }
            } else {
                throw new InvalidArgumentException(rex_getString("app_welcome_register_error_resolvinguser", "Error resolving user"),"unknown_user");
            }
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
     * @return \Laminas\Diactoros\Response\EmptyResponse|JsonResponse|\Laminas\Diactoros\Response\TextResponse|Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function getCheckinLogAction(ServerRequestInterface $request, array $args = []) {#
        try {
            $yUser = self::getYComAuthUser();
            $data = btu_portal::getParticipantTodaysCheckins($yUser->getId());
        
            return self::response($data, 200);
        
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse|Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function getTimeSheetAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            $data = btu_portal::getParticipantTimeSheet($yUser->getId());
            
            return self::response($data, 200);
            
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
}