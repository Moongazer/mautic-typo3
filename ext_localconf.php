<?php

declare(strict_types=1);

defined('TYPO3') or die();

(function ($extKey): void {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('marketing_automation') === false) {
        throw new \Exception('Required extension is not loaded: EXT:marketing_automation.');
    }

    $marketingDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bitmotion\MarketingAutomation\Dispatcher\Dispatcher::class);
    $marketingDispatcher->addSubscriber(\Bitmotion\Mautic\Slot\MauticSubscriber::class);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '@import "EXT:mautic/Configuration/PageTS/Mod/Wizards/NewContentElement.tsconfig"'
    );

    //##################
    //      HOOKS      #
    //##################
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess'][$extKey] =
        \Bitmotion\Mautic\Slot\MauticSubscriber::class . '->setPreferredLocale';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc'][$extKey] =
        \Bitmotion\Mautic\Hooks\MauticTrackingHook::class . '->addTrackingCode';

    // Register for hook to show preview of tt_content element of CType="mautic_form" in page module
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['mautic_form'] =
        \Bitmotion\Mautic\Hooks\PageLayoutView\MauticFormPreviewRenderer::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] =
        \Bitmotion\Mautic\Hooks\TCEmainHook::class;

    if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof \Psr\Http\Message\ServerRequestInterface
        && \TYPO3\CMS\Core\Http\ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postTransform']['mautic_tag'] =
            \Bitmotion\Mautic\Hooks\MauticTagHook::class . '->setTags';
    }

    //##################
    //       FORM      #
    //##################
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Bitmotion\Mautic\Form\FormDataProvider\MauticFormDataProvider::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class,
        ],
        'before' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
        ],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530047235] = [
        'nodeName' => 'updateSegmentsControl',
        'priority' => 30,
        'class' => \Bitmotion\Mautic\FormEngine\FieldControl\UpdateSegmentsControl::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551778913] = [
        'nodeName' => 'updateTagsControl',
        'priority' => 30,
        'class' => \Bitmotion\Mautic\FormEngine\FieldControl\UpdateTagsControl::class,
    ];

    //#################
    //   FAL DRIVER   #
    //#################
    $driverRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Driver\DriverRegistry::class);
    $driverRegistry->registerDriverClass(
        \Bitmotion\Mautic\Driver\AssetDriver::class,
        \Bitmotion\Mautic\Driver\AssetDriver::DRIVER_SHORT_NAME,
        \Bitmotion\Mautic\Driver\AssetDriver::DRIVER_NAME,
        'FILE:EXT:mautic/Configuration/FlexForm/AssetDriver.xml'
    );

    //#################
    //   EXTRACTOR    #
    //#################
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::class)->registerExtractionService(\Bitmotion\Mautic\Index\Extractor::class);

    //##################
    //      PLUGIN     #
    //##################
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Mautic',
        'Form',
        [\Bitmotion\Mautic\Controller\FrontendController::class => 'form'],
        [\Bitmotion\Mautic\Controller\FrontendController::class => 'form'],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    //##################
    //      ICONS      #
    //##################
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $icons = [
        'tx_mautic-mautic-icon' => 'EXT:mautic/Resources/Public/Icons/Extension.svg',
    ];

    foreach ($icons as $identifier => $source) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => $source]
        );
    }

    //##################
    //     EXTCONF     #
    //##################
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey] = [
            'transformation' => [
                'form' => [],
                'formField' => [],
            ],
        ];
    }

    //######################
    // FORM TRANSFORMATION #
    //######################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['form']['mautic_finisher_campaign_prototype'] = \Bitmotion\Mautic\Transformation\Form\CampaignFormTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['form']['mautic_finisher_standalone_prototype'] = \Bitmotion\Mautic\Transformation\Form\StandaloneFormTransformation::class;

    //#######################
    // FIELD TRANSFORMATION #
    //#######################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['AdvancedPassword'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Checkbox'] = \Bitmotion\Mautic\Transformation\FormField\CheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['ContentElement'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Date'] = \Bitmotion\Mautic\Transformation\FormField\DatetimeTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['DatePicker'] = \Bitmotion\Mautic\Transformation\FormField\DatetimeTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Email'] = \Bitmotion\Mautic\Transformation\FormField\EmailTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['GridRow'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Fieldset'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['FileUpload'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Hidden'] = \Bitmotion\Mautic\Transformation\FormField\HiddenTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['ImageUpload'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['MultiCheckbox'] = \Bitmotion\Mautic\Transformation\FormField\MultiCheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['MultiSelect'] = \Bitmotion\Mautic\Transformation\FormField\MultiSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Number'] = \Bitmotion\Mautic\Transformation\FormField\NumberTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Page'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Password'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['RadioButton'] = \Bitmotion\Mautic\Transformation\FormField\RadioButtonTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['SingleSelect'] = \Bitmotion\Mautic\Transformation\FormField\SingleSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['StaticText'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['SummaryPage'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Telephone'] = \Bitmotion\Mautic\Transformation\FormField\TelephoneTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Text'] = \Bitmotion\Mautic\Transformation\FormField\TextTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Textarea'] = \Bitmotion\Mautic\Transformation\FormField\TextareaTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['Url'] = \Bitmotion\Mautic\Transformation\FormField\UrlTransformation::class;

    // Register custom field transformation classes
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['transformation']['formField']['CountryList'] = \Bitmotion\Mautic\Transformation\FormField\CountryListTransformation::class;

    //##################
    //     LOGGING     #
    //##################
    // Turn logging off by default
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Bitmotion']['Mautic'] = [
        'writerConfiguration' => [
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                \TYPO3\CMS\Core\Log\Writer\NullWriter::class => [],
            ],
        ],
    ];

    if (\TYPO3\CMS\Core\Core\Environment::getContext()->isDevelopment()) {
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Bitmotion']['Mautic'] = [
            'writerConfiguration' => [
                \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        'logFileInfix' => 'mautic',
                    ],
                ],
            ],
        ];
    }
})('mautic');