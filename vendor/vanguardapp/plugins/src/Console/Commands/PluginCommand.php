<?php

namespace Vanguard\Plugins\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

abstract class PluginCommand extends Command
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the plugin.'],
        ];
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return rtrim($this->laravel->getNamespace(), "\\");
    }

    /**
     * The namespace of the plugin itself.
     *
     * @return string
     */
    protected function pluginNamespace()
    {
        return sprintf("%s\%s", $this->rootNamespace(), $this->studlyName());
    }

    /**
    * Build the directory for the class if necessary.
    *
    * @param  string  $path
    * @return string
    */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * The path to the plugin directory.
     * @return string
     */
    protected function pluginPath()
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $this->getNameInput());

        return $this->laravel['path.base'].'/plugins/'.str_replace('\\', '/', Str::studly($name));
    }

    /**
     * Check if plugin exists on a given path.
     *
     * @param string $pluginPath
     * @return bool
     */
    protected function pluginExists($pluginPath)
    {
        return $this->files->exists($pluginPath);
    }

    /**
     * Name of the plugin in StudlyCase format.
     *
     * @return string
     */
    protected function studlyName()
    {
        return Str::studly($this->getNameInput());
    }

    /**
     * Name of the plugin in snake-case format.
     *
     * @return string
     */
    protected function snakeName()
    {
        return Str::snake($this->getNameInput(), '-');
    }
}
