<?php
/**
 * NotificationsInner
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
 * Class representing the NotificationsInner model.
 *
 * @package BSC\Model
 * @author  Swagger Codegen team
 */
class NotificationsInner 
{
        /**
     * notification id
     *
     * @var int|null
     * @SerializedName("id")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $id;

    /**
     * id of notification - participant connection
     *
     * @var int|null
     * @SerializedName("linkid")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $linkid;

    /**
     * @var string|null
     * @SerializedName("type")
     * @Assert\Choice({ "CUSTOM", "EVENT_REMINDER", "CONSULTING_REMINDER" })
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $type;

    /**
     * read state
     *
     * @var bool|null
     * @SerializedName("read")
     * @Assert\Type("bool")
     * @Type("bool")
     */
    protected $read;

    /**
     * if type is EVENT_REMINDER or CONSULTING_REMINDER thgis field stores the related event id / consulting id
     *
     * @var int|null
     * @SerializedName("related_id")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $relatedId;

    /**
     * notification content/text
     *
     * @var string|null
     * @SerializedName("content")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $content;

    /**
     * date of creation
     *
     * @var string|null
     * @SerializedName("createdate")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $createdate;

    /**
     * timestamp of creation
     *
     * @var int|null
     * @SerializedName("createtimestamp")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $createtimestamp;

    /**
     * date of last update
     *
     * @var string|null
     * @SerializedName("updatedate")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $updatedate;

    /**
     * timestamp of last update
     *
     * @var int|null
     * @SerializedName("updatetimestamp")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $updatetimestamp;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->linkid = isset($data['linkid']) ? $data['linkid'] : null;
        $this->type = isset($data['type']) ? $data['type'] : null;
        $this->read = isset($data['read']) ? $data['read'] : null;
        $this->relatedId = isset($data['relatedId']) ? $data['relatedId'] : null;
        $this->content = isset($data['content']) ? $data['content'] : null;
        $this->createdate = isset($data['createdate']) ? $data['createdate'] : null;
        $this->createtimestamp = isset($data['createtimestamp']) ? $data['createtimestamp'] : null;
        $this->updatedate = isset($data['updatedate']) ? $data['updatedate'] : null;
        $this->updatetimestamp = isset($data['updatetimestamp']) ? $data['updatetimestamp'] : null;
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
     * @param int|null $id  notification id
     *
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets linkid.
     *
     * @return int|null
     */
    public function getLinkid()
    {
        return $this->linkid;
    }

    /**
     * Sets linkid.
     *
     * @param int|null $linkid  id of notification - participant connection
     *
     * @return $this
     */
    public function setLinkid($linkid = null)
    {
        $this->linkid = $linkid;

        return $this;
    }

    /**
     * Gets type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type.
     *
     * @param string|null $type
     *
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = $type;

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
     * @param bool|null $read  read state
     *
     * @return $this
     */
    public function setRead($read = null)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Gets relatedId.
     *
     * @return int|null
     */
    public function getRelatedId()
    {
        return $this->relatedId;
    }

    /**
     * Sets relatedId.
     *
     * @param int|null $relatedId  if type is EVENT_REMINDER or CONSULTING_REMINDER thgis field stores the related event id / consulting id
     *
     * @return $this
     */
    public function setRelatedId($relatedId = null)
    {
        $this->relatedId = $relatedId;

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
     * @param string|null $content  notification content/text
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
     * @param string|null $createdate  date of creation
     *
     * @return $this
     */
    public function setCreatedate($createdate = null)
    {
        $this->createdate = $createdate;

        return $this;
    }

    /**
     * Gets createtimestamp.
     *
     * @return int|null
     */
    public function getCreatetimestamp()
    {
        return $this->createtimestamp;
    }

    /**
     * Sets createtimestamp.
     *
     * @param int|null $createtimestamp  timestamp of creation
     *
     * @return $this
     */
    public function setCreatetimestamp($createtimestamp = null)
    {
        $this->createtimestamp = $createtimestamp;

        return $this;
    }

    /**
     * Gets updatedate.
     *
     * @return string|null
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * Sets updatedate.
     *
     * @param string|null $updatedate  date of last update
     *
     * @return $this
     */
    public function setUpdatedate($updatedate = null)
    {
        $this->updatedate = $updatedate;

        return $this;
    }

    /**
     * Gets updatetimestamp.
     *
     * @return int|null
     */
    public function getUpdatetimestamp()
    {
        return $this->updatetimestamp;
    }

    /**
     * Sets updatetimestamp.
     *
     * @param int|null $updatetimestamp  timestamp of last update
     *
     * @return $this
     */
    public function setUpdatetimestamp($updatetimestamp = null)
    {
        $this->updatetimestamp = $updatetimestamp;

        return $this;
    }
}

