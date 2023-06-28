<?php

namespace Karneaud\Generator;

use SwaggerGen\Generator\AbstractGenerator;
use SwaggerGen\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class RequestGenerator extends AbstractGenerator implements GeneratorInterface
{
    public function build(array $options)
    {
        if (isset($options['paths'])) {
            $loader = new FilesystemLoader(__DIR__ . '/Templates/');
            $twig = new Environment($loader,['debug'=>true, 'cache'=>false]);
            $twig->addExtension(new \Twig\Extension\DebugExtension()); 
            
            foreach ($options['paths'] as $path => $info) {
                foreach ($info as $method => $details) {
                    $method = ucfirst($method);
                    $class_parameters = $details['parameters'] ?? [];
                    $namespace = $this->namespace;
                    $class_name = $this->getClassName("{$method}{$path}");
                    $content = $twig->render('Request.twig', compact('namespace','class_name','class_parameters','method'));
                    $this->addClass($class_name,$content);
                }
            }

            $this->addClass('RequestInterface', $twig->render('RequestInterface.twig', ['namespace' => $this->namespace ] ));
            $this->addClass('AbstractRequest', $twig->render('AbstractRequest.twig', ['namespace' => $this->namespace ] ));

        }
    }

    function getNamespace() : string {
        return "{$this->namespace}\\Message\\Request";
    }

    protected function getDirPath(string $path = null) : string {
        return join(DIRECTORY_SEPARATOR,explode("/",$this->namespace . (is_null($path)? "/$path" : '')));
    }

    protected function getClassName(string $string) : string {
        return array_reduce(explode("/",$string), function($carry, $item) {
          if(($item = ucfirst($item)) === 'Api') return $carry;
          
          $item = ucfirst(preg_replace("/api|{|}/", "", $item));
          
          return "$carry$item";
        },'');
    }
}
