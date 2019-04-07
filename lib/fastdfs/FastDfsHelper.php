<?php
namespace App\Lib\FastDfs;
/**
 * Class FastDfsHelper
 * @package Lib
 */
class FastDfsHelper
{
    private $_tracker;

    private $_group_name;
    private $_storage;
    private $_storage_arr;

    private $_error; // array('no' => fastdfs_get_last_error_no(), 'info' => fastdfs_get_last_error_info(), 'act' => $act)

    private static $instance = null;

    /**
     * FastDfsHelper constructor.
     */
    private function __construct(){
        return true;
    }

    /**
     * destruct
     */
    public function __destruct(){
        fastdfs_tracker_close_all_connections();
    }

    /**
     * @param string $group_name
     * @return bool|FastDfsHelper|null
     */
    public static function getInstance($group_name = ''){
        if (self::$instance == null){
            self::$instance = new FastDfsHelper();

            if( !self::$instance->_loadTracker() ){
                return false;
            }
            if (!self::$instance->storageConnection($group_name)) {
                return false;
            }
        }

        return self::$instance;
    }

    /**
     * 连接 tracker
     * @return bool
     */
    private function _loadTracker(){
        $this->_tracker = fastdfs_tracker_get_connection(); //获取一个 tracker
        if( !$this->_tracker ){
            return false;
        }

        if( !fastdfs_active_test($this->_tracker) ){    //测试当前storage的状态
            $this->_setLastError('fastdfs_active_test(_tracker)');
            return false;
        }

        return true;
    }

    /**
     * fastdfs_get_last_error_no 错误记录数
     * fastdfs_get_last_error_info 错误信息
     * @param string $act
     */
    private function _setLastError( $act = '' ){
        $this->_error = array('no' => fastdfs_get_last_error_no(), 'info' => fastdfs_get_last_error_info(), 'act' => $act);
    }

    /**
     * 连接storage
     * @param string $group_name
     * @return boolean
     */
    public function storageConnection($group_name){

        if( !isset($this->_storage_arr[$group_name]) ){
            $this->_storage_arr[$group_name] = fastdfs_tracker_query_storage_store($group_name, $this->_tracker);
            if( !$this->_storage_arr[$group_name] ){
                return false;
            }

            $server = fastdfs_connect_server($this->_storage_arr[$group_name]['ip_addr'], $this->_storage_arr[$group_name]['port']);
            if( !$server ){
                $this->_setLastError('fastdfs_connect_server(_storage)');
                return false;
            }

            if( !fastdfs_active_test($server) ){
                $this->_setLastError('fastdfs_active_test(server)');
                return false;
            }
            $this->_storage_arr[$group_name]['sock'] = $server['sock'];
            unset($server);
        }

        if( !isset($this->_storage_arr[$group_name]) ){
            $this->_group_name    = null;
            $this->_storage       = null;
            return false;
        }else{
            $this->_group_name    = $group_name;
            $this->_storage       = &$this->_storage_arr[$group_name];
        }

        return true;
    }

    /**
     * 上传文件
     * @param string $local_filename 本地文件路径
     * @param boolean $file_ext_name 文件后缀名
     * @param boolean $is_file_buff 是否是字符内容
     * @return boolean|array array('group_name' => '', 'filename' => '')
     */
    public function upload( $local_filename, $file_ext_name = null, $is_file_buff = false ){
        if($is_file_buff){
            $act = 'fastdfs_storage_upload_by_filebuff';
        }else{
            $act = 'fastdfs_storage_upload_by_filename';
        }
        $file_info = $act($local_filename, $file_ext_name, array(), null, $this->_tracker, $this->_storage);
        if( !$file_info ){
            $this->_setLastError($act.'('.$local_filename.')');
            return false;
        }

        return $file_info;
    }

    /**
     * 上传从文件
     * @param string $local_filename  本地文件路径
     * @param string $master_filename storage里的主文件路径
     * @param string $prefix_name 后缀
     * @param boolean $file_ext_name 文件后缀名
     * @param boolean $is_file_buff 是否是字符内容
     * @return boolean|array  array('group_name' => '', 'filename' => '')
     */
    public function uploadSlave( $local_filename, $master_filename, $prefix_name, $file_ext_name = null, $is_file_buff = false ){
        if($is_file_buff){
            $act = 'fastdfs_storage_upload_slave_by_filebuff';
        }else{
            $act = 'fastdfs_storage_upload_slave_by_filename';
        }
        $file_info = $act($local_filename, $this->_group_name, $master_filename, $prefix_name, $file_ext_name, array(), $this->_tracker, $this->_storage);
        if( !$file_info ){
            $this->_setLastError($act.'('.$local_filename.','.$master_filename.','.$prefix_name.')');
            return false;
        }

        return $file_info;
    }

    /**
     * 删除文件
     * @param string $group_name
     * @param string $filename
     */
    public function delete($group_name, $filename){
        return fastdfs_storage_delete_file($group_name, $filename);
    }

    /**
     * 下载文件
     * @param string $group_name
     * @param string $filename
     */
    public function down($group_name, $filename){
        $file_content = fastdfs_storage_download_file_to_buff($group_name, $filename);
        return $file_content;
    }

    /**
     * @param $group_name
     * @param $filename
     * @return mixed
     */
    public function exist($group_name, $filename){
        return fastdfs_storage_file_exist($group_name, $filename);
    }

    /**
     * 获取最后的错误信息
     */
    public function lastError(){
        if( !$this->_error ){
            $this->__setLastError();
        }
        return $this->_error;
    }

    /**
     * @param $file
     * @return array|bool
     */
    public function uploadFile($file) {
        if (!is_array($file)) {
            $fileContent = $file;
            $pathInfo = pathinfo($file);
            $fileSuffix = $pathInfo['extension'];
            $is_file_buff = false;
        }else{
            $curlFile = new \CurlFile($file['tmp_name'], $file['type'], $file['name']);
            $fileSuffix = getSuffix($curlFile->getPostFilename());
            $fileContent = file_get_contents($curlFile->getFilename());
            $is_file_buff = true;
        }
        return $this->upload($fileContent,$fileSuffix,$is_file_buff);
    }

    /**
     * 获取版本
     * @return mixed
     */
    public function getVersion(){
        return fastdfs_client_version();
    }
}
