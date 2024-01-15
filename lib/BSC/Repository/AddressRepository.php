<?php

namespace BSC\Repository;

use rex_logger;
use rex_sql;
use rex_sql_exception;
use rex_yform_manager_collection;
use rex_yform_manager_dataset;
use rex_yform_manager_table;

class AddressRepository
{
    const ADDRESS_TABLE = 'rex_bsc_address';
    const USER_ADDRESS_TABLE = 'rex_bsc_user_address';
    const USER_TABLE = 'rex_ycom_user';

    public static function findUserAddresses($userId): rex_yform_manager_collection
    {
        return rex_yform_manager_table::get(self::ADDRESS_TABLE)
            ->query()
            ->where('user', $userId)
            ->find();
    }

    public static function findUserAddressRelation(int $userId, int $addressId): null|rex_yform_manager_dataset
    {
        return rex_yform_manager_table::get(self::USER_ADDRESS_TABLE)
            ->query()
            ->where('user', $userId, '=')
            ->where('address', $addressId, '=')
            ->findOne();
    }

    public static function insertAddress(array $fields): null|string
    {
        $sql = rex_sql::factory();
        try {
            $sql->setTable(self::ADDRESS_TABLE);
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

    public static function insertUserAddressRelation(int $userId, int $addressId): void
    {
        $relation = self::findUserAddressRelation($userId, $addressId);
        if (is_null($relation)) {
            $sql = rex_sql::factory();
            try {
                $sql->setTable(self::USER_ADDRESS_TABLE)
                    ->setValue('user', $userId)
                    ->setValue('address', $addressId)
                    ->insert();
            } catch (rex_sql_exception $e) {
                rex_logger::logException($e);
            }
        }
    }

    public static function setUserTermsOfUseAccepted(int $userId): void
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