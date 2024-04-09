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
 * Coupon profile field.
 *
 * File         field.class.php
 * Encoding     UTF-8
 *
 * @package     profilefield_coupon
 * @copyright   Sebsoft.nl
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_field_coupon
 *
 * @package     profilefield_coupon
 * @copyright   Sebsoft.nl
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_field_coupon extends profile_field_base {

    /**
     * @var bool
     */
    private $issignup;

    /**
     * Constructor method.
     * @param int $fieldid id of the profile from the user_info_field table
     * @param int $userid id of the user for whom we are displaying data
     * @param object $fielddata optional data for the field object plus additional fields 'hasuserdata', 'data' and 'dataformat'
     *    with user data. (If $fielddata->hasuserdata is empty, user data is not available and we should use default data).
     *    If this parameter is passed, constructor will not call load_data() at all.
     */
    public function __construct($fieldid = 0, $userid = 0, $fielddata = null) {
        $this->issignup = ($userid <= 0);
        parent::__construct($fieldid, $userid, $fielddata);
    }

    /**
     * Overwrite the base class to display the data for this field
     */
    public function display_data() {
        // This is a processor only. Do not display anything.
    }

    /**
     * Overwrite base class method, data in this field type is potentially too large to be included in the user object.
     * @return bool
     */
    public function is_user_object_data() {
        // No storage, no user data.
        return false;
    }

    /**
     * Add fields for editing a text profile field.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        if ($this->issignup) {
            // ONLY display on signup.
            $mform->addElement('text', $this->inputname, format_string($this->field->name));
            $mform->setType($this->inputname, \block_coupon\helper::get_code_param_type());
        }
    }

    /**
     * Return the field type and null properties.
     * This will be used for validating the data submitted by a user.
     *
     * @return array the param type and null property
     * @since Moodle 3.2
     */
    public function get_field_properties() {
        return [\block_coupon\helper::get_code_param_type(), NULL_NOT_ALLOWED];
    }

    /**
     * Load user data for this profile field, ready for editing.
     * @param stdClass $user
     */
    public function edit_load_user_data($user) {
        if ($this->data !== null) {
            $this->data = clean_text($this->data, $this->dataformat);
            $user->{$this->inputname} = ['text' => $this->data, 'format' => $this->dataformat];
        }
    }

    /**
     * Saves the data coming from form
     * @param stdClass $usernew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     */
    public function edit_save_data($usernew) {
        if (!isset($usernew->{$this->inputname})) {
            // Field not present in form, probably locked and invisible - skip it.
            return;
        }

        // If it IS set, process it.
        // @codingStandardsIgnoreStart
        try {
            \block_coupon\helper::claim_coupon($usernew->{$this->inputname}, $usernew->id);
            unset($usernew->{$this->inputname});
        } catch (Exception $ex) {
            // TODO: maybe handle the error? //
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Validate the form field from profile page
     *
     * @param stdClass $usernew
     * @return  string  contains error message otherwise null
     */
    public function edit_validate_field($usernew) {
        global $DB;

        $errors = [];
        // Get input value.
        if (isset($usernew->{$this->inputname}) && !empty($usernew->{$this->inputname})) {
            $value = $usernew->{$this->inputname};
            $conditions = [
                'submission_code' => $value,
                'claimed' => 0,
            ];
            $coupon = $DB->get_record('block_coupon', $conditions);
            if (empty($coupon)) {
                $errors[$this->inputname] = get_string('error:invalid_coupon_code', 'block_coupon');
            } else if (!is_null($coupon->userid) &&
                    $coupon->typ != \block_coupon\coupon\generatoroptions::ENROLEXTENSION) {
                $errors[$this->inputname] = get_string('error:coupon_already_used', 'block_coupon');
            }
        }

        return $errors;
    }

}
