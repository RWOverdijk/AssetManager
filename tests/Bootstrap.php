<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\Loader\StandardAutoloader;

chdir(__DIR__);


$previousDir = '.';

while (!file_exists('config/application.config.php')) {
    $dir = dirname(getcwd());

    if ($previousDir === $dir) {
        throw new RuntimeException(
            'Unable to locate "config/application.config.php":'
            . ' is AssetManager in a sub-directory of your application skeleton?'
        );
    }

    $previousDir = $dir;
    chdir($dir);
}

if (!(@include_once __DIR__ . '/../../vendor/autoload.php') && !@(include_once __DIR__ . '/../../../autoload.php')) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

if (!$config = @include __DIR__ . '/TestConfiguration.php') {
    $config = require __DIR__ . '/TestConfiguration.php.dist';
}

$loader = new StandardAutoloader();
$loader->registerNamespace('AssetManager', __DIR__ . '/../src/AssetManager');
$loader->registerNamespace('AssetManagerTest', __DIR__ . '/AssetManagerTest');
$loader->register();

$serviceManager = new ServiceManager(new ServiceManagerConfig(
    isset($config['service_manager']) ? $config['service_manager'] : array()
));
$serviceManager->setService('ApplicationConfig', $config);

/* @var $moduleManager \Zend\ModuleManager\ModuleManager */
$moduleManager = $serviceManager->get('ModuleManager');
$moduleManager->loadModules();
