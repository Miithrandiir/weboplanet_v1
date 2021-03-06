<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class VerificationsExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('is_numeric', [$this, 'isNumeric']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_numeric', [$this, 'isNumeric']),
        ];
    }

    public function isNumeric($value)
    {
        return is_numeric($value);
    }
}
