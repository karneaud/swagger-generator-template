<?php

namespace Karneaud\Generator;

use SwaggerGen\Generator\AbstractGenerator;
use SwaggerGen\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class RequestGenerator extends AbstractGenerator implements GeneratorInterface
{
    
    private function extractRequestBodyParameters(array $openapi, string $path, string $method = 'get')
    {
        $parameters = [];
        $details = $openapi['paths'][$path][$method];
        $pathParameters = $details['parameters'] ?? [];
        foreach ($pathParameters as $pathParameter) {
            $parameters[$pathParameter['name']] = $pathParameter['schema']['type'];
        }

        if(!empty($requestBody = ($details['requestBody'] ?? []))){
            
            if (isset($requestBody['content']['application/json']['schema']['$ref'])) {
                $ref = $requestBody['content']['application/json']['schema']['$ref'];
                $componentSchema = $openapi['components']['schemas'][ substr($ref,strrpos($ref,'/') +1 )];
                $parameters = array_merge($parameters, array_map(fn($prop) => $prop['type'] ?? null,$componentSchema['properties']));
            } else if(isset($requestBody['content']['application/json']['schema']['properties'])) {
                $requestBodyParameters = $requestBody['content']['application/json']['schema']['properties'];
                foreach ($requestBodyParameters as $key => $requestBodyParameter) {
                    $parameters[$key] = $requestBodyParameter['type'];
                }
            }
        }
    
        return $parameters;
    }


    public function build(array $options)
    {
        if (isset($options['paths'])) {
            $loader = new FilesystemLoader(__DIR__ . '/Templates/');
            $twig = new Environment($loader,['debug'=>true, 'cache'=>false]);
            $twig->addExtension(new \Twig\Extension\DebugExtension()); 
            
            foreach ($options['paths'] as $path => $info) {
                foreach ($info as $method => $details) {
                    $class_parameters = $this->extractRequestBodyParameters($options,$path,$method);
                    $endpoint = $path;
                    $method = ucfirst($method);
                    $namespace = $this->namespace;
                    $class_name = $this->getClassName("{$method}{$path}");
                    $content = $twig->render('Request.twig', compact('namespace','class_name','class_parameters','method','endpoint'));
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
