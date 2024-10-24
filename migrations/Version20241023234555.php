<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241023234555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AspireSync Tables';
    }

    const string ADD_SYNC_PLUGINS_PGSQL = <<<'SQL'
          create table sync_plugins (
              id uuid not null,
              name varchar(1024) not null,
              slug varchar(255) not null,
              current_version varchar(32) default null,
              updated timestamp without time zone default current_timestamp,
              status varchar(32) not null default 'open',
              pulled_at timestamp without time zone default current_timestamp,
              metadata jsonb default null,
              primary key(id)
          )
      SQL;

    const string ADD_SYNC_PLUGIN_FILES_PGSQL = <<<'SQL'
          create table sync_plugin_files (
              id uuid not null,
              plugin_id uuid not null,
              file_url varchar(1024) default null,
              type varchar(32) not null,
              version varchar(32) not null,
              metadata jsonb default null,
              created timestamp without time zone default current_timestamp,
              processed timestamp without time zone default null,
              hash varchar(255) default null,
              primary key (id),
              constraint fk_sync_plugin_id
                  foreign key(plugin_id)
                  references sync_plugins(id)
                  on delete cascade
          )
      SQL;

    const string ADD_SYNC_THEMES_PGSQL = <<<'SQL'
        create table sync_themes (
            id uuid not null,
            name varchar(1024) not null,
            slug varchar(255) not null,
            current_version varchar(32) default null,
            updated timestamp without time zone default current_timestamp,
            pulled_at timestamp without time zone default null,
            metadata jsonb default null,
            primary key (id)
        )
    SQL;

    const string ADD_SYNC_THEME_FILES_PGSQL = <<<'SQL'
          create table sync_theme_files (
              id uuid not null,
              theme_id uuid not null,
              file_url varchar(1024) default null,
              type varchar(32) not null,
              version varchar(32) not null,
              metadata jsonb default null,
              created timestamp without time zone default current_timestamp,
              processed timestamp without time zone default null,
              hash varchar(255) default null,
              primary key (id),
              constraint fk_sync_theme_id
                  foreign key(theme_id)
                  references sync_themes(id)
                  on delete cascade
          )
      SQL;

    const string ADD_SYNC_STATS_PGSQL = <<<'SQL'
        create table sync_stats (
            id uuid not null,
            command varchar(255) not null,
            stats jsonb not null,
            created_at timestamp without time zone default current_timestamp,
            primary key (id)
        )
    SQL;

    const string ADD_SYNC_REVISIONS_PGSQL = <<<'SQL'
        create table sync_revisions (
            action varchar(255) not null,
            revision varchar(255) not null,
            added_at timestamp without time zone default current_timestamp
        )
    SQL;

    // TODO: give this the sync_ prefix treatment (needs a PR in AspireSync)
    const string ADD_SYNC_NOT_FOUND_ITEMS_PGSQL = <<<'SQL'
        create table not_found_items (
            id uuid not null,
            item_type varchar(32) not null,
            item_slug varchar(255) not null,
            created_at timestamp without time zone default current_timestamp,
            updated_at timestamp without time zone default current_timestamp
        )
    SQL;

    const string ADD_INDEXES_PGSQL = <<<'SQL'
        create index idx_sync_plugins_name on sync_plugins(name);
        create index idx_sync_plugins_slug on sync_plugins(slug);
        create index idx_sync_themes_name on sync_themes(name);
        create index idx_sync_themes_slug on sync_themes(slug);
        create index idx_sync_plugin_files_hash on sync_plugin_files using hash (hash);
        create index idx_sync_theme_files_hash on sync_theme_files using hash (hash);
    SQL;

    const string DROP_TABLES_SQL = <<<'SQL'
        drop table if exists sync_stats;
        drop table if exists sync_plugin_files;
        drop table if exists sync_plugins;
        drop table if exists sync_theme_files;
        drop table if exists sync_themes;
        drop table if exists sync_revisons;
        drop table if exists not_found_items;
    SQL;

    public function up(Schema $schema): void
    {
        foreach ([
                     self::ADD_SYNC_PLUGINS_PGSQL,
                     self::ADD_SYNC_PLUGIN_FILES_PGSQL,
                     self::ADD_SYNC_THEMES_PGSQL,
                     self::ADD_SYNC_THEME_FILES_PGSQL,
                     self::ADD_SYNC_STATS_PGSQL,
                     self::ADD_SYNC_REVISIONS_PGSQL,
                     self::ADD_SYNC_NOT_FOUND_ITEMS_PGSQL,
                     self::ADD_INDEXES_PGSQL,
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
