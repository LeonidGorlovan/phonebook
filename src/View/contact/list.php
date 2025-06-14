<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Phone Book</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
            Add Contact
        </button>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($contacts)): ?>
                        <div class="alert alert-info">
                            Your phone book is empty. Add your first contact using the "Add Contact" button.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="contacts-table-body">
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr id="contact-<?= $contact['id'] ?>">
                                            <td>
                                                <?php if (!empty($contact['image_path'])): ?>
                                                    <img src="/<?= htmlspecialchars($contact['image_path']) ?>"
                                                         alt="Contact photo" class="rounded-circle" width="50" height="50">
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px;">
                                                        <?= htmlspecialchars(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($contact['phone']) ?></td>
                                            <td><?= htmlspecialchars($contact['email']) ?></td>
                                            <td>
                                                <a href="/contacts/<?= $contact['id'] ?>" class="btn btn-sm btn-info">
                                                    View
                                                </a>
                                                <button class="btn btn-sm btn-danger delete-contact" data-id="<?= $contact['id'] ?>">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContactModalLabel">Add New Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-contact-form" enctype="multipart/form-data">
                    <?= \App\Middleware\CsrfMiddleware::getTokenField() ?>

                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                        <div class="invalid-feedback" id="first_name_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                        <div class="invalid-feedback" id="last_name_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                        <div class="invalid-feedback" id="phone_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback" id="email_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Photo (optional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png">
                        <div class="form-text">JPEG or PNG, max 5MB</div>
                        <div class="invalid-feedback" id="image_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-contact">Save Contact</button>
            </div>
        </div>
    </div>
</div>

<!-- Contact List JavaScript -->
<script>
    $(document).ready(function() {
        // Add contact via AJAX
        $('#save-contact').click(function() {
            var formData = new FormData($('#add-contact-form')[0]);

            // Add CSRF token
            formData.append('_csrf_token', '<?= \App\Middleware\CsrfMiddleware::getToken() ?>');

            // Reset error messages
            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: '/contacts',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '<?= \App\Middleware\CsrfMiddleware::getToken() ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Close modal and reset form
                        $('#addContactModal').modal('hide');
                        $('#add-contact-form')[0].reset();

                        // Add new contact to the table
                        var contact = response.contact;
                        var imagePath = contact.image_path ?
                            '<img src="/' + contact.image_path + '" alt="Contact photo" class="rounded-circle" width="50" height="50">' :
                            '<div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">' +
                            contact.first_name.charAt(0) + contact.last_name.charAt(0) + '</div>';

                        var newRow = '<tr id="contact-' + contact.id + '">' +
                            '<td>' + imagePath + '</td>' +
                            '<td>' + contact.first_name + ' ' + contact.last_name + '</td>' +
                            '<td>' + contact.phone + '</td>' +
                            '<td>' + contact.email + '</td>' +
                            '<td>' +
                            '<a href="/contacts/' + contact.id + '" class="btn btn-sm btn-info">View</a> ' +
                            '<button class="btn btn-sm btn-danger delete-contact" data-id="' + contact.id + '">Delete</button>' +
                            '</td>' +
                            '</tr>';

                        // If table was empty, refresh the page to show the table
                        if ($('#contacts-table-body').length === 0) {
                            location.reload();
                        } else {
                            $('#contacts-table-body').prepend(newRow);
                        }
                    } else {
                        // Display validation errors
                        $.each(response.errors, function(field, message) {
                            $('#' + field).addClass('is-invalid');
                            $('#' + field + '_error').text(message).show();
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        alert('CSRF token validation failed. Please refresh the page and try again.');
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Delete contact via AJAX
        $(document).on('click', '.delete-contact', function() {
            if (confirm('Are you sure you want to delete this contact?')) {
                var contactId = $(this).data('id');

                $.ajax({
                    url: '/contacts/delete/' + contactId,
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': '<?= \App\Middleware\CsrfMiddleware::getToken() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#contact-' + contactId).fadeOut('slow', function() {
                                $(this).remove();

                                // If no more contacts, reload the page to show empty message
                                if ($('#contacts-table-body tr').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            alert('Failed to delete contact.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            alert('CSRF token validation failed. Please refresh the page and try again.');
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                    }
                });
            }
        });

        // Add CSRF token to the add contact form
        $('#add-contact-form').append('<?= \App\Middleware\CsrfMiddleware::getTokenField() ?>');
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>