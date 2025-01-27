<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\Rules\Punctuation\TrailingCommaMultiLineRule;
use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\TwigCsFixer;

$ruleset = new Ruleset();
$ruleset->addStandard(new TwigCsFixer());
$ruleset->removeRule(TrailingCommaMultiLineRule::class); // "Prettier" does not support this
$ruleset->removeRule(OperatorSpacingRule::class); // "Prettier" does not support this
$config = new Config('CustomKK');
$config->setRuleset($ruleset);

return $config;
