<?php
namespace GrumphpDrupalCheck;

use GrumPHP\Extension\ExtensionInterface;

/**
 * Load extensions for GrumPHP (2.x).
 */
class ExtensionLoader implements ExtensionInterface
{
    #[\Override]
    public function imports(): iterable
    {
        yield __DIR__ . '/../Services.yaml';
    }

}
