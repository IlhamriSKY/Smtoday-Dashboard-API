<?php

namespace Vanguard\Plugins\Console\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Vanguard\Plugins\Vanguard;

class RemovePluginCommand extends PluginCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vanguard:remove-plugin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes the specified plugin from the system.';

    /**
     * Execute the console command.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if (! $this->pluginExists($this->pluginPath())) {
            $this->error("The plugin [{$this->studlyName()}] does not exist.");
            return;
        }

        if ($this->pluginIsStillActive()) {
            $message = "The [{$this->studlyName()}] plugin is still active. Please remove it from the list of active";
            $message .= " plugins inside the VanguardServiceProvider first.";
            $this->error($message);
            return;
        }

        $this->warn("Removing [{$this->studlyName()}] plugin...");
        $this->files->deleteDirectory($this->pluginPath());
        $this->updateApplicationComposerFile();
        $this->uninstallPluginDependency();
        $this->info('Plugin removed successfully.');
    }

    /**
     * Check if the plugin that should be deleted is still active.
     *
     * @return bool
     */
    private function pluginIsStillActive()
    {
        $pluginClassName = sprintf(
            "%s\%s",
            $this->pluginNamespace(),
            $this->studlyName()
        );

        return isset(Vanguard::availablePlugins()[$pluginClassName]);
    }

    /**
     * Uninstall plugin composer dependency.
     */
    private function uninstallPluginDependency()
    {
        $pluginFullName = sprintf("%s/%s", config('plugins.composer.vendor'), $this->snakeName());

        $command = Process::fromShellCommandline("composer remove {$pluginFullName}");
        $command->setWorkingDirectory(base_path());
        $command->run();

        if (! $command->isSuccessful()) {
            throw new ProcessFailedException($command);
        }
    }

    /**
     * Update the main application composer file and remove
     * the reference to the plugin.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function updateApplicationComposerFile()
    {
        $composer = json_decode($this->files->get(base_path('composer.json')), true);

        $composer['repositories'] = collect($composer['repositories'])->filter(function ($repo) {
            return ! isset($repo['url']) || $repo['url'] != './plugins/' . $this->studlyName();
        })->toArray();

        $this->files->put(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
