<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/27
 * Time: 下午10:15
 */
namespace Lib\ElasticSearch;

use Elasticsearch\ClientBuilder;

class Es
{
    protected $client;
    protected $config;
    protected $initParams;

    /**
     * Es constructor.
     * @param null $config
     * @throws \Exception
     */
    public function __construct($config=null){
        if (empty($config)) {
            $this->config = config('elasticsearch.elasticsearch');
        }else if(!check_es_config($config)){
            throw new \Exception('config is not valid!');
        }else{
            $this->config = $config;
        }

        try{
            if (null == $this->client) {
                $this->client = ClientBuilder::create($this->config['hosts'])->build();
            }
        }catch (\Exception $e) {
            $r = $e->getMessage();
            throw new \Exception($r);
        }
        $this->initParams = array(
            'index' => $this->config['index'],
            'type' => $this->config['type'],
        );
    }

    /**
     * 创建索引文档
     * @param $fields
     * @param null $id
     * @return array
     */
    public function createIndex() {
        try{
            $res = $this->client->indices()->create(array('index' => $this->config['index']));
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * 删除文档
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function deleteDocById($id) {
        try{
            $params = $this->initParams;
            $params['id'] = $id;
            $res = $this->client->indices()->delete($params);
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * 设置全文索引 mapping
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function setMapping($data) {
        try{
            $params = $this->initParams;
            if (!is_array($data)) {
                $data = object2array(json_decode($data));
            }
            $mapParam = [];
            foreach ($data as $field => $field_type) {
                $mapParam[$field] = [
                    'type' => $field_type,
                    'analyzer' => 'ik_max_word',
                    'search_analyzer' => 'ik_max_word',
                ];
            }
            $params['body'][$params['type']]['properties'] = $mapParam;
            $res = $this->client->indices()->putMapping($params);
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * 增加文档
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function addDoc($data) {
        try{
            $params = $this->initParams;
            if (!is_array($data)) {
                $data = object2array(json_decode($data));
            }
            if (array_key_exists('id',$data)) {
                $params['id'] = $data['id'];
            }
            $params['body'] = $data;
            $res = $this->client->index($params);
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * 单字段匹配
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function search($data) {
        try{
            $params = $this->initParams;
            if (!is_array($data)) {
                $data = object2array(json_decode($data));
            }
            $field = key($data);
            $query = [
                'match' => [
                    $field => [
                        'query' => $data[$field],
                        'minimum_should_match' => '90%'
                    ]
                ]
            ];
            $highlight = [
                'fields' => [
                    $field => [
                        'pre_tags' => '<strong>',
                        'post_tags' => '</strong>',
                    ]
                ]
            ];
            $params['body']['query'] = $query;
            $params['body']['highlight'] = $highlight;
            $res = $this->client->search($params);
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * 多字段匹配
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function searchMulti($data) {
        try{
            $params = $this->initParams;
            if (!is_array($data)) {
                $data = object2array(json_decode($data));
            }
            $field = key($data);
            $field_arr = explode(",",$field);
            $query = [
                'multi_match' => [
                    'query' => $data[$field],
                    'type' => "best_fields",
                    'fields' => $field_arr,
                    'tie_breaker' => 0.3
                ]
            ];
            $highlight = [];
            foreach ($field_arr as $field) {
                $highlight['fields'][$field] = [
                    'pre_tags' => '<strong>',
                    'post_tags' => '</strong>',
                ];
            }
            $params['body']['query'] = $query;
            $params['body']['highlight'] = $highlight;
            $res = $this->client->search($params);
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /**
     *批量创建索引文档
     */
    public function multiCreateIndex() {
        $params = ['body' => []];

        for ($i = 1; $i <= 1234567; $i++) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'my_index',
                    '_type' => 'my_type',
                    '_id' => $i
                ]
            ];

            $params['body'][] = [
                'my_field' => 'my_value',
                'second_field' => 'some more values'
            ];

            // Every 1000 documents stop and send the bulk request
            if ($i % 1000 == 0) {
                $responses = $this->client->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($responses);
            }
        }

        // Send the last batch if it exists
        if (!empty($params['body'])) {
            $responses = $this->client->bulk($params);
        }
    }
}