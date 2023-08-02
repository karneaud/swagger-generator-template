<?php
namespace Karneaud\Generator;

use SwaggerGen\Generator\AbstractGenerator;
use SwaggerGen\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ModelGenerator extends AbstractGenerator implements GeneratorInterface {
	
	use SwaggerGeneratorTrait;
	
	public const DATE_FORMAT = 'Y-m-d H:i:s';

	private $_is_error = false;

	
	public function build(array $options)
    {
        if (isset($options['components']) && isset($options['components']['schemas'])) {
            $loader = new FilesystemLoader(__DIR__ . '/Templates/');
            $twig = new Environment($loader,['debug'=>true, 'cache'=>false]);
            $twig->addExtension(new \Twig\Extension\DebugExtension()); 
            
            foreach ($options['components']['schemas'] as $model => $info) {
				$model_name = str_replace('Dto','',$model);
				$attributes = $this->extractProperties($info['properties'] ?? []);
				$namespace = $this->namespace;
				$content = $twig->render('Model.twig', compact('namespace','model_name','attributes'));
				$this->addClass("{$model_name}Model",$content);
			}
        }
    }

	protected function extractProperties($properties = null) {
		foreach ($properties as $key => $value) {
			if(!is_null($value) && !array_key_exists('$ref', $value)) {
				$value['type'] = $this->fixType($value['type']);
				$value['format'] = $this->fixType($value['format'] ?? null );
				$value['nullable'] = $value['nullable'] ?? false;
				$value['default'] =  $value['nullable'] ? $this->blankValue(0,$value['format']) : $this->blankValue(0,$value['format']);
			} else {
				$value['type'] = 'object';
				$value['format'] = 'object';
				$value['nullable'] = true;
				$value['default'] = $this->blankValue('null','int');
			}

			$properties[$key] = $value;
		}

		return $properties;
	}

	

    function getNamespace() : string {
        return "{$this->namespace}\\Model";
    }

    protected function getDirPath(string $path = null) : string {
        return join(DIRECTORY_SEPARATOR,explode("/",$this->namespace . (is_null($path)? "/$path" : '')));
    }
}
