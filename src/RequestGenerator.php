<?php

namespace Karneaud\Generator;

use SwaggerGen\Generator\AbstractGenerator;
use SwaggerGen\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class RequestGenerator extends AbstractGenerator implements GeneratorInterface
{

    use SwaggerGeneratorTrait;

    public function build(array $options)
    {
        if (isset($options['paths'])) {
            $loader = new FilesystemLoader(__DIR__ . '/Templates/');
            $twig = new Environment($loader,['debug'=>true, 'cache'=>false]);
            $twig->addExtension(new \Twig\Extension\DebugExtension()); 
            
            foreach ($options['paths'] as $path => $info) {
                foreach ($info as $method => $details) {
                    $class_parameters = $this->extractParameters($options,$path,$method);
                    $endpoint = $path;
                    $method = ucfirst($method);
                    $namespace = $this->namespace;
                    $model = $this->getMethodModel($details); 
                    $class_name = $this->getClassName("{$method}{$path}");
                    $content = $twig->render('Request.twig', compact('namespace','class_name','class_parameters','method','endpoint','model'));
                    $this->addClass($class_name,$content);
                }
            }

            $this->addClass('RequestInterface', $twig->render('RequestInterface.twig', ['namespace' => $this->namespace ] ));
            $this->addClass('AbstractRequest', $twig->render('AbstractRequest.twig', ['namespace' => $this->namespace ] ));

        }
    }

    private function getMethodModel($details) {
        $component = $details['requestBody']['content']['application/json']['schema']['$ref'] ?? '';
        $component = basename($component);

        return $component ?? null;
    }

    function getNamespace() : string {
        return "{$this->namespace}\\Message\\Request";
    }

    protected function getDirPath(string $path = null) : string {
        return join(DIRECTORY_SEPARATOR,explode("/",$this->namespace . (is_null($path)? "/$path" : '')));
    }
}
