<?php
/**
 * @date     10.06.2020 16:11
 * @author   Peter Schulze [p.schulze@bitshifters.de]
 */

namespace BSC\Controller;

use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use OAuth2\Response;
use btu_portal;

class Notification extends AbstractController
{
    /**
     * mark notification read
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function markNotificationReadAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            
            $notificationID = intval($args['route_parameter']['id']);
            $notification = btu_portal::getNotification($notificationID, $yUser->getId());
            
            if(!is_array($notification)) {
                throw new NotFoundException("The notification id provided is invalid.", "unknown_conversation");
            }
            
            if(!$notification['allowed']) {
                throw new InvalidArgumentException('User is not allowed to mark that notification read', 'wrong_user_id');
            }
            
            if(btu_portal::markNotificationRead($notificationID, $yUser->getId())) {
                return new Response([], 200);
            } else {
                throw new \Exception("Error marking notification read", "error_notification_read");
            }
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
    
    /**
     * get unread notifications for logged in user
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function getNotificationsAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            $notifications = btu_portal::getParticipantNotifications($yUser->getId());
            
            return new Response($notifications, 200);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
    
    /**
     * get unread notifications for logged in user
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function getUnreadNotificationsAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            $unread = btu_portal::getParticipantUnreadNotificationsCount($yUser->getId());
            
            return new Response(["unread" => $unread], 200);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
}