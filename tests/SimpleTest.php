<?php
use Notoj\ReflectionClass,
    Notoj\ReflectionMethod;

/** @test */
class simpletest extends \phpunit_framework_testcase
{
    /** yet another comment {{{
     * This is a bloody comment that nobody is going to read
     *
     * @expect(True) 
     * @bar(False)
     * @bar hola que tal?
     */
    function testNormal() 
    {
        $reflection = new ReflectionClass($this);
        $annotation = $reflection->getAnnotations();
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]['method'], 'test');
        $this->assertEquals($annotation[0]['args'], NULL);
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testNormal') {
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
}
