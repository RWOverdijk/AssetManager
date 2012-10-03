# AssetManager 1.2.0
By [Wesley Overdijk](http://blog.spoonx.nl/) and [Marco Pivetta](http://ocramius.github.com/)

[![Build Status](https://secure.travis-ci.org/RWOverdijk/AssetManager.png?branch=master)](http://travis-ci.org/RWOverdijk/AssetManager)

## Introduction
This module is intended for usage with a default directory structure of a
[ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication/). It provides functionality to load
assets and static files from your module directories through simple configuration.
This allows you to avoid having to copy your files over to the `public/` directory, and makes usage of assets very
similar to what already is possible with view scripts, which can be overridden by other modules.
In a nutshell, this module allows you to package assets with your module working *out of the box*.

## Installation

 1.  Add `"minimum-stability": "dev"` to your `composer.json`
 2.  Require assetmanager:

```sh
./composer.phar require rwoverdijk/assetmanager
#when asked for a version, type "dev-master" or "1.*". The latter being prefered.
```

## Usage

Take a look at the [wiki](https://github.com/RWOverdijk/AssetManager/wiki) for a quick start and more information.
A lot, if not all of the topics, have been covered in-dept there.

**Sample module config:**

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

*Please be careful, since this module will serve every file as-is, including PHP code.*

## Questions / support
If you're having trouble with the asset manager there are a couple of resources that might be of help.
* The [FAQ wiki page](https://github.com/RWOverdijk/AssetManager/wiki/FAQ), where you'll perhaps find your answer.
* RWOverdijk at irc.freenode.net #zftalk.dev
* Issue tracker. (Please try to not submit unrelated issues).
* By email

## Todo
The task list has been slimmed down a lot lately. However, there are still a couple of things that should be done.

 * Warming up the cache
 * Renewing the cache
