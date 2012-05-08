<?php
use Notoj\Notoj,
    Notoj\ReflectionClass,
    Notoj\ReflectionObject,
    Notoj\ReflectionFunction,
    Notoj\ReflectionMethod;


/** @test */
class CacheTest extends \phpunit_framework_testcase
{
    /** @var_name("foo") */
    protected $bar;
    function testCacheInit() {
        define('CACHE', tempnam("/tmp", "notoj_test"));
        unlink(CACHE);
        $this->assertFalse(is_file(CACHE));
        Notoj::enableCache(CACHE);
        $this->assertTrue(is_file(CACHE));
        $this->assertEquals("<?php\n", file_get_contents(CACHE));
    }

    /** @depends testCacheInit */
    function testCacheContent() {
        $obj = new ReflectionClass(__CLASS__);
        $arr = $obj->getAnnotations();
        Notoj::saveCache();
        $content = file_get_contents(CACHE);
        $this->assertTrue(strpos($content, sha1($obj->getDocComment())) !== FALSE);
    }

}
