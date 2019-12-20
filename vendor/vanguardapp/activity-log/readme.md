User Activity Log plugin for [Vanguard - Advanced PHP Login and User Management](https://vanguardapp.io)
system.

This plugin was originaly part of the Vanguard itself, but it has been extracted as a separate plugin starting from Vanguard 4.

## Installation

This plugin requires Vanguard `4.0.0` or greater.

### Installation via Composer

To install the plugin first you will need to pull it via composer 
by running the following command

```
composer require vanguardapp/activity-log
```

The composer will install the plugin for you as well as it's dependencies.

The next step is to register the plugin by adding the 
`\Vanguard\UserActivity\UserActivity::class` 
to the list of Vanguard plugins inside the `VanguardServiceProvider`:

```php
protected function plugins()
    {
        return [
            //...
            \Vanguard\UserActivity\UserActivity::class,
        ];
    }
```

As soon as your plugin is registered, you should publish the 
plugins migrations by running the following command:

```
php artisan vendor:publish --provider="Vanguard\UserActivity\UserActivity"  --tag="migrations"
```

And, as the last step of the installation, you will need to
run the following commands to make all the necessary database modifications:

```
php artisan migrate
php artisan db:seed --class="ActivityPermissionsSeeder"
```

At this point the plugin will be fully installed and ready to go.
It is configured to listen for most of the events that are coming from
Vanguard and to put the into the activity log.

### Manual Installation

If you plan to make the modifications to the plugin and customize it to
fit your needs, it's much easier if you add it to your project manually.

To do so, you will need to download the ZIP archive from GitHub
by clicking the green "Clone or download" button and then choosing
the "Download ZIP" option from the dropdown.

Once you have the ZIP file on your computer, extract it to the 
`plugins/ActivityLog` folder (you will need to create this folder
since it probably won't be present in your Vanguard installation).

Next step is to update your main `composer.json` file located in 
Vanguard's root directory and add the following object to the `repositories`
array:

```
{
    "type": "path",
    "url": "./plugins/ActivityLog"
}
```

This will tell the composer that your plugin is located in `/plugins/ActivityLog`
directory and that it should be installed from there. 

Now, add the following to the composer's `require` section 

```
"vanguardapp/activity-log": "*"
```

And run `composer update`.

Composer will now install the plugin from your local directory instead
of pulling it from GitHub, which means that you will be able to make 
the changes to the plugin itself and customize it to fit your needs.

The rest of the process is the same as when the plugin is installed 
by directly fetching it via composer from the GitHub repository, so you
will need to do all the same steps as above, which in short involves 
updating the `VanguardServiceProvider` and running the commands to 
publish plugin's static assets and to update the database.

## Dashboard Widgets

A plugin provides user activity dashboard widget that is visible for all users with a role `User`.

To activate the widget add the `Vanguard\UserActivity\Widgets\ActivityWidget::class` to the widgets array in `VanguardServiceProvider`:

```php
protected function widgets()
{
    return [
       //...
       \Vanguard\UserActivity\Widgets\ActivityWidget::class,
    ];
}
```

## License

This plugin is an open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT). 
