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
        $this->assertEquals(1, count($annotations));
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
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]->getName(), 'test');
        $this->assertEquals($annotation[0]->getArgs(), array(array("foobar")));
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testClass') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, count($annotation));
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
        $this->assertEquals(1, count($annotation));
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
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]->getName(), 'test');
        $this->assertEquals($annotation[0]->getArgs(), array(array("foobar")));
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testObject') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, count($annotation));
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
        $this->assertEquals(0, count($annotations));
    }

    /**
     * @param Request $request object
     * @param string  $name    user name, spaces 'n all
     * @param string  $section which section to render
     */
    function testStrErrorNicely()
    {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(1, count($annotations));
    }

    function testNoAnnotations() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(0, count($annotations));
    }

    public static function fileProvider() 
    {
        $args = array();
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
            'getClasses' => 'tClass', 
            'getFunctions' => 'tFunction', 
            'getMethods'  => 'tMethod',
            'getProperties' => 'tProperty',
        );
        foreach ($methods as $method  => $class) {
            $class = "Notoj\\$class";
            foreach ($obj->$method() as $annotations) {
                $this->assertTrue($annotations instanceof $class);
            }
        }
    }


    /**
     *  @dataProvider fileProvider
     */
    public function testNotojFile($file) 
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
            } else if (isset($annotations['function'])) {
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
            foreach ($annotations['annotations'] as $annotation) {
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
        foreach ($foo->getClasses('FOOBAR') as $class) {
            $this->assertTrue($class instanceof \Notoj\tClass);
            $this->assertTrue(!empty($class['@foobar']));
            $this->assertTrue($class['@foobar'] instanceof \Notoj\Annotation);
            $i++;
        }
        $this->assertTrue($i > 0);
    }

    public function testNotojDir() 
    {
        $foo = new \Notoj\Dir(__DIR__ . '/fixtures');
        $annotations = $foo->getAnnotations();

        $this->AssertTrue($annotations[0]->hasAnnotation('foobar'));
        $this->AssertFalse($annotations[0]->hasAnnotation('foobardasdas'));

        $this->assertEquals($annotations->get('fooinvalid'), array());
        $this->assertEquals(
            $annotations->has('xxxdasdaysdasadjhasjd,barfoo'),
            false
        );
        $this->assertEquals(
            $annotations->has('xxxdasdaysdasadjhasjd,foobar,barfoo'),
            true
        );
        $this->assertEquals(
            $annotations->getOne('xxxdasdaysdasadjhasjd,foobar,barfoo'),
            new \Notoj\Annotation('foobar')
        );
        foreach ($annotations->get('foobar,barfoo') as $annotation) {
            $this->assertTrue($annotation instanceof \Notoj\Annotation\Object);
            $this->assertTrue( file_exists($annotation->getFile()) );
            $this->assertTrue($annotation->isClass());
            $this->assertFalse($annotation->isMethod());
            $this->assertFalse($annotation->isProperty());
        }

        $this->assertEquals(NULL, $annotations->getClassInfo('not-found-class'));
        $classInfo = $annotations->getClassInfo('foobar');
        $this->assertEquals(gettype($classInfo), 'array');
        $this->assertTrue(count($classInfo) > 1);
        $this->assertEquals($classInfo['class']['type'], 'class');
        $this->assertEquals($classInfo['method'][0]['type'], 'method');
    }

    public function testNotojFileNamespaces() 
    {
        $foo = new \Notoj\File(__DIR__ . "/fixtures/namespace.php");
        $annotations = $foo->getAnnotations();
        foreach ($annotations as $id => $annotation) {
            if ($id < 2) {
                $expected = explode("\\", isset($annotation['class']) ? $annotation['class'] : $annotation['function']);
                $expected = array_pop($expected);
            } else if ($annotation->isClass()) {
                $expected = $annotation['class'];
            } else {
                $expected = $annotation['function'];
            }
            foreach ($annotation['annotations'] as $ann) {
                $this->assertEquals($ann->getName(), $expected);
            }
            $this->assertEquals($annotation->get('fooobar'), array());
            if ($annotation->has('foobar')) {
                $this->assertEquals($annotation->get('foobar'), array(new \Notoj\Annotation('foobar', array())));
            }
        }
    }

    public function testParentClass()
    {
        $foo = new \Notoj\File(__DIR__ . "/fixtures/extended.php");
        $annotations = $foo->getAnnotations();
        foreach ($annotations->get('Foobar') as $object) {
            $parent = $object->getParent();
            $this->assertNotNull($parent);
            $here = 0;
            foreach($parent as $ann) {
                $this->assertEquals($ann, new \Notoj\Annotation('XX'));
                $here++;
            }
            $this->assertEquals(1, $here);
        }
    }

    public function testParentClass2()
    {
        $foo = new \Notoj\File(__DIR__ . "/fixtures/extended.php");
        $annotations = $foo->getAnnotations();
        foreach ($annotations->get('Foo') as $object) {
            $parent = $object->getParent()->getParent()->getParent();
            $this->assertEquals(null, $object->getParent()->getParent()->getParent()->getParent());
            $this->assertNotNull($parent);
            $here = 0;
            foreach($parent as $ann) {
                $this->assertEquals($ann, new \Notoj\Annotation('XX'));
                $here++;
            }
            $this->assertEquals(1, $here);
        }
    }

    public function testNestedAnnatations()
    {
        require __DIR__ . '/fixtures/extended.php';
        $class = new ReflectionClass("Extended");
        $annotations = $class->GetAnnotations();
        $this->assertEquals($annotations[0], new \Notoj\Annotation(
            'Foobar',
            array(
                new \Notoj\Annotation('Foobar', array('foobar')),
                'foobar'
            )
        ));
    }
}
