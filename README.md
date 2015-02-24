# AssetManager
By [Wesley Overdijk](http://blog.spoonx.nl/) and [Marco Pivetta](http://ocramius.github.com/)

[![Build Status](https://secure.travis-ci.org/RWOverdijk/AssetManager.png?branch=master)](http://travis-ci.org/RWOverdijk/AssetManager)
[![Latest Stable Version](https://poser.pugx.org/RWOverdijk/AssetManager/v/stable.png)](https://packagist.org/packages/RWOverdijk/AssetManager)

## Introduction
This module is intended for usage with a default directory structure of a
[ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication/). It provides functionality to load
assets and static files from your module directories through simple configuration.
This allows you to avoid having to copy your files over to the `public/` directory, and makes usage of assets very
similar to what already is possible with view scripts, which can be overridden by other modules.
In a nutshell, this module allows you to package assets with your module working *out of the box*.

## Installation

 1.  Require assetmanager:

```sh
./composer.phar require rwoverdijk/assetmanager
#when asked for a version, type "1.*".
```

## Usage

Take a look at the **[wiki](https://github.com/RWOverdijk/AssetManager/wiki)** for a quick start and more information.
A lot, if not all of the topics, have been covered in-dept there.

**Sample module config:**

```php
<?php
return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'js/d.js' => array(
                    'js/a.js',
                    'js/b.js',
                    'js/c.js',
                ),
            ),
            'paths' => array(
                __DIR__ . '/some/particular/directory',
            ),
            'map' => array(
                'specific-path.css' => __DIR__ . '/some/particular/file.css',
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
* Join us on gitter [![Gitter chat](https://badges.gitter.im/SpoonX/Dev.png)](https://gitter.im/SpoonX/Dev)
* The [FAQ wiki page](https://github.com/RWOverdijk/AssetManager/wiki/FAQ), where you'll perhaps find your answer.
* [RWOverdijk at irc.freenode.net #zftalk.dev or #spoonx](http://webchat.freenode.net/?channels=zftalk.dev%2Czftalk%2Cspoonx&uio=MTE9MTAz8d)
* [Issue tracker](https://github.com/RWOverdijk/AssetManager/issues). (Please try to not submit unrelated issues).

## Todo
The task list has been slimmed down a lot lately. However, there are still a couple of things that should be done.

 * Renewing the cache
