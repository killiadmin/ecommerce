/**
 * Sets up a click event handler for deleting items.
 * @param {string} selector
 * @return {void}
 */
function setupDeleteItemHandler(selector) {
    $(selector).on("click", function (event) {
        event.preventDefault();

        const $this = $(this);
        const itemId = $this.data("item-id");
        const $row = $('tr[data-item-id="' + itemId + '"]');

        toggleDeleteButton($this, false);

        $.ajax({
            url: $this.attr("href"),
            type: "POST",
            dataType: "json",
            success: function (response) {
                if (!response.success) {
                    showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.");
                    return;
                }

                //Updating display after deletion
                handleItemRemoval($row, $this);
            },
            error: function () {
                showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.");
            }
        });
    });
}

/**
 * Utility function to enable/disable delete button
 * @param $button
 * @param enable
 */
function toggleDeleteButton($button, enable) {
    if (enable) {
        $button.addClass("delete-item");
    } else {
        $button.removeClass("delete-item");
    }
}

/**
 * Function to display error message and restore button
 * @param $button
 * @param message
 */
function showAlertAndRestoreButton($button, message) {
    alert(message);
    toggleDeleteButton($button, true);
}

/**
 * Function to manage the deletion of an article
 * @param $row
 */
function handleItemRemoval($row) {
    const itemQuantity = parseInt($row.find(".quantity[data-quantity]").data("quantity"), 10);
    $row.remove();
    updateBadgeQuantitiesFromRemove(itemQuantity);
}

/**
 * Updates the quantities displayed on badge elements for both desktop and mobile views
 * when an item is removed from the basket
 * @param {number} itemQuantity
 * @return {void}
 */
function updateBadgeQuantitiesFromRemove(itemQuantity) {
    const badgeQuantityDesk = $("#badge-quantity-desk");
    const badgeQuantityMobile = $("#badge-quantity-mobile");

    const badges = [
        { $element: badgeQuantityDesk, currentCount: parseInt(badgeQuantityDesk.text(), 10) || 0 },
        { $element: badgeQuantityMobile, currentCount: parseInt(badgeQuantityMobile.text(), 10) || 0 }
    ];

    let flagEmpty = ""

    badges.forEach(function (badge) {
        const newCount = badge.currentCount - itemQuantity;
        badge.$element.text(newCount >= 0 ? newCount : 0);
        flagEmpty = newCount;
    });

    if (flagEmpty === 0) {
        $("#myBasket").append($("<tr>").attr({colspan: 4}).text("Votre panier est vide "));
    }
}

/**
 * Attaches a debounced input event listener to the specified selector.
 * @param {string} selector
 * @param {number} delay
 * @param {function} callback
 * @return {void}
 */
function debounceInput(selector, delay, callback) {
    let debounceTimer;

    $(selector).on("input", function () {
        clearTimeout(debounceTimer);

        const itemId = $(this).data("item-id");
        const newQuantity = $(this).val();

        debounceTimer = setTimeout(() => {
            callback(itemId, newQuantity);
        }, delay);
    });
}

/**
 * Updates the quantity of a specific item in the shopping cart.
 * @param {number|string} itemId
 * @param {number} newQuantity
 * @return {void}
 */
function updateQuantity(itemId, newQuantity) {
    const $row = $('tr[data-item-id="' + itemId + '"]');
    let itemQuantity = parseInt($row.find(".quantity[data-quantity]").data("quantity"), 10);

    $.ajax({
        url: `/mon-panier/update/${itemId}`,
        type: "PUT",
        contentType: "application/json",
        data: JSON.stringify({ quantity: newQuantity }),
        success: function (data) {
            if (data.success) {
                itemQuantity = data.newQuantity;
                $row.find(".quantity[data-quantity]").data("quantity", itemQuantity);
                updateBadgeQuantitiesFromChangeQuantity(data.itemQuantityChange);
            } else {
                alert(`Erreur : ${data.message}`);
            }
        },
        error: function (error) {
            console.error("Erreur lors de la mise à jour : ", error);
        }
    });
}

/**
 * Updates the badge quantities for both desktop and mobile views based on the change in item quantity.
 * @param {number} itemQuantityChange
 * @return {void}
 */
function updateBadgeQuantitiesFromChangeQuantity(itemQuantityChange) {
    const badgeQuantityDesk = $("#badge-quantity-desk");
    const badgeQuantityMobile = $("#badge-quantity-mobile");

    const badges = [
        { $element: badgeQuantityDesk, currentCount: parseInt(badgeQuantityDesk.text(), 10) || 0 },
        { $element: badgeQuantityMobile, currentCount: parseInt(badgeQuantityMobile.text(), 10) || 0 }
    ];

    let totalQuantity = 0;

    badges.forEach(function (badge) {
        const newCount = badge.currentCount + itemQuantityChange;
        badge.$element.text(newCount >= 0 ? newCount : 0);
        totalQuantity = newCount;
    });
}

$(document).ready(function () {
    setupDeleteItemHandler(".delete-item");

    debounceInput(".quantity-input", 400, updateQuantity);
});
