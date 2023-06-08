<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Controller;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use Psr\Http\Message\ResponseInterface;
use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Bitmotion\Mautic\Service\MauticAuthorizeService;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;

class BackendController extends ActionController
{
    const FLASH_MESSAGE_QUEUE = 'marketingautomation.mautic.flashMessages';

    public function __construct(private ModuleTemplateFactory $moduleTemplateFactory)
    {
    }

    public function showAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $emConfiguration = new YamlConfiguration();
        /** @var MauticAuthorizeService $authorizeService */
        $authorizeService = GeneralUtility::makeInstance(MauticAuthorizeService::class);

        if ($authorizeService->validateCredentials() === true) {
            if (!$authorizeService->validateAccessToken()) {
                if ($authorizeService->accessTokenToBeRefreshed()) {
                    $authorizeService->refreshAccessToken();
                    $emConfiguration->reloadConfigurations();
                } else {
                    $this->view->assign('authorizeButton', $authorizeService->getAuthorizeButton());
                }
            } else {
                $authorizeService->checkConnection();
            }
        }

        $this->view->assign('configuration', $emConfiguration);
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    /**
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function saveAction(array $configuration)
    {
        $emConfiguration = new YamlConfiguration();

        if (substr($configuration['baseUrl'], -1) === '/') {
            $configuration['baseUrl'] = rtrim($configuration['baseUrl'], '/');
        }

        if (!empty($emConfiguration->getAccessToken()) && !$emConfiguration->isSameCredentials($configuration)) {
            $configuration['accessToken'] = '';
        }

        $emConfiguration->save($configuration);
        $this->redirect('show');
    }
}
