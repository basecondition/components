<?php
/**
 * @date     22.07.2020 12:42
 * @author   Peter Schulze [p.schulze@bitshifters.de]
 */

namespace BSC\Controller;


use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use BSC\Exception\AccessDeniedException;
use BSC\Model\ConsultingDateAdd;
use Psr\Http\Message\ServerRequestInterface;
use btu_portal;
use rex_category;
use rex_article_content;
use rex_global_settings;

class Consulting extends AbstractController {
    
    /**
     * get consulting menu / overview with facts, title, and description
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Peter Schulze
     */
    public static function getOverviewAction(ServerRequestInterface $request, array $args = []) {
        try {
            // get base article
            $consultingBase = rex_global_settings::getDefaultValue("consultingBaseCategory");
            
            if((int)$consultingBase == 0) {
                throw new NotFoundException("Basis-Kategorie nicht definiert.", "consulting_error_basecategory_not_found");
            }
            
            // get all online articles
            $consultingArticles = rex_category::get($consultingBase)->getArticles(true);
            $consultingMenu = [];
            
            foreach($consultingArticles as $art) {
                if($art->isStartArticle()) {
                    continue;
                }
                
                $artContent = new rex_article_content($art->getId());
                
                if(!is_null($artContent)) {
                    $content = $artContent->getArticle();
                    
                    if($content != "") {
                        $consultingMenu[] = json_decode($artContent->getArticle());
                    }
                }
            }
            
            return self::response($consultingMenu, 200);
            
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
    
    /**
     * get consulting date
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Peter Schulze
     */
    public static function getDateAction(ServerRequestInterface $request, array $args = []) {
        try {
            $yUser = self::getYComAuthUser();
            $id = intval($args['route_parameter']['id']);
    
            if($id <= 0) {
                throw new InvalidArgumentException('ID is not valid', 'invalid_id');
            }
    
            // get consulting date and check
            $consultingDate = btu_portal::factory()->getArray("
                SELECT
                    id,
                    participant,
                    UNIX_TIMESTAMP(`date`) AS `date`,
                    location,
                    consultant,
                    notice,
                    article_id
                FROM
                    portal_consulting
                WHERE
                    id = :id
            ", [':id' => $id]);
    
            if(!$consultingDate || (is_array($consultingDate) && count($consultingDate) == 0)) {
                throw new InvalidArgumentException('Date not found', 'invalid_id');
            } elseif((int)$consultingDate[0]['participant'] != $yUser->getId()) {
                throw new AccessDeniedException('User is not allowed to see a consulting date of another user', 'wrong_user_id');
            }
    
            $consultingDate = $consultingDate[0];
            $consultingDate['id'] = (int)$consultingDate['id'];
            $consultingDate['article_id'] = (int)$consultingDate['article_id'];
            $consultingDate['time'] = date("H:i", (int)$consultingDate['date']);
            $consultingDate['date'] = date("Y-m-d", (int)$consultingDate['date']);
            unset($consultingDate['participant']);
    
            // set article
            $artContent = new rex_article_content((int)$consultingDate['article_id']);
    
            if(!is_null($artContent)) {
                $consultingDate['article'] = json_decode($artContent->getArticle());
            } else {
                $consultingDate['article'] = NULL;
            }
    
    
            return self::response($consultingDate, 200, true);
    
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (AccessDeniedException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 403);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
    
    /**
     * add consulting date
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function addDateAction(ServerRequestInterface $request, array $args = []) {
        try {
            $consultingDate = self::deserialize($request->getBody()->getContents(), ConsultingDateAdd::class, 'json');
            $yUser = self::getYComAuthUser();
    
            $date = $consultingDate->getDate();
            $time = $consultingDate->getTime();
            
            $dateTime = strtotime("$date $time:00");
            $dateTime = date("Y-m-d H:i:00", $dateTime);
            
            $sql = btu_portal::factory();
            $sql->setTable('portal_consulting');
            $sql->setValues([
                'participant' => $yUser->getId(),
                'article_id' => $consultingDate->getId(),
                'date' => $dateTime,
                'location' => (is_null($consultingDate->getLocation()) || $consultingDate->getLocation() == "" ? NULL : $consultingDate->getLocation()),
                'consultant' => (is_null($consultingDate->getConsultant()) || $consultingDate->getConsultant() == "" ? NULL : $consultingDate->getConsultant()),
                'notice' => (is_null($consultingDate->getNotice()) || $consultingDate->getNotice() == "" ? NULL : $consultingDate->getNotice()),
                'createdate' => date("Y-m-d H:i:s"),
                'createuser' => 'API'
            ]);
            
            if((bool)$sql->insert()) {
                return self::response(null, 201);
            } else {
                throw new \Exception('Error saving consulting', 'error_consulting_add');
            }
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
    
    /**
     * update consulting date
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function updateDateAction(ServerRequestInterface $request, array $args = []) {
        try {
            $yUser = self::getYComAuthUser();
            $id = intval($args['route_parameter']['id']);
    
            if ($id <= 0) {
                throw new InvalidArgumentException('ID is not valid', 'invalid_id');
            }
            
            // get consulting date and check
            $consultingDateFromDB = btu_portal::factory()->getArray("
                SELECT
                    *
                FROM
                    portal_consulting
                WHERE
                    id = :id
            ", [':id' => $id]);
            
            if(!$consultingDateFromDB || (is_array($consultingDateFromDB) && count($consultingDateFromDB) == 0)) {
                throw new InvalidArgumentException('Date not found', 'invalid_id');
            } elseif((int)$consultingDateFromDB[0]['participant'] != $yUser->getId()) {
                throw new AccessDeniedException('User is not allowed to change a consulting date of another user', 'wrong_user_id');
            }
            
            $consultingDate = self::deserialize($request->getBody()->getContents(), ConsultingDateAdd::class, 'json');
    
            $date = $consultingDate->getDate();
            $time = $consultingDate->getTime();
    
            $dateTime = strtotime("$date $time:00");
            $dateTime = date("Y-m-d H:i:00", $dateTime);
            
            $sql = btu_portal::factory();
            $sql->setTable('portal_consulting');
            $sql->setValues([
                'date' => $dateTime,
                'location' => (is_null($consultingDate->getLocation()) || $consultingDate->getLocation() == "" ? NULL : $consultingDate->getLocation()),
                'consultant' => (is_null($consultingDate->getConsultant()) || $consultingDate->getConsultant() == "" ? NULL : $consultingDate->getConsultant()),
                'notice' => (is_null($consultingDate->getNotice()) || $consultingDate->getNotice() == "" ? NULL : $consultingDate->getNotice()),
                'updatedate' => date("Y-m-d H:i:s"),
                'updateuser' => 'API'
            ]);
            $sql->setWhere(['id' => $id]);
            
            if((bool)$sql->update()) {
                return self::response(null, 204);
            } else {
                throw new \Exception('Error saving consulting', 'error_consulting_update');
            }
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (AccessDeniedException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 403);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
    
    /**
     * delete consulting date
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function deleteDateAction(ServerRequestInterface $request, array $args = []) {
        try {
            $yUser = self::getYComAuthUser();
            $id = intval($args['route_parameter']['id']);
            
            if ($id <= 0) {
                throw new InvalidArgumentException('ID is not valid', 'invalid_id');
            }
            
            // get consulting date and check
            $consultingDateFromDB = btu_portal::factory()->getArray("
                SELECT
                    *
                FROM
                    portal_consulting
                WHERE
                    id = :id
            ", [':id' => $id]);
            
            if(!$consultingDateFromDB || (is_array($consultingDateFromDB) && count($consultingDateFromDB) == 0)) {
                throw new InvalidArgumentException('Date not found', 'invalid_id');
            } elseif((int)$consultingDateFromDB[0]['participant'] != $yUser->getId()) {
                throw new AccessDeniedException('User is not allowed to delete a consulting date of another user', 'wrong_user_id');
            }
    
            $query = "
                DELETE FROM
                    portal_consulting
                WHERE
                    id = :id
                ";
    
            $res = btu_portal::factory()->setQuery($query, [':id' => $id]);
            
            if((bool)$res) {
                return self::response(null, 204);
            } else {
                throw new \Exception('Error deleting consulting', 'error_consulting_delete');
            }
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (AccessDeniedException $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 403);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
}