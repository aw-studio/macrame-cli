<?php

namespace App\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeVueCrudAction extends BaseMakeAction
{

    protected $appDirectory = '/src/';

    protected $name;

    public function __construct(string $name, protected $command)
    {
        $this->name = $name = strtolower($name);
    }

    public function execute()
    {
        $files = [
            'crud.api.ts.stub' => [
                'path' => 'entities/' . $this->name . '/',
                'name' => 'api.ts',
            ],
            'crud.form.ts.stub' => [
                'path' => 'entities/' . $this->name . '/',
                'name' => $this->name . '.form.ts',
            ],
            'crud.index.ts.stub' => [
                'path' => 'entities/' . $this->name . '/',
                'name' => $this->name . '.index.ts',
            ],
            'crud.types.ts.stub' => [
                'path' => 'entities/' . $this->name . '/',
                'name' => 'types.ts',
            ],
            'routes.ts.stub' => [
                'path' => 'Pages/' . $this->name . '/',
                'name' => 'routes.ts',
            ],
            'CrudIndex.vue.stub' => [
                'path' => 'Pages/' . $this->name . '/',
                'name' => 'Index.vue',
            ],
            'CrudShow.vue.stub' => [
                'path' => 'Pages/' . $this->name . '/',
                'name' => 'Show.vue',
            ],
            'CrudCreateModal.vue.stub' => [
                'path' => 'Pages/' . $this->name . '/components/',
                'name' => 'Add' . ucfirst($this->name) . 'Modal.vue',
            ],
        ];

        foreach ($files as $stub => $file) {
            $targetPath = $this->appPath($file['path'] . $file['name']);

            if (File::exists($targetPath)) {
                $this->command->error('File already exists at ' . $targetPath);
                continue;
            }

            File::ensureDirectoryExists($this->appPath($file['path']));

            $stubPath = base_path('stubs/vue/' . $stub);

            $content = $this->replaceNameInContent(File::get($stubPath));

            File::put($targetPath, $content);

            if (Str::contains($file['path'], 'entities')) {
                $this->registerEnitityFile($file);
            }
        }

        $this->registerRoutes();
    }

    protected function registerEnitityFile($file)
    {
        $entitiesIndexPath = $this->appPath('entities/index.ts');
        $entitiesIndexContent = File::get($entitiesIndexPath);

        $fileExportName = Str::replace(['.vue', '.ts'], '', $file['name']);
        $exportStatement = "export * from './$this->name/$fileExportName';";

        if (! Str::contains($entitiesIndexContent, "// $this->name")) {
            $entitiesIndexContent = $entitiesIndexContent . "\n\n// $this->name";
        }

        if (! Str::contains($entitiesIndexContent, $exportStatement)) {
            $entitiesIndexContent = $entitiesIndexContent . "\n$exportStatement";
        }

        File::put($entitiesIndexPath, $entitiesIndexContent);
    }

    protected function registerRoutes()
    {
        $newRouteImport = 'import { routes as ' . lcfirst($this->name) . "Routes } from '@/pages/$this->name/routes';";

        $routerPath = $this->appPath('plugins/router.ts');

        $routerContent = File::get($routerPath);

        // add import statement
        if (! Str::contains($routerContent, $newRouteImport)) {
            preg_match_all("/import.*?\/routes';\n/", $routerContent, $matches);
            $lastMatch = $matches[0][count($matches[0]) - 1];
            $routerContent = Str::replace($lastMatch, $lastMatch . "$newRouteImport\n\n", $routerContent);
        } else {
            $this->command->error('Route Import already exists');
        }

        // spread routes to base routes children
        if (! Str::contains($routerContent, "...{$this->name}Routes,\n")) {
            $routerContent = preg_replace_callback('/children:\s?\[((?:.*?|\n)*?)\]/', function ($matches) {
                return Str::replaceLast("Routes,\n", "Routes,\n            ...{$this->name}Routes,\n", $matches[0]);
            }, $routerContent);
        } else {
            $this->command->error("{$this->name}Routes are already registered");
        }

        File::put($routerPath, $routerContent);
    }

}
