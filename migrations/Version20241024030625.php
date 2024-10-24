<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241024030625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Full Plugins and Themes Tables';
    }

    const string ADD_PLUGINS_PGSQL = <<<'SQL'
                     create table plugins (
                         id uuid not null,
                         sync_id uuid not null,
                         slug varchar(255) not null,
                         name varchar(1024) not null,
                         short_description varchar(150) not null,
                         description text not null,
                         version varchar(32) not null,
                         author varchar(255) not null,
                         requires varchar(255) not null,
                         requires_php varchar(8) not null,
                         tested varchar(255) not null,
                         download_link varchar(1024) not null,
                         added timestamp without time zone not null,
                         last_updated timestamp without time zone not null,
                         author_profile varchar(255) default null,
                         rating smallint default 0,
                         ratings jsonb default null,
                         num_ratings int default 0,
                         support_threads int default 0,
                         support_threads_resolved int default 0,
                         active_installs int default 0,
                         downloaded int default 0,
                         homepage varchar(255) default null,
                         banners jsonb default null,
                         tags jsonb default null,
                         donate_link varchar(255) default null,
                         contributors jsonb default null,
                         icons jsonb default null,
                         source jsonb default null,
                         business_model varchar(16) default null,
                         commercial_support_url varchar(255) default null,
                         support_url varchar(255) default null,
                         preview_link varchar(255) default null,
                         repository_url varchar(255) default null,
                         requires_plugins jsonb default null,
                         compatibility jsonb default null,
                         screenshots jsonb default null,
                         sections jsonb default null,
                         versions jsonb default null,
                         upgrade_notice jsonb default null,
                         primary key (id),
                         constraint fk_plugins_sync_id
                           foreign key(sync_id)
                           references sync_plugins (id)
                     )
                     SQL;


    const string ADD_AUTHORS_PGSQL = <<<'SQL'
                     create table authors (
                         id uuid not null,
                         user_nicename varchar(255) not null,
                         profile varchar(255) default null,
                         avatar varchar(255) default null,
                         display_name varchar(255) default null,
                         author varchar(255) default null,
                         author_url varchar(255) default null,
                         primary key (id)
                     )
                     SQL;


    const string ADD_THEMES_PGSQL = <<<'SQL'
                     create table themes (
                             id uuid not null,
                             sync_id uuid not null,
                             slug varchar(255) not null,
                             name varchar(1024) not null,
                             version varchar(32) not null,
                             author_id uuid not null,
                             download_link varchar(1024) not null,
                             requires_php varchar(8) not null,
                             last_updated timestamp without time zone not null,
                             creation_time timestamp without time zone not null,
                             preview_url varchar(255) default null,
                             screenshot_url varchar(255) default null,
                             ratings jsonb default null,
                             rating smallint default 0,
                             num_ratings int default 0,
                             reviews_url varchar(255) default null,
                             downloaded int default 0,
                             active_installs int default 0,
                             homepage varchar(255) default null,
                             sections jsonb default null,
                             tags jsonb default null,
                             versions jsonb default null,
                             requires jsonb default null,
                             is_commercial bool default false,
                             external_support_url varchar(255) default null,
                             is_community bool default false,
                             external_repository_url varchar(255) default null,
                             primary key (id),
                             constraint fk_themes_author_id
                               foreign key(author_id)
                               references authors (id),
                             constraint fk_themes_sync_id
                               foreign key(sync_id)
                               references sync_themes (id)
                             )
                     SQL;

    const string DROP_TABLES_SQL = <<<'SQL'
        drop table if exists plugins;
        drop table if exists themes;
        drop table if exists authors;
    SQL;

    public function up(Schema $schema): void
    {
        foreach ([
                     self::ADD_PLUGINS_PGSQL,
                     self::ADD_AUTHORS_PGSQL,
                     self::ADD_THEMES_PGSQL,
                 ] as $section) {
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
