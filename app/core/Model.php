<?php

require_once __DIR__ . '/Database.php';

class Model
{
    public function __construct()
    {
    }

    protected function from(string $logicalTable): SupabaseQuery
    {
        return Database::from($logicalTable);
    }

    protected function table(string $name): string
    {
        return Database::table($name);
    }
}
