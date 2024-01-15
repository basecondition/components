<?php
/**
 * Clang
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
 * Class representing the Clang model.
 *
 * @package BSC\Model
 * @author  Swagger Codegen team
 */
class Clang 
{
        /**
     * @var int|null
     * @SerializedName("id")
     * @Assert\Type("int")
     * @Type("int")
     */
    protected $id;

    /**
     * @var string|null
     * @SerializedName("name")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $name;

    /**
     * @var string|null
     * @SerializedName("code")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $code;

    /**
     * @var bool|null
     * @SerializedName("default")
     * @Assert\Type("bool")
     * @Type("bool")
     */
    protected $default;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->code = isset($data['code']) ? $data['code'] : null;
        $this->default = isset($data['default']) ? $data['default'] : null;
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
     * @param int|null $id
     *
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name.
     *
     * @param string|null $name
     *
     * @return $this
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets code.
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets code.
     *
     * @param string|null $code
     *
     * @return $this
     */
    public function setCode($code = null)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Gets default.
     *
     * @return bool|null
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Sets default.
     *
     * @param bool|null $default
     *
     * @return $this
     */
    public function setDefault($default = null)
    {
        $this->default = $default;

        return $this;
    }
}

