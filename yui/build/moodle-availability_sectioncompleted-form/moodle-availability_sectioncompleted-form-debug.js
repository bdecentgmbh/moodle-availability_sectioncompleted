YUI.add('moodle-availability_sectioncompleted-form', function (Y, NAME) {

/**
 * JavaScript for form editing section completion conditions.
 *
 * @module moodle-availability_sectioncompleted-form
 */
M.availability_sectioncompleted = M.availability_sectioncompleted || {}; // eslint-disable-line camelcase

/**
 * @class M.availability_sectioncompleted.form
 * @extends M.core_availability.plugin
 */
M.availability_sectioncompleted.form = Y.Object(M.core_availability.plugin);

/**
 * Sections available for selection.
 *
 * @property sections
 * @type Array
 */
M.availability_sectioncompleted.form.sections = null;

/**
 * Moodle branch, such as 405 or 501.
 *
 * @property branch
 * @type Number
 */
M.availability_sectioncompleted.form.branch = 0;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} sections Objects with the id, formatted name and number of tracked activities of each section
 * @param {Number} branch Moodle branch
 */
M.availability_sectioncompleted.form.initInner = function(sections, branch) {
    this.sections = sections;
    this.branch = branch;
};

/**
 * Gets a string of this plugin.
 *
 * @method getString
 * @param {String} identifier String identifier
 * @param {Mixed} a Optional string parameter
 * @return {String} The string
 */
M.availability_sectioncompleted.form.getString = function(identifier, a) {
    return M.util.get_string(identifier, 'availability_sectioncompleted', a);
};

/**
 * Gets the number of tracked activities of the selected section.
 *
 * @method getCount
 * @param {Y.Node} node Condition node
 * @return {Number} Number of activities, 0 if no section is selected
 */
M.availability_sectioncompleted.form.getCount = function(node) {
    var sectionid = node.one('select[name=id]').get('value');
    var count = 0;
    Y.each(this.sections, function(section) {
        if (String(section.id) === sectionid) {
            count = section.count;
        }
    });
    return count;
};

/**
 * Fills the minimum select with "all" and every smaller number of the section's activities.
 *
 * A saved number the section no longer reaches is kept as an extra option.
 *
 * @method updateMinimum
 * @param {Y.Node} node Condition node
 * @param {Number} value Number to select, 0 for all
 */
M.availability_sectioncompleted.form.updateMinimum = function(node, value) {
    var count = this.getCount(node);
    var getString = this.getString;
    var option = function(i) {
        return '<option value="' + i + '">' + (i === 1 ? getString('option_min1') : getString('option_min', i)) + '</option>';
    };

    var html = '<option value="0">' + this.getString('option_all') + '</option>';
    for (var i = 1; i < count; i++) {
        html += option(i);
    }
    if (value > 0 && value >= count) {
        html += option(value);
    }
    var select = node.one('select[name=m]');
    select.set('innerHTML', html);
    select.set('value', String(value));
};

M.availability_sectioncompleted.form.getNode = function(json) {
    var selectclass = this.branch >= 500 ? 'form-select' : 'custom-select';
    var title = this.getString('title');

    var html = '<span class="col-form-label pe-3">' + title + '</span>' +
        '<span class="availability-group mb-3">' +
        '<label><span class="accesshide">' + title + ' </span>' +
        '<select class="' + selectclass + '" name="id" title="' + title + '">' +
        '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    Y.each(this.sections, function(section) {
        // The names have already been escaped using format_string.
        html += '<option value="' + section.id + '">' + section.name + '</option>';
    });
    html += '</select></label> ' +
        '<label><span class="accesshide">' + this.getString('label_min') + ' </span>' +
        '<select class="' + selectclass + '" name="m" title="' + this.getString('label_min') + '"></select></label> ' +
        '<label><span class="accesshide">' + this.getString('label_state') + ' </span>' +
        '<select class="' + selectclass + '" name="e" title="' + this.getString('label_state') + '">' +
        '<option value="complete">' + this.getString('option_complete') + '</option>' +
        '<option value="pass">' + this.getString('option_pass') + '</option>' +
        '<option value="fail">' + this.getString('option_fail') + '</option>' +
        '</select></label></span>';
    var node = Y.Node.create('<span class="d-flex flex-wrap align-items-center">' + html + '</span>');

    // Set initial values.
    if (json.id !== undefined && node.one('select[name=id] > option[value="' + json.id + '"]')) {
        node.one('select[name=id]').set('value', String(json.id));
    }
    if (json.e !== undefined && node.one('select[name=e] > option[value="' + json.e + '"]')) {
        node.one('select[name=e]').set('value', json.e);
    }
    this.updateMinimum(node, json.m > 0 ? json.m : 0);

    // Add event handlers (first time only).
    if (!M.availability_sectioncompleted.form.addedEvents) {
        M.availability_sectioncompleted.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function(e) {
            // Another section offers other numbers: keep the chosen one if the section has more activities.
            var form = M.availability_sectioncompleted.form;
            var item = e.currentTarget.ancestor('.availability_sectioncompleted');
            var minimum = parseInt(item.one('select[name=m]').get('value'), 10) || 0;
            form.updateMinimum(item, minimum < form.getCount(item) ? minimum : 0);
        }, '.availability_sectioncompleted select[name=id]');
        root.delegate('change', function() {
            M.core_availability.form.update();
        }, '.availability_sectioncompleted select');
    }

    return node;
};

M.availability_sectioncompleted.form.fillValue = function(value, node) {
    var selected = node.one('select[name=id]').get('value');
    if (selected === 'choose') {
        value.id = 'choose';
    } else {
        value.id = parseInt(selected, 10);
    }
    value.e = node.one('select[name=e]').get('value');
    value.m = parseInt(node.one('select[name=m]').get('value'), 10) || 0;
};

M.availability_sectioncompleted.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    if (value.id === 'choose') {
        errors.push('availability_sectioncompleted:error_selectcmid');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
