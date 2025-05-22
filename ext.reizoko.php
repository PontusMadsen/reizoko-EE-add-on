<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Extension;

class Reizoko_ext extends Extension
{
    protected $addon_name = 'reizoko';
    public $settings = array();
    public $version = '1.3.37';

    public function __construct($settings = '')
    {
        $this->settings = array();
        
        // If settings is a string, unserialize it
        if (is_string($settings) && !empty($settings)) {
            $this->settings = unserialize($settings);
        } elseif (is_array($settings)) {
            $this->settings = $settings;
        }
        
        // Debug settings
        ee()->load->library('logger');
        ee()->logger->developer('Reizoko: Settings in constructor: ' . print_r($this->settings, TRUE));
    }

    /**
     * Settings method required by ExpressionEngine
     */
    public function settings()
    {
        return array();
    }

    public function activate_extension()
    {
        $this->settings = array();
        
        // Insert the css hook
        ee()->db->insert('extensions', array(
            'class'    => __CLASS__,
            'method'   => 'add_cp_css',
            'hook'     => 'cp_css_end',
            'settings' => serialize($this->settings),
            'priority' => 10,
            'version'  => $this->version,
            'enabled'  => 'y'
        ));
        
        // Insert the js hook
        ee()->db->insert('extensions', array(
            'class'    => __CLASS__,
            'method'   => 'add_cp_js',
            'hook'     => 'cp_js_end',
            'settings' => serialize($this->settings),
            'priority' => 10,
            'version'  => $this->version,
            'enabled'  => 'y'
        ));
        
        return TRUE;
    }

    public function update_extension($current = '')
    {
        if ($current == '' || $current == $this->version) {
            return FALSE;
        }
        
        // Update to current version
        ee()->db->where('class', __CLASS__);
        ee()->db->update('extensions', array('version' => $this->version));
        
        return TRUE;
    }

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
        
        return TRUE;
    }

    public function add_cp_js()
    {
        $site_id = ee()->config->item('site_id');
        $js_key = 'cpjs' . $site_id;
        $js_url_key = $js_key . '_url';
        
        ee()->load->library('logger');
        ee()->logger->developer('Reizoko: Adding JS for site ' . $site_id);
        ee()->logger->developer('Reizoko: JS settings: ' . print_r($this->settings, TRUE));
        
        $output = '';
        
        // Add external URL first
        if (isset($this->settings[$js_url_key]) && !empty($this->settings[$js_url_key])) {
            $url = $this->settings[$js_url_key];
            ee()->logger->developer('Reizoko: Adding external JS URL: ' . $url);
            
            // Add script loading code
            $output .= "document.write('<script src=\"" . $url . "\"><\\/script>');\n\n";
        }
        
        // Then add direct code
        $custom_js = isset($this->settings[$js_key]) ? $this->settings[$js_key] : '';
        if (!empty($custom_js)) {
            ee()->logger->developer('Reizoko: Adding direct JS code');
            $output .= $custom_js;
        }
        
        ee()->logger->developer('Reizoko: JS output length: ' . strlen($output));
        return $output;
    }

    public function add_cp_css()
    {
        $site_id = ee()->config->item('site_id');
        $css_key = 'cpcss' . $site_id;
        $css_url_key = $css_key . '_url';
        
        ee()->load->library('logger');
        ee()->logger->developer('Reizoko: Adding CSS for site ' . $site_id);
        ee()->logger->developer('Reizoko: CSS settings: ' . print_r($this->settings, TRUE));
        
        $output = '';
        
        // Put import at the very beginning
        if (isset($this->settings[$css_url_key]) && !empty($this->settings[$css_url_key])) {
            $url = $this->settings[$css_url_key];
            ee()->logger->developer('Reizoko: Adding external CSS URL: ' . $url);
            
            // Add import statement as separate block
            $output .= "@import url('" . $url . "');\n\n";
        }
        
        // Then add custom CSS
        $custom_css = isset($this->settings[$css_key]) ? $this->settings[$css_key] : '';
        if (!empty($custom_css)) {
            ee()->logger->developer('Reizoko: Adding direct CSS code');
            $output .= $custom_css;
        }
        
        ee()->logger->developer('Reizoko: CSS output length: ' . strlen($output));
        return $output;
    }
}