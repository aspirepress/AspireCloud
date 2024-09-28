<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Helpers;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class DbHelper
{
    public const DB_NAME = 'functional_tests';

    public static ExtendedPdoInterface $pdo;

    private static function configurePdo(): ExtendedPdoInterface
    {
        $container = ContainerHelper::getContainer();
        $pdo       = $container->get(ExtendedPdo::class);
        self::$pdo = $pdo;
        return $pdo;
    }

    public static function getPdo(): ExtendedPdoInterface
    {
        if (! isset(self::$pdo)) {
            return self::configurePdo();
        }

        return self::$pdo;
    }

    /**
     * @throws ExceptionInterface
     */
    public static function setupDb(): void
    {
        $pdo = self::configurePdo();

        $pdo->exec('DROP SCHEMA IF EXISTS ' . self::DB_NAME . ' CASCADE');
        $pdo->exec('CREATE SCHEMA ' . self::DB_NAME);

        $phinx   = new PhinxApplication();
        $command = $phinx->find('migrate');

        $commandSeed = $phinx->find('seed:run');

        $arguments = [
            '-e'              => 'functional_tests',
            '--configuration' => 'migrations/config/phinx.php',
        ];

        $output = new ConsoleOutput();
        $input  = new ArrayInput($arguments);
        $command->run($input, $output);
        $commandSeed->run($input, $output);
    }
}
