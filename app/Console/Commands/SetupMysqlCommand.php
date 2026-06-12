<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PDO;
use Throwable;

class SetupMysqlCommand extends Command
{
    protected $signature = 'lostfound:setup-mysql';

    protected $description = 'Create contactappdb (laravel user) and migrate Lost-Found to MySQL';

    public function handle(): int
    {
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'laravel', 'Rupp2357.!');
            $pdo->exec(
                'CREATE DATABASE IF NOT EXISTS contactappdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
            $this->info('Database contactappdb is ready.');
        } catch (Throwable $e) {
            $this->error('Could not connect as laravel: '.$e->getMessage());
            $this->line('Run database/grant-laravel.sql in Workbench (as root) for LostFoundDB, or ensure lab MySQL user exists.');

            return self::FAILURE;
        }

        $this->updateEnvForMysql();
        Artisan::call('config:clear');
        $this->info('Updated .env for MySQL (contactappdb).');

        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);

        if ($this->call('lostfound:check-db') === self::SUCCESS) {
            $this->newLine();
            $this->info('Lost-Found now uses MySQL.');
            $this->line('To use LostFoundDB instead, run database/grant-laravel.sql in Workbench, then set DB_DATABASE=LostFoundDB and DB_PASSWORD="2103#Davit" in .env.');

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    private function updateEnvForMysql(): void
    {
        $envPath = base_path('.env');
        $env = File::get($envPath);

        $mysqlBlock = <<<'ENV'
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=contactappdb
DB_USERNAME=laravel
DB_PASSWORD=Rupp2357.!
ENV;

        if (preg_match('/DB_CONNECTION=sqlite/s', $env)) {
            $env = preg_replace(
                '/DB_CONNECTION=sqlite\n(?:#.*\n|DB_.*\n)*/',
                $mysqlBlock."\n",
                $env
            );
        } else {
            $env = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $env);
            $env = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=contactappdb', $env);
            $env = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=laravel', $env);
            $env = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=Rupp2357.!', $env);
        }

        File::put($envPath, $env);
    }
}
