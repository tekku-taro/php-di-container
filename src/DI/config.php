<?php
namespace Taro\DI;

/**
 * DI コンテナ用の設定ファイル
 *
 * コンテナに一括で登録するサービスを記述する
 * loadConfig() でコンテナにロードする
 *
 * services: 毎回新しいサービスインスタンスを作成
 * singletons: 一度作成したインスタンスを共有
 *
 * 登録するサービスの特徴を踏まえてどちらかの配列に追加
 *
 * 記述方法
 * '登録名' => クロージャ又はクラス名,
 *
 * 'test_service'=>function (\ClassA $a) {
 *      $c = new \ClassC();
 *      $a->execRun($c);
 * },
 *
 */
return [
    'services'=>[

    ],
    'singletons'=>[

    ]

];
