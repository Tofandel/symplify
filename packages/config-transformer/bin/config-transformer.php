<?php

declare(strict_types=1);

use Symplify\ConfigTransformer\Kernel\ConfigTransformerKernel;
use Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;

$possibleAutoloadPaths = [
    // dependency
    __DIR__ . '/../../../autoload.php',
    // monorepo
    __DIR__ . '/../../../vendor/autoload.php',
    // after split package
    __DIR__ . '/../vendor/autoload.php',
];

foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (file_exists($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;
        break;
    }
}

$scoperAutoloadFilepath = __DIR__ . '/../vendor/scoper-autoload.php';
if (file_exists($scoperAutoloadFilepath)) {
    require_once $scoperAutoloadFilepath;
}


$codeSnifferAutoload = getcwd() . '/vendor/squizlabs/php_codesniffer/autoload.php';
if (file_exists($codeSnifferAutoload)) {
    require_once $codeSnifferAutoload;
}

$kernelBootAndApplicationRun = new KernelBootAndApplicationRun(ConfigTransformerKernel::class);
$kernelBootAndApplicationRun->run();
