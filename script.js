$(document).ready(function() {
    const formatDate = inputDate => inputDate.split('-').reverse().join('/');

    // Initialize DataTable
    $('#progs').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json',
        },
        // columnDefs: [
        //     { targets: [1, 2], visible: false } // Hide columns 2 and 3 (zero-based indexing)
        // ],
        columnDefs: [
            {
                targets: 1,
                className: 'noVis'
            }
        ],
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'colvis',
                        columns: ':not(.noVis)',
                        popoverTitle: 'Επιλογή ορατών στηλών'
                    },
                    'excelHtml5',
                    'pdfHtml5'
                ]
            }
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


    // Function to display Bootstrap alert
    function showAlert(message, type) {
        var alertClass = 'alert-' + type; // Bootstrap alert class
        var alertHTML = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                            message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
        $('#alertContainer').append(alertHTML); // Append alert to container
    }


    function replaceSelect2(schId){
        var $sch1 = $('#sch1');
        // Get the school name from db
        $.get('db.php', { sch_id: schId }, function(data) { 
            // Create a new span element with the school name
            var $customTextElement = $('<span>&nbsp;&nbsp;&nbsp;<b>' + data + '</b></span>');
            // Append the custom text element to the same location where the Select2 element was
            $customTextElement.insertAfter($sch1);
            // Create a hidden input element
            var $hiddenInput = $('<input type="hidden" name="sch1" value="' + schId + '">');
            // Append the hidden input after the custom text element
            $hiddenInput.insertAfter($customTextElement);
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
        var lockBasic = $(this).data('lock-basic');
        var isAdmin = $(this).data('admin');

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
            $('.save-btn').hide();
            $('.close-btn').prop("disabled", false);
        } else {
            $("#editForm :input").prop("disabled", false);
            // disable #vev if canVev is not set
            if (!$(this).data('canvev')){
                $("#vev").prop("disabled", true);
            }
            $('.modal-title').text('Επεξεργασία προγράμματος');
            $('.save-btn').show();
            $('.close-btn').prop("disabled", false);
            if (lockBasic && !isAdmin) {
                // disable basic fields
                // Array of input IDs to disable
                var inputsToDisable = ['sch1', 'sch2', 'titel', 'nam1', 'nam2', 'nam3', 'eid1', 'eid2', 'eid3'];

                // Loop through the array and disable each input
                $.each(inputsToDisable, function(index, id) {
                    $('#' + id).prop('disabled', true);
                });
            }
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
                    showAlert('Επιτυχής αποθήκευση!', 'success');
                    $('#editModal').modal('hide');
                } else {
                    showAlert('Σφάλμα: ' + response.error, 'error');
                }
            },
            error: function(err) {
                console.log(err.responseText);
                // Handle any errors from the AJAX request
                showAlert('Σφάλμα αποθήκευσης...', 'error');
            }
        });
    });

    // load parameters from config.json to #configModal
    function loadConfigData() {
        $.get("config.json", function(data) {
            $.each(data, function(index, setting) {
                var inputHtml = '';
    
                if (typeof setting.value === 'boolean') {
                    inputHtml = '<div class="form-check">' +
                                    '<input class="form-check-input" type="checkbox" id="' + setting.name + '" ' + (setting.value ? 'checked' : '') + '>' +
                                    '<label class="form-check-label" for="' + setting.name + '">' + setting.description + '</label>' +
                                '</div>';
                } else {
                    inputHtml = '<div class="mb-3">' +
                                    '<label for="' + setting.name + '" class="form-label">' + setting.description + '</label>' +
                                    '<input type="text" class="form-control" id="' + setting.name + '" value="' + setting.value + '">' +
                                '</div>';
                }
    
                $('#configModal .modal-body').append(inputHtml);
            });
        });
    }

    // save parameters from modal to file
    function saveConfigData() {
        var configData = [];
    
        $('#configModal .modal-body input').each(function() {
            var name = $(this).attr('id');
            var description = $(this).closest('.mb-3').find('label').text();
            var value = $(this).is(':checkbox') ? $(this).prop('checked') : $(this).val();
    
            configData.push({
                name: name,
                description: description,
                value: value
            });
        });
        // console.log(configData);
    
        $.ajax({
            type: "POST",
            url: "save_config.php",
            data: { configData: JSON.stringify(configData) },
            success: function(response) {
                showAlert("Επιτυχής αποθήκευση παραμέτρων!", 'success');
                $('#configModal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error("Error saving configuration:", error);
                showAlert("Σφάλμα αποθήκευσης παραμέτρων. Παρακαλώ δοκιμάστε ξανά...", 'error');
            }
        });
    }

    // Load configuration data when the modal is shown
    $('#configModal').on('shown.bs.modal', function() {
        $('#configModal .modal-body').empty(); // Clear previous content
        loadConfigData(); // Load configuration data
    });

    // Save configuration data when Save Changes button is clicked
    $('#saveConfigBtn').on('click', function() {
        saveConfigData(); // Save configuration data
        
    });
    
    
    
  
});