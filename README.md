# AssetManager
By [Wesley Overdijk](http://blog.spoonx.nl/) and [Marco Pivetta](http://ocramius.github.com/)

[![Build Status](https://secure.travis-ci.org/RWOverdijk/AssetManager.png)](http://travis-ci.org/RWOverdijk/AssetManager)

## Introduction
This module is intended for usage with a default directory structure of a
[ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication/). It provides functionality to load
assets and generally static files from your modules' directories as of your configuration.
This allows you to avoid having to copy your files over to the `public/` directory, and makes usage of assets very
similar to what already is possible with view scripts, which can be overridden by other modules.

## Installation
------------
 1.  Add `"minimum-stability": "dev"` to your `composer.json`
 2.  Require assetmanager:

```sh
./composer.phar require rwoverdijk/assetmanager
#when asked for a version, type "dev-master"
```

## Usage

In your module's config, define following:

```php
<?php
return array(
    'asset_manager' => array(
        // adding MyModule/public to the asset directories
        'paths' => __DIR__ . '/public',

        'map' => array(
            // overrides (with high priority) used when we want to
            // expose single particular files
            'specific-path.css' => __DIR__ . '/some/particular/file.css',
        ),
    ),
);
```

*Please be careful, since this module will serve every file as-is, including PHP code*

## Todo
The task list is still long, but the module provides useful functionality for dev environments for now.

 * Caching of responses to avoid having the PHP interpreter and the application being bootstrapped each time
 * Compiling assets into a publicly available directory via CLI command
 * Integration with [Assetic](https://github.com/kriswallsmith/assetic) to provide not only links to asset files, but
   also to asset collections and respective filters/caching settings
 * Routing (to allow obtaining paths to compiled/cached assets)
