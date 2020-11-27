<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EvaluationsExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('diff_date', [$this, 'diffDate']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('diff_date', [$this, 'diffDate']),
        ];
    }

    public function diffDate(\DateTime $value)
    {
        $diff = (strtotime($value->format('Y-m-d H:i:s'))-time());
        return $diff;
    }
}
