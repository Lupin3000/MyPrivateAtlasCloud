<?php
class LoadConfig
{
	protected $config_path = '';
	protected $config_array = array();

	/**
	 * Constructor for configuration
	 *
	 * @param string $str_path
	 */
	public function __construct($str_path) {
		$this->config_path = (string) trim($str_path);
		$this->config_array = parse_ini_file($this->config_path, true);
	}

	/**
	 * Return array with configuration settings
	 *
	 * @return array
	 */
	public function getConfigArray() {
		return $this->config_array;
	}

	/**
	 * Return string with settings of domain
	 *
	 * @return string
	 */
	public function getServerUrl() {
		return $this->config_array['server']['URL'];
	}

}
