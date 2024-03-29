$(document).ready(function() {
    const formatDate = inputDate => inputDate.split('-').reverse().join('/');

    // Initialize DataTable
    $('#progs').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json',
        }
        //order: [[1, 'asc']]
    });

    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
    });


    // Initialize sch1 select2 with options
    $('#sch1').select2({
        ajax: {
            url: 'db.php?all_schools=true', // The URL to retrieve the options
            dataType: 'json',
            delay: 500,
            processResults: function(data) { return { results: data }; },
            cache: true
        },
        minimumInputLength: 0, // You can adjust this according to your needs
        dropdownParent: $('#editForm')
    });

    // Initialize sch2 select2 with options
    $('#sch2').select2({
        ajax: {
            url: 'db.php?all_schools=true', // The URL to retrieve the options
            dataType: 'json',
            delay: 500,
            processResults: function(data) { return { results: data }; },
            cache: true
        },
        minimumInputLength: 0, // You can adjust this according to your needs
        dropdownParent: $('#editForm')
    });

    function replaceSelect2(schId){
        var $sch1 = $('#sch1');
        // Get the school name from db
        $.get('db.php', { sch_id: schId }, function(data) { 
            // Create a new span element with the school name
            var $customTextElement = $('<span>&nbsp;&nbsp;&nbsp;<b>' + data + '</b></span>');
            // Append the custom text element to the same location where the Select2 element was
            $customTextElement.insertAfter($sch1);
            // Remove the Select2 element completely
            $sch1.select2('destroy').remove();
            $('#editForm')[0].reset();
        });   
    }
    $('.add-record').on('click', function() {
        // Use AJAX to get record details and populate the view modal
        // Show the view modal
        $('#editModal').modal('show');
        // make school tab active
        $('#editTabs a[href="#school"]').tab('show');
        // enable all inputs (in case they're disabled)
        $("#editForm :input").prop("disabled", false);
        // get sch id (if not admin)
        var schId = $(this).data('schid');
        if (!!schId) {
            replaceSelect2(schId);
        } else {
            $('#editForm')[0].reset();
        }
    });


    // Edit / View button click event handler
    $('#progs').on('click', '.edit-record, .view-record', function() {
        var triggeredClasses = $(this).attr('class').split(' ');
        var triggeredClass = triggeredClasses[2];
        
        // Extract record ID and fetch details from the server
        var recordId = $(this).data('record-id');
        var schId = $(this).data('sch-id');

        // Use AJAX to get record details and populate the edit modal
        $.get('db.php', { id: recordId }, function(data) { 
            // console.log(data);
            
            $.each(data, function(key, value) {
                var fieldId = key;
                var $field = $('#' + fieldId);
                
                // Set the value of the form field
                if (key === 'praxidate') {
                    $field.val(formatDate(value));
                } else {
                    $field.val(value);
                }
                
                if (fieldId === 'sch1') {
                    // Check if the option exists in the Select2 dropdown
                    var $select2 = $field.data('select2');
                    var $option = $select2.$element.find('option[value="' + value + '"]');
                    
                    if ($option.length === 0) {
                        // If the option doesn't exist, add it
                        $field.append(new Option(data.sch1name, value, true, true)).trigger('change');
                    } else {
                        // If the option exists, trigger 'change' without clearing existing options
                        $field.val(value).trigger('change');
                    }
                    // if school, disable sch1
                    if (schId > 0) {
                        $("#sch1").prop("disabled", true);
                    }
                }
            });
        });
        // if view, disable all inputs
        if (triggeredClass === 'view-record') {
            $("#editForm :input").prop("disabled", true);
            $('.modal-title').text('Προβολή προγράμματος');
        } else {
            $("#editForm :input").prop("disabled", false);
            $('.modal-title').text('Επεξεργασία προγράμματος');
        }
    
        // Show the edit modal
        $('#editModal').modal('show');
        // make school tab active
        $('#editTabs a[href="#school"]').tab('show');
    });

    // editForm submit handler
    $('#editForm').submit(function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Serialize the form data to send to db.php
        var formData = $(this).serialize();

        // Perform an AJAX POST request to db.php to save the edited data
        $.ajax({
            url: 'db.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                // Handle the response from db.php (e.g., success message or error handling)
                if (response.success) {
                    alert('Επιτυχής αποθήκευση!');
                    $('#editModal').modal('hide');
                } else {
                    alert('Σφάλμα: ' + response.error);
                }
            },
            error: function(err) {
                console.log(err.responseText);
                // Handle any errors from the AJAX request
                alert('Σφάλμα αποθήκευσης...');
            }
        });
    });
  
});