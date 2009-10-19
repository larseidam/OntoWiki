<?php

require_once 'test_base.php';
require_once 'OntoWiki/Application.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';

class OntoWiki_ApplicationTest extends PHPUnit_Framework_TestCase
{
    protected $_application;
    
    public function setUp()
    {
        $this->_application = OntoWiki_Application::getInstance();
    }
    
    public function testGetInstance()
    {
        $newInstance = OntoWiki_Application::getInstance();
        
        $this->assertSame($this->_application, $newInstance);
    }
    
    public function testSetValue()
    {
        $this->_application->foo = 'bar';
        
        $this->assertEquals($this->_application->foo, 'bar');
    }
    
    public function testIssetValue()
    {
        $this->assertEquals(isset($this->_application->anotherFoo), false);
    }
    
    public function testGetValue()
    {
        $this->assertEquals($this->_application->YetAnotherFoo, null);
        
        $this->_application->YetAnotherFoo = 'bar';
        
        $this->assertEquals($this->_application->YetAnotherFoo, 'bar');
    }
    
    public function testSetUrlBase()
    {
        $this->_application->setUrlBase('http://example.com/test', true);
        $this->assertEquals($this->_application->urlBase, 'http://example.com/test');
        $this->assertEquals($this->_application->staticUrlBase, 'http://example.com/test');
        
        define('_OWBOOT', 'index.php');
        $this->_application->setUrlBase('http://example.com/test/', false);
        $this->assertEquals($this->_application->urlBase, 'http://example.com/test/index.php');
        $this->assertEquals($this->_application->staticUrlBase, 'http://example.com/test/');
    }
}

?>
