<?php

return [
    'name'              => 'Reizoko',
    'description'       => 'Custom Control Panel Styling for ExpressionEngine',
    'version'           => '1.3.37',
    'author'            => 'Vincent Rijnbeek & Pontus Madsen',
    'author_url'        => 'reizoko.jp',
    'namespace'         => 'PontusMadsen\Reizoko',
    'settings_exist'    => true,
    'settings_callback' => function() {
        return ee('CP/URL', 'addons/settings/reizoko')->compile();
    },
    'models' => []
];