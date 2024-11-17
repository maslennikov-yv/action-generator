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
            realpath(__DIR__ . '/../stubs/action.stub') => 'action.stub',
            realpath(__DIR__ . '/../stubs/action.create.stub') => 'action.create.stub',
            realpath(__DIR__ . '/../stubs/action.destroy.stub') => 'action.destroy.stub',
            realpath(__DIR__ . '/../stubs/action.index.stub') => 'action.index.stub',
            realpath(__DIR__ . '/../stubs/action.show.stub') => 'action.show.stub',
            realpath(__DIR__ . '/../stubs/action.update.stub') => 'action.update.stub',
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
