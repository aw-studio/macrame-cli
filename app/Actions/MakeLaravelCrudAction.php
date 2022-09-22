<?php

namespace App\Actions;

use App\Commands\MakeCrudCommand;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class MakeLaravelCrudAction extends BaseMakeAction
{
    protected $name;

    public function __construct(string $name, protected MakeCrudCommand $command)
    {
        $this->name = $name = strtolower($name);
    }

    public function execute()
    {
        $this->ensureBasicLaravelFilesExist();

          $files = [
            'controlller.php.stub' => [
                'path' => 'Http/Controllers/',
                'name' => ucfirst($this->name) . 'Controller.php',
            ],
            'index.php.stub' => [
                'path' => 'Http/Indexes/',
                'name' => ucfirst($this->name) . 'Index.php',
            ],
            'resource.php.stub' => [
                'path' => 'Http/Resources/',
                'name' => ucfirst($this->name) . 'Resource.php',
            ],
        ];

        foreach ($files as $stub => $file) {
            $targetPath = $this->basePath('admin/'.$file['path'] . $file['name']);

            if (File::exists($targetPath)) {
                $this->command->error('File already exists at ' . $targetPath);
                continue;
            }

            File::ensureDirectoryExists($this->basePath('admin/'.$file['path']));

            $stubPath = base_path('stubs/laravel/admin/' . $stub);

            $content = $this->replaceNameInContent(File::get($stubPath));

            File::put($targetPath, $content);

        }
    }

    public function ensureBasicLaravelFilesExist()
    {
        // Create Laravel App Model Files if not already existing
        if (! File::exists($this->appPath('/Models/' . ucfirst($this->name) . '.php'))) {
            $this->executeArtisan(['make:model', ucfirst($this->name), '-m']);
        }

        if (! File::exists($this->appPath('/Http/Resources/' . ucfirst($this->name) . 'Resource.php'))) {
            $this->executeArtisan(['make:resource', ucfirst($this->name) . 'Resource']);
        }

        if (! File::exists($this->appPath('/Http/Controllers/' . ucfirst($this->name) . 'Controller.php'))) {
            $this->executeArtisan(['make:controller', ucfirst($this->name) . 'Controller']);
        }
    }

    public function executeArtisan($commands)
    {
        $process = new Process(array_merge(['php', 'artisan'], $commands), getcwd());

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->command->error($buffer);
            } else {
                $this->command->info($buffer);
            }
        });
    }
}
