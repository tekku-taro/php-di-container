# Php DI Container



Dependency Injection に必要な基本的な機能を備えた１ファイルのコンテナです。

## 使い方



### コンテナ生成

```php
$di = new Container();
```



### コンテナへサービスの登録

```php
$di->bind(登録名, クラスやクロージャ);
```



### コンテナへサービスの登録（シングルトン）

```php
$di->singleton(登録名, クラスやクロージャ);
```



### サービスの依存解決

```php
$di->resolve(登録名, ['key'=>$value]);
```

**['変数名'=>値]** : 必要ならばパラメータとして渡す変数の配列



### サービスクラスのメソッドインジェクション

```php
$di->callMethod(サービスインスタンス, メソッド名, ['key'=>$value]);
```

**第３引数**：メソッドに渡す変数配列

```php
$di->callMethod(登録名, メソッド名, ['key'=>$value], ['key'=>$value]);
```

**第４引数**：クラスに渡す変数配列



## コンフィグファイル

専用のコンフィグファイル(config.php)を作成し、そこから登録するサービスを一括で読み込める



### DI コンテナ用の設定ファイル (config.php)

コンテナに一括で登録するサービスを記述する

loadConfig() でコンテナにロードする

```php
$filePath = "path/to/your/config.php";
$di->loadConfig($filePath);
```

- **services**: 毎回新しいサービスインスタンスを作成
- **singletons**: 一度作成したインスタンスを共有

登録するサービスの特徴を踏まえてどちらかの配列に追加

#### 記述方法

```php
return [
    'services'=>[
        '登録名' => クロージャ又はクラス名,
        'test_service'=>function (\ClassA $a) {
          $c = new \ClassC();
          $a->execRun($c);
        },
    ],
    'singletons'=>[
    ]

];
```



## ライセンス (License)

**Php DI Container**は[MIT license](https://opensource.org/licenses/MIT)のもとで公開されています。

**Php DI Container** is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

@author tekku-taro @2020