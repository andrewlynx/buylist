<?php

namespace App\Security;

use App\DTO\User\Settings;
use App\Entity\User;
use App\Repository\UserRepository;
use App\UseCase\User\RegistrationHandler;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class OAuthGoogleAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var RegistrationHandler
     */
    private $registrationHandler;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param ClientRegistry $clientRegistry
     * @param UserRepository $userRepository
     * @param RegistrationHandler $registrationHandler
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        ClientRegistry $clientRegistry,
        UserRepository $userRepository,
        RegistrationHandler $registrationHandler,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->userRepository = $userRepository;
        $this->registrationHandler = $registrationHandler;
        $this->urlGenerator = $urlGenerator;

    }

    /**
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/connect/',
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'google_auth';
    }

    /**
     * @param Request $request
     *
     * @return AccessToken|mixed
     */
    public function getCredentials(Request $request): AccessToken
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return User|null|UserInterface
     *
     * @throws Exception
     */
    public function getUser($credentials, UserProviderInterface $userProvider): User
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        return $this->findOrRegisterUser($googleUser);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return null|Response|void
     */
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?Response
    {
        return new RedirectResponse(
            $this->urlGenerator->generate('index')
        );
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     *
     * @return Response
     *
     * @throws Exception
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();
        $this->updateUserLocaleOnFirstLogin($user, $request);

        $this->registrationHandler->login($user);

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse(
            $this->urlGenerator->generate(
                'index',
                ['_locale' => $user->getLocale() ?? $request->getDefaultLocale()]
            )
        );
    }

    /**
     * @return OAuth2Client
     */
    public function getGoogleClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return true;
    }

    /**
     * @param GoogleUser $googleUser
     *
     * @return User
     *
     * @throws Exception
     */
    private function findOrRegisterUser(GoogleUser $googleUser): User
    {
        /** @var User $user */
        $user = $this->userRepository
            ->findOneBy(['email' => $googleUser->getEmail()]);

        if (!$user) {
            $user = $this->registrationHandler->registerFromGoogle($googleUser);
        }

        return $user;
    }

    /**
     * @param User $user
     * @param Request $request
     *
     * @return User
     *
     * @throws Exception
     */
    private function updateUserLocaleOnFirstLogin(User $user, Request $request): User
    {
        if (!$user->getLocale() || true) {
            $dto = new Settings();
            $dto->locale = $request->getLocale();
            $user = $this->registrationHandler->updateSettings($user, $dto);
        }

        return $user;
    }
}