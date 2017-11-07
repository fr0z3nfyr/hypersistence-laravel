<?php

namespace Hypersistence\Console;

use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;

class AuthMakeCommand extends Command
{
    use DetectsApplicationNamespace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hypersistence:make-auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold basic login model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createDirectories();
        
        if(file_exists(app_path('Models/User.php'))) {
            if (! $this->confirm("The User model already exists. Do you want to replace it?")) {
                    exit;
            }
        }

        file_put_contents(
            app_path('Models/User.php'),
            $this->compileModelStub()
        );
        $this->info('Authentication scaffolding generated successfully.');

    }

    /**
     * Create the directories for the files.
     *
     * @return void
     */
    protected function createDirectories()
    {
        if (! is_dir($directory = app_path('Models/'))) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Compiles the User Model stub.
     *
     * @return string
     */
    protected function compileModelStub()
    {
        return str_replace(
            '{{namespace}}',
            $this->getAppNamespace(),
            file_get_contents(__DIR__.'/stubs/models/User.stub')
        );
    }
}
