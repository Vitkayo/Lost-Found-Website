<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckDatabaseCommand extends Command
{
    protected $signature = 'lostfound:check-db';

    protected $description = 'Verify MySQL connection and print fix steps if needed';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
            $name = DB::connection()->getDatabaseName();
            $count = DB::table('items')->count();
            $this->info("MySQL OK — database: {$name}, items: {$count}");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Database connection failed: '.$e->getMessage());
            $this->newLine();
            $this->line('In MySQL Workbench (Local Instance Connection), run:');
            $this->line('  database/grant-laravel.sql');
            $this->newLine();
            $this->line('Or use ContactAppDB (lab user already has access):');
            $this->line('  CREATE DATABASE IF NOT EXISTS ContactAppDB;');
            $this->line('  Then set DB_DATABASE=ContactAppDB in .env');

            return self::FAILURE;
        }
    }
}
