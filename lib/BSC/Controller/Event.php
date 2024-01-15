<?php
/**
 * User: joachimdorr
 * Date: 15.05.20
 * Time: 09:30
 */

namespace BSC\Controller;

use BSC\Exception\AccessDeniedException;
use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use btu_portal;
use rex_global_settings;

class Event extends AbstractController {
    public static function getAvailableEventsAction(ServerRequestInterface $request, array $args = []) {
        try {
            $yUser = self::getYComAuthUser();
            $events = btu_portal::getAvailableEventsForFrontend($yUser->getId());
            
            return self::response($events, 200, true);

        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
    
    /**
     * get event
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Peter Schulze
     */
    public static function getEventAction(ServerRequestInterface $request, array $args = []) {
        try {
            $yUser = self::getYComAuthUser();
            $id = intval($args['route_parameter']['id']);
            
            if ($id <= 0) {
                throw new InvalidArgumentException('ID is not valid', 'invalid_id');
            }
            
            // get consulting date and check
            $event = btu_portal::getParticipantEvent($yUser->getId(), $id, true);
            
            if(is_null($event) || $event['type'] == 'BLOCK' || (int)$event['canceled'] == 1) {
                throw new InvalidArgumentException('Event not found', 'invalid_id');
            } elseif((int)$event['subscribed'] == 0) {
                throw new AccessDeniedException('User is not allowed to view this event', 'not_subscribed');
            }
            
            return self::response($event, 200, true);
    
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
     * subscribe to event
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Joachim Doerr
     */
    public static function subscribeAction(ServerRequestInterface $request, array $args = [])  {
        try {
            $yUser = self::getYComAuthUser();
            $eventID = intval($args['route_parameter']['id']);

//            $event = btu_portal::getEvent($eventID);
//
//            if(!$event || !is_array($event)) {
//                throw new NotFoundException(rex_getString("app_error_event_notfound"), 'event_not_found');
//            }
            
            // check if user is allowed to subscribe
            if(!btu_portal::isParticipantAllowedToSubscribe($yUser->getId(), $eventID)) {
                throw new InvalidArgumentException(rex_getString("app_subscribe_error_notallowed"), 'subscription_not_allowed');
            }
            
            // subscribe
            $subscribeSuccess = btu_portal::subscribeParticipants([$eventID], [$yUser->getId()]);

            // milestone reminder needed?
            if($subscribeSuccess) {
                $event = btu_portal::getEvent($eventID);

                if((int)$event['requires_milestone'] == 1) {
                    btu_portal::checkMilestoneNotification($yUser->getId());
                }
            }
    
            $return = new \stdClass();
            $return->success = true;
            $return->event = btu_portal::getParticipantEvent($yUser->getId(), $eventID, true);
    
            return self::response($return, 200, true);
            
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
    
    /**
     * unsubscribe from event
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Joachim Doerr
     */
    public static function unsubscribeAction(ServerRequestInterface $request, array $args = [])  {
        try {
            $yUser = self::getYComAuthUser();
            $eventID = intval($args['route_parameter']['id']);
            $event = btu_portal::getEvent($eventID);
    
            if(!$event || !is_array($event)) {
                throw new NotFoundException(rex_getString("app_error_event_notfound"), 'event_not_found');
            }
            
            if(!btu_portal::isParticipantAllowedToUnsubscribe($yUser->getId(), $eventID)) {
                throw new InvalidArgumentException(rex_getString("app_unsubscribe_error_notallowed"), 'unsubscription_not_allowed');
            }
            
            // unsubscribe
            btu_portal::unsubscribeParticipants([$eventID], [$yUser->getId()]);
            
            $return = new \stdClass();
            $return->success = true;
            $return->event = btu_portal::getParticipantEvent($yUser->getId(), $eventID);
            
            return self::response($return, 200, true);
            
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
    
    /**
     * hide event in user timeline
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Joachim Doerr
     */
    public static function hideAction(ServerRequestInterface $request, array $args = [])  {
        try {
            $yUser = self::getYComAuthUser();
            $eventID = intval($args['route_parameter']['id']);
            $event = btu_portal::getEvent($eventID);
            
            if(!$event || !is_array($event)) {
                throw new NotFoundException(rex_getString("app_error_event_notfound"), 'event_not_found');
            }
            
            $link = btu_portal::getParticipantLinkByParticipantAndEvent($yUser->getId(), $eventID);
    
            // check if user is linked
            if(!isset($link['id'])) {
                throw new InvalidArgumentException(rex_getString("app_hide_error_notallowed"), 'hiding_not_allowed');
            }
            
            // hide
            btu_portal::hideEventSubscription($link['id']);
            return self::response(null, 204, true);
            
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
    
    /**
     * unhide/reshow event in user timeline
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Joachim Doerr
     */
    public static function unhideAction(ServerRequestInterface $request, array $args = [])  {
        try {
            $yUser = self::getYComAuthUser();
            $eventID = intval($args['route_parameter']['id']);
            $event = btu_portal::getEvent($eventID);
            
            if(!$event || !is_array($event)) {
                throw new NotFoundException(rex_getString("app_error_event_notfound"), 'event_not_found');
            }
            
            $link = btu_portal::getParticipantLinkByParticipantAndEvent($yUser->getId(), $eventID);
            
            // check if user is linked
            if(!isset($link['id'])) {
                throw new InvalidArgumentException(rex_getString("app_hide_error_notallowed"), 'hiding_not_allowed');
            }
            
            // hide
            btu_portal::unhideEventSubscription($link['id']);
            return self::response(null, 204, true);
            
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
    
    /**
     * subscribe to event
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function submitCodeAction(ServerRequestInterface $request, array $args = []) {
        try {
            /** @var \BSC\Model\Code $code */
            $code = self::deserialize($request->getBody()->getContents(), \BSC\Model\Code::class, 'json');
            $calledFromPath = trim($code->getPath());
            
            $yUser = self::getYComAuthUser();
        
            // 1. get path with code
            $path = btu_portal::getPathByCode($code->getCode());
        
            // 2. get event with code
            $event = null;
        
            if(is_null($path)) {
                $event = btu_portal::getEventByCode($code->getCode());
            }
        
            // check if code is used in current events and paths
            if(is_null($path) && is_null($event)) {
                throw new NotFoundException(rex_getString("app_code_error_notfound"), "error_code_unused");
            }
        
            if (!is_null($path)) {
                // if path param is provided check for match
                if(!is_null($calledFromPath) && $calledFromPath != "") {
                    // check base categories
                    if((int)$calledFromPath == 0) {
                        $categories = array_filter(explode(",", $path['categories']));
                        
                        if(!in_array($calledFromPath, $categories)) {
                            throw new NotFoundException(rex_getString("app_code_error_notfound"), "error_code_unused");
                        }
                    }
                    // check parent path
                    else {
                        if($calledFromPath != $path['parent']) {
                            throw new NotFoundException(rex_getString("app_code_error_notfound"), "error_code_unused");
                        }
                    }
                }
                
                btu_portal::addActivationForParticipant($yUser->getId(), 'PATH', $path['id'], strtoupper($code->getCode()));
            } else {
                // if path param is provided check for match
                if(!is_null($calledFromPath) && $calledFromPath != "") {
                    // check base categories
                    if((int)$calledFromPath == 0) {
                        $categories = array_filter(explode(",", $event['categories']));
            
                        if(!in_array($calledFromPath, $categories)) {
                            throw new NotFoundException(rex_getString("app_code_error_notfound"), "error_code_unused");
                        }
                    }
                    // check path
                    else {
                        $paths = array_filter(explode(",", $event['paths']));
                        
                        if(!in_array($calledFromPath, $paths)) {
                            throw new NotFoundException(rex_getString("app_code_error_notfound"), "error_code_unused");
                        }
                    }
                }
                
                btu_portal::addActivationForParticipant($yUser->getId(), 'EVENT', $event['id'], strtoupper($code->getCode()));
            }
        
            $menuTree = btu_portal::getAvailableEventsForFrontend($yUser->getId());
        
            // try to get path to code entity (event or path)
            $pathUri = btu_portal::getPathForReference($menuTree, (is_null($path) ? $event['id'] : $path['id']), (is_null($path) ? 'EVENT' : 'PATH'));
        
            $return = new \stdClass();
            $return->tree = $menuTree;
            $return->code = true;
            $return->codepath = $pathUri;
        
            if($pathUri == "") {
                if(!is_null($path)) {
                    $return->message = sprintf(rex_getString("app_code_message_eventpath_success"), $path['name']);
                } else {
                    $return->message = sprintf(rex_getString("app_code_message_event_success"), $event['title']);
                }
            }
        
            return self::response($return, 200, true);
        
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
}