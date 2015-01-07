<?php
namespace Notoj\Test;

use notoj\reflectionclass,
    notoj\reflectionmethod;

/** @test */
class ArgumentsTest extends \phpunit_framework_testcase
{
    /** @some_function(foo:bar, bar:xxx) */
    function testNamedArgs2() 
    {
        $reflection  = getReflection(__METHOD__);
        $annotations = $reflection->getAnnotations();
        $args        = $annotations[0]->getArgs();
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'xxx'), $args);
    }

    /** @some_function(foo=bar, bar=xxx) */
    function testNamedArgs1() 
    {
        $reflection  = getReflection(__METHOD__);
        $annotations = $reflection->getAnnotations();
        $args        = $annotations[0]->getArgs();
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'xxx'), $args);
    }

    /** @some_function(foo="bar", bar="xxx") */
    function testNamedArgs() 
    {
        $reflection  = getReflection(__METHOD__);
        $annotations = $reflection->getAnnotations();
        $args        = $annotations[0]->getArgs();
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'xxx'), $args);
    }

    /** @some_function(1.99, foo="bar", bar="xxx") */
    function testMixedArgs() 
    {
        $reflection  = getReflection(__METHOD__);
        $annotations = $reflection->getAnnotations();
        $args        = $annotations[0]->getArgs();
        $this->assertEquals(array(1.99, 'foo' => 'bar', 'bar' => 'xxx'), $args);
    }

    /** @some_function(1, 2, 3) */
    function testArgs()
    {
        $reflection  = getReflection(__METHOD__);
        $annotations = $reflection->getAnnotations();
        $args        = $annotations[0]->getArgs();
        $this->assertEquals(array(1,2,3), $args);
    }

    /** @some_function({foo:"bar", "bar":"foo", "arr":[{z:2}, 1]}, [1,2,3]) */
    function testJson()
    {
        $reflection  = getReflection(__METHOD__);
        $annotations = $reflection->getAnnotations();
        $args        = $annotations[0]->getArgs();
        $expected    = array (
          array (
            'foo' => 'bar',
            'bar' => 'foo',
            'arr' => 
            array (
              array (
                'z' => '2',
              ),
              '1',
            ),
          ),
          array (
            '1',
            '2',
            '3',
          ),
        );
        $this->assertEquals($expected, $args);
    }

    /** @some_function(foo={foo:"bar", "bar":"foo", "arr":[{z:2}, 1]}, bar=[1,2,3]) */
    function testNamedJson() 
    {
        $reflection  = getReflection(__METHOD__);
        $annotations = $reflection->getAnnotations();
        $args        = $annotations[0]->getArgs();
        $expected    = array (
          'foo' => array (
            'foo' => 'bar',
            'bar' => 'foo',
            'arr' => 
            array (
              array (
                'z' => '2',
              ),
              '1',
            ),
          ),
          'bar' => array (
            '1',
            '2',
            '3',
          ),
        );
        $this->assertEquals($expected, $args);
    }

    /**
     *  @expectedException \RuntimeException
     */
    public function testAnnotationArguments()
    {
        $f = new \Notoj\Annotation\Object(array());
        $f['foo'] = 'bar';
    }

    /**
     *  @expectedException \RuntimeException
     */
    public function testAnnotationInvalidMeta()
    {
        $f = new \Notoj\Annotation\Object(array());
        $f->setMetadata(array('foo'));
    }

}
