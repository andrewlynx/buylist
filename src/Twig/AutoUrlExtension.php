<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AutoUrlExtension extends AbstractExtension
{
    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('auto_url', [$this, 'autoUrl']),
        ];
    }

    /**
     * @param string|null $string
     *
     * @return string|null
     */
    public function autoUrl(?string $string): ?string
    {
        $pattern = "/http[s]?:\/\/[a-zA-Z0-9.\-\/?#=&]+/";
        $replacement = "<a href=\"$0\" target=\"_blank\">$0</a>";
        $string = preg_replace($pattern, $replacement, $string);

        return $string;
    }
}
