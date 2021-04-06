var CRM = CRM || {};

function on_any_change(filter_id) {
  jQuery('#' + filter_id).children('h3').css('background-color', '#98FB98');
  jQuery('#' + filter_id).children('.save_changes_button').fadeIn();
}

function input_box_change() {
  var filter_id = jQuery(this).attr('filter_id');

  var allPassedValidation = true;

// TODO only adjust messages for this one

  jQuery('#' + filter_id).find('input').each(function () {
    var thisPassedValidation = true;

    // does it contain a full stop? if so bail
    if (jQuery(this).val().indexOf('.') >= 0 && jQuery(this).hasClass('correction_from') && filter_id != 'equivalent_domain') {
      jQuery(this).siblings('.error_msg').text('Top level domains can\'t have full stops.');
      allPassedValidation = false;
      thisPassedValidation = false;

      // input box can't be empty
    } else if (jQuery(this).val().length == 0) {
      jQuery(this).siblings('.error_msg').text('Corrections cannot be empty.');
      allPassedValidation = false;
      thisPassedValidation = false;
    }

    if (jQuery(this).siblings('.error_msg').is(':visible') && thisPassedValidation) {
      jQuery(this).siblings('.error_msg').fadeOut(function () {
        jQuery(this).text('');
      });

    } else if (!jQuery(this).siblings('.error_msg').is(':visible') && !thisPassedValidation) {
      jQuery(this).siblings('.error_msg').fadeIn();
    }
  });

  if (!allPassedValidation) {
    jQuery('#' + filter_id).children('h3').css('background-color', '#FFB6C1');
    jQuery('#' + filter_id).children('.save_changes_button').fadeOut();
    return;
  }

  on_any_change(filter_id);
}

function delete_button() {
  var filter_id = jQuery(this).attr('filter_id');
  on_any_change(filter_id);

  jQuery(this).parent().parent().fadeOut(function () {
    jQuery(this).remove();
  });

  return false;
}

function on_save() {
  var filter_id = jQuery(this).attr('filter_id');

  var aInputValuesFrom = [];
  var aInputValuesTo = [];

  jQuery('#' + filter_id).find('.correction_from').each(function () {
    aInputValuesFrom.push(jQuery(this).val());
  });

  jQuery('#' + filter_id).find('.correction_to').each(function () {
    aInputValuesTo.push(jQuery(this).val());
  });

  CRM.api3('EmailAmender', 'update_corrections', {
      'sequential': '1',
      'domain_level': filter_id,
      'correction_keys': aInputValuesFrom,
      'correction_values': aInputValuesTo
    }
    , {
      success: function (data) {
        //  TODO indicate a successful save
        // cj.each(data, function(key, value) {// do something });
        jQuery('#' + filter_id).children('.save_changes_button').fadeOut();
        jQuery('#' + filter_id).find('h3').css('background-color', '#CDE8FE');
      }
    });
}

function on_tld_save() {
  var aInputValues = new Array();
  jQuery('#compound_tld').find(':text').each(function () {
    aInputValues.push(jQuery(this).val());
  });
  CRM.api3('EmailAmender', 'update_compound_t_l_ds', {'sequential': '1', 'compound_tlds': aInputValues}
    , {
      success: function (data) {
        //  TODO indicate a successful save
        // cj.each(data, function(key, value) {// do something });
        jQuery('#compound_tld').find('.save_tld_changes').fadeOut();
        jQuery('#compound_tld').find('h3').css('background-color', '#CDE8FE');
      }
    });
}

jQuery('.add_new_correction').click(function () {
  var filter = jQuery(this).attr('filter_id');

  var newFilterRow = '<tr style="display: none"><td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;"></td><td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;"></td><td></td></tr>';
  jQuery('#' + filter + '_table tbody').append(newFilterRow);

  // Add new input boxes
  var newFilterTextBox = '<input type="text" originalValue="unset" filter_id="' + filter + '"/>';
  jQuery('#' + filter + '_table tbody').children().last().children(':lt(2)').append(newFilterTextBox);
  jQuery('#' + filter + '_table tbody').children().last().children(':lt(2)').append('<span class="error_msg" style="display: none;"></span>');

  // add the correction_from and correction_to classes
  jQuery('#' + filter + '_table tbody').children().last().children(':eq(0)').find('input').addClass('correction_from');
  jQuery('#' + filter + '_table tbody').children().last().children(':eq(1)').find('input').addClass('correction_to');

  // Add change listener to both new input boxes
  jQuery('#' + filter + '_table tbody').children().last().find('input').change(input_box_change);

  // Add new delete button
  var newDeleteButton = jQuery('<a href="#" class="deleteButton" filter_id="' + filter + '">Delete this correction</a>').click(delete_button);
  jQuery('#' + filter + '_table tbody').children().last().children().last().append(newDeleteButton);

  jQuery('#' + filter + '_table').find('tr:hidden').fadeIn();

});

// Very similar to .add_new_correction, except we're switching around the display of the 'key' box and the 'value' box
jQuery('.add_new_equivalent').click(function () {
  var filter = jQuery(this).attr('filter_id');

  var newFilterRow = '<tr style="display: none"><td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;"></td><td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;"></td><td></td></tr>';
  jQuery('#' + filter + '_table tbody').append(newFilterRow);

  // Add new input boxes
  var newFilterTextBox = '<input type="text" originalValue="unset" filter_id="' + filter + '"/>';
  jQuery('#' + filter + '_table tbody').children().last().children(':lt(2)').append(newFilterTextBox);
  jQuery('#' + filter + '_table tbody').children().last().children(':lt(2)').append('<span class="error_msg" style="display: none;"></span>');

  // add the correction_from and correction_to classes
  jQuery('#' + filter + '_table tbody').children().last().children(':eq(0)').find('input').addClass('correction_to');
  jQuery('#' + filter + '_table tbody').children().last().children(':eq(1)').find('input').addClass('correction_from');

  // Add change listener to both new input boxes
  jQuery('#' + filter + '_table tbody').children().last().find('input').change(input_box_change);

  // Add new delete button
  var newDeleteButton = jQuery('<a href="#" class="deleteButton" filter_id="' + filter + '">Delete this equivalent</a>').click(delete_button);
  jQuery('#' + filter + '_table tbody').children().last().children().last().append(newDeleteButton);

  jQuery('#' + filter + '_table').find('tr:hidden').fadeIn();

});

jQuery('.add_new_compound_tld').click(function () {
  var filter = jQuery(this).attr('filter_id');

  var newCompoundTLDRow = '<tr style="display: none"><td></td><td></td></tr>';
  jQuery('#compound_tld_table tbody').append(newCompoundTLDRow);

  // Add new input boxes
  var newCompoundTLDTextBox = '<input type="text" originalValue="unset" filter_id="compound_tld"/>';
  jQuery('#compound_tld_table tbody').children().last().children(':lt(1)').append(newCompoundTLDTextBox);
  jQuery('#compound_tld_table tbody').children().last().children(':lt(1)').append('<span class="error_msg" style="display: none;"></span>');

  // Add change listener to both new input boxes
  jQuery('#compound_tld_table tbody').children().last().find('input').change(input_box_change);

  // Add new delete button
  var newDeleteButton = jQuery('<a href="#" class="deleteButton" filter_id="compound_tld">Delete this compound tld</a>').click(delete_button);
  jQuery('#compound_tld_table tbody').children().last().children().last().append(newDeleteButton);

  jQuery('#compound_tld_table').find('tr:hidden').fadeIn();
});

jQuery('#email_amender_enabled').click(function () {
  CRM.api3('Setting', 'create', {
      'emailamender.email_amender_enabled': jQuery(this).is(':checked')
    }
    , {
      success: function (data) {

      }
    });
});

// add event listeners
jQuery('.deleteButton').click(delete_button);
jQuery('input').change(input_box_change);
jQuery('.save_correction_changes').click(on_save);
jQuery('.save_tld_changes').click(on_tld_save);

jQuery('h3').css('-webkit-transition', 'background-color 0.4s linear');
jQuery('h3').css('-moz-transition', 'background-color 0.4s linear');
jQuery('h3').css('-ms-transition', 'background-color 0.4s linear');
jQuery('h3').css('-o-transition', 'background-color 0.4s linear');
jQuery('h3').css('transition', 'background-color 0.4s linear');
