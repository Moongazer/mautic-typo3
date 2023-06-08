<?php

defined('TYPO3') || die;

call_user_func(
    function ($extensionKey) {
        // Assign the hooks for pushing newly created and edited forms to Mautic
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDuplicate'][1489959059] =
            \Bitmotion\Mautic\Hooks\MauticFormHook::class;

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDelete'][1489959059] =
            \Bitmotion\Mautic\Hooks\MauticFormHook::class;

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormSave'][1489959059] =
            \Bitmotion\Mautic\Hooks\MauticFormHook::class;

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript',
            'Mautic'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            $extensionKey,
            'tools',
            'api',
            'bottom',
            [
                \Bitmotion\Mautic\Controller\BackendController::class => 'show, save',
            ],
            [
                'access' => 'admin',
                'iconIdentifier' => 'tx_mautic-mautic-icon',
                'labels' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf',
            ]
        );
    },
    'mautic'
);
