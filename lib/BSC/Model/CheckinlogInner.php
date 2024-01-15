<?php
/**
 * CheckinlogInner
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
 * Class representing the CheckinlogInner model.
 *
 * @package BSC\Model
 * @author  Swagger Codegen team
 */
class CheckinlogInner 
{
        /**
     * event id
     *
     * @var int|null
     * @SerializedName("event_id")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $eventId;

    /**
     * if type &#x3D; BLOCK, related BLOCKEVENT id
     *
     * @var int|null
     * @SerializedName("parent")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $parent;

    /**
     * event type
     *
     * @var string|null
     * @SerializedName("type")
     * @Assert\Choice({ "BLOCKEVENT", "SINGLEEVENT", "BLOCK" })
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $type;

    /**
     * block title
     *
     * @var string|null
     * @SerializedName("title")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $title;

    /**
     * \\[only appears when type &#x3D; BLOCKEVENT\\] if type &#x3D; BLOCKEVENT, number of all non-canceled blocks included
     *
     * @var int|null
     * @SerializedName("blocks")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $blocks;

    /**
     * block index (related to all blocks)
     *
     * @var int|null
     * @SerializedName("block_index")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $blockIndex;

    /**
     * block day
     *
     * @var int|null
     * @SerializedName("block_day")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $blockDay;

    /**
     * @var bool|null
     * @SerializedName("checkout_required")
     * @Assert\Type("bool")
     * @Type("bool")
     */
    protected $checkoutRequired;

    /**
     * whether there are restrictions for allowed times to checkin/checkout (implies if some checkin errors can occur or not)
     *
     * @var bool|null
     * @SerializedName("checkin_ignore_offsets")
     * @Assert\Type("bool")
     * @Type("bool")
     */
    protected $checkinIgnoreOffsets;

    /**
     * start date (as JS timestamp)
     *
     * @var int|null
     * @SerializedName("startdate")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $startdate;

    /**
     * end date (as JS timestamp)
     *
     * @var int|null
     * @SerializedName("enddate")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $enddate;

    /**
     * @var bool|null
     * @SerializedName("checkin_required")
     * @Assert\Type("bool")
     * @Type("bool")
     */
    protected $checkinRequired;

    /**
     * checkin state (order by successfuls)
     *
     * @var string|null
     * @SerializedName("checkin_state")
     * @Assert\Choice({ "CHECKIN", "TOOKPART", "SICKCALL", "TOOEARLY", "TOOLATE" })
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $checkinState;

    /**
     * checkin state date
     *
     * @var int|null
     * @SerializedName("checkin_date")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $checkinDate;

    /**
     * checkout state (order by successfuls)
     *
     * @var string|null
     * @SerializedName("checkout_state")
     * @Assert\Choice({ "CHECKOUT", "TOOEARLY", "TOOLATE" })
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $checkoutState;

    /**
     * checkout state date
     *
     * @var int|null
     * @SerializedName("checkout_date")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $checkoutDate;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->eventId = isset($data['eventId']) ? $data['eventId'] : null;
        $this->parent = isset($data['parent']) ? $data['parent'] : null;
        $this->type = isset($data['type']) ? $data['type'] : null;
        $this->title = isset($data['title']) ? $data['title'] : null;
        $this->blocks = isset($data['blocks']) ? $data['blocks'] : null;
        $this->blockIndex = isset($data['blockIndex']) ? $data['blockIndex'] : null;
        $this->blockDay = isset($data['blockDay']) ? $data['blockDay'] : null;
        $this->checkoutRequired = isset($data['checkoutRequired']) ? $data['checkoutRequired'] : false;
        $this->checkinIgnoreOffsets = isset($data['checkinIgnoreOffsets']) ? $data['checkinIgnoreOffsets'] : true;
        $this->startdate = isset($data['startdate']) ? $data['startdate'] : null;
        $this->enddate = isset($data['enddate']) ? $data['enddate'] : null;
        $this->checkinRequired = isset($data['checkinRequired']) ? $data['checkinRequired'] : true;
        $this->checkinState = isset($data['checkinState']) ? $data['checkinState'] : null;
        $this->checkinDate = isset($data['checkinDate']) ? $data['checkinDate'] : null;
        $this->checkoutState = isset($data['checkoutState']) ? $data['checkoutState'] : null;
        $this->checkoutDate = isset($data['checkoutDate']) ? $data['checkoutDate'] : null;
    }

    /**
     * Gets eventId.
     *
     * @return int|null
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Sets eventId.
     *
     * @param int|null $eventId  event id
     *
     * @return $this
     */
    public function setEventId($eventId = null)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Gets parent.
     *
     * @return int|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets parent.
     *
     * @param int|null $parent  if type = BLOCK, related BLOCKEVENT id
     *
     * @return $this
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;

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
     * @param string|null $type  event type
     *
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets title.
     *
     * @param string|null $title  block title
     *
     * @return $this
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets blocks.
     *
     * @return int|null
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Sets blocks.
     *
     * @param int|null $blocks  \\[only appears when type = BLOCKEVENT\\] if type = BLOCKEVENT, number of all non-canceled blocks included
     *
     * @return $this
     */
    public function setBlocks($blocks = null)
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * Gets blockIndex.
     *
     * @return int|null
     */
    public function getBlockIndex()
    {
        return $this->blockIndex;
    }

    /**
     * Sets blockIndex.
     *
     * @param int|null $blockIndex  block index (related to all blocks)
     *
     * @return $this
     */
    public function setBlockIndex($blockIndex = null)
    {
        $this->blockIndex = $blockIndex;

        return $this;
    }

    /**
     * Gets blockDay.
     *
     * @return int|null
     */
    public function getBlockDay()
    {
        return $this->blockDay;
    }

    /**
     * Sets blockDay.
     *
     * @param int|null $blockDay  block day
     *
     * @return $this
     */
    public function setBlockDay($blockDay = null)
    {
        $this->blockDay = $blockDay;

        return $this;
    }

    /**
     * Gets checkoutRequired.
     *
     * @return bool|null
     */
    public function isCheckoutRequired()
    {
        return $this->checkoutRequired;
    }

    /**
     * Sets checkoutRequired.
     *
     * @param bool|null $checkoutRequired
     *
     * @return $this
     */
    public function setCheckoutRequired($checkoutRequired = null)
    {
        $this->checkoutRequired = $checkoutRequired;

        return $this;
    }

    /**
     * Gets checkinIgnoreOffsets.
     *
     * @return bool|null
     */
    public function isCheckinIgnoreOffsets()
    {
        return $this->checkinIgnoreOffsets;
    }

    /**
     * Sets checkinIgnoreOffsets.
     *
     * @param bool|null $checkinIgnoreOffsets  whether there are restrictions for allowed times to checkin/checkout (implies if some checkin errors can occur or not)
     *
     * @return $this
     */
    public function setCheckinIgnoreOffsets($checkinIgnoreOffsets = null)
    {
        $this->checkinIgnoreOffsets = $checkinIgnoreOffsets;

        return $this;
    }

    /**
     * Gets startdate.
     *
     * @return int|null
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * Sets startdate.
     *
     * @param int|null $startdate  start date (as JS timestamp)
     *
     * @return $this
     */
    public function setStartdate($startdate = null)
    {
        $this->startdate = $startdate;

        return $this;
    }

    /**
     * Gets enddate.
     *
     * @return int|null
     */
    public function getEnddate()
    {
        return $this->enddate;
    }

    /**
     * Sets enddate.
     *
     * @param int|null $enddate  end date (as JS timestamp)
     *
     * @return $this
     */
    public function setEnddate($enddate = null)
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * Gets checkinRequired.
     *
     * @return bool|null
     */
    public function isCheckinRequired()
    {
        return $this->checkinRequired;
    }

    /**
     * Sets checkinRequired.
     *
     * @param bool|null $checkinRequired
     *
     * @return $this
     */
    public function setCheckinRequired($checkinRequired = null)
    {
        $this->checkinRequired = $checkinRequired;

        return $this;
    }

    /**
     * Gets checkinState.
     *
     * @return string|null
     */
    public function getCheckinState()
    {
        return $this->checkinState;
    }

    /**
     * Sets checkinState.
     *
     * @param string|null $checkinState  checkin state (order by successfuls)
     *
     * @return $this
     */
    public function setCheckinState($checkinState = null)
    {
        $this->checkinState = $checkinState;

        return $this;
    }

    /**
     * Gets checkinDate.
     *
     * @return int|null
     */
    public function getCheckinDate()
    {
        return $this->checkinDate;
    }

    /**
     * Sets checkinDate.
     *
     * @param int|null $checkinDate  checkin state date
     *
     * @return $this
     */
    public function setCheckinDate($checkinDate = null)
    {
        $this->checkinDate = $checkinDate;

        return $this;
    }

    /**
     * Gets checkoutState.
     *
     * @return string|null
     */
    public function getCheckoutState()
    {
        return $this->checkoutState;
    }

    /**
     * Sets checkoutState.
     *
     * @param string|null $checkoutState  checkout state (order by successfuls)
     *
     * @return $this
     */
    public function setCheckoutState($checkoutState = null)
    {
        $this->checkoutState = $checkoutState;

        return $this;
    }

    /**
     * Gets checkoutDate.
     *
     * @return int|null
     */
    public function getCheckoutDate()
    {
        return $this->checkoutDate;
    }

    /**
     * Sets checkoutDate.
     *
     * @param int|null $checkoutDate  checkout state date
     *
     * @return $this
     */
    public function setCheckoutDate($checkoutDate = null)
    {
        $this->checkoutDate = $checkoutDate;

        return $this;
    }
}

