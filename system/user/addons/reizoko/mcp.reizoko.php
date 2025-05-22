<?php

class Reizoko_mcp
{
    protected $addon_name = 'reizoko';
    protected $base_url;
    
    public function __construct()
    {
        $this->base_url = ee('CP/URL')->make('addons/settings/'.$this->addon_name);
    }
    
    public function index()
    {
        // Check if we're saving settings
        if (isset($_POST) && !empty($_POST)) {
            return $this->save_settings();
        }
        
        // Create settings form manually without using the shared form
        $settings = $this->get_settings();
        
        // Create basic HTML form rather than using the shared form
        $form = $this->build_settings_form($settings);
        
        // Add element picker scripts
        $this->add_element_picker_scripts();
        
        // Return the form without using the shared form view
        return array(
            'heading' => lang('reizoko_settings'),
            'body' => $form
        );
    }
    
    private function add_element_picker_scripts()
    {
        // Add CSS for the element picker
        ee()->cp->add_to_head('<style>
            body.reizoko-picker-active * {
                cursor: crosshair !important;
            }
            
            .reizoko-highlight {
                outline: 2px dashed rgb(0, 0, 0) !important;
                outline-offset: 2px !important;
                background-color: rgba(0, 0, 0, 0.1) !important;
            }
            
            .reizoko-picker-button {
                display: inline-block;
                margin-right: 10px;
                padding: 10px 20px;
                background-color:rgb(0, 0, 0);
                color: white;
                border-radius: 3px;
                cursor: pointer;
                margin-bottom: 10px;
                border: none;
                font-size: 13px;
            }
            .reizoko-picker-button i {
                padding-left:0;
                margin-left:-3px;
                // margin-right:10px;
                // content: "\e211";
                // padding-right:10px;
                // font-size:24px;
                // font-family: Font Awesome 6 Pro;
            }
            
            .reizoko-picker-button:hover {
                background-color:rgb(66, 66, 66);
            }
            
            .reizoko-picker-button:active {
                background-color:rgb(48, 48, 48);
            }
            
            .reizoko-picker-button.active {
                background-color:rgb(0, 0, 0);
            }
            
            .reizoko-selector-list {
                margin: 10px 0;
                padding: 10px;
                background-color: #f5f5f5;
                border: 1px solid #ddd;
                border-radius: 3px;
                max-height: 150px;
                overflow-y: auto;
                display: none;
            }
            
            .reizoko-selector {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 5px 0;
                padding: 8px;
                background-color: white;
                border: 1px solid #ddd;
                border-radius: 3px;
                cursor: pointer;
                font-family: monospace;
                font-size: 12px;
            }
            
            .reizoko-selector:hover {
                background-color: #eaf3ff;
            }
            
            .reizoko-copy-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 10px 15px;
                background-color: #4CAF50;
                color: white;
                border-radius: 3px;
                z-index: 10000;
                display: none;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            
            .reizoko-copy-icon {
                margin-left: 5px;
                opacity: 0.5;
                font-size: 14px;
            }
            
            /* Make the picker higher z-index than EE elements */
            #reizoko-picker-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                background: transparent;
                display: none;
            }
        </style>');
        
        // Add overlay div to the body
        ee()->cp->add_to_foot('<div id="reizoko-picker-overlay"></div><div class="reizoko-copy-message">Copied to clipboard!</div>');
        
        // Add JavaScript for the element picker - this version uses direct DOM manipulation
        ee()->cp->add_to_foot('<script type="text/javascript">
            $(document).ready(function() {
                console.log("Reizoko element picker initializing...");
                
                // Add the overlay div if it doesn\'t exist
                if ($("#reizoko-picker-overlay").length === 0) {
                    $("body").append(\'<div id="reizoko-picker-overlay"></div>\');
                }
                
                // Add the copy message if it doesn\'t exist
                if ($(".reizoko-copy-message").length === 0) {
                    $("body").append(\'<div class="reizoko-copy-message">Copied to clipboard!</div>\');
                }
                
                // Create a global object for our picker
                window.ReizokoElementPicker = {
                    active: false,
                    overlay: $("#reizoko-picker-overlay"),
                    copyMessage: $(".reizoko-copy-message"),
                    currentTextarea: null,
                    cmInstances: {},
                    selectorLists: {},
                    buttons: {},
                    currentHighlight: null,
                    
                    // Initialize CodeMirror instances
                    initCodeMirror: function() {
                        if (typeof CodeMirror !== "undefined") {
                            $("textarea[data-mode]").each(function() {
                                var mode = $(this).data("mode") || "css";
                                var textareaId = $(this).attr("id");
                                var cm = CodeMirror.fromTextArea(this, {
                                    lineNumbers: true,
                                    mode: mode,
                                    theme: "default",
                                    indentWithTabs: true,
                                    indentUnit: 4
                                });
                                
                                // Store reference to CodeMirror instance
                                ReizokoElementPicker.cmInstances[textareaId] = cm;
                            });
                        }
                    },
                    
                    // Create picker buttons
                    createButtons: function() {
                        $("textarea[data-mode=\'css\']").each(function() {
                            var textareaId = $(this).attr("id");
                            var $pickerButton = $("<button type=\'button\' class=\'reizoko-picker-button\'><i class=\'fas fa-crosshairs\'></i> Pick Element</button>");
                            var $selectorList = $("<div class=\'reizoko-selector-list\' id=\'" + textareaId + "-selectors\'></div>");
                            
                            // Add button and selector list before textarea
                            $(this).closest(".field-pair").find("label").after($pickerButton).after($selectorList);
                            
                            // Store references
                            ReizokoElementPicker.buttons[textareaId] = $pickerButton;
                            ReizokoElementPicker.selectorLists[textareaId] = $selectorList;
                            
                            // Button click handler
                            $pickerButton.on("click", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log("Pick button clicked");
                                ReizokoElementPicker.togglePicker(textareaId);
                            });
                            
                            // Handle selector click
                            $selectorList.on("click", ".reizoko-selector", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                var selector = $(this).find(".selector-text").text();
                                ReizokoElementPicker.copyToClipboard(selector);
                            });
                        });
                    },
                    
                    // Toggle picker state
                    togglePicker: function(textareaId) {
                        console.log("Toggling picker for " + textareaId);
                        this.active = !this.active;
                        this.currentTextarea = textareaId;
                        
                        if (this.active) {
                            // Activate picker
                            $(".reizoko-picker-button").removeClass("active");
                            this.buttons[textareaId].addClass("active");
                            $("body").addClass("reizoko-picker-active");
                            
                            // Show overlay and bind events
                            this.overlay.show();
                            
                            // Show message to user
                            // alert("Element picker is active. Click on any element to get its CSS selector. Press ESC to cancel.");
                            
                            // Setup event handlers on the overlay
                            this.setupOverlayEvents();
                        } else {
                            this.deactivatePicker();
                        }
                    },
                    
                    // Setup events on the overlay
                    setupOverlayEvents: function() {
                        console.log("Setting up overlay events");
                        var self = this;
                        
                        // mousemove event for highlighting
                        this.overlay.on("mousemove", function(e) {
                            // Get element under cursor (ignoring the overlay)
                            self.overlay.hide();
                            var element = document.elementFromPoint(e.clientX, e.clientY);
                            self.overlay.show();
                            
                            // Update highlight
                            if (self.currentHighlight) {
                                $(self.currentHighlight).removeClass("reizoko-highlight");
                            }
                            
                            self.currentHighlight = element;
                            $(self.currentHighlight).addClass("reizoko-highlight");
                        });
                        
                        // click event for selection
                        this.overlay.on("click", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Get element under cursor (ignoring the overlay)
                            self.overlay.hide();
                            var element = document.elementFromPoint(e.clientX, e.clientY);
                            self.overlay.show();
                            
                            console.log("Element clicked:", element);
                            
                            // Generate and display selectors
                            var selectors = self.generateSelectors($(element));
                            self.displaySelectors(selectors);
                            
                            // Deactivate picker
                            self.deactivatePicker();
                        });
                        
                        // keydown event for ESC key
                        $(document).on("keydown.reizokoPicker", function(e) {
                            if (e.keyCode === 27) { // ESC key
                                self.deactivatePicker();
                            }
                        });
                    },
                    
                    // Deactivate the picker
                    deactivatePicker: function() {
                        console.log("Deactivating picker");
                        
                        // Remove highlight from current element
                        if (this.currentHighlight) {
                            $(this.currentHighlight).removeClass("reizoko-highlight");
                            this.currentHighlight = null;
                        }
                        
                        // Remove active class from buttons and body
                        $(".reizoko-picker-button").removeClass("active");
                        $("body").removeClass("reizoko-picker-active");
                        
                        // Hide overlay and unbind events
                        this.overlay.hide();
                        this.overlay.off("mousemove click");
                        $(document).off("keydown.reizokoPicker");
                        
                        // Reset state
                        this.active = false;
                    },
                    
                    // Copy text to clipboard and show notification
                    copyToClipboard: function(text) {
                        console.log("Copying to clipboard:", text);
                        
                        // Create a temporary textarea element
                        var $temp = $("<textarea>");
                        $("body").append($temp);
                        $temp.val(text).select();
                        
                        try {
                            // Execute copy command
                            var success = document.execCommand("copy");
                            if (success) {
                                // Show success message
                                this.showCopyNotification();
                            } else {
                                console.error("Copy command failed");
                                alert("Failed to copy to clipboard. Your browser may not support this feature.");
                            }
                        } catch (err) {
                            console.error("Copy failed:", err);
                            alert("Failed to copy to clipboard: " + err);
                        }
                        
                        // Remove the temporary element
                        $temp.remove();
                        
                        // Hide selector list
                        // this.selectorLists[this.currentTextarea].hide();
                    },
                    
                    // Show copy notification
                    showCopyNotification: function() {
                        var self = this;
                        
                        // Show the notification
                        this.copyMessage.fadeIn(200);
                        
                        // Hide after 2 seconds
                        setTimeout(function() {
                            self.copyMessage.fadeOut(200);
                        }, 2000);
                    },
                    
                    // Generate different CSS selectors for an element
                    generateSelectors: function($el) {
                        var selectors = [];
                        
                        try {
                            // ID selector (most specific)
                            if ($el.attr("id")) {
                                selectors.push("#" + $el.attr("id"));
                            }
                            
                            // Class selector
                            if ($el.attr("class")) {
                                var classes = $el.attr("class").split(/\\s+/);
                                var filteredClasses = classes.filter(function(cls) {
                                    return cls && !cls.match(/reizoko-/); // Filter out our own classes
                                });
                                
                                if (filteredClasses.length > 0) {
                                    selectors.push("." + filteredClasses.join("."));
                                }
                            }
                            
                            // Title attribute selector - Added this specifically
                            if ($el.attr("title")) {
                                selectors.push($el.prop("tagName").toLowerCase() + "[title=\\"" + $el.attr("title") + "\\"]");
                            }
                            
                            // Tag selector with class
                            if ($el.attr("class")) {
                                var classes = $el.attr("class").split(/\\s+/);
                                var filteredClasses = classes.filter(function(cls) {
                                    return cls && !cls.match(/reizoko-/); // Filter out our own classes
                                });
                                
                                if (filteredClasses.length > 0) {
                                    selectors.push($el.prop("tagName").toLowerCase() + "." + filteredClasses[0]);
                                }
                            }
                            
                            // Tag selector
                            selectors.push($el.prop("tagName").toLowerCase());
                            
                            // Tag with attribute selectors for common attributes
                            var commonAttrs = ["type", "name", "value", "href", "src", "alt"]; // "title" removed from here
                            for (var i = 0; i < commonAttrs.length; i++) {
                                var attr = commonAttrs[i];
                                if ($el.attr(attr)) {
                                    selectors.push($el.prop("tagName").toLowerCase() + "[" + attr + "=\\"" + $el.attr(attr) + "\\"]");
                                    break; // Just add one attribute selector
                                }
                            }
                            
                            // Hierarchical selector (parent > child)
                            var $parent = $el.parent();
                            if ($parent.length && $parent[0] !== document) {
                                var parentTag = $parent.prop("tagName").toLowerCase();
                                if (parentTag !== "body" && parentTag !== "html") {
                                    var childTag = $el.prop("tagName").toLowerCase();
                                    selectors.push(parentTag + " > " + childTag);
                                }
                            }
                        } catch (e) {
                            console.error("Error generating selectors:", e);
                            selectors.push($el.prop("tagName").toLowerCase()); // Fallback to tag selector
                        }
                        
                        return selectors;
                    },
                    
                    // Display selectors in the selector list
                    displaySelectors: function(selectors) {
                        var $listContainer = this.selectorLists[this.currentTextarea];
                        $listContainer.empty();
                        
                        console.log("Displaying selectors:", selectors);
                        
                        // Display up to 5 selectors, prioritizing more specific ones
                        var count = Math.min(selectors.length, 5);
                        
                        for (var i = 0; i < count; i++) {
                            var $selector = $(
                                "<div class=\'reizoko-selector\'>" +
                                "<span class=\'selector-text\'>" + selectors[i] + "</span>" +
                                "<span class=\'reizoko-copy-icon\'>📋</span>" +
                                "</div>"
                            );
                            $listContainer.append($selector);
                        }
                        
                        // Show the container
                        $listContainer.show();
                    }
                };
                
                // Initialize the picker
                ReizokoElementPicker.initCodeMirror();
                ReizokoElementPicker.createButtons();
                
                console.log("Reizoko element picker initialized");
            });
        </script>');
    }
    
    private function get_settings()
    {
        $query = ee()->db->select('settings')
            ->from('extensions')
            ->where('class', 'Reizoko_ext')
            ->limit(1)
            ->get();
            
        $settings = array();
        
        if ($query->num_rows() > 0) {
            $row = $query->row();
            if (!empty($row->settings)) {
                $settings = unserialize($row->settings);
            }
        }
        
        return $settings;
    }
    
    private function build_settings_form($settings)
    {
        // Add CSS for the form
        ee()->cp->add_to_head('<style>
            textarea {
                width: 100%;
                min-height: 250px;
                font-family: monospace;
            }
            .field-group {
                margin-bottom: 40px;
            }
            .field-pair {
                margin-bottom: 20px;
            }
            .field-pair label {
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .field-pair .desc {
                margin-bottom: 5px;
                color: #666;
            }
            .section {
                margin-bottom: 30px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 20px;
            }
            .section h2 {
                margin-bottom: 15px;
            }
            .success-message {
                background-color: #dff0d8;
                color: #3c763d;
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid #d6e9c6;
                border-radius: 4px;
            }
            .url-field {
                width: 100%;
                padding: 8px;
                margin-bottom: 0;
                border: 1px solid #ccc;
                border-radius: 3px;
            }
            .url-field-container {
                margin-top: 15px;
            }
            .group-divider {
                height: 1px;
                background-color: #eee;
                margin: 30px 0;
            }
        </style>');
        
        // Start building the form manually
        $form = '<form method="post" action="'.$this->base_url->compile().'">';
        
        // Add CSRF token if it exists
        if (function_exists('CSRF_TOKEN')) {
            $form .= '<input type="hidden" name="CSRF_TOKEN" value="'.CSRF_TOKEN.'" />';
        } elseif (defined('XID_SECURE_HASH')) {
            $form .= '<input type="hidden" name="XID" value="'.XID_SECURE_HASH.'" />';
        }
        
        // Add any success messages
        if (ee()->session->flashdata('message_success')) {
            $form .= '<div class="success-message">';
            $form .= ee()->session->flashdata('message_success');
            $form .= '</div>';
        }
        
        // Site-specific settings section
        $form .= '<div class="section">';
        $form .= '<h2>'.lang('site_specific_settings').'</h2>';
        
        // Get all site IDs and names
        $siteIds = ee()->db->select('site_id, site_label')->from('sites')->get();
        
        if ($siteIds->num_rows() == 0) {
            // No sites found, create a default site section
            
            // CSS Group
            $form .= '<div class="field-group">';
            
            // CSS Direct Code
            $form .= $this->build_field_pair(
                'cpcss1',
                lang('custom_css'),
                lang('css_instructions'),
                'textarea',
                isset($settings['cpcss1']) ? $settings['cpcss1'] : '',
                'data-mode="css"'
            );
            
            // CSS URL
            $form .= $this->build_field_pair(
                'cpcss1_url',
                lang('css_url_instructions'),
                '',
                'text',
                isset($settings['cpcss1_url']) ? $settings['cpcss1_url'] : '',
                'class="url-field" placeholder="https://example.com/styles.css"'
            );
            
            $form .= '</div>'; // End CSS group
            
            // Divider between CSS and JS
            $form .= '<div class="group-divider"></div>';
            
            // JS Group
            $form .= '<div class="field-group">';
            
            // JS Direct Code
            $form .= $this->build_field_pair(
                'cpjs1',
                lang('custom_js'),
                lang('js_instructions'),
                'textarea',
                isset($settings['cpjs1']) ? $settings['cpjs1'] : '',
                'data-mode="javascript"'
            );
            
            // JS URL
            $form .= $this->build_field_pair(
                'cpjs1_url',
                lang('js_url_instructions'),
                '',
                'text',
                isset($settings['cpjs1_url']) ? $settings['cpjs1_url'] : '',
                'class="url-field" placeholder="https://example.com/script.js"'
            );
            
            $form .= '</div>'; // End JS group
            
        } else {
            // Site-specific settings
            foreach($siteIds->result_array() as $row) {
                $siteId = (string)$row['site_id'];
                $siteLabel = isset($row['site_label']) ? $row['site_label'] : 'Site '.$siteId;
                
                // CSS Label - only show site label if multiple sites
                $cssLabel = ($siteIds->num_rows() > 1) ? lang('custom_css').' - '.$siteLabel : lang('custom_css');
                $cssKey = 'cpcss' . $siteId;
                
                // CSS Group
                $form .= '<div class="field-group">';
                
                // CSS Direct Code
                $form .= $this->build_field_pair(
                    $cssKey,
                    $cssLabel,
                    lang('css_instructions'),
                    'textarea',
                    isset($settings[$cssKey]) ? $settings[$cssKey] : '',
                    'data-mode="css"'
                );
                
                // CSS URL
                $form .= $this->build_field_pair(
                    $cssKey.'_url',
                    lang('css_url_instructions'),
                    '',
                    'text',
                    isset($settings[$cssKey.'_url']) ? $settings[$cssKey.'_url'] : '',
                    'class="url-field" placeholder="https://example.com/styles.css"'
                );
                
                $form .= '</div>'; // End CSS group
                
                // Divider between CSS and JS
                $form .= '<div class="group-divider"></div>';
                
                // JS Label - only show site label if multiple sites
                $jsLabel = ($siteIds->num_rows() > 1) ? lang('custom_js').' - '.$siteLabel : lang('custom_js');
                $jsKey = 'cpjs' . $siteId;
                
                // JS Group
                $form .= '<div class="field-group">';
                
                // JS Direct Code
                $form .= $this->build_field_pair(
                    $jsKey,
                    $jsLabel,
                    lang('js_instructions'),
                    'textarea',
                    isset($settings[$jsKey]) ? $settings[$jsKey] : '',
                    'data-mode="javascript"'
                );
                
                // JS URL
                $form .= $this->build_field_pair(
                    $jsKey.'_url',
                    lang('js_url_instructions'),
                    '',
                    'text',
                    isset($settings[$jsKey.'_url']) ? $settings[$jsKey.'_url'] : '',
                    'class="url-field" placeholder="https://example.com/script.js"'
                );
                
                $form .= '</div>'; // End JS group
            }
        }
        $form .= '</div>'; // End site-specific section
        
        // Add submit button
        $form .= '<div class="form-submit">';
        $form .= '<input type="submit" class="btn submit" value="'.lang('save_settings').'">';
        $form .= '</div>';
        
        $form .= '</form>';
        
        return $form;
    }
    
    private function build_field_pair($name, $title, $desc, $type, $value, $attrs = '')
    {
        $html = '<div class="field-pair">';
        $html .= '<label for="'.$name.'">'.$title.'</label>';
        
        if (!empty($desc)) {
            $html .= '<div class="desc">'.$desc.'</div>';
        }
        
        $html .= '<div class="fields">';
        
        if ($type == 'textarea') {
            $html .= '<textarea name="'.$name.'" id="'.$name.'" '.$attrs.'>'.$value.'</textarea>';
        } elseif ($type == 'text') {
            $html .= '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$attrs.'>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    public function save_settings()
    {
        // Debug info
        ee()->load->library('logger');
        ee()->logger->developer('Reizoko: Attempting to save settings');
        ee()->logger->developer(print_r($_POST, TRUE));
        
        $settings = array();
        
        // Get all site IDs
        $siteIds = ee()->db->select('site_id')->from('sites')->get();
        
        // Process site-specific settings
        if ($siteIds->num_rows() == 0) {
            // No sites found, save default site settings
            
            // Save direct CSS
            if (isset($_POST['cpcss1'])) {
                $settings['cpcss1'] = $_POST['cpcss1'];
            }
            
            // Save CSS URL
            if (isset($_POST['cpcss1_url'])) {
                $settings['cpcss1_url'] = $_POST['cpcss1_url'];
            }
            
            // Save direct JS
            if (isset($_POST['cpjs1'])) {
                $settings['cpjs1'] = $_POST['cpjs1'];
            }
            
            // Save JS URL
            if (isset($_POST['cpjs1_url'])) {
                $settings['cpjs1_url'] = $_POST['cpjs1_url'];
            }
        } else {
            // Process site-specific settings
            foreach($siteIds->result_array() as $row) {
                $siteId = (string)$row['site_id'];
                
                // Save CSS settings
                $cssKey = 'cpcss' . $siteId;
                if (isset($_POST[$cssKey])) {
                    $settings[$cssKey] = $_POST[$cssKey];
                }
                
                // Save CSS URL
                $cssUrlKey = $cssKey . '_url';
                if (isset($_POST[$cssUrlKey])) {
                    $settings[$cssUrlKey] = $_POST[$cssUrlKey];
                }
                
                // Save JS settings
                $jsKey = 'cpjs' . $siteId;
                if (isset($_POST[$jsKey])) {
                    $settings[$jsKey] = $_POST[$jsKey];
                }
                
                // Save JS URL
                $jsUrlKey = $jsKey . '_url';
                if (isset($_POST[$jsUrlKey])) {
                    $settings[$jsUrlKey] = $_POST[$jsUrlKey];
                }
            }
        }
        
        // Ensure we have at least one extension record to update
        $query = ee()->db->select('extension_id')
            ->from('extensions')
            ->where('class', 'Reizoko_ext')
            ->get();
            
        if ($query->num_rows() == 0) {
            // No extension record found, create one
            ee()->logger->developer('Reizoko: No extension record found, creating one');
            
            ee()->db->insert('extensions', array(
                'class'    => 'Reizoko_ext',
                'method'   => 'add_cp_css',
                'hook'     => 'cp_css_end',
                'settings' => serialize($settings),
                'priority' => 10,
                'version'  => '1.0',
                'enabled'  => 'y'
            ));
            
            // Also add JS hook
            ee()->db->insert('extensions', array(
                'class'    => 'Reizoko_ext',
                'method'   => 'add_cp_js',
                'hook'     => 'cp_js_end',
                'settings' => serialize($settings),
                'priority' => 10,
                'version'  => '1.0',
                'enabled'  => 'y'
            ));
        } else {
            // Update settings in DB for all matching extensions
            ee()->logger->developer('Reizoko: Updating existing extension records');
            
            ee()->db->where('class', 'Reizoko_ext')
                ->update('extensions', array(
                    'settings' => serialize($settings)
                ));
        }
        
        // Set success message using older API style
        ee()->session->set_flashdata('message_success', lang('settings_saved'));
        
        // Redirect to settings page
        ee()->functions->redirect($this->base_url->compile());
    }
}