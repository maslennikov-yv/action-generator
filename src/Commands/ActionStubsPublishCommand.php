<?php

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ActionStubsPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stub:publish
                    {--existing : Publish and overwrite only the files that have already been published}
                    {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish action stubs that are available for customization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            (new Filesystem)->makeDirectory($stubsPath);
        }

        $stubs = [
            __DIR__ . '/stubs/action.stub' => 'action.stub',
            __DIR__ . '/stubs/action_create.stub' => 'action_create.stub',
            __DIR__ . '/stubs/action_destroy.stub' => 'action_destroy.stub',
            __DIR__ . '/stubs/action_index.stub' => 'action_index.stub',
            __DIR__ . '/stubs/action_show.stub' => 'action_show.stub',
            __DIR__ . '/stubs/action_update.stub' => 'action_update.stub',
        ];

        foreach ($stubs as $from => $to) {
            $to = $stubsPath . DIRECTORY_SEPARATOR . ltrim($to, DIRECTORY_SEPARATOR);

            if ((!$this->option('existing') && (!file_exists($to) || $this->option('force')))
                || ($this->option('existing') && file_exists($to))) {
                file_put_contents($to, file_get_contents($from));
            }
        }

        $this->components->info('Action stubs published successfully.');
    }
}
