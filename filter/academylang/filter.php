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
            'analyse' => 'analyze',
            'appraisal' => 'comparative market analysis',
            'auction clearance rate' => 'auction success rate',
            'behaviour' => 'behavior',
            'behavioural' => 'behavioral',
            'benchtops' => 'counter tops',
            'bluebook' => 'blue&#8203;book (not used in USA)',
            'body corporate' => 'homeowners association',
            'Campaign Track' => 'Create One',
            'car boot' => 'trunk',
            'categorise' => 'categorize',
            'categorising' => 'categorizing',
            'centre' => 'center',
            'chattels' => 'included items',
            'cheque' => 'check',
            'colour' => 'color',
            'computerise' => 'computerize',
            'computerised' => 'computerized',
            'consultant' => 'agent',
            'conveyancing' => 'escrow',
            'defence' => 'defense',
            'demeanour' => 'demeanor',
            'diarise' => 'calendar',
            'doorknocking' => 'going door to door',
            'emphasise' => 'emphasize',
            'enquiry' => 'inquiry',
            'familiarise' => 'familiarize',
            'favour' => 'favor',
            'favourable' => 'favorable',
            'favourite' => 'favorite',
            'fixtures' => ' fixtures',
            'fortnightly' => 'bi-weekly',
            'fulfil' => 'fulfill',
            'harmonise' => 'harmonize',
            'high school' => 'secondary school',
            'honour' => 'honor',
            'house' => 'single family residence',
            'humanise' => 'humanize',
            'humour' => 'humor',
            'inspection' => 'showing',
            'inspect' => 'view',
            'kindergarten' => 'preschool',
            'jeopardise' => 'jeopardize',
            'primary school' => 'elementary school',
            'land size' => 'lot size',
            'legal services ' => 'remove',
            'letter box dropping' => 'going door to door',
            'lucky dip prize' => 'grab bag',
            'listing authority' => 'listing agreement',
            'memorise' => 'memorize',
            'maximise' => 'maximize',
            'mobile phone' => 'cell phone',
            'neighbour' => 'neighbor',
            'neighbourhood' => 'neighborhood',
            'open home' => 'open house',
            'open homes' => 'open houses',
            'organisation' => 'organization',
            'organisational' => 'organizational',
            'organise' => 'organize',
            'organised' => 'organized',
            'organiser' => 'organizer',
            'organising' => 'organizing',
            'pay rise' => 'pay raise',
            'personalise' => 'personalize',
            'petrol' => 'gas',
            'practise' => 'practice',
            'prioritise' => 'prioritize',
            'private seller' => 'for sale by owner (FSBO)',
            'property inspections' => 'showing',
            'programme' => 'program',
            'rates' => 'property taxes',
            'realise' => 'realize',
            'recognise' => 'recognize',
            'revitalise' => 'revitalize',
            'RPData' => 'Core Logic',
            'rubbish' => 'trash',
            'Sale &amp; Purchase Agreement' => 'Contract of Sale',
            'Sale and Purchase Agreement' => 'Contract of Sale',
            'sales consultant' => 'sales agent',
            'sausage sizzle' => 'bbq',
            'sceptical' => 'skeptical',
            'settlement' => 'closing',
            'settled' => 'closed',
            'sqm' => 'sq ft',
            'solicitor' => 'attorney',
            'specialised' => 'specialized',
            'summarise' => 'summarize',
            'superannuation' => 'social security pension',
            'thermal control' => 'temperature control',
            'tick off the list' => 'check off the list',
            'for rent' => 'to let',
            'unit' => 'condo',
            'utilise' => 'utilize',
            'valuer' => 'appraiser',
            'valuation' => 'appraisal',
            'vendor' => 'seller',
            'VPA' => 'SPA',
            'vendor paid advertising' => 'seller paid advertising',
            'vocalise' => 'vocalize',
            'whilst' => 'while',
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

        foreach ($this->dictionaries as $country => $dictionary) {
            if ($USER->country != $country) {
                return $text;
            }

            // Match single words.
            $text = preg_replace_callback("/\b(\w*)\b/",
                function($match) use ($dictionary) {
                    if (isset($dictionary[$match[0]])) {
                        return ($dictionary[$match[0]]);
                    } else {
                        return($match[0]);
                    }
                },  $text);

            // Match two word sets.
            $text = preg_replace_callback("/\b(\w* \w*)\b/",
                function($match) use ($dictionary) {
                    if (isset($dictionary[$match[0]])) {
                        return ($dictionary[$match[0]]);
                    } else {
                        return($match[0]);
                    }
                },  $text);

            // Match three word sets.
            $text = preg_replace_callback("/\b(\w* \w* \w*)\b/",
                function($match) use ($dictionary) {
                    if (isset($dictionary[$match[0]])) {
                        return ($dictionary[$match[0]]);
                    } else {
                        return($match[0]);
                    }
                },  $text);

            // Match four word sets.
            $text = preg_replace_callback("/\b(\w* \w* \w* \w*)\b/",
                function($match) use ($dictionary) {
                    if (isset($dictionary[$match[0]])) {
                        return ($dictionary[$match[0]]);
                    } else {
                        return($match[0]);
                    }
                },  $text);
        }

        return $text;
    }
}
