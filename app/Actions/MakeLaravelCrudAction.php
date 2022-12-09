<?php

namespace App\Actions;

use App\Commands\MakeCrudCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
            $targetPath = $this->basePath('admin/' . $file['path'] . $file['name']);

            if (File::exists($targetPath)) {
                $this->command->error('File already exists at ' . $targetPath);
                continue;
            }

            File::ensureDirectoryExists($this->basePath('admin/' . $file['path']));

            $stubPath = base_path('stubs/laravel/admin/' . $stub);

            $content = $this->replaceNameInContent(File::get($stubPath));

            File::put($targetPath, $content);
        }

        $this->registerRoutes();

        $this->command->info('Laravel CRUD files created.');
    }

       protected function registerRoutes()
    {

        $adminRoutes = File::get($this->basePath('/routes/admin.php'));

        $controllerClassName = 'Admin\\Http\\Controllers\\' . ucfirst($this->name) . 'Controller';

        // add controller use statement as the last import
        if (! Str::contains($adminRoutes, $controllerClassName)) {
            preg_match_all("/use .*?;/", $adminRoutes, $matches);
            $lastMatch = $matches[0][count($matches[0]) - 1];
            $newRouteImport = "use $controllerClassName;";
            $adminRoutes = Str::replace($lastMatch, $lastMatch . "\n$newRouteImport", $adminRoutes);
        } else {
            $this->command->error('Controller Namespace already imported');
        }

        // Add route resource below last Route declaration withtin the api group
        if (! Str::contains($adminRoutes, "Route::resource('{$this->name}s'")) {
            $adminRoutes = preg_replace_callback('/\'api\',\s+?\],\s+?function\s+?\(\)\s+?\{((?:.*?|\n)*?)\s+?\}\)\;/', function ($matches) {
                return Str::replaceLast(");\n", ");\n\n    //{$this->name}s\n    Route::resource('".$this->name."s', ".ucfirst($this->name)."Controller::class);\n", $matches[0]);
            }, $adminRoutes);
        } else {
            $this->command->error("{$this->name}s routes already registered");
        }

        File::put($this->basePath('/routes/admin.php'), $adminRoutes);
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
