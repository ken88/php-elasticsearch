<?php

# 搜索

require 'vendor/autoload.php';
$host = [
    'http://localhost:9200' //IP+端口
];
/**
 * number_of_replicas 是数据备份数，如果只有一台机器，设置为0
 * number_of_shards  是数据分片数，默认为5，有时候设置为3
 */
$client = Elasticsearch\ClientBuilder::create()->setHosts($host)->build();
$params = [
    'index' => 'goods',
    'body' => [
       'query' => [
           'match' => [
               'original_img' => 'images'
           ]
       ],
        'highlight' => [
            "pre_tags"=> ["<span style='color: red'>"],
            "post_tags"=> ["</span>"],
            'fields' => [
                'original_img' => new \stdClass()
            ]
        ]
    ]
];

$response = $client->search($params);
dd($response);
function dd($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    exit;
}
?>