<?php
require_once "vendor/autoload.php";
use Taro\DI\Container;

class ClassA
{
    public $var;
    public function __construct(ClassB $b, $var1)
    {
        echo 'ClassA has been created.' . PHP_EOL;
        echo $b;
        echo $var1 . PHP_EOL;
        $this->var = $var1;
    }

    public function execRun(ClassC $c)
    {
        $c->run();
    }
}

class ClassB
{
    public function __construct()
    {
        echo 'ClassB has been created.' . PHP_EOL;
    }
    public function __toString()
    {
        return 'This is ClassB' . PHP_EOL;
    }
}

class ClassC
{
    public function __construct()
    {
        echo 'ClassC has been created.' . PHP_EOL;
    }
    public function run($time = '60')
    {
        echo 'ClassC\'s method run is called' . PHP_EOL;
        echo 'time:'. $time . PHP_EOL;
    }
}

function getClassVariable(ClassA $a, $opt = 'オプション')
{
    echo 'function getClassVariable'. PHP_EOL;
    echo $opt . PHP_EOL;
    return $a->var;
}

$func = function (ClassA $a) {
    echo 'callback' . PHP_EOL;
};

echo 'instantiating...'.PHP_EOL;
$di = (new Container())->loadConfig('./src/DI/config_sample.php');

echo 'resolve ClassA'.PHP_EOL;
$a = $di->resolve(ClassA::class, ['var1'=>'var1 variable']);



$di->bind('callback_a', $func);

echo 'resolve callback_a'.PHP_EOL;
$f = $di->resolve('callback_a', ['var1'=>'var1 variable']);

$di->singleton('facade_a', ClassA::class);

echo 'resolve facade_a'.PHP_EOL;
$f = $di->resolve('facade_a', ['var1'=>'var1 variable']);
echo $f->var . PHP_EOL;
$f = $di->resolve('facade_a', ['var1'=>'var2 variable']);
echo $f->var . PHP_EOL;


echo 'resolve getClassVariable'.PHP_EOL;
$aVar= $di->resolve('getClassVariable', ['var1'=>'var2 variable']);
echo $aVar. PHP_EOL;

echo 'callmethod'.PHP_EOL;
$di->callMethod('facade_a', 'execRun', ['time'=>20]);

$di->callMethod(ClassA::class, 'execRun', null, ['var1'=>'var1 variable']);


echo 'test config.php'.PHP_EOL;
$obj = $di->resolve('test_singleton', ['name'=>'taro']);
echo $obj->name.PHP_EOL;
$obj = $di->resolve('test_singleton', ['name'=>'hanako']);
echo $obj->name.PHP_EOL;
$di->resolve('test_service', ['var1'=>'test2 argument var1']);
