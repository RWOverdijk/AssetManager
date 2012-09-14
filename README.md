# AssetManager
By [Wesley Overdijk](http://blog.spoonx.nl/) and [Marco Pivetta](http://ocramius.github.com/)

[![Build Status](https://secure.travis-ci.org/RWOverdijk/AssetManager.png?branch=master)](http://travis-ci.org/RWOverdijk/AssetManager)

## Introduction
This module is intended for usage with a default directory structure of a
[ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication/). It provides functionality to load
assets and generally static files from your modules' directories as of your configuration.
This allows you to avoid having to copy your files over to the `public/` directory, and makes usage of assets very
similar to what already is possible with view scripts, which can be overridden by other modules.

## Installation

 1.  Add `"minimum-stability": "dev"` to your `composer.json`
 2.  Require assetmanager:

```sh
./composer.phar require rwoverdijk/assetmanager
#when asked for a version, type "dev-master" or "1.*"
```

## Usage

Take a look at the [wiki](https://github.com/RWOverdijk/AssetManager/wiki) for a quick start and more information.

Sample module config:

```php
<?php
return array(
    'asset_manager' => array(

        'resolver_configs' => array(

            /* collections. Will serve content of all files for
             * "js/d.js". Entries will be resolved individually.
             * You are adviced to try and keep them in the "map" entry.
             */
            'collections' => array(
                'js/d.js' => array(
                    'js/a.js',
                    'js/b.js',
                    'js/c.js',
                ),
            ),

            // adding MyModule/public to the asset directories
            'paths' => array(
                __DIR__ . '/../public',
            ),


            // overrides (with high priority) used when we want to
            // expose single particular files
            'map' => array(
                'specific-path.css' => __DIR__ . '/some/particular/file.css',
            ),

            // Used when you want to define a priority per path.
            'prioritized_paths' => array(
                array(
                    'path'      => __DIR__ . '/../public_assets',
                    'priority'  => 100,
                ),
                array(
                    'path'      => __DIR__ . '/../fallback_assets',
                    'priority'  => 50,
                ),
                array(
                    'path'      => __DIR__ . '/../assets',
                    'priority'  => 10,
                ),
            ),
        ),

        'filters' => array(
            'js/d.js' => array(
                array(
                    // Note: You will need to require the classes used for the filters yourself.
                    'filter' => 'JSMin',
                ),
            ),
        ),

        'caching' => array(
            'default' => array(
                'cache'     => 'FilePath',
                'options' => array(
                    'dir' => 'cache'
                ),
            ),

            'js/d.js' => array(
                'cache'     => 'Apc',
            ),
        ),
    ),
);
```

*Please be careful, since this module will serve every file as-is, including PHP code*

## Todo
The task list is still long, but the module provides useful functionality already.

 * Compiling assets into a publicly available directory via CLI command
 * Routing (to allow obtaining paths to compiled/cached assets)
 * Allowing mime-type filters (as keys) and caching
 * Allowing mime-type fallback checks (verify by sequences)
