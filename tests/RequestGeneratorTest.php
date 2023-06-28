<?php

use Karneaud\Generator\RequestGenerator;
use PHPUnit\Framework\TestCase;
use SwaggerGen\Generator\GeneratorInterface;

class RequestGeneratorTest extends TestCase
{

    protected $generator;
    protected $namespace = 'Telecain\\SDK\\Client';
        
    function setUp():void {
        include __DIR__ . '/sample.php';
        $this->generator = new RequestGenerator($this->namespace, $options);
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
        $this->assertArrayHasKey('GetCampaignForId',$generatedFiles,print_r($generatedFiles,true));
        $this->assertEquals($this->generator->getNamespace(),$generatedFiles['GetCampaignForId']['namespace']);
        $this->assertStringContainsString('setId',$generatedFiles['GetCampaignForId']['content']);

    }
}
