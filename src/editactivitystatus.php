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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle/Totara LMS CPD plugin.
 *
 * A modern replacement for the legacy report_cpd module, which has numerous
 * security issues. Supports the existing tables without any fuss.
 *
 * @author Luke Carrier <luke@carrier.im> <luke@tdm.co>
 * @copyright 2014 Luke Carrier, The Development Manager Ltd
 * @license GPL v3
 */

use local_cpd\event\activity_status_created;
use local_cpd\event\activity_status_updated;
use local_cpd\form\activity_status_form;
use local_cpd\model\activity_status;
use local_cpd\url_generator;
use local_cpd\util;

require_once dirname(dirname(__DIR__)) . '/config.php';
require_once "{$CFG->libdir}/adminlib.php";
require_once __DIR__ . '/lib.php';

admin_externalpage_setup('local_cpd_manageactivitystatuses');

$id = optional_param('id', 0, PARAM_INT);

$listurl = url_generator::list_activity_status();

if ($id) {
    $status   = activity_status::get_by_id($id);
    $titlestr = util::string('editingactivitystatusx', $status->name);
} else {
    $status   = new activity_status();
    $titlestr = util::string('loggingactivitystatus');
}

$mform = new activity_status_form();

if ($mform->is_cancelled()) {
    redirect($listurl);
} elseif ($data = $mform->get_data()) {
    $status = activity_status::model_from_form($data);
    $status->id = ($id === 0) ? null : $id;
    $status->save();

    $event = ($id === 0)
            ? activity_status_created::instance($status)
            : activity_status_updated::instance($status);
    $event->trigger();

    redirect($listurl);
} else {
    $mform->set_data($status->model_to_form());

    echo $OUTPUT->header(),
         $OUTPUT->heading($titlestr);
    $mform->display();
    echo $OUTPUT->footer();
}
