<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024024350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Detailed Request Logging';
    }

    const string ADD_REQUEST_DATA_PGSQL = <<<'SQL'
        create table request_data(
            id uuid not null,
            request_path varchar(2048) not null,
            request_query_params jsonb default null,
            request_body jsonb default null,
            request_headers jsonb not null,
            response_code smallint not null,
            response_body text default null,
            response_headers jsonb not null,
            created_at timestamp without time zone default current_timestamp,
            primary key (id)
        );
        create index idx_request_data_path on request_data (request_path);
        create index idx_request_data_createdt on request_data (created_at);
    SQL;

    const string DROP_TABLES_SQL = <<<'SQL'
        drop table if exists request_data;
    SQL;

    public function up(Schema $schema): void
    {
        foreach ([self::ADD_REQUEST_DATA_PGSQL] as $section) {
            foreach (preg_split('/;\n/', $section) as $statement) {
                $this->addSql($statement);
            }
        }
    }

    public function down(Schema $schema): void
    {
        foreach ([self::DROP_TABLES_SQL] as $section) {
            foreach (preg_split('/;\n/', $section) as $statement) {
                $this->addSql($statement);
            }
        }
    }


}
