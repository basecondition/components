<?php
/**
 * User: joachimdorr
 * Date: 16.04.20
 * Time: 08:25
 */

namespace BSC\Trait;


use BSC\config;
use BSC\Exception\NotFoundException;
use BSC\Service\EntityManager;
use BSC\Model\Member;
use rex_sql;

trait Provider
{
    /**
     * @var null|string
     */
    protected static $uri = null;

    /**
     * @param $by
     * @param $value
     * @param bool $asObject
     * @param array $ignore
     * @param array $map
     * @param array $overwrite
     * @param bool $ignoreNull
     * @return mixed|object|null
     * @throws \rex_sql_exception
     * @throws NotFoundException
     * @author Joachim Doerr
     */
    protected static function getUserData($by, $value, $asObject = false, $ignore = [], $map = [], $overwrite = [], $ignoreNull = true)
    {
        $table = config::get('table.ycom_user');
        $query = "select * from $table where $by = :value";

        if ($asObject) {
            $result = EntityManager::findOneBy(Member::class, $query, [':value' => $value], $ignore, $map, $overwrite, $ignoreNull);

            if ($result instanceof Member && !empty($result->getId())) {
                return $result;
            }
        } else {
            $sql = rex_sql::factory();
            $result = $sql->getArray($query, [':value' => $value]);

            if (sizeof($result) > 0) {
                return $result[0];
            }
        }

        throw new NotFoundException('User not found', 'user_not_found');
    }

    /**
     * @return false|string
     * @author Joachim Doerr
     */
    protected static function getRequestBody()
    {
        return file_get_contents('php://input');
    }
}