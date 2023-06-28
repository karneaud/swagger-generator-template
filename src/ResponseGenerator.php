<?php

namespace Karneaud\Generator;

use SwaggerGen\Generator\AbstractGenerator;
use SwaggerGen\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ResponseGenerator extends AbstractGenerator implements GeneratorInterface
{

    public function build(array $options)
    {   
        $loader = new FilesystemLoader(__DIR__ . '/Templates/');
        $twig = new Environment($loader,['debug'=>true, 'cache'=>false]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());

        $this->addClass('ResponseInterface', $twig->render('ResponseInterface.twig',['namespace' => $this->namespace ]));
        $this->addClass('AbstractResponse', $twig->render('AbstractResponse.twig',['namespace' => $this->namespace ]));
        $templ = <<<PHP
        <?php
        namespace {$this->namespace}\Http;

        interface HttpResponseInterface
        {
            public function getStatusCode() : int;
            public function getReasonPhrase(): string;
        }
        PHP;
        $this->class_files['HttpResponseInterface'] = [
            'namespace' => "{$this->namespace}\\Http",
            'dir' => join(DIRECTORY_SEPARATOR,explode('\\',$this->namespace)) . "/Http",
            "content" => $templ
        ]; 
    }

    public function getNamespace() :string {
        return "{$this->namespace}\\Message\\Response";
    }
}
