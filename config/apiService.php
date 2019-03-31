<?php
return[

    'api_token' => 'key_token',// 自定义请求数据验证 token

    /**
     * 忽略验证路由
     */
    'ignore_api' => array('black_list'),

    /**
     * 忽略签名验证字段
     */
    'ignore_verification_fields' => array('serviceId','search_type','user_id'),

    /**
     * serviceId_mapping[serviceId => request_url]
     */
    'serviceId_mapping' => array(
        '692bb073-fa02d931-7b8cd4ce-b6f5d244' => array('api/v1/index'),
        'id2' => array('url2'),
        'id3' => array('url3'),
    ),

    /**
     *  request code
     */
    'code' => array(
        'Default code' => '0',
        'No authority' => '-1',
        'Parameter deletion' => '-2',
        'Incorrect parameter format' => '-3',
        'Request was aborted' => '-4',
        'Request success'  => '1',
        'NotFound httpException'  => '-5',
        // 其它 code
    ),

    /**
     *  request code_message
     */
    'code_message' => array(
        '0' => '未知操作',
        '-1' => '无权限',
        '-2' => '请求参数缺失!',
        '-3' => '请求参数格式有误!',
        '-4' => '请求失败!',
        '-5' => '请求路由有误!',
        '1'  => '请求成功!',
        // 其它 code_message
    )
];