<?php

namespace Vanguard\Plugins\Console\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GeneratePluginCommand extends PluginCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vanguard:make-plugin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold all the necessary files for the new plugin.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->pluginExists($this->pluginPath())) {
            $this->error('Plugin already exists!');
            return;
        }

        $this->warn('Generating plugin folder structure...');
        $this->generateFolderStructure();
        $this->generateFiles();
        $this->info('Plugin folder structure generated successfully.');

        $this->warn('Installing plugin as a local composer dependency...');
        $this->updateApplicationComposerFile();
        $this->installPlugin();
        $this->info('Plugin installed successfully.');
    }

    /**
     * Generate the plugin folder structure.
     */
    private function generateFolderStructure()
    {
        $pluginPath = $this->pluginPath();

        foreach ($this->getFolders() as $folder) {
            $this->makeDirectory($pluginPath . "/{$folder}");
        }
    }

    /**
     * Get the list of folders to create.
     *
     * @return array
     */
    protected function getFolders()
    {
        return [
            'database/factories',
            'database/migrations',
            'database/seeds',
            'resources/views',
            'routes',
            'src/Http/Controllers/Api',
            'src/Http/Controllers/Web',
            'src/Http/Requests',
            'tests'
        ];
    }

    /**
     * Generate necessary plugin files.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function generateFiles()
    {
        $pluginPath = $this->pluginPath();

        foreach ($this->getFiles() as $stubPath => $filePath) {
            $this->makeDirectory($pluginPath . "/{$filePath}");

            $stub = $this->files->get(__DIR__ . "/stubs/" . $stubPath);
            $stub = $this->replacePlaceholders($stub);

            $this->files->put($pluginPath . "/{$filePath}", $stub);
        }
    }

    /**
     * Get the list of files that should be created for the plugin.
     *
     * @return array
     */
    protected function getFiles()
    {
        return [
            'routes/web.stub' => 'routes/web.php',
            'routes/api.stub' => 'routes/api.php',
            'views/index.stub' => 'resources/views/index.blade.php',
            'config.stub' => 'config/config.php',
            'composer.stub' => 'composer.json',
            'assets/js/app.stub' => 'resources/assets/js/app.js',
            'assets/sass/app.stub' => 'resources/assets/sass/app.scss',
            'webpack.stub' => 'webpack.mix.js',
            'package.stub' => 'package.json',
            'unit-test.stub' => 'tests/Unit/ExampleTest.php',
            'feature-test.stub' => 'tests/Feature/FeatureTest.php',
            'gitignore.stub' => '.gitignore',
            'plugin.stub' => 'src/' . $this->studlyName() . ".php",
            'controller.stub' => 'src/Http/Controllers/Web/' . $this->studlyName() . "Controller.php",
        ];
    }

    /**
     * Replace placeholders within the stub files.
     *
     * @param string $stub
     * @return mixed
     */
    private function replacePlaceholders(string $stub)
    {
        return str_replace(
            [
                '$ROOT_NAMESPACE$',
                '$PLUGIN_NAMESPACE$',
                '$VENDOR$',
                '$AUTHOR_NAME$',
                '$AUTHOR_EMAIL$',
                '$SNAKE_NAME$',
                '$STUDLY_NAME$'
            ],
            [
                $this->rootNamespace(),
                $this->pluginNamespace(),
                config('plugins.composer.vendor'),
                config('plugins.composer.author.name'),
                config('plugins.composer.author.email'),
                $this->snakeName(),
                $this->studlyName()
            ],
            $stub
        );
    }

    /**
     * Update the main application composer file to include the
     * newly generated plugin.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function updateApplicationComposerFile()
    {
        $composer = json_decode($this->files->get(base_path('composer.json')), true);

        array_push($composer['repositories'], [
            'type' => 'path',
            'url' => './plugins/' . $this->studlyName()
        ]);


        $this->files->put(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Install the plugin via composer.
     */
    private function installPlugin()
    {
        $pluginFullName = sprintf("%s/%s", config('plugins.composer.vendor'), $this->snakeName());

        $command = Process::fromShellCommandline("composer require {$pluginFullName} \"*\"");

        $command->setWorkingDirectory(base_path());

        $command->run();

        if (! $command->isSuccessful()) {
            throw new ProcessFailedException($command);
        }
    }
}
