<?php 
require 'vendor/autoload.php';
class Elastic {
    private $hosts = [
        'http://localhost:9200' //IP+端口
    ];
    private  $client;
    public function __construct()
    {
        $this->client = Elasticsearch\ClientBuilder::create()->setHosts($this->hosts)->build();
    }

    # 创建索引
    public function createIndex($indexName) {
        $params = [
            'index' => "{$indexName}",
            'body' => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        $response = $this->client->indices()->create($params);
        $this->dd($response);
    }


    /**
     * 增加数据
     * @param $indexName 索引名
     */
    public function createInfo($indexName) {
        $params = [
            'index' => "{$indexName}",
            'type' => 'goods',
            'id'    => '2',
            'body'  => [
                'goods_name' => 'abc edf ',
                'cn_name' => '你好 泳衣',
                'shop_price' => '66.00',
                'original_img' => 'images/201501/source_img/128402_G_1420269895517.jpg',
                'mtime' => '2021-01-02'
            ]
        ];

        $response = $this->client->index($params);
        $this->dd($response);
    }

    /**
     * 批量增加数据
     * @param $indexName 索引名
     */
    public function createAll($indexName) {
        ini_set('memory_limit','2048M');    // 临时设置最大内存占用为1G
        set_time_limit(0);

        $connect = mysqli_connect("127.0.0.1","root","123456","test");
        if (mysqli_connect_errno($connect))
        {
            echo "连接 MySQL 失败: " . mysqli_connect_error();exit;
        }
        mysqli_set_charset($connect,"utf8");
        $sql = "select * from ly_goods";
        $result = mysqli_query($connect,$sql);

        // 获取数据
        $res = mysqli_fetch_all($result,MYSQLI_ASSOC);

        // 释放结果集
        mysqli_free_result($result);

        mysqli_close($connect);
        $arr = array_chunk($res,20000);
//        $this->dd(count($arr));
//        var_dump($arr);exit;


        foreach ($arr as $k) {
            # 封装数据
            foreach ($k as $document) {
                $params['body'][] = [
                    'index' => [
                        '_index' => "{$indexName}",
                        '_id'    => $document['goods_id'],
                        '_type' => 'goods'
                    ]
                ];

                $params['body'][] = [
                    'goods_id'       => (int)$document['goods_id'],
                    'goods_name'     => $document['goods_name'],
                    'cn_name'        => $document['cn_name'],
                    'shop_price'     => (float)$document['shop_price'],
                    'original_img'   => $document['original_img'],
                    'mtime'          => $document['mtime']
                ];
            }

            # 插入es
            if (isset($params) && !empty($params)) {
                $response = $this->client->bulk($params);
//            $this->dd($response);
                echo "ok";
            }
            unset($params);
        }
        exit;
    }

    /**
     * 更新文档
     *部分更新
     * 如果你要部分更新文档（如更改现存字段，或添加新字段），你可以在 body 参数中指定一个 doc 参数。这样 doc 参数内的字段会与现存字段进行合并。
     * @param $indexName 索引名
     * @param $id 更新的id
     */
    public function update($indexName,$id) {
        $params = [
            'index' => "{$indexName}",
            'id'    => $id,
            'body'  => [
                'doc' => [
                    'goods_name' => 'aaa bbb',
                    'cn_name' => '泳衣 泳裤 泳帽',
                ]
            ]
        ];
        $response = $this->client->update($params);
        $this->dd($response);
    }

    # 删除索引
    public function delIndex($indexName) {
        $deleteParams = [
            'index' => "{$indexName}"
        ];
        $response = $this->client->indices()->delete($deleteParams);
        $this->dd($response);
    }

    /**
     * 删除数据
     * @param $indexName 索引名字
     * @param $id id
     */
    public function del($indexName,$id) {
        $params = [
            'index' => "{$indexName}",
            'id'    => "{$id}"
        ];

        $response = $this->client->delete($params);
        $this->dd($response);
    }

    public function dd($data) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;
    }

}

$es = new Elastic();
//$es->createIndex('test'); # 创建goods 索引
//$es->createInfo('test'); # 增加数据
$es->createAll('oms'); # 批量增加数据
//$es->update('test',2); # 更新数据
//$es->delIndex('test'); # 删除 goods 索引
//$es->delIndex('test',1); # 删除 数据



?>