<?php

namespace App\Controller;

use App\Service\RateLimiter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LimitedController extends AbstractController
{
    private RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    #[Route('/limited-resource')]
    public function getLimitedResource(Request $request)
    {
        if ($this->limiter->limit($request)) {
            return new Response('Limited', 429);
        }

        return new Response('OKe', 200);
    }
}
