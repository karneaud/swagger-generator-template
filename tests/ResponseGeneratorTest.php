<?php

use Karneaud\Generator\ResponseGenerator;
use PHPUnit\Framework\TestCase;
use SwaggerGen\Generator\GeneratorInterface;

class ResponseGeneratorTest extends TestCase
{

    protected $generator;
    protected $namespace = 'Telecain\\SDK\\Client';
        
    function setUp():void {
        include __DIR__ . '/sample.php';
        $this->generator = new ResponseGenerator($this->namespace, $options);
    }

    public function testClassInstanceOfGenerator( ){
        $this->assertInstanceOf(GeneratorInterface::class, $this->generator);
    }

    public function testClassFilesCountGreaterThanZero()
    {
        $generatedFiles = $this->generator->getGeneratedClassFiles();
        $this->assertGreaterThanOrEqual(0, count($generatedFiles));
    }

    public function testClassNameAndContent() {
        $generatedFiles = $this->generator->getGeneratedClassFiles();
        $this->assertArrayHasKey('ResponseInterface',$generatedFiles,print_r($generatedFiles,true));
        $this->assertEquals($this->generator->getNamespace(),$generatedFiles['AbstractResponse']['namespace']);
        $this->assertEquals('Telecain/SDK/Client/Http',$generatedFiles['HttpResponseInterface']['dir']);

    }
}
