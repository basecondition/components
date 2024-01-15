<?php

namespace BSC\Repository;

use rex_logger;
use rex_sql;
use rex_sql_exception;
use rex_yform_manager_collection;
use rex_yform_manager_dataset;
use rex_yform_manager_table;

class YComGroupRepository extends AbstractRepository
{
    const GROUP_TABLE = 'rex_ycom_group';
    const PERMISSION_TABLE = 'rex_bsc_permission';
    const GROUP_PERMISSION_TABLE = 'rex_bsc_group_permission';

    public static function findGroupByMandantKey(string $key): ?rex_yform_manager_dataset
    {
        return self::findGroupByName($key);
    }

    public static function findGroupByName(string $name): ?rex_yform_manager_dataset
    {
        return parent::findOneByKey(self::GROUP_TABLE, 'name', $name);
    }

    public static function findGroupById(int $id): ?rex_yform_manager_dataset
    {
        return parent::findOneByKey(self::GROUP_TABLE, 'id', $id);
    }

    public static function insertGroup(array $fields): null|string
    {
        return parent::insert($fields, self::GROUP_TABLE);
    }

    public static function findPermissionsByGroupId(int $id): ?rex_yform_manager_collection
    {
        // select p.*, gp.* from rex_bsc_permission p inner join rex_bsc_group_permission gp on p.id = gp.permission
        // oder für suche nach schlüsse
        // select p.*, gp.*, yg.* from rex_bsc_permission p
        //  inner join rex_bsc_group_permission gp on p.id = gp.permission
        //  inner join rex_ycom_group yg on yg.id = gp.group
        // where yg.name = 'name'
        return rex_yform_manager_table::get(self::PERMISSION_TABLE)
            ->query()
            ->join(self::GROUP_PERMISSION_TABLE, null, self::PERMISSION_TABLE . '.id', self::GROUP_PERMISSION_TABLE.'.permission')
            ->where(self::GROUP_PERMISSION_TABLE.'.group', $id)
            ->find();
    }

    public static function findPermissionByName(string $name): ?rex_yform_manager_dataset
    {
        return parent::findOneByKey(self::PERMISSION_TABLE, 'name', $name);
    }

    public static function insertPermission(array $fields): null|string
    {
        return parent::insert($fields, self::PERMISSION_TABLE);
    }
}