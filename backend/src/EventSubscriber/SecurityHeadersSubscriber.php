<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $origin = $request->headers->get('Origin');
        $allowedOrigins = $this->getAllowedOrigins();
        
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } elseif (in_array('*', $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '3600');

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        if ($request->getMethod() === 'OPTIONS') {
            $response->setStatusCode(200);
            $response->setContent('');
        }
    }

    private function getAllowedOrigins(): array
    {
        $envOrigins = $_ENV['CORS_ALLOWED_ORIGINS'] ?? null;
        
        if ($envOrigins) {
            return array_map('trim', explode(',', $envOrigins));
        }

        return [
            'http://localhost',
            'http://localhost:80',
            'http://localhost:5173',
            'http://localhost:3000',
            'http://127.0.0.1',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:3000',
            '*'
        ];
    }
}

