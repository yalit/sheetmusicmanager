<?php

namespace App\Security;

use App\Entity\Security\Member;
use App\Repository\WebDavTokenRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class WebDavAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly WebDavTokenRepository $tokenRepository) {}

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->headers->get('Authorization', ''), 'Basic ');
    }

    public function authenticate(Request $request): Passport
    {
        $encoded = substr($request->headers->get('Authorization', ''), 6);
        $parts   = explode(':', base64_decode($encoded), 2);
        $email      = $parts[0] ?? '';
        $plainToken = $parts[1] ?? '';

        if ($email === '' || $plainToken === '') {
            throw new BadCredentialsException();
        }

        return new Passport(
            new UserBadge($email),
            new CustomCredentials(
                function (string $plainToken, UserInterface $user): bool {
                    assert($user instanceof Member);

                    $token = $this->tokenRepository->findOneBy(['member' => $user]);

                    if ($token === null) {
                        return false;
                    }

                    if ($token->getExpiresAt() <= new \DateTimeImmutable()) {
                        return false;
                    }

                    return password_verify($plainToken, $token->getHashedToken());
                },
                $plainToken
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->start($request, $exception);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new Response(
            'Authentication required',
            Response::HTTP_UNAUTHORIZED,
            ['WWW-Authenticate' => 'Basic realm="Sheet Music Manager DAV"']
        );
    }
}
