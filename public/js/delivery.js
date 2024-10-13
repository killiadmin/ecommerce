/**
 * Toggles the visibility of billing fields based on the state of a billing checkbox.
 *
 * @param {object} billingCheckbox
 * @param {object} billingFields
 * @return {void}
 */
function toggleBillingFields(billingCheckbox, billingFields) {
    billingCheckbox.is(":checked") ? billingFields.show() : billingFields.hide();
}

$(document).ready(function() {
    let billingCheckbox = $('input[type="checkbox"][name$="[billing]"]');
    let billingFields = $(".billing-field");

    toggleBillingFields(billingCheckbox, billingFields);
    billingCheckbox.change(function() {
        toggleBillingFields(billingCheckbox, billingFields);
    });
});
