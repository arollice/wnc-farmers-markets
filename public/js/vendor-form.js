'use strict';

$(document).ready(function() {
    console.log("Initializing Select2 on #markets_attended...");
    
    if ($('#markets_attended').length) {
        // Check if Select2 is available
        if (typeof $.fn.select2 !== 'function') {
            console.error("Select2 is not available.");
            return;
        }
        
        $('#markets_attended').select2({
            tags: true, // Allows adding new items
            placeholder: "Select or add markets you attend",
            allowClear: true
        });
        $('#markets_attended').on('change', function() {
            console.log("Selection changed:", $(this).val());
        });
    } else {
        console.error("Error: #markets_attended element NOT found!");
    }
});
