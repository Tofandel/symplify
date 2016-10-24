<?php

declare(strict_types=1);

/*
 * This file is part of Symplify
 * Copyright (c) 2016 Tomas Votruba (http://tomasvotruba.cz).
 */

namespace Symplify\PHP7_Sculpin\Output;

use Nette\Utils\FileSystem;
use SplFileInfo;
use Symplify\PHP7_Sculpin\Configuration\Configuration;
use Symplify\PHP7_Sculpin\Renderable\File\File;

final class FileSystemWriter
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param SplFileInfo[] $files
     */
    public function copyStaticFiles(array $files)
    {
        foreach ($files as $file) {
            $relativeDestination = substr($file->getPathname(), strlen($this->configuration->getSourceDirectory()));
            $absoluteDestination = $this->configuration->getOutputDirectory() . $relativeDestination;

            FileSystem::copy($file->getRealPath(), $absoluteDestination, true);
        }
    }

    /**
     * @param File[] $files
     */
    public function copyRenderableFiles(array $files)
    {
        foreach ($files as $file) {
            $absoluteDestination = $this->configuration->getOutputDirectory()
                . DIRECTORY_SEPARATOR
                . $file->getOutputPath();

            FileSystem::createDir(dirname($absoluteDestination));
            file_put_contents($absoluteDestination, $file->getContent());
        }
    }
}
