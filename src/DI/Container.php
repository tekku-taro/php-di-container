<?php
/**
 * Php DI Container
 *
 * Dependency Injection に必要な基本的な機能を備えた
 * １ファイルのコンテナです。
 *
 * コンテナ生成
 * $di = new Container();
 *
 * コンテナへサービスの登録
 * $di->bind(登録名, クラスやクロージャ);
 *
 * コンテナへサービスの登録（シングルトン）
 * $di->singleton(登録名, クラスやクロージャ);
 *
 * サービスの依存解決
 * $di->resolve(登録名, ['key'=>$value]);
 * ['変数名'=>値] : 必要ならばパラメータとして渡す変数の配列
 *
 * サービスクラスのメソッドインジェクション
 * $di->callMethod(サービスインスタンス, メソッド名, ['key'=>$value]);
 * 第３引数：メソッドに渡す変数配列
 *
 * $di->callMethod(登録名, メソッド名, ['key'=>$value], ['key'=>$value]);
 * 第４引数：クラスに渡す変数配列
 *
 * 専用のコンフィグファイル(config.php)を作成し、そこから登録するサービスを一括で読み込める
 *
 * @author tekku-taro @2020
 */


namespace Taro\DI;

use ErrorException;
use ReflectionClass;
use ReflectionFunction;

/**
 * Container Class for Dependency Injection
 */
class Container
{
    /**
     * 登録したサービスを保管する配列
     *
     * @var array
     */
    protected $storage;


    /**
     * 設定ファイルを読み込み、ファイル内のサービス一覧をコンテナに登録する
     *
     * @param string $configPath
     * @return object $this
     */
    public function loadConfig($configPath)
    {
        if (!is_readable($configPath)) {
            $configPath = 'config.php';
        }
        // config.phpファイルから登録サービス一覧を読み込む
        $rawdata = include($configPath);
        
        if (empty($rawdata)) {
            throw new ErrorException('config.php cannot be read.');
        }

        // services インスタンス非共有
        foreach ($rawdata['services'] as $abstract => $closure) {
            $this->bind($abstract, $closure);
        }
        
        // singleton インスタンス共有
        foreach ($rawdata['singletons'] as $abstract => $closure) {
            $this->singleton($abstract, $closure);
        }

        return $this;
    }

    /**
     * コンテナにサービスを登録
     *
     * @param string $abstract
     * @param mixed $concrete
     * @param boolean $shared
     * @return array
     */
    public function bind($abstract, $concrete, $shared = false)
    {
        return $this->storage[$abstract] = ['concrete'=>$concrete,'sharedObject'=>null, 'shared'=> $shared];
    }

    /**
     * コンテナにサービスを登録
     * 一度作成したインスタンスは以降共有する
     *
     * @param string $abstract
     * @param mixed $concrete
     * @return void
     */
    public function singleton($abstract, $concrete)
    {
        return $this->bind($abstract, $concrete, true);
    }

    /**
     * $abstractで指定されたサービスのインスタンスを返す
     * 最初にコンテナに登録済みか確認し、
     * あれば 対応する $concreteから作成
     * 無ければ $abstract から作成
     *
     * @param string $abstract
     * @param array $params
     * @return object $instance
     */
    public function resolve($abstract, $params = [])
    {
        if (isset($this->storage[$abstract])) {
            // インスタンスを共有する場合
            if ($this->storage[$abstract]['shared']) {
                if (!empty($this->storage[$abstract]['sharedObject'])) {
                    return $this->storage[$abstract]['sharedObject'];
                }
                $concrete = $this->storage[$abstract]['concrete'];
                $instance = $this->make($concrete, $params);
                $this->storage[$abstract]['sharedObject'] = $instance;
                return $instance;
            }
            // $concrete　からインスタンスを生成
            $concrete = $this->storage[$abstract]['concrete'];
            $instance = $this->make($concrete, $params);
            return $instance;
        }
        // $abstract　からインスタンスを生成
        return $this->make($abstract, $params);
    }


    /**
     * $abstract のパラメーターの依存解決後、
     * インスタンス生成／関数実行して結果を返す
     *
     * @param mixed $abstract
     * @param array $params
     * @return mixed
     */
    public function make($abstract, $params = [])
    {
        // $abstractが関数かのフラグ
        $isFunc = false;

        // $abstractがクラスか、関数か、クロージャかにより、
        // 作成する Reflection クラスを切り替える
        if (is_string($abstract) &&  class_exists($abstract)) {
            $reflectionClass = new ReflectionClass($abstract);
            $reflection = $reflectionClass->getConstructor();
        } elseif (is_string($abstract) &&  function_exists($abstract)) {
            $reflection = new ReflectionFunction($abstract);
            $isFunc = true;
        } elseif (!is_string($abstract) && get_class($abstract) == 'Closure') {
            $reflection = new ReflectionFunction($abstract);
            $isFunc = true;
        } else {
            return $abstract;
        }

        // 依存解決したパラメーターの配列
        $resolvedParams = [];
        // var_dump($reflection);
        if ($reflection) {
            $resolvedParams = $this->resolveParamters($reflection, $params);
        }

        if ($isFunc) {
            // 関数なら実行した結果を返す
            // var_dump($resolvedParams);
            return call_user_func_array($abstract, $resolvedParams);
        } else {
            // クラスのインスタンスを返す
            return $reflectionClass->newInstanceArgs($resolvedParams);
        }
    }

    /**
     * クラスのメソッドインジェクション
     *
     * @param mixed $instance
     * @param string $method
     * @param array $params
     * @param array $classParams
     * @return mixed
     */
    public function callMethod($instance, $method, $params = [], $classParams = [])
    {
        if (is_string($instance)) {
            $instance = $this->resolve($instance, $classParams);
        }

        if (!is_object($instance)) {
            throw new ErrorException('$instance is not an object!');
        }

        //インスタンスのリフレクションメソッドを取得
        $reflectionClass = new ReflectionClass($instance);
        $reflectionMethod = $reflectionClass->getMethod($method);

        // メソッドのパラメータ依存解決
        $resolvedParams = $this->resolveParamters($reflectionMethod, $params);

        // メソッドを実行して結果を返す
        return call_user_func_array([$instance, $method], $resolvedParams);
    }


    /**
     * Reflection オブジェクトの パラメーターの依存解決
     *
     * @param mixed $reflection
     * @param array $params
     * @return array
     */
    protected function resolveParamters($reflection, $params = [])
    {
        // 依存解決したパラメーターの配列
        $resolvedParams = [];
        foreach ($reflection->getParameters() as $parameter) {
            // パラメーターの変数名
            $paramName = $parameter->getName();
            // パラメーターからReflectionClassを取得
            $paramClass = $parameter->getClass();
                
            if ($paramClass == null) {
                // クラスではない 変数パラメーターにあれば渡す
                if (isset($params[$paramName])) {
                    $resolvedParams[$paramName] = $params[$paramName];
                    unset($params[$paramName]);
                // オプションパラメーターならば、デフォルト値を取得
                } elseif ($parameter->isOptional()) {
                    $resolvedParams[$paramName] = $parameter->getDefaultValue();
                }
            } else {
                //　パラメーターはクラス （クロージャも）
                $className = $paramClass->getName();
                // クラスの依存解決を行い、結果を配列に格納
                $resolvedParams[$paramName] = $this->resolve($className, $params);
            }
        }

        return $resolvedParams;
    }

    /**
     * get storage array
     *
     * @return array
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
