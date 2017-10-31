<?php

namespace SITBundle\Monolog;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\SecurityContext;

class ErrorProcessor
{
    private $requestStack;
    private $tokenStorage;

    public function __construct(RequestStack $requestStack, TokenStorage $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    public function processRecord(array $record)
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request) {
            $record['extra']['host'] = $request->getHost();
            $record['extra']['url'] = $request->getRequestUri();
            $record['extra']['referer'] = $request->headers->get('referer');
            $record['extra']['IP'] = $request->getClientIp();
            $record['extra']['UserAgent'] = $request->headers->get('User-Agent');

            if($this->tokenStorage->getToken() != null)
                $record['extra']['User'] = $this->tokenStorage->getToken()->getUser();
        }

        return $record;
    }
}