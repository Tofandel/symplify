<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Nette\Rules\RequireTemplateInNetteControlRule\Fixture;

use Nette\Application\UI\Control;

final class ControlWithoutExplicitTemplate extends Control
{
    public function render()
    {
    }
}
