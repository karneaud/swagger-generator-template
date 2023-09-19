<?php

namespace Karneaud\Generator;

trait SwaggerGeneratorTrait {

    protected function getClassName(string $string) : string {
        return array_reduce(explode("/",$string), function($carry, $item) {
          if(($item = ucfirst($item)) === 'Api') return $carry;
          
          $item = ucfirst(preg_replace("/api|{|}/", "", $item));
          
          return "$carry$item";
        },'');
    }

    function fixType($type = 'string') {
        switch(true) {
            case in_array($type,['integer','number','enum']): 
                $type = 'int'; break;
            case $type == 'boolean': 
                $type = 'bool'; break;
            case $type == 'double':
                $type = 'float'; break;
            case !in_array($type,['int','bool','array','object']):
                $type = 'string';
            default : break;
        }

        return $type;
    }

    private function extractParameters(array $openapi, string $path, string $method = 'get')
    {
        $parameters = [];
        $details = $openapi['paths'][$path][$method];
        $pathParameters = $details['parameters'] ?? [];
        foreach ($pathParameters as $pathParameter) {
            $parameters[$pathParameter['name']] = $this->fixType($pathParameter['schema']['type']);
        }

        if(!empty($requestBody = ($details['requestBody'] ?? []))){
            if (isset($requestBody['content']['application/json']['schema']['$ref'])) {
                $ref = $requestBody['content']['application/json']['schema']['$ref'];
                $componentSchema = $openapi['components']['schemas'][ substr($ref,strrpos($ref,'/') +1 )];
                $parameters = array_merge($parameters, array_map(fn($prop) => 
                $prop['type'] ?? (isset($prop['$ref']) ? $openapi['components']['schemas'][ substr($prop['$ref'],strrpos($prop['$ref'],'/') +1 )]['type'] : 'string'),$componentSchema['properties']));
                $parameters = array_map([$this,'fixType'],array_filter($parameters));
            } else if(isset($requestBody['content']['application/json']['schema']['properties'])) {
                $requestBodyParameters = $requestBody['content']['application/json']['schema']['properties'];
                foreach ($requestBodyParameters as $key => $requestBodyParameter) {
                    $parameters[$key] = $this->fixType($requestBodyParameter['type']);
                }
            } else if($type = $requestBody['content']['application/json']['schema']['type'] ){
                if($type && $type == 'array') {
                    $parameters = array_merge($parameters,['items'=>'array']);
                }
            }
             
        }
    
        return $parameters;
    }

    private function blankValue($value = '', string $type = 'string') {
        settype($value,$type);
        
        return $value;
    }
    
    protected function notScalarType(string $type): bool{
		return !in_array($type, ['int', 'string', 'bool', 'float', 'array','object']);
	}
}