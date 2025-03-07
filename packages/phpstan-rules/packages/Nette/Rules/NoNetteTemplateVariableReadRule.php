<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\NodeAnalyzer\NetteTypeAnalyzer;
use Symplify\Astral\ValueObject\AttributeKey;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Nette\Rules\NoNetteTemplateVariableReadRule\NoNetteTemplateVariableReadRuleTest
 * @implements Rule<PropertyFetch>
 */
final class NoNetteTemplateVariableReadRule implements Rule, DocumentedRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Avoid "$this->template->%s" for read access, as it can be defined anywhere. Use local "$%s" variable instead';

    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private NetteTypeAnalyzer $netteTypeAnalyzer
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return PropertyFetch::class;
    }

    /**
     * @param PropertyFetch $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->isThisPropertyFetch($node->var, 'template')) {
            return [];
        }

        if (! $this->netteTypeAnalyzer->isInsideComponentContainer($scope)) {
            return [];
        }

        if ($this->simpleNameResolver->isName($node->name, 'flashes')) {
            return [];
        }

        $assignedToVar = $node->getAttribute(AttributeKey::ASSIGNED_TO);
        if ($assignedToVar instanceof Expr && $this->isPayloadAjaxJuggling($assignedToVar)) {
            return [];
        }

        if ($scope->isInExpressionAssign($node)) {
            return [];
        }

        $templateVariableName = $this->simpleNameResolver->getName($node->name);

        $errorMessage = sprintf(self::ERROR_MESSAGE, $templateVariableName, $templateVariableName);
        return [$errorMessage];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function render()
    {
        if ($this->template->key === 'value') {
            return;
        }
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function render()
    {
        $this->template->key = 'value';
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function isThisPropertyFetch(Expr $expr, string $propertyName): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        if (! $this->simpleNameResolver->isName($expr->var, 'this')) {
            return false;
        }

        return $this->simpleNameResolver->isName($expr->name, $propertyName);
    }

    private function isPayloadAjaxJuggling(Expr $expr): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        return $this->isThisPropertyFetch($expr->var, 'payload');
    }
}
