<?php

namespace SSPOpenIdConnect;

class Install {
  public static function postInstall() {
    $reflector = new \ReflectionClass('SimpleSAML_Configuration');
    $location = dirname(dirname(dirname($reflector->getFileName()))) . '/modules/openidconnect';
    $file = $location . '/default-enable';
    if (!file_exists($file)) {
      mkdir($location, 0775);
      touch($file);
      mkdir($location . '/www', 0775);
      copy(dirname(__FILE__) . '/../www/resume.php', $location . '/www/resume.php');
      // @todo - Throw an error here if we're unsuccessful?
    }
  }
}
