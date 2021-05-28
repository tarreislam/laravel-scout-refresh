<?php

namespace Tarre\LaravelScoutRefresh\Console;

use Artisan;
use File;
use Illuminate\Console\Command;
use Laravel\Scout\Searchable;
use ReflectionClass;
use SplFileInfo;
use Str;

class ScoutRefreshAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:refresh-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush and import all models with the "Searchable" trait';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->getFilesFromDirectory()
            ->each(function (SplFileInfo $fileInfo) {
                $this->handleFile($fileInfo);
            });

        return 0;
    }

    /**
     * @param $cmd
     * @param $class
     */
    protected function scoutCmd($cmd, $class)
    {
        $escapedClass = str_replace('\\', '\\\\', $class);
        $exitCode = Artisan::call("scout:$cmd $escapedClass");
        $this->artisanOutput($exitCode);
    }

    /**
     * @param $exitCode
     */
    protected function artisanOutput($exitCode)
    {
        if ($exitCode === 0) {
            $this->info(Artisan::output());
        } else {
            $this->warn(Artisan::output());
        }
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return string
     */
    protected function getClassNameFromSplFileInfo(SplFileInfo $fileInfo)
    {
        $className = preg_replace('/^app/', 'App', $fileInfo->getPathname());
        $className = str_replace('.php', '', $className);
        $className = str_replace(DIRECTORY_SEPARATOR, '\\', $className);
        $className = "\\$className";
        return $className;
    }

    /**
     * @param $class
     * @return boolean
     */
    protected function hasTrait($class, $trait)
    {
        $traits = array_keys((new ReflectionClass($class))->getTraits());

        return in_array($trait, $traits);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function getFilesFromDirectory()
    {
        $files = File::allFiles('app');

        return collect($files);
    }

    protected function handleFile(SplFileInfo $fileInfo)
    {
        $class = $this->getClassNameFromSplFileInfo($fileInfo);

        if ($this->hasTrait($class, Searchable::class)) {
            $this->scoutCmd('flush', $class);
            $this->scoutCmd('import', $class);
        }

    }
}
