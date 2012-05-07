<?php
use Notoj\ReflectionClass,
    Notoj\ReflectionObject,
    Notoj\ReflectionFunction,
    Notoj\ReflectionMethod;

/** @expect(True) */
function someFunction() {
}

/** @test */
class simpletest extends \phpunit_framework_testcase
{

    /** @var_name("foo") */
    protected $bar;

    /** yet another comment {{{
     * This is a bloody comment that nobody is going to read
     *
     * @expect(True) 
     * @bar(False)
     * @bar hola que tal?
     */
    function testClass()
    {
        $reflection = new ReflectionClass($this);
        $annotation = $reflection->getAnnotations();
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]['method'], 'test');
        $this->assertEquals($annotation[0]['args'], NULL);
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testClass') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, count($annotation));
                $this->assertEquals($annotation[0]['method'], 'expect');
                $this->assertEquals($annotation[0]['args'][0], true);
                $this->assertequals($annotation[1]['method'], 'bar');
                $this->assertEquals($annotation[1]['args'][0], false);
                $this->assertequals($annotation[2]['method'], 'bar');
                $this->assertEquals($annotation[2]['args'][0], 'hola que tal?');
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property instanceof \Notoj\ReflectionProperty);
            if ($property->getName() === 'bar') {
                $annotation = $property->getAnnotations();
                $this->assertEquals($annotation[0]['method'], 'var_name');
                $this->assertEquals($annotation[0]['args'][0], 'foo');
            }
        }
    }
    /* }}} */

    public function testFunction() {
        $function   = new ReflectionFunction('someFunction');
        $annotation = $function->getAnnotations();
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]['method'], 'expect');
        $this->assertEquals($annotation[0]['args'][0], true);
    }

    /** yet another comment {{{
     * This is a bloody comment that nobody is going to read
     *
     * @expect(True) 
     * @bar(False)
     * @bar hola que tal?
     */
    function testObject() 
    {
        $reflection = new ReflectionObject($this);
        $annotation = $reflection->getAnnotations();
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]['method'], 'test');
        $this->assertEquals($annotation[0]['args'], NULL);
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testObject') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, count($annotation));
                $this->assertEquals($annotation[0]['method'], 'expect');
                $this->assertEquals($annotation[0]['args'][0], true);
                $this->assertequals($annotation[1]['method'], 'bar');
                $this->assertEquals($annotation[1]['args'][0], false);
                $this->assertequals($annotation[2]['method'], 'bar');
                $this->assertEquals($annotation[2]['args'][0], 'hola que tal?');
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property instanceof \Notoj\ReflectionProperty);
            if ($property->getName() === 'bar') {
                $annotation = $property->getAnnotations();
                $this->assertEquals($annotation[0]['method'], 'var_name');
                $this->assertEquals($annotation[0]['args'][0], 'foo');
            }
        }
    }
    /* }}} */

    /** @test( dasda @bar) */
    function testError() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(0, count($annotations));
    }

    function testNoAnnotations() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(0, count($annotations));
    }

    function testNotojParseFile() {
        $notoj = new \Notoj\Notoj;
        $annotations = $notoj->parseFile(__FILE__);
        $this->assertTrue(is_array($annotations));
        $this->assertTrue(count($annotations) >= 4);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ReflectionClass) {
                $this->AssertEquals($annotation->getName(), __CLASS__);
            } else if ($annotation instanceof ReflectionMethod) {
                $name = $annotation->getName();
                $this->assertTrue(is_callable(array($this, $name)));
            }
        }
    }
}
