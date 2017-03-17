<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Schema\Schema;

class CalculationRepository
{
    const STATE_IN_QUEUE = 0;
    const STATE_RUNNING = 1;
    const STATE_FINISHED_SUCCESSFUL = 11;
    const STATE_FINISHED_WITH_ERRORS = 12;

    /** @var Connection $connection */
    protected $connection;

    /** @var Schema $schema */
    protected $schema;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        $this->schema = new Schema();
        $table = $this->schema->createTable(Table::CALCULATIONS);
        $table->addColumn('id', 'integer', array("unsigned" => true, "autoincrement" => true));
        $table->addColumn('calculation_id', 'string', ['length' => 36, 'notnull' => false]);
        $table->addColumn('added_to_queue', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('calculation_started', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('calculation_finished', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('state', 'integer', ['default' => 0]);
        $table->addColumn('output', 'text', ['default' => '']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(array('calculation_id'));
    }

    public function addCalculation(string $id): void
    {
        $dateTime = new \DateTime();

        $this->connection->insert(Table::CALCULATIONS, array(
            'calculation_id' => $id,
            'added_to_queue' => $dateTime->format(DATE_ATOM)
        ));
    }

    public function calculationStarted(string $id): void
    {
        $dateTime = new \DateTime();

        $this->connection->update(
            Table::CALCULATIONS,
            array(
                'calculation_started' => $dateTime->format(DATE_ATOM),
                'state' => $this::STATE_RUNNING
            ),
            array('calculation_id' => $id)
        );
    }

    public function calculationFinished(string $id, bool $withSuccess, string $output): void
    {
        $dateTime = new \DateTime();
        $state = $this::STATE_FINISHED_SUCCESSFUL;
        if (! $withSuccess) {$state = $this::STATE_FINISHED_WITH_ERRORS;}

        $this->connection->update(
            Table::CALCULATIONS,
            array(
                'calculation_finished' => $dateTime->format(DATE_ATOM),
                'state' => $state,
                'output' => $output
            ),
            array('calculation_id' => $id)
        );
    }

    public function fetchAll(): array
    {
        return $this->connection->fetchAll(sprintf('SELECT * FROM %s ORDER BY %s', Table::CALCULATIONS, 'id'));
    }

    public function fetchAllInQueue(): array
    {
        return $this->connection->fetchAll(sprintf('SELECT * FROM %s WHERE %s = %s ORDER BY %s', Table::CALCULATIONS, 'state', 0, 'id'));
    }

    public function findByCalculationId(string $id): array
    {
        return $this->connection->fetchAssoc(sprintf('SELECT * FROM %s WHERE calculation_id = \'%s\'', Table::CALCULATIONS, $id));
    }

    public function cleanup(): void
    {
        $startedButNotFinishedJobs = $this->connection->fetchAll(sprintf('SELECT id FROM %s WHERE state = %s', Table::CALCULATIONS, $this::STATE_RUNNING));
        foreach ($startedButNotFinishedJobs as $job) {
            $this->connection->update(
                Table::CALCULATIONS,
                array('state' => $this::STATE_IN_QUEUE),
                array('id' => $job['id'])
            );
        }
    }

    public function createTable(): void
    {
        $queries = $this->schema->toSql($this->connection->getDatabasePlatform());
        foreach ($queries as $query){
            $this->connection->executeQuery($query);
        }
    }

    public function dropTable(): void
    {
        try {
            $queries = $this->schema->toDropSql($this->connection->getDatabasePlatform());
            foreach ($queries as $query){
                $this->connection->executeQuery($query);
            }
        } catch (TableNotFoundException $e) {}
    }

    public function truncateTable(): void
    {
        $this->dropTable();
        $this->createTable();
    }

    public function reset(): void
    {
        $this->truncateTable();
    }
}
