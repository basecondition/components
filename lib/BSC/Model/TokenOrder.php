<?php
/**
 * TokenOrder
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
 * Class representing the TokenOrder model.
 *
 * @package BSC\Model
 * @author  Swagger Codegen team
 */
class TokenOrder 
{
        /**
     * @var string
     * @SerializedName("grant_type")
     * @Assert\NotNull()
     * @Assert\Choice({ "password", "refresh_token" })
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $grantType;

    /**
     * @var string
     * @SerializedName("client_id")
     * @Assert\NotNull()
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $clientId;

    /**
     * @var string
     * @SerializedName("client_secret")
     * @Assert\NotNull()
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $clientSecret;

    /**
     * @var string|null
     * @SerializedName("refresh_token")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $refreshToken;

    /**
     * @var string|null
     * @SerializedName("username")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $username;

    /**
     * @var string|null
     * @SerializedName("password")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $password;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->grantType = isset($data['grantType']) ? $data['grantType'] : null;
        $this->clientId = isset($data['clientId']) ? $data['clientId'] : null;
        $this->clientSecret = isset($data['clientSecret']) ? $data['clientSecret'] : null;
        $this->refreshToken = isset($data['refreshToken']) ? $data['refreshToken'] : null;
        $this->username = isset($data['username']) ? $data['username'] : null;
        $this->password = isset($data['password']) ? $data['password'] : null;
    }

    /**
     * Gets grantType.
     *
     * @return string
     */
    public function getGrantType()
    {
        return $this->grantType;
    }

    /**
     * Sets grantType.
     *
     * @param string $grantType
     *
     * @return $this
     */
    public function setGrantType($grantType)
    {
        $this->grantType = $grantType;

        return $this;
    }

    /**
     * Gets clientId.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Sets clientId.
     *
     * @param string $clientId
     *
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Gets clientSecret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Sets clientSecret.
     *
     * @param string $clientSecret
     *
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Gets refreshToken.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Sets refreshToken.
     *
     * @param string|null $refreshToken
     *
     * @return $this
     */
    public function setRefreshToken($refreshToken = null)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Gets username.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets username.
     *
     * @param string|null $username
     *
     * @return $this
     */
    public function setUsername($username = null)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets password.
     *
     * @param string|null $password
     *
     * @return $this
     */
    public function setPassword($password = null)
    {
        $this->password = $password;

        return $this;
    }
}


