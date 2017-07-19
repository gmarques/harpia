<?php

namespace Modulos\IntegracaoUema\Traits;

use PDO;
use PDOException;

trait MSSQLConnection
{
    final public function getMSSQLConn()
    {
        try {
            $database = env('MSSQL_DB_DATABASE', 'nead');
            $user = env('MSSQL_DB_USER', '');
            $pass = env('MSSQL_DB_PASS', '');

            $connection = new PDO("odbc:DRIVER=freetds;SERVERNAME=mssql;DATABASE={$database};charset=UTF-8",
                $user, $pass);

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
        } catch (PDOException $e) {
            throw $e;
        }

        return $connection;
    }
}
