<?php

namespace Vanguard\Http\Controllers\Web;

use Artisan;
use DB;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Http\Request;
use Session;
use Vanguard\Http\Controllers\Controller;

class InstallController extends Controller
{
    public function index()
    {
        return view('install.start');
    }

    public function requirements()
    {
        $requirements = $this->getRequirements();
        $allLoaded = $this->allRequirementsLoaded();

        return view('install.requirements', compact('requirements', 'allLoaded'));
    }

    public function permissions()
    {
        if (! $this->allRequirementsLoaded()) {
            return redirect()->route('install.requirements');
        }

        $folders = $this->getPermissions();
        $allGranted = $this->allPermissionsGranted();

        return view('install.permissions', compact('folders', 'allGranted'));
    }

    public function databaseInfo()
    {
        if (! $this->allRequirementsLoaded()) {
            return redirect()->route('install.requirements');
        }

        if (! $this->allPermissionsGranted()) {
            return redirect()->route('install.permissions');
        }

        return view('install.database');
    }

    public function installation(Request $request)
    {
        if (! $this->allRequirementsLoaded()) {
            return redirect()->route('install.requirements');
        }

        if (! $this->allPermissionsGranted()) {
            return redirect()->route('install.permissions');
        }

        $dbCredentials = $request->only('host', 'username', 'password', 'database', 'prefix');

        if (! $this->dbCredentialsAreValid($dbCredentials)) {
            return redirect()->route('install.database')
                ->withInput(array_except($dbCredentials, 'password'))
                ->withErrors("Connection to your database cannot be established. 
                Please provide correct database credentials.");
        }

        Session::put('install.db_credentials', $dbCredentials);

        return view('install.installation');
    }

    public function install()
    {
        try {
            $db = Session::pull('install.db_credentials');

            copy(base_path('.env.example'), base_path('.env'));

            $this->reloadEnv();

            $path = base_path('.env');
            $env = file_get_contents($path);

            $env = str_replace('DB_HOST='.env('DB_HOST'), 'DB_HOST='.$db['host'], $env);
            $env = str_replace('DB_DATABASE='.env('DB_DATABASE'), 'DB_DATABASE='.$db['database'], $env);
            $env = str_replace('DB_USERNAME='.env('DB_USERNAME'), 'DB_USERNAME='.$db['username'], $env);
            $env = str_replace('DB_PASSWORD='.env('DB_PASSWORD'), 'DB_PASSWORD="'.$db['password'].'"', $env);
            $env = str_replace('DB_PREFIX='.env('DB_PREFIX'), 'DB_PREFIX='.$db['prefix'], $env);

            file_put_contents($path, $env);

            $this->setDatabaseCredentials($db);
            config(['app.debug' => true]);

            \Setting::set('app_name', \request('app_name'));
            \Setting::save();

            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('jwt:secret', ['--force' => true]);
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true, '--class' => 'AnnouncementsDatabaseSeeder']);

            return redirect()->route('install.complete');
        } catch (\Exception $e) {
            @unlink(base_path('.env'));
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('install.error');
        }
    }

    private function reloadEnv()
    {
        (new LoadEnvironmentVariables)->bootstrap(app());
    }

    public function complete()
    {
        return view('install.complete');
    }

    public function error()
    {
        return view('install.error');
    }

    /**
     * @return array
     */
    private function getRequirements()
    {
        $requirements = [
            'PHP Version (>= 7.2.0)' => version_compare(phpversion(), '7.2.0', '>='),
            'BCMath Extension'   => extension_loaded('bcmath'),
            'OpenSSL Extension'   => extension_loaded('openssl'),
            'PDO Extension'       => extension_loaded('PDO'),
            'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
            'Mbstring Extension'  => extension_loaded('mbstring'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'Ctype PHP Extension' => extension_loaded('ctype'),
            'JSON PHP Extension' => extension_loaded('json'),
            'GD Extension' => extension_loaded('gd'),
            'Fileinfo Extension' => extension_loaded('fileinfo')
        ];

        if (extension_loaded('xdebug')) {
            $requirements['Xdebug Max Nesting Level (>= 500)'] = (int)ini_get('xdebug.max_nesting_level') >= 500;
        }

        return $requirements;
    }

    /**
     * @return bool
     */
    private function allRequirementsLoaded()
    {
        $allLoaded = true;

        foreach ($this->getRequirements() as $loaded) {
            if ($loaded == false) {
                $allLoaded = false;
            }
        }

        return $allLoaded;
    }

    /**
     * @return array
     */
    private function getPermissions()
    {
        return [
            'public/upload/users' => is_writable(public_path('upload/users')),
            'storage/app' => is_writable(storage_path('app')),
            'storage/framework/cache' => is_writable(storage_path('framework/cache')),
            'storage/framework/sessions' => is_writable(storage_path('framework/sessions')),
            'storage/framework/views' => is_writable(storage_path('framework/views')),
            'storage/logs' => is_writable(storage_path('logs')),
            'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            'Base Directory' => is_writable(base_path('')),
        ];
    }

    private function allPermissionsGranted()
    {
        $allGranted = true;

        foreach ($this->getPermissions() as $permission => $granted) {
            if ($granted == false) {
                $allGranted = false;
            }
        }

        return $allGranted;
    }

    private function dbCredentialsAreValid($credentials)
    {
        $this->setDatabaseCredentials($credentials);

        try {
            DB::statement("SHOW TABLES");
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param $credentials
     */
    private function setDatabaseCredentials($credentials)
    {
        $default = config('database.default');

        config([
            "database.connections.{$default}.host"     => $credentials['host'],
            "database.connections.{$default}.database" => $credentials['database'],
            "database.connections.{$default}.username" => $credentials['username'],
            "database.connections.{$default}.password" => $credentials['password'],
            "database.connections.{$default}.prefix"   => $credentials['prefix']
        ]);
    }
}
