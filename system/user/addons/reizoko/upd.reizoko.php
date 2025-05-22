<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Installer;

class Reizoko_upd extends Installer
{
    public $has_cp_backend = 'y'; 
    public $has_publish_fields = 'n';
    public $version = '1.3.37';

    public function install()
    {
        parent::install();
        return true;
    }

    public function update($current = '')
    {
        parent::update($current);
        return true;
    }

    public function uninstall()
    {
        parent::uninstall();
        return true;
    }
}