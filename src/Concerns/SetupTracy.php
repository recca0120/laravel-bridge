<?php

namespace Recca0120\LaravelBridge\Concerns;

use Illuminate\Database\Events\QueryExecuted;
use Recca0120\LaravelTracy\Tracy;

trait SetupTracy
{
    /**
     * @param array $config
     *
     * @return static
     */
    public function setupTracy($config = [])
    {
        $tracy = Tracy::instance($config);
        $databasePanel = $tracy->getPanel('database');
        $this->getEvents()->listen(QueryExecuted::class, function ($event) use ($databasePanel) {
            $sql = $event->sql;
            $bindings = $event->bindings;
            $time = $event->time;
            $name = $event->connectionName;
            $pdo = $event->connection->getPdo();

            $databasePanel->logQuery($sql, $bindings, $time, $name, $pdo);
        });

        return $this;
    }
}
