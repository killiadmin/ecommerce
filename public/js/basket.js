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
        const $row = $(`tr[data-item-id="${itemId}"]`);

        toggleDeleteButton($this, false);

        $.ajax({
            url: $this.attr("href"),
            type: "POST",
            dataType: "json",
            success: (response) => handleDeleteResponse(response, $this, $row),
            error: () => showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.")
        });
    });
}

/**
 * Handles the response for item deletion.
 * @param {object} response
 * @param {object} $button
 * @param {object} $row
 */
function handleDeleteResponse(response, $button, $row) {
    if (!response.success) {
        showAlertAndRestoreButton($button, "Une erreur est survenue lors de la suppression de l'article.");
        return;
    }
    handleItemRemoval($row);
}

/**
 * Utility function to enable/disable delete button.
 * @param {object} $button
 * @param {boolean} enable
 */
function toggleDeleteButton($button, enable) {
    $button.toggleClass("delete-item", enable);
}

/**
 * Function to display error message and restore button.
 * @param {object} $button
 * @param {string} message
 */
function showAlertAndRestoreButton($button, message) {
    alert(message);
    toggleDeleteButton($button, true);
}

/**
 * Function to manage the deletion of an article.
 * @param {object} $row
 */
function handleItemRemoval($row) {
    const itemQuantity = parseInt($row.find(".quantity[data-quantity]").data("quantity"), 10);
    $row.remove();
    updateBadgeQuantities(itemQuantity, "remove");
    updateTotalPrices();
}

/**
 * Updates the badge quantities for both desktop and mobile views.
 * @param {number} itemQuantity
 * @param {string} action "remove" or "change"
 */
function updateBadgeQuantities(itemQuantity, action) {
    const badgeQuantityDesk = $("#badge-quantity-desk");
    const badgeQuantityMobile = $("#badge-quantity-mobile");

    const badges = [
        { $element: badgeQuantityDesk, currentCount: parseInt(badgeQuantityDesk.text(), 10) || 0 },
        { $element: badgeQuantityMobile, currentCount: parseInt(badgeQuantityMobile.text(), 10) || 0 }
    ];

    let flagEmpty = "";

    badges.forEach(function (badge) {
        const newCount = action === "remove" ? badge.currentCount - itemQuantity : badge.currentCount + itemQuantity;
        badge.$element.text(Math.max(newCount, 0));
        flagEmpty = newCount;
    });

    if (flagEmpty === 0) {
        $("#myBasket").append($("<tr>").attr({ colspan: 4 }).text("Votre panier est vide "));
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
    const $row = $(`tr[data-item-id="${itemId}"]`);

    $.ajax({
        url: `/mon-panier/update/${itemId}`,
        type: "PUT",
        contentType: "application/json",
        data: JSON.stringify({ quantity: newQuantity }),
        success: (data) => handleQuantityUpdateSuccess(data, $row),
        error: (error) => console.error("Erreur lors de la mise à jour : ", error)
    });
}

/**
 * Handles the success of a quantity update.
 * @param {object} data
 * @param {object} $row
 */
function handleQuantityUpdateSuccess(data, $row) {
    if (!data.success) {
        console.error("Erreur lors de la mise à jour : ", data.message);
        return;
    }

    const { itemPrice, itemTva, newQuantity, itemQuantityChange } = data;

    $row.find(".quantity[data-quantity]").data("quantity", newQuantity);
    updateBadgeQuantities(itemQuantityChange, "change");
    updateTotalPrices(itemPrice, itemTva, newQuantity);
}

/**
 * Updates the total prices, total quantities, and total item count in the HTML elements.
 */
function updateTotalPrices() {
    let totalHT = 0;
    let totalTTC = 0;
    let totalQuantity = 0;
    let totalCount = 0;
    let discountPercent = parseFloat($("#totalPriceHtWithDiscount").data("reduction")) / 100;

    const tvaRate = 1.2;

    $("tr[data-item-id]").each(function () {
        const itemHTPrice = parseFloat($(this).find(".price[data-price]").data("price"));
        const itemQuantity = parseInt($(this).find(".quantity[data-quantity]").data("quantity"), 10);

        if (!isNaN(itemHTPrice) && !isNaN(itemQuantity)) {
            const itemTTCPrice = Math.ceil(itemHTPrice * tvaRate);
            const itemTotalTTC = itemTTCPrice * itemQuantity;

            totalHT += itemHTPrice * itemQuantity;
            totalTTC += itemTotalTTC;
            totalQuantity += itemQuantity;
            totalCount++;
        }
    });

    const discountedTotalHT = totalHT * (1 - discountPercent);
    const discountedTotalTTC = Math.ceil(discountedTotalHT * tvaRate);

    $("#totalCount").text(totalCount);
    $("#totalQuantity").text(totalQuantity);
    $("#totalPriceHt").text(`${Math.ceil(totalHT)} €`);
    $("#totalPriceTtc").text(`${Math.ceil(totalTTC)} €`);
    $(".total-price-ht-discount").text(`${Math.ceil(discountedTotalHT)} €`);
    $(".total-price-ttc-discount").text(`${discountedTotalTTC} €`);
}

$(document).ready(function () {
    setupDeleteItemHandler(".delete-item");
    debounceInput(".quantity-input", 400, updateQuantity);
});
