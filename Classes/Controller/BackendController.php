<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Controller;

use Bitmotion\Mautic\Domain\Model\Dto\EmConfiguration;
use Bitmotion\Mautic\Service\MauticAuthorizeService;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class BackendController extends ActionController
{
    const FLASH_MESSAGE_QUEUE = 'marketingautomation.mautic.flashMessages';

    /**
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    public function showAction()
    {
        $emConfiguration = new EmConfiguration();
        /** @var MauticAuthorizeService $authorizeService */
        $authorizeService = GeneralUtility::makeInstance(MauticAuthorizeService::class);

        if ($authorizeService->validateCredentials() === true) {
            if (!$authorizeService->validateAccessToken()) {
                if ((int)GeneralUtility::_GP('tx_marketingauthorizemautic_authorize') !== 1
                    && !$authorizeService->accessTokenToBeRefreshed()
                ) {
                    $this->view->assign('authorizeButton', $authorizeService->getAuthorizeButton());
                } else {
                    $authorizeService->authorize();
                }
            } else {
                $authorizeService->checkConnection();
            }
        }

        $this->view->assign('configuration', $emConfiguration);
    }

    public function saveAction(array $configuration)
    {
        $emConfiguration = new EmConfiguration();

        if (substr($configuration['baseUrl'], -1) === '/') {
            $configuration['baseUrl'] = rtrim($configuration['baseUrl'], '/');
        }

        $emConfiguration->save($configuration);
        $this->redirect('show');
    }
}
