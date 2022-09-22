<?php

namespace App\Actions;

abstract class BaseMakeAction
{
    protected $appDirectory = '/app/';

    protected function appPath(string $path)
    {
        return $this->basePath($this->appDirectory . ltrim($path));
    }

    protected function basePath(string $path)
    {
        return getcwd() . '/' . ltrim($path, '/');
    }

    protected function replaceNameInContent($content)
    {
        return str_replace(['{{Name}}', '{{name}}'], [
            ucfirst($this->name),
            lcfirst($this->name),
        ], $content);
    }
}
