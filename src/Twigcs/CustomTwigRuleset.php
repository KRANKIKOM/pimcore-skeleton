<?php

namespace App\Twigcs;

use FriendsOfTwig\Twigcs\RegEngine\RulesetBuilder;
use FriendsOfTwig\Twigcs\RegEngine\RulesetConfigurator;
use FriendsOfTwig\Twigcs\Rule;
use FriendsOfTwig\Twigcs\Validator\Violation;
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;

/**
 * @psalm-suppress UnusedClass
 *
 * The official twigcs ruleset, based on http://twig.sensiolabs.org/doc/coding_standards.html.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class CustomTwigRuleset implements RulesetInterface
{
    public function __construct(private int $twigMajorVersion)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        $configurator = new RulesetConfigurator();
        $configurator->setTwigMajorVersion($this->twigMajorVersion);
        new RulesetBuilder($configurator);

        return [
            new Rule\LowerCaseVariable(Violation::SEVERITY_ERROR),
            new Rule\TrailingSpace(Violation::SEVERITY_ERROR),
            new Rule\UnusedMacro(Violation::SEVERITY_WARNING),
            new Rule\UnusedVariable(Violation::SEVERITY_WARNING),
        ];
    }
}
