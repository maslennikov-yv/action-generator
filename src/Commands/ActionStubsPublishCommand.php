<?php

declare(strict_types=1);

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
    protected $signature = 'action:stub:publish
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
            // action
            realpath(__DIR__ . '/../stubs/action.stub') => 'action.stub',
            realpath(__DIR__ . '/../stubs/action.create.stub') => 'action.create.stub',
            realpath(__DIR__ . '/../stubs/action.destroy.stub') => 'action.destroy.stub',
            realpath(__DIR__ . '/../stubs/action.index.stub') => 'action.index.stub',
            realpath(__DIR__ . '/../stubs/action.show.stub') => 'action.show.stub',
            realpath(__DIR__ . '/../stubs/action.update.stub') => 'action.update.stub',
            // action.controller
            realpath(
                __DIR__ . '/../stubs/action.controller.dataset.destroy.stub'
            ) => 'action.controller.dataset.destroy.stub',
            realpath(
                __DIR__ . '/../stubs/action.controller.dataset.show.stub'
            ) => 'action.controller.dataset.show.stub',
            realpath(
                __DIR__ . '/../stubs/action.controller.dataset.store.stub'
            ) => 'action.controller.dataset.store.stub',
            realpath(__DIR__ . '/../stubs/action.controller.dataset.stub') => 'action.controller.dataset.stub',
            realpath(
                __DIR__ . '/../stubs/action.controller.dataset.update.stub'
            ) => 'action.controller.dataset.update.stub',
            realpath(__DIR__ . '/../stubs/action.controller.stub') => 'action.controller.stub',
            realpath(__DIR__ . '/../stubs/action.controller.test.stub') => 'action.controller.test.stub',
            // action.data
            realpath(__DIR__ . '/../stubs/action.data.stub') => 'action.data.stub',
            realpath(__DIR__ . '/../stubs/action.data.create.stub') => 'action.data.create.stub',
            realpath(__DIR__ . '/../stubs/action.data.destroy.stub') => 'action.data.destroy.stub',
            realpath(__DIR__ . '/../stubs/action.data.show.stub') => 'action.data.show.stub',
            // action.dataset
            realpath(__DIR__ . '/../stubs/action.dataset.stub') => 'action.dataset.stub',
            realpath(__DIR__ . '/../stubs/action.dataset.create.stub') => 'action.dataset.create.stub',
            realpath(__DIR__ . '/../stubs/action.dataset.destroy.stub') => 'action.dataset.destroy.stub',
            realpath(__DIR__ . '/../stubs/action.dataset.update.stub') => 'action.dataset.update.stub',
            // action.test
            realpath(__DIR__ . '/../stubs/action.test.stub') => 'action.test.stub',
            realpath(__DIR__ . '/../stubs/action.test.destroy.stub') => 'action.test.destroy.stub',
            realpath(__DIR__ . '/../stubs/action.test.index.stub') => 'action.test.index.stub',
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
