<?php

/**
 * Snipcart shopping cart block
 *
 * @package   block_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_snipcart extends block_base {
    public function init() {
        $this->title = get_string('shoppingcart', 'block_snipcart');
    }
    
    function has_config() {
        return false;
    }

    function applicable_formats() {
        return array('all' => false,
                     'course-index-category' => true,
                     'enrol' => true,
            );
    }
    
    public function instance_allow_multiple() {
        return false;
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $shoppingcart = '<div class="snipcart-summary">';
        $shoppingcart.= '  <div>Number of items: <span class="snipcart-total-items"></span></div>';
        $shoppingcart.= '  <div>Total price: <span class="snipcart-total-price"></span></div>';
        $shoppingcart.= '</div>';
        $shoppingcart.= '<div class="checkout">';
        $shoppingcart.= '  <a href="#" class="snipcart-checkout btn btn-success">' . get_string('checkout', 'block_snipcart') . '</a>';
        $shoppingcart.= '</div>';
        
        $this->content         =  new stdClass;
        $this->content->text   = $shoppingcart;

        return $this->content;
    }

}
