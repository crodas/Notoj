<?php
namespace Notoj\Test;

use Notoj\ReflectionClass,
    Notoj\ReflectionObject,
    Notoj\ReflectionFunction,
    Notoj\ReflectionProperty,
    Notoj\ReflectionMethod;

/** @zzexpect(True) */
function someFunction() {
}

/** @invalid_me*/
// foo
function foo() {
}

/**
 * @test(
 *      ["foobar"]
 * )
 */
class simpletest extends \phpunit_framework_testcase
{
    /** @var_name("foo") */
    protected $bar;

    /** 
     *  @test({
     *      "foo": "bar",
     *      "bar": "foobar",
     *      99: [0, 12, "foobar",
                [99]]
    **  }, "something else")
     */
    function testMultiline() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $args = array(
            array(
                'foo' => 'bar',
                'bar' => 'foobar',
                99  => array(0, 12, "foobar", array(99)),
            ),
            "something else"
        );
        $this->assertEquals(1, $annotations->count());
        $this->assertEquals("test", $annotations[0]->getName());
        $this->assertEquals($args, $annotations[0]->getArgs());
    }


    /** yet another comment {{{
     * This is a bloody comment that nobody is going to read
     *
     * @zzexpect(True) 
     * @bar(False)
     * @bar hola que tal?
     */
    function testClass()
    {
        $reflection = new ReflectionClass($this);
        $annotation = $reflection->getAnnotations();
        $this->assertEquals(1, $annotation->count());
        $this->assertEquals($annotation[0]->getName(), 'test');
        $this->assertEquals($annotation[0]->getArgs(), array(array("foobar")));
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testClass') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, $annotation->count());
                $this->assertEquals($annotation[0]->getName(), 'zzexpect');
                $this->assertEquals(current($annotation[0]->getArgs()), true);
                $this->assertequals($annotation[1]->getName(), 'bar');
                $this->assertEquals(current($annotation[1]->getArgs()), false);
                $this->assertequals($annotation[2]->getName(), 'bar');
                $this->assertEquals(current($annotation[2]->getArgs()), 'hola que tal?');
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property instanceof \Notoj\ReflectionProperty);
            if ($property->getName() === 'bar') {
                $annotation = $property->getAnnotations();
                $this->assertEquals($annotation[0]->getName(), 'var_name');
                $this->assertEquals(current($annotation[0]->getArgs()), 'foo');
            }
        }
    }
    /* }}} */

    public function testFunction() {
        $function   = new ReflectionFunction(__NAMESPACE__ . '\someFunction');
        $annotation = $function->getAnnotations();
        $this->assertEquals(1, $annotation->count());
        $this->assertEquals($annotation[0]->getName(), 'zzexpect');
        $this->assertEquals(current($annotation[0]->getArgs()), true);
        $this->assertEquals($function->getStartLine(), 11);
    }

    /** yet another comment {{{
     * This is a bloody comment that nobody is going to read
     *
     * @zzexpect(True) 
     * @bar(False)
     * @bar hola que tal?
     */
    function testObject() 
    {
        $reflection = new ReflectionObject($this);
        $annotation = $reflection->getAnnotations();
        $this->assertEquals(1, $annotation->count());
        $this->assertEquals($annotation[0]->getName(), 'test');
        $this->assertEquals($annotation[0]->getArgs(), array(array("foobar")));
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testObject') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, $annotation->count());
                $this->assertEquals($annotation[0]->getName(), 'zzexpect');
                $this->assertEquals(current($annotation[0]->getArgs()), true);
                $this->assertequals($annotation[1]->getName(), 'bar');
                $this->assertEquals(current($annotation[1]->getArgs()), false);
                $this->assertequals($annotation[2]->getName(), 'bar');
                $this->assertEquals(current($annotation[2]->getArgs()), 'hola que tal?');
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property instanceof \Notoj\ReflectionProperty);
            if ($property->getName() === 'bar') {
                $annotation = $property->getAnnotations();
                $this->assertEquals($annotation[0]->getName(), 'var_name');
                $this->assertEquals(current($annotation[0]->getArgs()), 'foo');
            }
        }
    }
    /* }}} */


    /** @test( dasda @bar) */
    function testError() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(0, $annotations->count());
    }

    /**
     * @param Request $request object
     * @param string  $name    user name, spaces 'n all
     * @param string  $section which section to render
     */
    function testStrErrorNicely()
    {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(1, $annotations->count());
    }

    function testNoAnnotations() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(0, $annotations->count());
    }

    public static function fileProvider() 
    {
        $args = array(array(__FILE__));
        foreach (glob(__DIR__ . "/../lib/Notoj/*.php") as $file) {
            $args[] = array($file);
        }
        $args[] = array(__FILE__);
        return array_merge($args, $args);
    }

    /**
     *  @dataProvider fileProvider
     */
    public function testNotojFileGetBys($file) 
    {
        $obj = new \Notoj\File($file);
        $methods = array(
            'getClasses' => 'zClass', 
            'getFunctions' => 'zFunction', 
            'getMethods'  => 'zMethod',
            'getProperties' => 'zProperty',
        );
        foreach ($methods as $method  => $class) {
            $class = "Notoj\\Object\\$class";
            foreach ($obj->$method() as $annotations) {
                $this->assertTrue($annotations instanceof $class);
            }
        }
    }


    /**
     *  @dataProvider fileProvider
     */
    public function ztestNotojFile($file) 
    {
        $obj = new \Notoj\File($file);
        foreach ($obj->getAnnotations() as $annotations) {
            $this->AssertEquals(realpath($file), $annotations->GetFile());
            if ($annotations->isMethod()) {
                $this->assertTrue($annotations instanceof \Notoj\tMethod);
                $this->assertTrue($annotations->isPublic());
                if (!preg_match('/Parser.php/', $file)) {
                    $this->assertEquals(
                        $annotations->isStatic(),
                        in_array($annotations->getName(), array('tokenName', 'getInstance')),
                        $annotations->GetName() . ' on ' . $file
                    );
                }

                $refl = new ReflectionMethod($annotations['class'], $annotations['function']);
                $meta = $refl->getAnnotations()->getMetadata();
                $this->assertTrue(is_array($meta['params']));
                foreach ($meta['params'] as $param) {
                    $this->assertEquals('$', $param[0]);
                }
            } else if ($annotations->isProperty()) {
                $this->assertTrue($annotations instanceof \Notoj\tProperty);
                $refl = new ReflectionProperty($annotations['class'], $annotations['property']);
                $this->assertTrue(is_array($annotations['visibility']));
                $this->assertTrue(count($annotations['visibility']) >= 1);
            } else if ($annotations->isFunction()) {
                $this->assertTrue($annotations instanceof \Notoj\tFunction);
                $refl = new ReflectionFunction($annotations['function']);
                $meta = $refl->getAnnotations()->getMetadata();
                $this->assertTrue(is_array($meta['params']));
                foreach ($meta['params'] as $param) {
                    $this->assertEquals('$', $param[0]);
                }
            } elseif ($annotations->isClass()) {
                $this->assertTrue($annotations instanceof \Notoj\tClass);
                $this->assertFalse($annotations->isAbstract());
                $this->assertFalse($annotations->isFinal());
                $refl = new ReflectionClass($annotations['class']);
            }

            $this->assertEquals((array)$refl->getAnnotations(), (array)$annotations['annotations']);
        }
    }

    public function testNotojFileInvalid()
    {
        $foo = new \Notoj\File(__FILE__);
        foreach ($foo->getAnnotations() as $annotations) {
            foreach ($annotations as $annotation) {
                $this->assertNotEquals($annotation->getName(), 'invalid_me');
            }
        }
    }

    /**
     *  @expectedException \RuntimeException
     */
    public function testNotojFileNotFound()
    {
        new \Notoj\File(__DIR__ . "/fixtures/not-found.php");
    }

    /**
     *  @expectedException \RuntimeException
     */
    public function testNotojDirNotFound()
    {
        new \Notoj\File(__DIR__ . "/fixtures/not-found/");
    }

    public function testNotojDirProviders() 
    {
        $foo = new \Notoj\Dir(__DIR__ . '/fixtures');
        $i   = 0;
        foreach ($foo->getProperties('fooba') as $property) {
            $this->assertEquals($property->getName(), 'fooba');
            $this->assertEquals($property->getClass()->getName(), "foobar");
            $i++;
        }
        $this->assertEquals($i, 1);

        $i   = 0;
        foreach ($foo->getMethods('something') as $method) {
            $this->assertEquals($method->getName(), 'something');
            $this->assertEquals($method->getClass()->getName(), "foobar");
            $i++;
        }
        $this->assertEquals(1, $i);

        $i = 0;
        foreach ($foo->getFunctions() as $function) {
            $this->assertTrue($function instanceof \Notoj\Object\zFunction);
            $i++;
        }
        $this->assertTrue($i > 0);



        $i   = 0;
        foreach ($foo->getClasses('FOOBAR') as $class) {
            $this->assertTrue($class instanceof \Notoj\Object\zClass);
            $this->assertTrue(!empty($class['foobar']));
            $this->assertTrue($class['foobar'] instanceof \Notoj\Annotation\Annotation);
            $this->assertEquals(array(), $class->getMethods('xxx'));
            $this->assertEquals(array(), $class->getProperties('xxx'));
            if ($class->getName() == '\foobar') {
                $this->assertEquals(1, count($class->getMethods('something')));
                $this->assertEquals(1, count($class->getMethods()));
                $this->assertEquals(1, count($class->getProperties()));
                $this->assertEquals(1, count($class->getProperties('fooba')));
            }
            $i++;
        }
        $this->assertTrue($i > 0);
    }

    public function testNotojDir() 
    {
        $foo = new \Notoj\Dir(__DIR__ . '/fixtures');

        $this->AssertTrue($foo->has('foobar'));
        $this->AssertFalse($foo->has('foobardasdas'));

        $this->assertEquals($foo->get('fooinvalid'), array());
        $this->assertEquals(
            $foo->has('xxxdasdaysdasadjhasjd,barfoo'),
            false
        );
        $this->assertEquals(
            $foo->has('xxxdasdaysdasadjhasjd,foobar,barfoo'),
            true
        );
        $this->assertEquals(
            $foo->getOne('xxxdasdaysdasadjhasjd,foobar,barfoo')->getName(),
            'foobar'
        );
        foreach ($foo->get('foobar,barfoo') as $annotation) {
            $this->assertTrue($annotation->getObject() instanceof \Notoj\Object\Base);
            $this->assertTrue( file_exists($annotation->getFile()) );
            $this->assertTrue($annotation->isClass());
            $this->assertFalse($annotation->isMethod());
            $this->assertFalse($annotation->isProperty());
        }
    }

    public function testNotojFileNamespaces() 
    {
        $foo = new \Notoj\File(__DIR__ . "/fixtures/namespace.php");
        foreach ($foo as $id => $annotation) {
            if ($id < 2) {
                $expected = explode("\\", $annotation->getObjectName());
                $expected = array_pop($expected);
            } else {
                $expected = $annotation->getObjectName();
            }
            $this->assertEquals($annotation->getName(), $expected);
        }
        
        $this->assertEquals($foo->get('fooobar'), array());
        $this->assertTrue($foo->has('foobar'));
    }

    public function testParentClass()
    {
        $foo = new \Notoj\File(__DIR__ . "/fixtures/extended.php");
        foreach ($foo->getClasses('Foobar') as $class) {
            $parent = $class->GetParent($foo);
            $this->assertNotNull($parent);
            $total = 0;
            foreach ($parent->getAnnotations() as $annotation) {
                $this->assertEquals($annotation->Getname(), 'xx');
                $total++;
            }
            $this->assertEquals(1, $total);
        }
    }

    public function testParentClass2()
    {
        $foo = new \Notoj\File(__DIR__ . "/fixtures/extended.php");
        foreach ($foo->getClasses('Foo') as $class) {
            $parent = $class->GetParent($foo)->getParent($foo)->getParent($foo);
            $this->assertNotNull($parent);
            $this->assertNull($parent->getParent($foo));
            $total = 0;
            foreach ($parent->getAnnotations() as $annotation) {
                $this->assertEquals($annotation->Getname(), 'xx');
                $total++;
            }
            $this->assertEquals(1, $total);
        }
    }

    public function testNestedAnnatations()
    {
        require __DIR__ . '/fixtures/extended.php';
        $class = new ReflectionClass("Extended");
        $annotations = $class->GetAnnotations();
        $this->AssertEquals($annotations[0]->getName(), 'foobar');
        $args = $annotations[0]->GetArgs();
        $this->assertTrue($args[0] instanceof \Notoj\Annotation\Annotation);
        $this->AssertEquals('foobar', $args[0]->getName());
        $this->AssertEquals(array('foobar'), $args[0]->getArgs());
        $this->AssertEquals('foobar', $args[1]);
    }

    public function testFilesystem()
    {
        $fs = new \Notoj\Filesystem(array(
            __DIR__,
            __DIR__ . "/../lib/Notoj/Notoj.php",
            __DIR__ . "/xxx-yyy.php",
        ));
        $this->assertEquals(1, count($fs->getClasses('Notoj')));
        $this->assertEquals(1, count($fs->getMethods('something')));
    }

    public function testMethodVisibility()
    {
        $fs = new \Notoj\Filesystem(__DIR__);
        foreach ($fs->getMethods() as $method) {
            if ($method->isPublic()) {
                $this->assertNotEquals($method->isPublic(), $method->isProtected());
                $this->assertNotEquals($method->isPublic(), $method->isPrivate());
            }
        }
        
    }
}
