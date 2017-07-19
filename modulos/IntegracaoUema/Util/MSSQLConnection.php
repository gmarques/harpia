<?php

namespace Modulos\IntegracaoUema\Util;

use PDO;
use PDOException;

class MSSQLConnection
{
    protected $conn;

    public function __construct()
    {
        try {
            $database = env('MSSQL_DB_DATABASE', 'nead');
            $user = env('MSSQL_DB_USER', '');
            $pass = env('MSSQL_DB_PASS', '');

            $connection = new PDO("odbc:DRIVER=freetds;SERVERNAME=mssql;DATABASE={$database};charset=iso-8859-1",
                $user, $pass);

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
        } catch (PDOException $e) {
            throw $e;
        }

        $this->conn = $connection;

        return $this;
    }

    public function fetch($sql)
    {
        try {
            $query = $this->conn->query($sql);

            return $query->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function fetchAll($sql)
    {
        try {
            $query = $this->conn->query($sql);

            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
}
