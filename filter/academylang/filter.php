<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    filter
 * @subpackage academylang
 * @copyright  Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_academylang extends moodle_text_filter {

    /** @var string[] Country specific dictionaries for localisation.
     * Use zero-width space (&#8203;) to prevent recursive replacements.
     */
    private $dictionaries = array(
        'US' => array(
            'appraisal' => 'comparative market analysis', 'appraisals' => 'comparative market analyses',
            'auction clearance rate' => 'auction success rate',
            'benchtop' => 'counter top', 'benchtops' => 'counter tops',
            'bluebook' => 'blue&#8203;book (Not Used in the USA)',
            'body corporate' => 'homeowners association',
            'Campaign Track' => 'Create One',
            'car boot' => 'trunk',
            'categorise' => 'categorize', 'categorised' => 'categorized', 'categorising' => 'categorizing',
            'centre' => 'center', 'centred' => 'centered',
            'chattels' => 'included items',
            'cheque' => 'check', 'cheques' => 'checks',
            'computerise' => 'computerize', 'computerised' => 'computerized',
            'consultant' => 'agent', 'consultants' => 'agents',
            'conveyancing' => 'escrow',
            'defence' => 'defense',
            'diarise' => 'calendar',
            'doorknock' => 'going door to door', 'doorknocking' => 'going door to door',
            'emphasise' => 'emphasize', 'emphasises' => 'emphasizes', 'emphasised' => 'emphasized',
            'enquiry' => 'inquiry',
            'familiarise' => 'familiarize', 'familiarises' => 'familiarizes', 'familiarised' => 'familiarized',
            'fixtures' => ' fixtures',
            'fortnightly' => 'bi-weekly',
            'fulfil' => 'fulfill', 'fulfils' => 'fulfills',
            'garage sale' => 'yard sale', 'garage sales' => 'yard sales',
            'high school' => 'secondary school', 'high schools' => 'secondary schools',
            'house' => 'single family residence',
            'inspection' => 'showing', 'inspections' => 'showings',
            'inspect' => 'view',
            'kindergarten' => 'preschool',
            'primary school' => 'elementary school', 'primary schools' => 'elementary schools',
            'land size' => 'lot size',
            'letter box dropping' => 'going door to door', 'letterbox drop' => 'door drop',
            'lucky dip prize' => 'grab bag', 'lucky dip prizes' => 'grab bags',
            'listing authority' => 'listing agreement',
            'memorise' => 'memorize', 'memorises' => 'memorizes', 'memorised' => 'memorized',
            'mobile phone' => 'cell phone', 'mobile phones' => 'cell phones',
            'open home' => 'open house', 'open homes' => 'open houses',
            'organisation' => 'organization', 'organisations' => 'organizations',
            'organisational' => 'organizational',
            'pay rise' => 'pay raise',
            'petrol' => 'gas',
            'practise' => 'practice', 'practises' => 'practices', 'practised' => 'practiced',
            'private seller' => 'for sale by owner (FSBO)', 'private sellers' => 'for sale by owners (FSBOs)',
            'property inspection' => 'showing', 'property inspections' => 'showings',
            'programme' => 'program', 'programmes' => 'programs',
            'rates' => 'property taxes',
            'RPData' => 'Core Logic',
            'rubbish' => 'garbage',
            'Sale &amp; Purchase Agreement' => 'Contract of Sale',
            'Sale and Purchase Agreement' => 'Contract of Sale',
            'sales consultant' => 'sales agent', 'sales consultants' => 'sales agents',
            'sausage sizzle' => 'bbq',
            'sceptical' => 'skeptical',
            'settlement' => 'closing',
            'settled' => 'closed',
            'sqm' => 'sq ft',
            'solicitor' => 'attorney', 'solicitors' => 'attorneys',
            'superannuation' => 'social security pension',
            'thermal control' => 'temperature control',
            'tick off the list' => 'check off the list',
            'for rent' => 'to let',
            'unit' => 'condo', 'units' => 'condos',
            'valuer' => 'appraiser',
            'valuation' => 'appraisal',
            'vendor' => 'seller', 'vendors' => 'sellers',
            'VPA' => 'SPA',
            'vendor paid advertising' => 'seller paid advertising',
            'whilst' => 'while',
        )
    );

    /** @var string[] Country specific word segments for localisation.
     * Use zero-width space (&#8203;) to prevent recursive replacements.
     */
    private $segments = array(
        'US' => array(
            '(\w+)yse' => '${1}yze', '(\w+)yses' => '${1}yzes', '(\w+)ysed' => '${1}yzed',
            '(\w+[dlmntv])ise' => '${1}ize',
            '(\w+[dlmntv])ised' => '${1}ized',
            '(\w+[dlmntv])ises' => '${1}izes',
            '(\w+[dlmntv])iser' => '${1}izer',
            '(\w+[dlmntv])ising' => '${1}izing',
            '(\w+)lisation' => '${1}lization',
            '(\w{3,10})our(\w*)' => '${1}or${2}',
        )
    );

    /**
     * Constructor.
     */
    public function __construct() {
        /* Capitalise the first letter of the words in the dictionary. */
        foreach ($this->dictionaries as $country => $words) {
            foreach ($words as $local => $translation) {
                if (ctype_lower($local[0])) {
                    $this->dictionaries[$country][ucfirst($local)] = ucfirst($translation);
                }
            }
        }

        /* Capitalise the starting letter of all words in the dictionary. */
        foreach ($this->dictionaries as $country => $words) {
            foreach ($words as $local => $translation) {
                if (ctype_lower($local[0])) {
                    $this->dictionaries[$country][ucwords($local)] = ucwords($translation);
                }
            }
        }
    }

    /**
     * Filters the text to localise the content using the dictionary.
     *
     * @param string $text some HTML content.
     * @param array $options options passed to the filters
     * @return string the HTML content after the filtering has been applied.
     */
    public function filter($text, array $options = array()) {
        global $USER;

        if (!isloggedin() or isguestuser() or !is_string($text) or empty($text)) {
            // Non-string data can not be filtered anyway.
            return $text;
        }

        if (!isset($USER->country)) {
            return $text;
        }

        foreach ($this->dictionaries[$USER->country] as $search => $replace) {
            $text = preg_replace("/\b(" . $search . ")\b/", $replace,  $text);
        }

        foreach ($this->segments[$USER->country] as $search => $replace) {
            $text = preg_replace("/\b" . $search . "\b/", $replace,  $text);
        }

        return $text;
    }
}
