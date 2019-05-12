<?php
  namespace Service;
  class ConfigProvider {

    private $configFileName;
    private $configFile;

    public function __construct($configFileName) {
      $this->configFile = $this->parseConfig($configFileName);
    }

    public function getParameter($paramName){
        return $this->configFile[$paramName];
    }

    private function parseConfig($configFileName) {
      // true argument returns an associative array instead of and jsonobject
      return json_decode(file_get_contents($configFileName), true);
    }


  }

?>
