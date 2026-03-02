<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('security/login.html.twig', [
            'error'                  => $authenticationUtils->getLastAuthenticationError(),
            'last_username'          => $authenticationUtils->getLastUsername(),
            'page_title'             => 'Sheet Music Manager',
            'csrf_token_intention'   => 'authenticate',
            'username_label'         => 'Email',
            'password_label'         => 'Password',
            'sign_in_label'          => 'Sign in',
            'username_parameter'     => '_username',
            'password_parameter'     => '_password',
            'remember_me_enabled'    => true,
            'remember_me_parameter'  => '_remember_me',
            'remember_me_checked'    => false,
            'remember_me_label'      => 'Remember me',
            'forgot_password_enabled' => false,
        ]);
    }

    #[Route('/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by the firewall logout listener.');
    }
}
