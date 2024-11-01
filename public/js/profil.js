/**
 * Updates the account status by sending an AJAX request to the server.
 * Updates the UI elements based on the response indicating whether the account is professional or not.
 *
 * @return void
 */
function updateAccountStatus() {
    $.ajax({
        url: "/profil/account",
        method: "POST",
        success: function(response) {
            if (!response.success) {
                alert("Erreur lors de la mise à jour du statut.");
                return;
            }

            const isProfessional = response.isProfessional;
            const accountToggleButton = $(".account-toggle-button");
            const accountModeMessage = $(".account-mode-message");

            if (isProfessional) {
                accountToggleButton.addClass("active");
                accountToggleButton.find("span:nth-child(2)").css("left", "65px");
                $(".fa-house-user").hide();
                $(".fa-building").show();
                $(".badge-particular").hide();
                $(".badge-professional").show();
                accountModeMessage.text("Vous êtes particulier ?");
            } else {
                accountToggleButton.removeClass("active");
                accountToggleButton.find("span:nth-child(2)").css("left", "5px");
                $(".fa-house-user").show();
                $(".fa-building").hide();
                $(".badge-particular").show();
                $(".badge-professional").hide();
                accountModeMessage.text("Vous êtes professionnel ?");
            }
        },
        error: function() {
            alert("Une erreur est survenue lors de la mise à jour de votre statut professionnel.");
        }
    });
}

$(document).ready(function(){
    $(".account-toggle-button").on("click", function(){
        updateAccountStatus();
    });
});
