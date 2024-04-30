# Swagger PHP Generator Template
A PHP template for genrating an SDK client package for APIs using swagger yaml and [Swagger PHP Generator](https://github.com/karneaud/swagger-php-generator) 
## Usage

```
composer generate -- --model 'ModelGenerator' --yaml-path './swagger.yml' --namespace '\SDK\Client' --dir './src' --generator-class-path 'Classpath\Generator' --request 'RequestGenerator' 
```