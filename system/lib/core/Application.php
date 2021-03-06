<?php
/*---------------------------------------------------------------------------
 * SimplePHP - A Simple PHP Framework for PHP 5.3+
 *---------------------------------------------------------------------------
 * Copyright 2013, starlight36 <me@starlight36.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *-------------------------------------------------------------------------*/

namespace lib\core;

/**
 * 系统应用类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:21
 */
class Application {

	/**
	 * 本类单例
	 * @var Application
	 */
	private static $instance = NULL;

	/**
	 * 属性变量
	 * @var array
	 */
	private $attribute = array();
	
	/**
	 * 网站根目录路径
	 * @var string
	 */
	private $webRoot = NULL;
	
	/**
	 * 系统目录根路径
	 * @var string
	 */
	private $systemRoot = NULL;

	/**
	 * 配置对象
	 * @var Config
	 */
	private $config = NULL;

	/**
	 * 日志类对象
	 * @var Log
	 */
	private $log = NULL;

	/**
	 * 视图对象
	 * @var View
	 */
	private $view = NULL;

	/**
	 * 请求对象
	 * @var Request
	 */
	private $request = NULL;

	/**
	 * 应答对象
	 * @var Response
	 */
	private $response = NULL;

	/**
	 * 请求会话对象
	 * @var Session
	 */
	private $session = NULL;
	
	/**
	 * Action对象
	 * @var Action 
	 */
	private $action = NULL;
	
	/**
	 * 过滤器链对象
	 * @var FilterChain 
	 */
	private $filterChain = NULL;

	/**
	 * 私有构造方法
	 */
	private function __construct() {
		$this->webRoot = WEB_ROOT;
		$this->systemRoot = SYS_PATH;
		$this->config = new Config();
		$this->log = new Log($this);
		$this->request = new Request($this);
		$this->response = new Response($this);
		$this->session = new Session($this);
		$this->view = new View($this);
	}

	/**
	 * 取得应用程序单例的方法
	 * @param boolean $forceNew 是否强制创建新的单例
	 * @return Application
	 */
	public static function getInstance($forceNew = FALSE) {
		if($forceNew == FALSE && self::$instance instanceof self) {
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * 设置一个属性
	 * @param string $key    属性值
	 * @param string $val    属性键名
	 */
	public function setAttribute($key, $val) {
		assert(!is_null($key));
		$this->attribute[$key] = $val;
	}

	/**
	 * 读取一个属性
	 * @param string $key 属性名
	 */
	public function getAttribute($key = NULL) {
		if($key === NULL) {
			return $this->attribute;
		}
		if(array_key_exists($key, $this->attribute)) {
			return $this->attribute[$key];
		} else {
			return NULL;
		}
	}
	
	/**
	 * 取得WebRoot路径
	 * @return string
	 */
	public function getWebRoot() {
		return $this->webRoot;
	}
	
	/**
	 * 取得系统根路径
	 * @return string
	 */
	public function getSystemRoot() {
		return $this->systemRoot;
	}

	/**
	 * 取得配置对象
	 * @return Config
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * 取得日志对象
	 * @return Log
	 */
	public function getLog() {
		return $this->log;
	}

	/**
	 * 取得视图对象
	 * @return View
	 */
	public function getView() {
		return $this->view;
	}

	/**
	 * 取得请求对象
	 * @return Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * 取得应答对象
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * 取得会话对象
	 * @return Session
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * 取得Action对象
	 * @return Action
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * 启动应用程序 
	 */
	public function run() {
		// 验证Action是否存在
		if (!class_exists($this->request->getRequestAction()) 
				|| !method_exists($this->request->getRequestAction()
						, $this->request->getRequestMethod())) {
			header('HTTP/1.1 404 Not Found');
			include 'lib/misc/404.phtml';
			if (!defined('IN_PHPUNIT')) die();
		}
		
		// 初始化Action对象
		$reflectionClass = new \ReflectionClass($this->request->getRequestAction());
		$this->action = $reflectionClass->newInstance();
		
		//启动过滤器责任链
		$this->filterChain = new FilterChain($this);
		$this->filterChain->invoke();
	}

}

/* EOF */