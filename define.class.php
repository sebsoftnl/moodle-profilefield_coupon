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
 * Text profile field definition.
 *
 * File         define.class.php
 * Encoding     UTF-8
 *
 * @package     profilefield_coupon
 * @copyright   Sebsoft.nl
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_define_coupon
 *
 * @package     profilefield_coupon
 * @copyright   Sebsoft.nl
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_coupon extends profile_define_base {
    /**
     * Add elements for creating/editing a text profile field.
     * @param moodleform $form
     */
    public function define_form_specific($form) {
        // We do not inject _anything_ except a descriptive message.
        $form->addElement('static', '_msg', '', get_string('explain:nosettings', 'profilefield_coupon'));
        $form->setType('_message', PARAM_RAW);
    }

    /**
     * Alter form based on submitted or existing data
     * @param moodleform $mform
     */
    public function define_after_data(&$mform) {
        // Since this is a processing field, hide/disable some contradictive options.
        $remove = ['locked', 'forceunique'];
        $force = [
            'signup' => 1,
            'visible' => PROFILE_VISIBLE_PRIVATE, // Or it won't show up on signup...
        ];
        $mform->freeze($remove);
        foreach ($force as $field => $value) {
            $mform->setConstant($field, $value);
            $mform->freeze($field);
        }

        $mform->insertElementBefore(
            $mform->createElement(
                'static',
                '_remark',
                '',
                get_string('explain:disabledfields', 'profilefield_coupon')
            ),
            'required'
        );
    }
}
