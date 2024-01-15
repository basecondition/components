<?php
/**
 * User: joachimdorr
 * Date: 08.04.20
 * Time: 09:03
 */

namespace BSC\Service;


use BSC\Model\Error;
use BSC\Trait\Serializer;
use BSC\Trait\ModelPropertiesMapper;
use Doctrine\Common\Annotations\AnnotationException;
use rex_logger;
use rex_sql;

class EntityManager
{
    use ModelPropertiesMapper;
    use Serializer;

    /**
     * @param $entityClassName
     * @param $query
     * @param array $ignore
     * @param array $map
     * @param array $overwrite
     * @param bool $ignoreNull
     * @return object
     * @author Joachim Doerr
     */
    public static function findOneBy($entityClassName, $query, $params = [], $ignore = [], $map = [], $overwrite = [], $ignoreNull = true)
    {
        try {
            $sql = rex_sql::factory();
            $result = $sql->getArray($query, $params);

            if (is_array($result) && sizeof($result) > 0) {
                $result = $result[0];
            }

            $item = $result;

            foreach ($result as $key => $value) {
                if ($ignoreNull && empty($value)) {
                    unset($item[$key]);
                }

                if (in_array($key, $ignore)) {
                    unset($item[$key]);
                }

                if (array_key_exists($key, $map)) {
                    $item[$map[$key]] = $value;
                }

                if (array_key_exists($key, $overwrite)) {
                    $item[$key] = $overwrite[$key];
                }
            }

            return self::deserialize(json_encode($item), $entityClassName, 'json');
        } catch (\rex_sql_exception $e) {
            rex_logger::logException($e);
            return new Error(array('error' => 'sql_exception', 'errorDescription' => $e->getMessage()));
        }
    }

    /*
    public static function findBy($entityClassName, $query, $ignore = [], $map = [], $overwrite = [], $ignoreNull = true)
    {
        // $sql = rex_sql::factory();
        // return $sql->getArray("select * from {$tables[$table]} where id in ('" . implode('\',\'', $IDs) . "')" . SqlPagerHelper::getLimit($page, $size));
    }
    */

    /**
     * @param $entity
     * @param $table
     * @param array $ignore
     * @param array $map
     * @param array $overwrite
     * @param bool $ignoreNull
     * @param bool $getId
     * @return object|Error|integer
     * @author Joachim Doerr
     */
    public static function persist($entity, $table, $ignore = [], $map = [], $overwrite = [], $ignoreNull = false, $getId = false)
    {
        try {
            $result = self::modelPropertiesToSerializerNames($entity);
            
            if (sizeof($result) > 0) {
                $id = null;
                $properties = [];
                
                foreach ($result as $item) {
                    if ($item->getName() == 'id') {
                        $id = $item->getValue();
                        continue;
                    }
                    
                    if (in_array($item->getName(), $ignore))
                        continue;
                    
                    if (array_key_exists($item->getName(), $map)) {
                        $properties[$map[$item->getName()]] = $item->getValue();
                    } else {
                        $properties[$item->getName()] = $item->getValue();
                    }
                }

                if (is_null($id) && array_key_exists('id', $overwrite) && is_numeric($overwrite['id']))
                    $id = intval($overwrite['id']);
                
                if (!is_null($id) && $id === 0)
                    $id = null;

                $sql = rex_sql::factory();
                $sql->setTable($table);
                
                if (sizeof($properties) > 0) {
                    foreach ($properties as $name => $value) {
                        if (is_null($id) && is_null($value) || array_key_exists($name, $overwrite) || $ignoreNull && is_null($value))
                            continue;
                        
                        $sql->setValue($name, $value);
                    }
                }
                
                if (sizeof($overwrite) > 0) {
                    foreach ($overwrite as $name => $value) {
                        $sql->setValue($name, $value);
                    }
                }
                
                if (!is_null($id)) {
                    if (is_numeric($id)) {
                        $sql->setWhere('id = :id', ['id' => intval($id)]);
                    }
                    $sql->update();
                } else {
                    $sql->insert();
                }
                
                if (method_exists($entity, 'setId'))
                    $entity->setId($sql->getLastId());
                
                if ($getId)
                    return $sql->getLastId();
            }

            return $entity;

        } catch (AnnotationException $e) {
            //rex_logger::logException($e);
            return new Error(['error' => 'annotation_exception', 'errorDescription' => $e->getMessage()]);
        } catch (\ReflectionException $e) {
            //rex_logger::logException($e);
            return new Error(['error' => 'annotation_exception', 'errorDescription' => $e->getMessage()]);
        } catch (\rex_sql_exception $e) {
            //rex_logger::logException($e);
            return new Error(['error' => 'annotation_exception', 'errorDescription' => $e->getMessage()]);
        }
    }

    /**
     * @param null $page
     * @param null $size
     * @return string
     * @author Joachim Doerr
     */
    private static function getLimit($page = null, $size = null)
    {
        $limit = '';
        if (is_int($page) && is_int($size)) {
            $limit = ' limit ' . ($page * $size) . ',' . $size;
        }
        return $limit;
    }
}