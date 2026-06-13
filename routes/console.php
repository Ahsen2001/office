<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('office:backup {--path= : Optional backup directory}', function () {
    $connection = config('database.default');
    $database = config("database.connections.{$connection}.database");

    if ($connection !== 'mysql' || ! $database) {
        $this->error('Office backups currently support the MySQL connection only.');

        return Command::FAILURE;
    }

    $backupPath = $this->option('path') ?: storage_path('app/backups');
    File::ensureDirectoryExists($backupPath);

    $file = $backupPath.DIRECTORY_SEPARATOR.$database.'-'.now()->format('Y-m-d-His').'.sql';
    $config = config("database.connections.{$connection}");

    $command = [
        'mysqldump',
        '--single-transaction',
        '--quick',
        '--skip-lock-tables',
        '--host='.$config['host'],
        '--port='.$config['port'],
        '--user='.$config['username'],
    ];

    if (! empty($config['password'])) {
        $command[] = '--password='.$config['password'];
    }

    $command[] = $database;

    $handle = fopen($file, 'wb');
    if ($handle === false) {
        $this->error('Unable to create backup file.');

        return Command::FAILURE;
    }

    $process = new Process($command, base_path(), null, null, 300);
    $process->run(function ($type, $buffer) use ($handle) {
        if ($type === Process::OUT) {
            fwrite($handle, $buffer);
        }
    });

    fclose($handle);

    if (! $process->isSuccessful()) {
        File::delete($file);
        $this->error('Backup failed: '.$process->getErrorOutput());

        return Command::FAILURE;
    }

    $this->info('Backup created: '.$file);

    return Command::SUCCESS;
})->purpose('Create a timestamped MySQL backup for the Office Service database.');
