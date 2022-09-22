<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\File;


class MakeParserCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:parser {name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new content parser';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!$this->dirIsLaravelApp()){
            $this->error('Can\'t add Parser Class. You are not inside a Laravel Application');
        }

        $targetPath =  getcwd(). '/app/Casts/Parsers/'.ucfirst($this->argument('name').'Parser.php');

        if(File::exists($targetPath)){
            $this->error('File already exists ');
            return;
        }
        
        $stub = base_path('stubs/laravel/Parser.php.stub');

        $name = strtolower($this->argument('name'));

        $content = str_replace(['{{Name}}', '{{name}}'], [
            ucfirst($name),
            lcfirst($name),
        ], File::get($stub));

        File::put($targetPath, $content);


      
    }

    public function dirIsLaravelApp()
    {
        if(File::exists(getcwd().'/artisan')){
            return true;
        }

        return false;
    }
}
