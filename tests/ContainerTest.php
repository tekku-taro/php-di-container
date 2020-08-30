<?php
require_once "vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use Taro\DI\Container;

class ContainerTest extends TestCase
{
    public function setUp():void
    {
        $this->c = new ClassC;
        $this->b = new ClassB;
        $this->a = new ClassA($this->b, 'var1 variable');
    }

    public function testLoadConfig()
    {
        $filePath =  __DIR__ .'/config_sample.php';
        $expected = [
            'test_service'=>['concrete'=>function (\ClassA $a) {
                $c = new \ClassC();
                $a->execRun($c);
            },'sharedObject'=>null, 'shared'=> false],
            'test_singleton'=>['concrete'=>function ($name) {
                $obj = new \stdClass();
                $obj->name = $name;
                return $obj;
            },'sharedObject'=>null, 'shared'=> true],
        ];
        $di = (new Container())->loadConfig($filePath);

        $this->assertEquals($expected, $di->getStorage());
    }

    public function testBind()
    {
        $func = function (ClassA $a) {
            echo 'callback' . PHP_EOL;
        };

        $di = new Container();

        $di->bind('callback_a', $func);

        $expected = [
            'callback_a'=>['concrete'=> $func,'sharedObject'=>null, 'shared'=> false]
        ];

        $this->assertEquals($expected, $di->getStorage());
    }

    public function testSingleton()
    {
        $func = function (ClassA $a) {
            echo 'callback' . PHP_EOL;
        };

        $di = new Container();

        $di->singleton('callback_b', $func);

        $expected = [
            'callback_b'=>['concrete'=> $func,'sharedObject'=>null, 'shared'=> true]
        ];

        $this->assertEquals($expected, $di->getStorage());
    }

    public function testResolveWithoutPreBinding()
    {
        $di = new Container();
        $a = $di->resolve(ClassA::class, ['var1'=>'var1 variable']);

        $this->assertEquals($this->a, $a);
    }

    public function testResolveWithBinding()
    {
        $di = new Container();
        $di->bind('class_a', ClassA::class);

        $a = $di->resolve('class_a', ['var1'=>'var1 variable']);

        $this->assertEquals($this->a, $a);
    }

    public function testResolveSingleton()
    {
        $di = new Container();
        $di->singleton('class_a', ClassA::class);

        $a = $di->resolve('class_a', ['var1'=>'first']);
        $expected = 'first';

        $this->assertEquals($expected, $a->var);
        
        $f = $di->resolve('class_a', ['var1'=>'second']);
        $this->assertEquals($expected, $a->var);
    }

    public function testResolveFunction()
    {
        $di = new Container();
        $option = '関数のオプション';
        $result = $di->resolve('getClassVariable', ['var1'=>'a in func','opt'=>$option]);
        $expected = ['a in func', $option];
        $this->assertEquals($expected, $result);
    }

    public function testCallMethod()
    {
        $di = new Container();
        $option = '関数のオプション';
        $di->bind('class_a', ClassA::class);
        $result = $di->callMethod('class_a', 'execRun', ['time'=>20], ['var1'=>'a in callmethod']);
        $expected = '20秒後';
        $this->assertEquals($expected, $result);

        $a = $di->resolve('class_a', ['var1'=>'var1 variable']);
        $result = $di->callMethod($a, 'execRun');
        $expected = '60秒後';
        $this->assertEquals($expected, $result);
    }
}

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

    public function execRun(ClassC $c, $time = null)
    {
        print 'test ' . $time . PHP_EOL;
        return $c->run($time);
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
    public function run($time = null)
    {
        if (is_null($time)) {
            $time = 60;
        }
        echo 'ClassC\'s method run is called' . PHP_EOL;
        echo 'time:'. $time . PHP_EOL;
        return $time . '秒後';
    }
}

function getClassVariable(ClassA $a, $opt = 'オプション')
{
    echo 'function getClassVariable'. PHP_EOL;
    echo $opt . PHP_EOL;
    return [$a->var,$opt];
}
