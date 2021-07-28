<?php

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HelperExtension extends AbstractExtension
{
    /**
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TokenStorageInterface $token
     * @param TranslatorInterface   $translator
     */
    public function __construct(TokenStorageInterface $token, TranslatorInterface $translator)
    {
        $this->token = $token;
        $this->translator = $translator;
    }

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'helper',
                [$this, 'helper'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string $string
     *
     * @return string|null
     */
    public function helper(string $string): ?string
    {
        /** @var User $user */
        $user = $this->token->getToken()->getUser();

        if ($user->getHelpers()) {
            $string = sprintf(
                '<span class="helper">
                    <span class="iconly-brokenInfo-Circle"></span>
                    <span class="helper-popup">%s</span>
                <span>',
                $this->translator->trans($string)
            );

            return $string;
        }

        return null;
    }
}
