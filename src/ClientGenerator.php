<?php

namespace Karneaud\Generator;

use SwaggerGen\Generator\AbstractGenerator;
use SwaggerGen\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ClientGenerator extends AbstractGenerator implements GeneratorInterface
{

    use SwaggerGeneratorTrait;

    public function build(array $options)
    {   
        $loader = new FilesystemLoader(__DIR__ . '/Templates/');
        $twig = new Environment($loader,['debug'=>true, 'cache'=>false]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $methods = [];
        
        foreach ($options['paths'] as $path => $info) {
            foreach ($info as $method => $details) {
                $namespace = $this->namespace;
                $methods[$path][$method] = ['name'=> $this->getClassName("{$method}{$path}"), 'has_parameters' => $this->hasParameters($details) ];
            }
        }
        
        $content = $twig->render('ApiTrait.twig', compact('namespace','methods'));
        $this->addClass('ApiTrait',$content);
        
        $content = $twig->render('AbstractClient.twig', compact('namespace','methods'));
        $this->addClass('AbstractClient',$content);
        
        $this->createInterfaceClass();
    }

    private function hasParameters(array $parameters) : bool {
        return isset($parameters['parameters']) || isset($parameters['requestBody']) ;
    }

    private function createInterfaceClass() {
        $templ = <<<PHP
        <?php
        namespace {$this->namespace};

        use {$this->namespace}\Message\Request\RequestInterface;

        interface ClientInterface
        {
            public function getDefaultParameters(): array;
            public function setDefaultParameters(array \$data);
            public function getApiVersion() : int;
            public function setApiVersion(int \$val);
            public function setBaseUrl(string \$value);
        }
        PHP;
        $this->addClass('ClientInterface',$templ);
    } 
}
