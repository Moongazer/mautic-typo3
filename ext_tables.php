<?php

declare(strict_types=1);

defined('TYPO3') or die();

(function ($extKey): void {
    // Assign the hooks for pushing newly created and edited forms to Mautic
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDuplicate'][1489959059] =
        \Bitmotion\Mautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDelete'][1489959059] =
        \Bitmotion\Mautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormSave'][1489959059] =
        \Bitmotion\Mautic\Hooks\MauticFormHook::class;

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extKey,
        'Configuration/TypoScript',
        'Mautic'
    );
    // @todo v12: registerModule is deprecated https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/FileStructure/ExtTables.html
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        $extKey,
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
})('mautic');
