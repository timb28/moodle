<?php

/**
 * Snipcart shopping cart block
 *
 * @package   block_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot."/enrol/snipcart/classes/snipcartaccounts.php");

class block_snipcart extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_snipcart');
    }
    
    function has_config() {
        return false;
    }

    function applicable_formats() {
        return array('all' => false,
                     'enrol-index' => true,
                     'site-index' => true,
                     'my-index' => true,
                     'course-index-category' => true,
            );
    }
    
    public function hide_header() {
        global $PAGE;
        
        // display the block header if the block is empty and visible to admins
        $pageformat = $PAGE->pagetype;
        return blocks_name_allowed_in_format('snipcart', $pageformat);
    }
    
    public function instance_allow_multiple() {
        return false;
    }
    
    public function get_content() {
        global $OUTPUT, $PAGE, $USER;
        
        // don't display the block if on a page within a course
        $pageformat = $PAGE->pagetype;
        if (!blocks_name_allowed_in_format('snipcart', $pageformat)) {
            $this->instance->visible = false;
            
            $this->content = null;
            return $this->content;
        }
        
        if ($this->content !== null) {
            return $this->content;
        }
        
        $plugin = enrol_get_plugin('snipcart');
        
        $manager = \enrol_snipcart\get_snipcartaccounts_manager();
        $currency = $plugin->get_currency_for_country($USER->country);
        $publicapikey = $manager->get_snipcartaccount_info($currency, 'publicapikey');
        

        $shoppingcart = '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>';
        $shoppingcart.= '<script type="text/javascript" src="https://cdn.snipcart.com/scripts/snipcart.js" id="snipcart"';
        $shoppingcart.= '   data-autopop="false"';
        $shoppingcart.= '   data-api-key="'. $publicapikey .'"></script>';
        $shoppingcart.= '<link type="text/css" href="https://cdn.snipcart.com/themes/base/snipcart.min.css" rel="stylesheet" />';
        $shoppingcart.= '<div class="snipcart-summary">';
        $shoppingcart.= '  <div class="snipcart-items">';
        $shoppingcart.= '    <span class="snipcart-total-items-label">'.get_string('items', 'block_snipcart').'</span> '
                     . '     <span class="snipcart-total-items"></span>'
                     . '   </div>';
        $shoppingcart.= '  <div class="snipcart-price">'
                . '<span class="snipcart-total-price-label">'.get_string('total', 'block_snipcart').' </span>'
                . '<span class="snipcart-total-price"></span></div>';
        $shoppingcart.= '</div>';
        $shoppingcart.= '<div class="snipcart-actions">';
        $shoppingcart.= '  <a href="#" class="snipcart-checkout btn btn-small btn-success">'
                . '<img src="'.$OUTPUT->pix_url('shopping_cart', 'block_snipcart').'" /> '
                . get_string('checkout', 'block_snipcart') . '</a>';
        $shoppingcart.= '</div>';
        $shoppingcart.= '<div class="snipcart-payment-types">';
        $shoppingcart.= '  <span class="snipcart-payment-type"><img src="'.$OUTPUT->pix_url('amex', 'block_snipcart').'" alt="American Express accepted" /></span>';
        $shoppingcart.= '  <span class="snipcart-payment-type"><img src="'.$OUTPUT->pix_url('master_card', 'block_snipcart').'" alt="Master Card accepted" /></span>';
        $shoppingcart.= '  <span class="snipcart-payment-type"><img src="'.$OUTPUT->pix_url('visa', 'block_snipcart').'" alt="Visa accepted" /></span>';
        $shoppingcart.= '</div>';
        
        $this->content         =  new stdClass;
        $this->content->text   = $shoppingcart;

        return $this->content;
    }

}
