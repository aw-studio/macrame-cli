<?php

namespace App\Commands;

use App\Actions\MakeLaravelCrudAction;
use App\Actions\MakeVueCrudAction;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class MakeCrudCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:crud {name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create required files for a new CRUD';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->dirIsLaravelApp()) {
            (new MakeVueCrudAction($this->argument('name'), $this))->execute();
            return;
        } else {
             (new MakeLaravelCrudAction($this->argument('name'), $this))->execute();
            return;
        }
    }



    public function dirIsLaravelApp()
    {
        if (File::exists(getcwd() . '/artisan')) {
            return true;
        }

        return false;
    }
}
