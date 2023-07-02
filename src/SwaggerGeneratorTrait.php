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

    private function extractParameters(array $openapi, string $path, string $method = 'get')
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

}