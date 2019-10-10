<?php
namespace App\Lib;

/**
 * -------------------------------
 * curl轻量级封装
 * -------------------------------
 * @author Echo
 * @copyright 微时代
 */
class CurlHandle {
	protected $_curlInfo	= null;
    protected $_curlError   = null;
	protected $_isPrintHeader	= false;
	protected $_headerInfo	= null;
    protected $_sslcert     = null;
    protected $_sslkey      = null;
    protected $_cainfo      = null;
    protected $_timeout     = 2;
    protected $_connection_timeout = 5;
	protected $_headers	=  null;

	/**
	 * -------------------------------
	 * 配置提交时header
	 * -------------------------------
	 * @param	string		$key	header名称
	 * @param	string		$val	header名称对应值
	 */
	public function setHeaders($key, $val){
		$headres = array_keys($this->_headers);
		if(!in_array($key, $headres)) return false;
		$this->_headers[$key] = $val;
	}

	public function setHttpHeaders($https=array()){
		if(empty($https)){
			$this->_httpHeaders = array('Content-Type:image/jpeg');
		}else{
			$this->_httpHeaders = $https;
		}
	}

	/**
	 * --------------------------------
	 * 返回header名对应的值
	 * --------------------------------
	 * @param	string		$key	header名称
	 */
	public function getHeaders($key){
		$headres = array_keys($this->_headers);
		if(!in_array($key, $headres))return null;
		return $this->_headers[$key];
	}

	/**
	 * ----------------------------------
	 * 请求完成后是否打印header信息
	 * ----------------------------------
	 * @param	bool		$isPrint	是否返回header信息
	 */
	public function isPrintHeader($isPrint){
		$this->_isPrintHeader = $isPrint;
	}

	/**
	 * ---------------------------------
	 * 保存curl请求的返回值
	 * ---------------------------------
	 * @param	array		$info	curl请求完成后返回的状态值
	 */
	public function setCurlInfo($info){
		$this->_curlInfo = $info;
	}

	/**
	 * --------------------------------
	 * 获取curl的返回值
	 * --------------------------------
	 */
	public function getCurlInfo(){
		return $this->_curlInfo;
	}

    /**
     * @return null
     */
    public function getCurlError()
    {
        return $this->_curlError;
    }

    /**
     * 配置sslcert认证文件路径
     * @param   string  $path
     * @return  void
     */
    public function setSSLcertFile($path){
        $this->_sslcert = $path;
    }

    /**
     * 配置sslkey认证文件路径
     * @param   string  $path
     * @return  void
     */
    public function setSSLkeyFile($path){
        $this->_sslkey = $path;
    }

    public function setCAInfo($path) {
        $this->_cainfo = $path;
    }

	/**
	 * ----------------------------------
	 * 执行curl提交动作
	 * ----------------------------------
	 * @param	string		$url	提交的目标url
	 * @param	string		$posts	post提交方式时的参数, 留空默认发送get请求
	 * @param	string		$file_name	如果是需要下载文件，提供文件名
	 */
	public function run($url, $posts = null,$file_name=''){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        if(!empty($file_name)){
            $file = fopen($file_name,'w+');
            curl_setopt($ch,CURLOPT_FILE,$file);
        }else{
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }
        curl_setopt($ch,CURLOPT_TIMEOUT,$this->_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->_connection_timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_headers['uagent']);
		//curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_HEADER, $this->_isPrintHeader);
        if (isset($this->_sslcert)) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $this->_sslcert);
        }
        if (isset($this->_sslkey)) {
            curl_setopt($ch, CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $this->_sslkey);
        }
        if (isset($this->_cainfo))
            curl_setopt($ch, CURLOPT_CAINFO, $this->_cainfo);
		if (isset($this->_headers['cookie']))
			curl_setopt($ch, CURLOPT_COOKIE, $this->_headers['cookie']);
		if (isset($this->_headers['referer']))
			curl_setopt($ch, CURLOPT_REFERER, $this->_headers['referer']);
		if (!empty($this->_httpHeaders))
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_httpHeaders);
        if (!empty($posts))
			curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
		$content = curl_exec($ch);
		$this->setCurlInfo(curl_getinfo($ch));
        if (!$content) {
            $this->_curlError = curl_errno($ch);
        }
		curl_close($ch);
		$this->_httpHeaders = null;
        if(!empty($file_name)){
            fclose($file);
            return true;
        }
		return $content;
	}
}
