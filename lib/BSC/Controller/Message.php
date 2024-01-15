<?php
/**
 * @date     11.05.2020 23:11
 * @author   Peter Schulze [p.schulze@bitshifters.de]
 */

namespace BSC\Controller;

use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use BSC\Model\MessageSend;
use Psr\Http\Message\ServerRequestInterface;
use OAuth2\Response;
use btu_portal;

class Message extends AbstractController
{
    /**
     * send new message (start new conversation)
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function sendMessageAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $messageSend = self::deserialize($request->getBody()->getContents(), MessageSend::class, 'json');
            //$messageSend = rex_extension::registerPoint(new rex_extension_point('BSC_API_PRE_CREATE_MESSAGE', $messageSend));
            $yUser = self::getYComAuthUser();

            $newID = btu_portal::saveMessage($yUser->getId(), $messageSend->getContent(), 'API');
            
            return new Response(["id" => $newID] , 200);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }

    /**
     * send reply to existing conversation
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function sendReplyAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $messageSend = self::deserialize($request->getBody()->getContents(), MessageSend::class, 'json');
            //$messageSend = rex_extension::registerPoint(new rex_extension_point('BSC_API_PRE_CREATE_REPLY', $messageSend));
            $yUser = self::getYComAuthUser();

            $conversationID = intval($args['route_parameter']['id']);
            $conversation = btu_portal::getMessage($conversationID);

            if(!is_array($conversation) || $conversation['parent'] != "") {
                throw new NotFoundException("The message id provided is invalid.", "unknown_conversation");
            }

            if((int)$conversation['participant'] !== (int)$yUser->getId()) {
                throw new InvalidArgumentException('User is not allowed to reply to a message of another user', 'wrong_user_id');
            }

            btu_portal::saveMessage($yUser->getId(),$messageSend->getContent() , 'API', $conversationID);
            return new Response([], 201);
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
     * mark conversation read
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function markConversationReadAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();

            $conversationID = intval($args['route_parameter']['id']);
            $conversation = btu_portal::getMessage($conversationID);

            if(!is_array($conversation) || $conversation['parent'] != "") {
                throw new NotFoundException("The message id provided is invalid.", "unknown_conversation");
            }

            if((int)$conversation['participant'] !== (int)$yUser->getId()) {
                throw new InvalidArgumentException('User is not allowed to mark a message of another user read', 'wrong_user_id');
            }

            btu_portal::markMessageRead($conversationID, true, true, 'BACKEND_USER');
            return new Response([], 201);
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
     * get single conversation with replies
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function getConversationAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();

            $conversationID = intval($args['route_parameter']['id']);
            $conversation = btu_portal::getMessage($conversationID);

            if(!is_array($conversation) || $conversation['parent'] != "") {
                throw new NotFoundException("The message id provided is invalid.", "unknown_conversation");
            }

            if((int)$conversation['participant'] !== (int)$yUser->getId()) {
                throw new InvalidArgumentException('User is not allowed to receive messages of another user', 'wrong_user_id');
            }

            $conversation = btu_portal::getConversation($conversationID, false, true);

            return new Response($conversation, 200);
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
     * get conversations ordered by last activity
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function getConversationsAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            $conversations = btu_portal::getParticipantConversations($yUser->getId());

            return new Response($conversations, 200);
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
     * get unread conversations for logged in user
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function getUnreadConversationsAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
            $unread = btu_portal::getParticipantUnreadConversationsCount($yUser->getId());
            
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