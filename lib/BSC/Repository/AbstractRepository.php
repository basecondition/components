<?php

namespace BSC\Repository;

use rex_logger;
use rex_sql;
use rex_sql_exception;
use rex_yform_manager_collection;
use rex_yform_manager_dataset;
use rex_yform_manager_table;

abstract class AbstractRepository
{
    protected static function findOneByKey(string $table, string $key, mixed $value, string $operator = '='): ?rex_yform_manager_dataset
    {
        return rex_yform_manager_table::get($table)
            ->query()
            ->where($key, $value, '=')
            ->findOne();
    }

    protected static function findByKey(string $table, string $key, mixed $value, string $operator = '='): rex_yform_manager_collection
    {
        return rex_yform_manager_table::get($table)
            ->query()
            ->where($key, $value, '=')
            ->find();
    }

    protected static function insert(array $fields, string $table): null|string
    {
        $sql = rex_sql::factory();
        try {
            $sql->setTable($table);
            foreach ($fields as $field => $value) {
                $sql->setValue($field, $value);
            }
            $sql->insert();
            return $sql->getLastId();
        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
        }
        return null;
    }
}