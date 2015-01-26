<?php

namespace Notoj\Test;
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
        $this->assertEquals(1, \Notoj\Cache::save());
        $this->assertEquals(0, \Notoj\Cache::save());
        $content = file_get_contents(CACHE);
        $this->assertTrue(strpos($content, sha1($obj->getDocComment())) !== FALSE);
    }


    /** @depends testCacheContent */
    function testLocalCache()
    {
        $tmp    = __DIR__ . '/tmp.cache';
        $target = __DIR__ . '/fixtures'; 
        @unlink($tmp);

        $dir = new \Notoj\Dir($target);
        $dir->setCache($tmp);
        $annotations = $dir->getAnnotations();
        $this->assertFalse($dir->isCached());

        $dir = new \Notoj\Dir($target);
        $annotations = $dir->getAnnotations();
        $this->assertFalse($dir->isCached());


        $dir = new \Notoj\Dir($target);
        $dir->setCache($tmp);
        $annotations = $dir->getAnnotations();
        $this->assertTrue($dir->isCached());

        $dir = new \Notoj\Dir($target);
        $dir->setCache($tmp);
        $annotations = $dir->getAnnotations();
        $this->assertTrue($dir->isCached());
    }



    /** I'm just a annotation without something useful */
    function testNoContent()
    {
        $arr = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals($arr->toCache(), serialize(new \Notoj\Annotations));
        $raw = getReflection(__METHOD__)->getDocComment();
        Notoj::parseDocComment($raw, $isCached);
        $this->assertTrue($isCached);
    }

}
