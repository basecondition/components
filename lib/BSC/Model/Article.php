<?php
/**
 * Article
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
 * Class representing the Article model.
 *
 * @package BSC\Model
 * @author  Swagger Codegen team
 */
class Article 
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
     * @SerializedName("content")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $content;

    /**
     * @var BSC\Model\Clang|null
     * @SerializedName("clang")
     * @Assert\Type("BSC\Model\Clang")
     * @Type("BSC\Model\Clang")
     */
    protected $clang;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->content = isset($data['content']) ? $data['content'] : null;
        $this->clang = isset($data['clang']) ? $data['clang'] : null;
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
     * @param string|null $content
     *
     * @return $this
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Gets clang.
     *
     * @return BSC\Model\Clang|null
     */
    public function getClang()
    {
        return $this->clang;
    }

    /**
     * Sets clang.
     *
     * @param BSC\Model\Clang|null $clang
     *
     * @return $this
     */
    public function setClang(Clang $clang = null)
    {
        $this->clang = $clang;

        return $this;
    }
}

