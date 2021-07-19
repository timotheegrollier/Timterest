<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Security\Core\Security;

class AppExtension extends AbstractExtension
{

private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pluralize', [$this, 'pluralize']),
        ];
    }

    public function pluralize(int $count,string $singular,?string $plural =null):string
    {   
        // dd($this->security->getUser());
        $plural ??= $singular . 's';
        $str = $count === 1 ? $singular : $plural;
        return "$count $str";
    }
}