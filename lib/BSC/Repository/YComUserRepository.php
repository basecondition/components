<?php

namespace BSC\Repository;

use rex_logger;
use rex_sql;
use rex_sql_exception;
use rex_yform_manager_collection;
use rex_yform_manager_dataset;
use rex_yform_manager_table;

class YComUserRepository
{
    const USER_TABLE = 'rex_ycom_user';

    public static function findUser(int $id): rex_yform_manager_dataset
    {
        return rex_yform_manager_table::get(self::USER_TABLE)
            ->query()
            ->where('id', $id)
            ->findOne();
    }

    public static function findUserByLoginName(string $name): rex_yform_manager_dataset
    {
        return rex_yform_manager_table::get(self::USER_TABLE)
            ->query()
            ->where('login', $name)
            ->findOne();
    }

    public static function findUserByMandantKey(string $key, ?int $offsetOrRowCount = null, ?int $rowCount = null): rex_yform_manager_collection
    {
        $query = rex_yform_manager_table::get(self::USER_TABLE)
            ->query()
            ->join('rex_ycom_group', null, 'rex_ycom_user.ycom_groups', 'rex_ycom_group.id')
            ->where('rex_ycom_group.name', $key);
        if (!is_null($offsetOrRowCount)) $query->limit($offsetOrRowCount, $rowCount);
        return $query->find();
    }

    public static function findUserByMandantId(int $id): rex_yform_manager_collection
    {
        return rex_yform_manager_table::get(self::USER_TABLE)
            ->query()
            ->whereListContains('ycom_groups', $id)
            ->find();
    }

    public static function updateUser(int $id, array $fields): void
    {
        $sql = rex_sql::factory();
        try {
            $sql->setTable(self::USER_TABLE);
            foreach ($fields as $field => $value) {
                $sql->setValue($field, $value);
            }
            $sql->setWhere('id = :id', ['id' => $id]);
            $sql->update();
        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
        }
    }

    public static function setTermsOfUseAccepted(int $userId): void
    {
        $sql = rex_sql::factory();
        try {
            $sql->setTable(self::USER_TABLE)
                ->setValue('termsofuse_accepted', 1)
                ->setWhere('id = :id', ['id' => $userId])
                ->update();
        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
        }
    }
}