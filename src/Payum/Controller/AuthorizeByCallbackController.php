<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Controller;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Request\AuthorizeByCallback;
use Payum\Bundle\PayumBundle\Controller\PayumController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthorizeByCallbackController extends PayumController
{
    public function doAction(Request $request): Response
    {
        if ($this->payum === null) {
            return new Response('', 500);
        }

        try {
            $response = $request->request->all();

            $token = $this->payum->getHttpRequestVerifier()->verify($request);

            $gateway = $this->payum->getGateway($token->getGatewayName());

            $payumRequest = new AuthorizeByCallback($token);
            $payumRequest->setResponse($response);

            $gateway->execute($payumRequest);

            $this->payum->getHttpRequestVerifier()->invalidate($token);
        } catch (NotFoundHttpException $e) {
            return new Response('', 404);
        } catch (\Exception $e) {
            return new Response('', 500);
        }

        return new Response('', 202);
    }
}
