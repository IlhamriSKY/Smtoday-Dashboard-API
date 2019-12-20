var timer;
var $btn = $("#resend-token");

/**
 * Start timeout which will show the "Resend Token" button
 * after 60 seconds.
 */
function startTimer() {
    timer = setTimeout(function () {
        $btn.removeClass('d-none');
    }, 60 * 1000);
}

/**
 * Resend phone verification token.
 */
function resendToken() {
    as.btn.loading($btn, $btn.data('loading-text'));

    $.post("/two-factor/resend", user ? {user: user} : {})
        .then(handleResendResponse)
        .catch(handleResendResponse);
}

/**
 * Handle response received from the server after
 * resend token request was sent.
 */
function handleResendResponse() {
    as.btn.stopLoading($btn);
    $btn.addClass('d-none');
    startTimer();
}

$btn.click(resendToken);
startTimer();