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
    updateTotalPrices();
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

    $.ajax({
        url: `/mon-panier/update/${itemId}`,
        type: "PUT",
        contentType: "application/json",
        data: JSON.stringify({ quantity: newQuantity }),
        success: function (data) {
            if (!data.success) {
                console.error("Erreur lors de la mise à jour : ", data.message);
            }

            const itemPrice = parseFloat(data.itemPrice);
            const itemTva = parseFloat(data.itemTva);

            $row.find(".quantity[data-quantity]").data("quantity", data.newQuantity);

            updateBadgeQuantitiesFromChangeQuantity(data.itemQuantityChange);

            updateTotalPrices(itemPrice, itemTva, data.newQuantity);
        },
        error: function (error) {
            console.error("Erreur lors de la mise à jour : ", error);
        }
    });
}

/**
 * Updates the total prices, total quantities, and total item count in the HTML elements.
 *
 * @return {void}
 */
function updateTotalPrices() {
    let totalHT = 0;
    let totalTTC = 0;
    let totalQuantity = 0;
    let totalCount = 0;

    $("tr[data-item-id]").each(function () {
        const itemPrice = parseFloat($(this).find(".price[data-price]").data("price"));
        const itemTva = parseFloat($(this).find(".tva[data-tva]").data("tva"));
        const itemQuantity = parseInt($(this).find(".quantity[data-quantity]").data("quantity"), 10);

        if (!isNaN(itemPrice) && !isNaN(itemQuantity)) {
            totalHT += itemPrice * itemQuantity;
            totalQuantity += itemQuantity;
            totalCount++;
        }

        if (!isNaN(itemTva)) {
            totalTTC += itemTva * itemQuantity;
        }
    });

    $("#totalCount").text(totalCount);
    $("#totalQuantity").text(totalQuantity);
    $("#totalPriceHt").text(totalHT + " €");
    $("#totalPriceTtc").text(totalTTC + " €");
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
