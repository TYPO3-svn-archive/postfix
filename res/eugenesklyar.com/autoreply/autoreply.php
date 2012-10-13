<?php

/**
 * Auto - Reply Plugin (for Goldfish)
 * Main processing file
 *
 * @version 0.1a
 *
 * Author(s): Yevgen Sklyar 
 * Date: May 10, 2010
 * License: GPL
 * www.eugenesklyar.com
 */

class autoreply extends rcube_plugin 
{
    public $task = 'settings';
    
    /**
     * Implementation of init() function
     */
    function init() 
    {
         $rcmail = rcmail::get_instance();
         
         $this->add_texts('localization/', true);
         $this->load_config();
      
         $rcmail->output->add_label('autoreply');
      
         $this->register_action('plugin.autoreply', array($this, 'autoreply_init'));
         $this->register_action('plugin.autoreply-save', array($this, 'autoreply_save'));
         $this->include_script('autoreply.js');
    }
    
    /**
     * Implementation of hook_init()
     */
    function autoreply_init() 
    {
        $rcmail = rcmail::get_instance();
       
        $this->add_texts('localization/', true);
        $this->register_handler('plugin.body', array($this, 'autoreply_form'));
        
        $rcmail->output->set_pagetitle($this->gettext('autoreply'));
        $rcmail->output->send('plugin');
    }
  
    /**
     * Implementation of hook_save()
     */
    function autoreply_save() 
    {
        $rcmail = rcmail::get_instance();
        $this->load_config();
        
        $this->add_texts('localization/', true);
        $this->register_handler('plugin.body', array($this, 'autoreply_form'));
        $rcmail->output->set_pagetitle($this->gettext('autoreply'));
        
        // POST variables
        $descname = get_input_value('_descname', RCUBE_INPUT_POST);
        $from = get_input_value('_from', RCUBE_INPUT_POST);
        $to = get_input_value('_to', RCUBE_INPUT_POST);
        $subject = get_input_value('_subject', RCUBE_INPUT_POST);
        $message = get_input_value('_html_message', RCUBE_INPUT_POST);
        $enabled = isset($_POST['_enabled']) ? TRUE : FALSE;
        $prefilled = get_input_value('_prefilled', RCUBE_INPUT_POST);
         
        if (!($res = $this->_save($prefilled, $descname, $from, $to, $subject, $message, $enabled))) {
            $rcmail->output->command('display_message', $this->gettext('successfully_saved') . ' ' . $_SESSION['username'], 'confirmation');
        } 
        else {
            $rcmail->output->command('display_message', $res, 'error');
        }
         
        rcmail_overwrite_action('plugin.autoreply');
        $rcmail->output->send('plugin');
    }
  
    /**
     * Implementation of hook_form()
     */
    public function autoreply_form() 
    {
    
        $rcmail = rcmail::get_instance();
        
        $this->add_texts('localization/', true);
        $this->load_config();
        
        // The file to preload the textboxes, and fetch the information
        include_once($this->home .'/sql/sql.php');
        
        // Check if the preload function exists
        if (!function_exists('autoreply_get')) {
                raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__,
                'message' => "Auto Reply: Broken SQL file"
            ), true, false);
            return $this->gettext('internal_error');
        } 
        else {
            // Pre-fill an array to prefill the fields
            $result = autoreply_get();
            
            if ($result == CONN_ERR) {
                $rcmail->output->command('display_message', $this->gettext('db_conn_err'), 'error');
            } 
            else {
               $prefilled = ($result) ? 1 : 0;
            }
        }
        
        $rcmail->output->set_env('product_name', $rcmail->config->get('product_name'));
        
        // Fields will be positioned inside of a table
        $table = new html_table(array('cols' => 2));
        
        /** === DESCNAME INPUT FIELD === */
        $field_id = 'descname';
        $input_descname = new html_inputfield(array('name' => '_descname', 'id' => $field_id, 'size' => 60));
        $table->add('title', html::label($field_id, Q($this->gettext('descname'))));
        $table->add(null, $input_descname->show($result[0]));
        
        /** === SUBJECT INPUT FIELD === */
        $field_id = 'subject';
        $input_subject = new html_inputfield(array('name' => '_subject', 'id' => $field_id, 'size' => 60));
        $table->add('title', html::label($field_id, Q($this->gettext('subject'))));
        $table->add(null, $input_subject->show($result[5]));
        
        /** === FROM INPUT FIELD === */
        $field_id = 'from';
        $input_from = new html_inputfield(array('name' => '_from', 'id' => $field_id, 'size' => 60));
        $table->add('title', html::label($field_id, Q($this->gettext('from'))));
        $table->add(null, $input_from->show($result[1]));
        
        /** === TO INPUT FIELD === */
        $field_id = 'to';
        $input_to = new html_inputfield(array('name' => '_to', 'id' => $field_id, 'size' => 60));
        $table->add('title', html::label($field_id, Q($this->gettext('to'))));
        $table->add(null, $input_to->show($result[2]));
        
        /** === MESSAGE INPUT FIELD === */
        $field_id = 'html_message';
        $input_message = new html_textarea(array('name' => '_html_message', 'id' => $field_id, 'cols' => 58, 'rows' => 20));
        $table->add('title', html::label($field_id, Q($this->gettext('msg'))));
        $table->add(null, $input_message->show($result[3]));
        
        /** === ENABLED CHECKBOX === */
        $field_id = 'enabled';
        $input_enabled = new html_checkbox(array('name' => '_enabled', 'id' => $field_id, 'value' => ($prefilled == 1) ? $result[4] : 0));
        $table->add('title', html::label($field_id, Q($this->gettext('enabled'))));
        $table->add(null, $input_enabled->show($result[4] ? TRUE : FALSE));
        
        /** === PRE-FILLED HIDDEN FIELD === */
        $hiddenfields = new html_hiddenfield($hidden);
        $field_id = 'prefilled';
        $input_prefilled = new html_hiddenfield(array('name' => '_prefilled', 'id' => $field_id, 'value' => $prefilled));
        $table->add(null, null);
        $table->add(null, $input_prefilled->show());
        
        // Build the table with the divs around it
        $out = html::div(array('class' => 'settingsbox', 'style' => 'margin: 0;'),
        html::div(array('id' => 'prefs-title', 'class' => 'boxtitle'), $this->gettext('maindiv_title') . ' - ' . $rcmail->user->data['username']) .  
        html::div(array('class' => 'boxcontent'), $table->show() . 
            html::p(null, 
                $rcmail->output->button(array(
                'command' => 'plugin.autoreply-save',
                'type' => 'input',
                'class' => 'button mainaction',
                'label' => 'save'
            )))
            )
        );
        
        // Construct the form
        $rcmail->output->add_gui_object('autoreplyform', 'autoreply-form');
        
        $out = $rcmail->output->form_tag(array(
            'id' => 'autoreply-form',
            'name' => 'autoreply-form',
            'method' => 'post',
            'action' => './?_task=settings&_action=plugin.autoreply-save',
        ), $out);
        
        return $out;
    }
    
    private function _save($prefilled, $descname, $from, $until, $subject, $message, $enabled)
    {
        $config = rcmail::get_instance()->config;
        include_once($this->home .'/sql/sql.php');
        
        // Check if the preload function exists
        if (!function_exists('autoreply_get')) {
                raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__,
                'message' => "Auto Reply: Broken SQL file"
            ), true, false);
            return $this->gettext('internal_error');
        } 
        else {
            // Pre-fill an array to prefill the fields
            $result = autoreply_save($prefilled, $descname, $from, $until, $subject, $message, $enabled);
        }
        
        switch ($result) {
            case AUTOREPLY_SUCCESS:
                return;
            case AUTOREPLY_UPD_FAIL;
                return $this->gettext('update_error');
            case AUTOREPLY_INS_FAIL;
                return $this->gettext('insert_error');
        }
    }
}
