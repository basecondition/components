<?php
/**
 * User: joachimdorr
 * Date: 05.04.20
 * Time: 09:28
 */

namespace BSC\Service;


use BSC\config;
use rex_sql;

class TanCodeGenerator
{
    /**
     * @param $id
     * @param bool $save
     * @return string
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function createTanCode($id = null, $save = true)
    {
        $tan = str_pad(mt_rand(0,9999), 4, "0", STR_PAD_LEFT);

        if ($save && !is_null($id)) {
            $sql = rex_sql::factory();
            $result = $sql->getArray('select * from '.config::get('table.ycom_user').' where id = :id', ['id' => $id]);

            if (sizeof($result) > 0 && empty($result[0]['tan'])) {
                $sql = rex_sql::factory();
                $sql->setTable(config::get('table.ycom_user'));
                $sql->setValue('verification_key', $tan);
                $sql->setWhere('id = :id', ['id' => $id]);
                $sql->update();
            }
        }

        return $tan;
    }
}