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
            'analyse' => 'analyze', 'analyses' => 'analyzes', 'analysed' => 'analyzed',
            'appraisal' => 'comparative market analysis', 'appraisals' => 'comparative market analyses',
            'auction clearance rate' => 'auction success rate',
            'behaviour' => 'behavior', 'behavioural' => 'behavioral',
            'benchtop' => 'counter top', 'benchtops' => 'counter tops',
            'bluebook' => 'blue&#8203;book (not used in USA)',
            'body corporate' => 'homeowners association',
            'Campaign Track' => 'Create One',
            'capitalise' => 'capitalize', 'capitalises' => 'capitalize', 'capitalised' => 'capitalized',
            'car boot' => 'trunk',
            'categorise' => 'categorize', 'categorised' => 'categorized', 'categorising' => 'categorizing',
            'centre' => 'center', 'centred' => 'centered',
            'chattels' => 'included items',
            'cheque' => 'check', 'cheques' => 'checks',
            'colour' => 'color', 'colours' => 'colors',
            'computerise' => 'computerize', 'computerised' => 'computerized',
            'consultant' => 'agent', 'consultants' => 'agents',
            'conveyancing' => 'escrow',
            'defence' => 'defense',
            'demeanour' => 'demeanor',
            'diarise' => 'calendar',
            'doorknocking' => 'going door to door',
            'emphasise' => 'emphasize', 'emphasises' => 'emphasizes', 'emphasised' => 'emphasized',
            'enquiry' => 'inquiry',
            'familiarise' => 'familiarize', 'familiarises' => 'familiarizes', 'familiarised' => 'familiarized',
            'favour' => 'favor', 'favours' => 'favors',
            'favourable' => 'favorable',
            'favourite' => 'favorite', 'favourites' => 'favorites',
            'fixtures' => ' fixtures',
            'fortnightly' => 'bi-weekly',
            'fulfil' => 'fulfill', 'fulfils' => 'fulfills',
            'harmonise' => 'harmonize', 'harmonises' => 'harmonizes', 'harmonised' => 'harmonized',
            'high school' => 'secondary school', 'high schools' => 'secondary schools',
            'honour' => 'honor', 'honours' => 'honors', 'honoured' => 'honored',
            'house' => 'single family residence',
            'humanise' => 'humanize', 'humanises' => 'humanizes', 'humanised' => 'humanized',
            'humour' => 'humor',
            'inspection' => 'showing', 'inspections' => 'showings',
            'inspect' => 'view',
            'kindergarten' => 'preschool',
            'jeopardise' => 'jeopardize', 'jeopardises' => 'jeopardizes', 'jeopardised' => 'jeopardized',
            'primary school' => 'elementary school', 'primary schools' => 'elementary schools',
            'land size' => 'lot size',
            'letter box dropping' => 'going door to door',
            'lucky dip prize' => 'grab bag', 'lucky dip prizes' => 'grab bags',
            'listing authority' => 'listing agreement',
            'memorise' => 'memorize', 'memorises' => 'memorizes', 'memorised' => 'memorized',
            'maximise' => 'maximize', 'maximises' => 'maximizes', 'maximised' => 'maximized',
            'mobile phone' => 'cell phone', 'mobile phones' => 'cell phones',
            'neighbour' => 'neighbor', 'neighbours' => 'neighbors',
            'neighbourhood' => 'neighborhood', 'neighbourhoods' => 'neighborhoods',
            'open home' => 'open house', 'open homes' => 'open houses',
            'organisation' => 'organization', 'organisations' => 'organizations',
            'organisational' => 'organizational',
            'organise' => 'organize', 'organises' => 'organizes', 'organised' => 'organized',
            'organiser' => 'organizer', 'organising' => 'organizing',
            'pay rise' => 'pay raise',
            'personalise' => 'personalize', 'personalised' => 'personalized',
            'petrol' => 'gas',
            'practise' => 'practice', 'practises' => 'practices', 'practised' => 'practiced',
            'prioritise' => 'prioritize', 'prioritises' => 'prioritizes', 'prioritised' => 'prioritized',
            'private seller' => 'for sale by owner (FSBO)',
            'property inspection' => 'showing', 'property inspections' => 'showings',
            'programme' => 'program', 'programmes' => 'programs',
            'rates' => 'property taxes',
            'realise' => 'realize', 'realises' => 'realizes', 'realised' => 'realized',
            'recognise' => 'recognize', 'recognises' => 'recognizes', 'recognised' => 'recognized',
            'revitalise' => 'revitalize', 'revitalises' => 'revitalizes', 'revitalised' => 'revitalized',
            'RPData' => 'Core Logic',
            'rubbish' => 'trash',
            'Sale &amp; Purchase Agreement' => 'Contract of Sale',
            'Sale and Purchase Agreement' => 'Contract of Sale',
            'sales consultant' => 'sales agent', 'sales consultants' => 'sales agents',
            'sausage sizzle' => 'bbq',
            'sceptical' => 'skeptical',
            'settlement' => 'closing',
            'settled' => 'closed',
            'sqm' => 'sq ft',
            'solicitor' => 'attorney', 'solicitors' => 'attorneys',
            'specialise' => 'specialize', 'specialises' => 'specializes', 'specialised' => 'specialized',
            'summarise' => 'summarize', 'summarises' => 'summarizes', 'summarised' => 'summarized',
            'superannuation' => 'social security pension',
            'thermal control' => 'temperature control',
            'tick off the list' => 'check off the list',
            'for rent' => 'to let',
            'unit' => 'condo', 'units' => 'condos',
            'utilise' => 'utilize', 'utilises' => 'utilizes', 'utilised' => 'utilized',
            'valuer' => 'appraiser',
            'valuation' => 'appraisal',
            'vendor' => 'seller', 'vendors' => 'sellers',
            'VPA' => 'SPA',
            'vendor paid advertising' => 'seller paid advertising',
            'vocalise' => 'vocalize', 'vocalises' => 'vocalizes', 'vocalised' => 'vocalized',
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
        // error_log('dict: ' . print_r($this->dictionaries, true));
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

            foreach ($dictionary as $search => $replace) {
                $text = preg_replace_callback("/\b(" . $search . ")\b/",
                    function($match) use ($replace) {
                        return($replace);
                    },  $text);
            }
        }
        return $text;
    }
}
