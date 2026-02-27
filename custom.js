// General AJAX handler function
function ajaxRequest(url, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: successCallback,
        error: errorCallback || function() { alert('Error occurred'); }
    });
}

// Base ready function; page-specific handlers are in individual pages
$(document).ready(function() {
    // Common code if needed
});