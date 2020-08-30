<?php
namespace Taro\DI;

return [
    'services'=>[
            'test_service'=>function (\ClassA $a) {
                $c = new \ClassC();
                $a->execRun($c);
            },
        ],
    'singletons'=>[
        'test_singleton'=>function ($name) {
            $obj = new \stdClass();
            $obj->name = $name;
            return $obj;
        } ,

        
    ]





];
