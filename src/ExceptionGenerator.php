<?php

namespace Karneaud\Generator;

use SwaggerGen\Generator\AbstractGenerator;
use SwaggerGen\Generator\GeneratorInterface;

class ResponseGenerator extends AbstractGenerator implements GeneratorInterface
{

    private $templ = <<<PHP
    <?php 
    namespace %s\Exception;
    class %s extends \Exception {}
    PHP;

    public function build(array $options)
    {   
         $this->addClass('ValidationException', sprintf($this->templ, $this->namespace, 'ValidationException') );
    }

    public function getNamespace() :string {
        return "{$this->namespace}\\Exception";
    }
}
