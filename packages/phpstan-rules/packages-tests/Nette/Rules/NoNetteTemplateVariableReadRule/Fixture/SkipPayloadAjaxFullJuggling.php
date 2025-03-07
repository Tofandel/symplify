<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Nette\Rules\NoNetteTemplateVariableReadRule\Fixture;

use Nette\Application\UI\Presenter;

abstract class SkipPayloadAjaxFullJuggling extends Presenter
{
    public function render()
    {
        $this->payload = $this->template;
    }
}
