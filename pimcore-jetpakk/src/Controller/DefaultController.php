<?php

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\Controller;

use Pimcore\Bundle\AdminBundle\Controller\Admin\LoginController;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends FrontendController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function defaultAction(Request $request): Response
    {
        $template = $this->document->getTemplate();
        if (empty($template)) {
            $template = '@PimcoreJetpakk/default/default.html.twig';
        }

        return $this->render($template);
    }    
}
