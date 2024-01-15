<?php
/**
 * MessageConversationsInner
 *
 * PHP version 5
 *
 * @category Class
 * @package  BSC\Model
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * BSC API
 *
 * BSC API
 *
 * OpenAPI spec version: 0.0.1
 * 
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 *
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace BSC\Model;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Class representing the MessageConversationsInner model.
 *
 * @package BSC\Model
 * @author  Swagger Codegen team
 */
class MessageConversationsInner 
{
        /**
     * conversation id (root message)
     *
     * @var int|null
     * @SerializedName("id")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $id;

    /**
     * flag if conversation root message and every reply from BTU side was read by client
     *
     * @var bool|null
     * @SerializedName("read")
     * @Assert\Type("bool")
     * @Type("bool")
     */
    protected $read;

    /**
     * content of the initial conversation message
     *
     * @var string|null
     * @SerializedName("content")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $content;

    /**
     * create timestamp of the initial conversation (root message)
     *
     * @var string|null
     * @SerializedName("createdate")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $createdate;

    /**
     * timestamp of the latest reply (falls back to timestamp of root message, when there is no reply)
     *
     * @var string|null
     * @SerializedName("latest_date")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $latestDate;

    /**
     * create user of initial conversation (root message); API (user) or BACKEND_USER (BTU)
     *
     * @var string|null
     * @SerializedName("createdby")
     * @Assert\Choice({ "API", "BACKEND_USER" })
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $createdby;

    /**
     * conversation was closed by BTU
     *
     * @var bool|null
     * @SerializedName("closed")
     * @Assert\Type("bool")
     * @Type("bool")
     */
    protected $closed;

    /**
     * sha1 hash of whole conversation to quickly identy changes
     *
     * @var string|null
     * @SerializedName("hash")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $hash;

    /**
     * sum of all replies
     *
     * @var int|null
     * @SerializedName("replies")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $replies;

    /**
     * @var BSC\Model\MessageConversationsInnerLatestReply[]|null
     * @SerializedName("latest_reply")
     * @Assert\All({
     *   @Assert\Type("BSC\Model\MessageConversationsInnerLatestReply")
     * })
     * @Type("array<BSC\Model\MessageConversationsInnerLatestReply>")
     */
    protected $latestReply;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->read = isset($data['read']) ? $data['read'] : null;
        $this->content = isset($data['content']) ? $data['content'] : null;
        $this->createdate = isset($data['createdate']) ? $data['createdate'] : null;
        $this->latestDate = isset($data['latestDate']) ? $data['latestDate'] : null;
        $this->createdby = isset($data['createdby']) ? $data['createdby'] : null;
        $this->closed = isset($data['closed']) ? $data['closed'] : null;
        $this->hash = isset($data['hash']) ? $data['hash'] : null;
        $this->replies = isset($data['replies']) ? $data['replies'] : null;
        $this->latestReply = isset($data['latestReply']) ? $data['latestReply'] : null;
    }

    /**
     * Gets id.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets id.
     *
     * @param int|null $id  conversation id (root message)
     *
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets read.
     *
     * @return bool|null
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * Sets read.
     *
     * @param bool|null $read  flag if conversation root message and every reply from BTU side was read by client
     *
     * @return $this
     */
    public function setRead($read = null)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Gets content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets content.
     *
     * @param string|null $content  content of the initial conversation message
     *
     * @return $this
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Gets createdate.
     *
     * @return string|null
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * Sets createdate.
     *
     * @param string|null $createdate  create timestamp of the initial conversation (root message)
     *
     * @return $this
     */
    public function setCreatedate($createdate = null)
    {
        $this->createdate = $createdate;

        return $this;
    }

    /**
     * Gets latestDate.
     *
     * @return string|null
     */
    public function getLatestDate()
    {
        return $this->latestDate;
    }

    /**
     * Sets latestDate.
     *
     * @param string|null $latestDate  timestamp of the latest reply (falls back to timestamp of root message, when there is no reply)
     *
     * @return $this
     */
    public function setLatestDate($latestDate = null)
    {
        $this->latestDate = $latestDate;

        return $this;
    }

    /**
     * Gets createdby.
     *
     * @return string|null
     */
    public function getCreatedby()
    {
        return $this->createdby;
    }

    /**
     * Sets createdby.
     *
     * @param string|null $createdby  create user of initial conversation (root message); API (user) or BACKEND_USER (BTU)
     *
     * @return $this
     */
    public function setCreatedby($createdby = null)
    {
        $this->createdby = $createdby;

        return $this;
    }

    /**
     * Gets closed.
     *
     * @return bool|null
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Sets closed.
     *
     * @param bool|null $closed  conversation was closed by BTU
     *
     * @return $this
     */
    public function setClosed($closed = null)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Gets hash.
     *
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Sets hash.
     *
     * @param string|null $hash  sha1 hash of whole conversation to quickly identy changes
     *
     * @return $this
     */
    public function setHash($hash = null)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Gets replies.
     *
     * @return int|null
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Sets replies.
     *
     * @param int|null $replies  sum of all replies
     *
     * @return $this
     */
    public function setReplies($replies = null)
    {
        $this->replies = $replies;

        return $this;
    }

    /**
     * Gets latestReply.
     *
     * @return BSC\Model\MessageConversationsInnerLatestReply[]|null
     */
    public function getLatestReply()
    {
        return $this->latestReply;
    }

    /**
     * Sets latestReply.
     *
     * @param BSC\Model\MessageConversationsInnerLatestReply[]|null $latestReply
     *
     * @return $this
     */
    public function setLatestReply(MessageConversationsInnerLatestReply $latestReply = null)
    {
        $this->latestReply = $latestReply;

        return $this;
    }
}


