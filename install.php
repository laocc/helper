<?php

define('_ROOT', substr(__DIR__, 0, strpos(__DIR__, '/vendor/laocc/'))); //网站根目录

$root = ['application', 'common', 'library', 'models', 'public', 'runtime'];
foreach ($root as $r) {
    if (!file_exists(_ROOT . "/{$r}")) mkdir(_ROOT . "/{$r}", 0740, true);
}
mkdir(_ROOT . "/application/www", 0740, true);
mkdir(_ROOT . "/application/www/controllers", 0740, true);
mkdir(_ROOT . "/application/www/views", 0740, true);
mkdir(_ROOT . "/application/www/views/index", 0740, true);
mkdir(_ROOT . "/application/public", 0740, true);
mkdir(_ROOT . "/application/public/www", 0740, true);

$IndexController = '<?php
namespace application\www\controllers;

use esp\core\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
    }
}';

$layout = '
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= \$_title ?></title>
    <?= \$_meta; ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="renderer" content="webkit"/>
    <?= \$_css ?>
</head>
<body>
<?php
echo \$_view_html;
?>
</body>
</html>
';

$option = '<?php
declare(strict_types=1);

ini_set(\'error_reporting\', strval(E_ALL));
ini_set(\'display_errors\', \'true\');
ini_set(\'date.timezone\', \'Asia/Shanghai\');

define("_ROOT", dirname(__DIR__, 1));
is_readable($auto = (_ROOT . "/vendor/autoload.php")) ? include($auto) : exit(\'composer dump-autoload --optimize\');
$option[\'autoload\'] = microtime(true);

$option[\'before\'] = function (&$option) {
    define(\'_CONFIG_LOAD\', 1);
};

$option[\'after\'] = function (&$option) {
};

return $option;';

$index = '<?php
$option = include(dirname(__DIR__) . \'/option.php\');
(new \esp\core\Dispatcher($option, \'www\'))->run();
';

file_put_contents(_ROOT . "/application/www/controllers/IndexController.php", $IndexController);
file_put_contents(_ROOT . "/application/www/views/index/index.php", '<h1>Esp Install Success</h1>');
file_put_contents(_ROOT . "/application/www/views/layout.php", $layout);
file_put_contents(_ROOT . "/application/public/option.php", $option);
file_put_contents(_ROOT . "/application/public/www/index.php", $index);

echo "Install Finish\n";