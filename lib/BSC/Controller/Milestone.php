<?php
/**
 * User: joachimdorr
 * Date: 16.04.20
 * Time: 20:20
 */

namespace BSC\Controller;


use BSC\config;
use btu_portal;
use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use BSC\Model\MilestoneCreate;
use BSC\Model\MilestonePatch;
use BSC\Service\EntityManager;
use BSC\Traits\ModelMapper;
use BSC\Traits\ModelPropertiesMapper;
use Laminas\Diactoros\Response\JsonResponse;
use OAuth2\Response;
use Psr\Http\Message\ServerRequestInterface;
use rex_extension;
use rex_extension_point;
use rex_sql;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class Milestone extends AbstractController
{
    use ModelPropertiesMapper;
    use ModelMapper;

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Joachim Doerr
     */
    public static function createMilestoneAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            /** @var MilestoneCreate $milestoneCreate */
            $milestoneCreate = self::deserialize($request->getBody()->getContents(), MilestoneCreate::class, 'json');
            $milestoneCreate = rex_extension::registerPoint(new rex_extension_point('BSC_API_PRE_CREATE_MILESTONE', $milestoneCreate));
            $yUser = self::getYComAuthUser();

            $now = new \DateTime();

            // validate request
            self::processValidation($milestoneCreate);

            if ($milestoneCreate->getType() > 0) {
                // check is exist as y_group
                $sql = rex_sql::factory();
                $result = $sql->getArray('select * from ' . config::get('table.ycom_group') . ' where id = :id', ['id' => $milestoneCreate->getType()]);
                
                if (count($result) <= 0) {
                    throw new InvalidArgumentException('Type '.$milestoneCreate->getType().' not found.', 'type_not_found');
                }
            }

            $result = EntityManager::persist(
                $milestoneCreate,
                config::get('table.milestones'),
                ['is_key'],
                [],
                [
                    'participant' => $yUser->getId(),
                    'createdate' => $now->format(DATE_W3C),
                    'createuser' => 'API',
                ],
                true
            );
    
            $participant = btu_portal::factory()->getParticipant($yUser->getId());

            if((int)$participant['termsofuse_science_accepted'] == 1) {
                // fix log
                $sqlLog = "
                UPDATE
                    portal_log
                SET
                    initiator_type = 'API',
                    initiator_id = :pid,
                    affected_participant = :pid
                WHERE
                    type = 'MILESTONE_CREATE'
                ORDER BY
                    id DESC
                LIMIT 1;
                ";
    
                btu_portal::factory()->setQuery($sqlLog, [':pid' => $yUser->getId()]);
            }

            // set key milestone action
            if ($milestoneCreate->isIsKey() || $participant['last_milestone_action'] == '') {
                btu_portal::factory()->updateParticipantMilestoneActionTime($yUser->getId());
            }

//            rex_extension::registerPoint(new rex_extension_point('BSC_API_POST_CREATE_MILESTONE', $data));

            return new Response([], 201);

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
     * @return JsonResponse|Response
     * @author Joachim Doerr
     */
    public static function getMemberMilestonesAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            $data = btu_portal::getParticipantMilestones($yUser->getId(), 'DESC');
            $data = rex_extension::registerPoint(new rex_extension_point('BSC_API_GET_MILESTONES', $data));

//            $result = [];
//            foreach ($data as $item) {
//                $milestone = self::mapDataToMilestone($item);
//                if (!is_null($milestone))
//                    $result[] = $milestone;
//            }

            return self::response($data, 200);

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
     * @return JsonResponse|Response
     * @author Joachim Doerr
     */
    public static function getMemberMilestoneAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            $id = intval($args['route_parameter']['id']);
            if ($id <= 0) throw new InvalidArgumentException('Id is not valid', 'invalid_id');

            $data = btu_portal::getMilestone($id);

            if (!empty($data['participant']) && $data['participant'] != $yUser->getId())
                throw new InvalidArgumentException('User is not allowed to get a milestone for other user', 'wrong_user_id');

            $data = rex_extension::registerPoint(new rex_extension_point('BSC_API_GET_MILESTONES', $data));
//            $milestone = self::mapDataToMilestone($data);

            return self::response($data, 200);

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
    public static function updateMilestoneAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $id = intval($args['route_parameter']['id']);
            
            if ($id <= 0) {
                throw new InvalidArgumentException('Id is not valid', 'invalid_id');
            }

            /** @var MilestonePatch $milestonePatch */
            $milestonePatch = self::deserialize($request->getBody()->getContents(), MilestonePatch::class, 'json');
            $milestonePatch = rex_extension::registerPoint(new rex_extension_point('BSC_API_PRE_UPDATE_MILESTONE', $milestonePatch));
            $yUser = self::getYComAuthUser();

            $now = new \DateTime();

            $milestoneData = btu_portal::getMilestone($id);

            if (!empty($milestoneData['participant']) && $milestoneData['participant'] != $yUser->getId()) {
                throw new AccessDeniedException('User is not allowed to change a milestone of another user', 'wrong_user_id');
            }

            // validate request
            self::processValidation($milestonePatch);

            $result = EntityManager::persist(
                $milestonePatch,
                config::get('table.milestones'),
                ['is_key'],
                [],
                [
                    'id' => $id,
                    'participant' => $yUser->getId(),
                    'updatedate' => $now->format(DATE_W3C),
                    'updateuser' => 'API',
                ],
                true
            );
    
            $participant = btu_portal::factory()->getParticipant($yUser->getId());
    
            if($result instanceof MilestonePatch && (int)$participant['termsofuse_science_accepted'] == 1) {
                btu_portal::updateLog('API', $yUser->getId(), $yUser->getId(),['type' => 'MILESTONE_UPDATE', 'reference_id' => $id]);
            }

            rex_extension::registerPoint(new rex_extension_point('BSC_API_POST_UPDATE_MILESTONE', $milestonePatch));

            return new Response([], 204);

        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (AccessDeniedException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 403);
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
    public static function deleteMemberMilestoneByIdAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $id = intval($args['route_parameter']['id']);
            if ($id <= 0) throw new InvalidArgumentException('Id is not valid', 'invalid_id');

            $yUser = self::getYComAuthUser();
            $data = btu_portal::getMilestone($id);

            if (!empty($data['participant']) && $data['participant'] != $yUser->getId())
                throw new InvalidArgumentException('User is not allowed to delete a milestone for other user', 'wrong_user_id');

            $success = btu_portal::deleteMilestone($id);
            $participant = btu_portal::getParticipant($yUser->getId());

            if($success && (int)$participant['termsofuse_science_accepted'] == 1) {
                btu_portal::updateLog('API', $yUser->getId(), $yUser->getId(),['type' => 'MILESTONE_DELETE', 'reference_id' => $id]);
            }

            return new Response([], 204);

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
}