<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    global $argv;
    $this->setBrowser("*" . $argv[2]);
    $path = dirname(__FILE__)."/..";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    require('config.test.php');
    $this->setBrowserUrl($configure['webserver']);
    $this->webPath = $configure['webpath'];
  }

  public function testSortSite()
  {
    $this->open($this->webPath."/index.php?project=InsightExample&date=2010-07-07");
    $this->click("sort13sort_0");
    try {
        $this->assertEquals("dash13.kitware", $this->getText("//table[@id='project_5_13']/tbody[1]/tr[1]/td[1]/a"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("redwall.kitware", $this->getText("//table[@id='project_5_13']/tbody[1]/tr[2]/td[1]/a"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("thurmite.kitware", $this->getText("//table[@id='project_5_13']/tbody[1]/tr[3]/td[1]/a"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("sort13sort_0");
    try {
        $this->assertEquals("dash13.kitware", $this->getText("//table[@id='project_5_13']/tbody[1]/tr[3]/td[1]/a"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("redwall.kitware", $this->getText("//table[@id='project_5_13']/tbody[1]/tr[2]/td[1]/a"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("thurmite.kitware", $this->getText("//table[@id='project_5_13']/tbody[1]/tr[1]/td[1]/a"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>
