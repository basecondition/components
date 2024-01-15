<?php

namespace BSC\Repository;

use rex_yform_manager_collection;
use rex_yform_manager_dataset;
use rex_yform_manager_table;

class MandantRepository extends AbstractRepository
{
    const MANDANT_TABLE = 'rex_bsc_mandant';

    public static function getAllMandants(): rex_yform_manager_collection
    {
        return rex_yform_manager_table::get(self::MANDANT_TABLE)->query()->find();
    }

    public static function getMandantByKey(string $key): ?rex_yform_manager_dataset
    {
        return parent::findOneByKey(self::MANDANT_TABLE, 'key', $key);
    }
}