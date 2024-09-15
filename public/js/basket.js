$(document).ready(function () {
    $(".delete-item").on("click", function (event) {
        event.preventDefault();

        var $this = $(this);
        var itemId = $this.data("item-id");
        var $row = $('tr[data-item-id="' + itemId + '"]');

        toggleDeleteButton($this, false);

        // Envoi de la requête Ajax
        $.ajax({
            url: $this.attr("href"),
            type: "POST",
            dataType: "json",
            success: function (response) {
                if (!response.success) {
                    showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.");
                    return;
                }

                // Mise à jour l'affichage après la suppression
                handleItemRemoval($row, $this);
            },
            error: function () {
                showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.");
            }
        });
    });

    /**
     * Fonction utilitaire pour activer/désactiver le bouton de suppression
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
     * Fonction pour afficher un message d'erreur et restaurer le bouton
     * @param $button
     * @param message
     */
    function showAlertAndRestoreButton($button, message) {
        alert(message);
        toggleDeleteButton($button, true);
    }

    /**
     * Fonction pour gérer la suppression d'un article
     * @param $row
     */
    function handleItemRemoval($row) {
        var itemQuantity = parseInt($row.find(".quantity[data-quantity]").data("quantity"), 10);
        $row.remove();
        updateBadgeQuantities(itemQuantity);
    }

    /**
     * Fonction pour mettre à jour les badges
     * @param itemQuantity
     */
    function updateBadgeQuantities(itemQuantity) {
        var badgeQuantityDesk = $("#badge-quantity-desk");
        var badgeQuantityMobile = $("#badge-quantity-mobile");

        var badges = [
            { $element: badgeQuantityDesk, currentCount: parseInt(badgeQuantityDesk.text(), 10) || 0 },
            { $element: badgeQuantityMobile, currentCount: parseInt(badgeQuantityMobile.text(), 10) || 0 }
        ];

        badges.forEach(function (badge) {
            var newCount = badge.currentCount - itemQuantity;
            badge.$element.text(newCount >= 0 ? newCount : 0);
        });
    }
});
