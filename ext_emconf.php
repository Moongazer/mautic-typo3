<?php

$EM_CONF['mautic'] = [
    'title' => 'Marketing Automation - Mautic Adapter',
    'description' => 'Add-on TYPO3 extension that enhances the "marketing-automation" TYPO3 extension by connecting it to the Mautic Marketing Automation platform: Determine "Persona" from Mautic segments. Also provides additional services e.g. language synchronisation between Mautic and TYPO3.',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'Florian Wessels, Jurian Jansen',
    'author_company' => 'Leuchtfeuer Digital Marketing',
    'author_email' => 'dev@leuchtfeuer.com',
    'version' => '4.4.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'marketing_automation' => '1.3.0-1.3.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'static_info_tables' => '6.7.0',
            'form' => '12.4.0',
        ],
    ],
];
