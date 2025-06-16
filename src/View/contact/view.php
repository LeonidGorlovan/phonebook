<?php
    if (!isset($contact) || !is_array($contact)) {
        header('Location: /contacts');
        exit;
    }

    include __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <a href="<?= url('contacts.index') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Contact Details</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if (!empty($contact['image_path'])): ?>
                            <img src="/<?= htmlspecialchars($contact['image_path']) ?>"
                                 alt="Contact photo"
                                 class="img-fluid rounded-circle"
                                 style="width: 200px; height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 200px; height: 200px; font-size: 72px;">
                                <?= htmlspecialchars(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Name:</div>
                        <div class="col-md-8">
                            <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Phone:</div>
                        <div class="col-md-8">
                            <a href="tel:<?= htmlspecialchars($contact['phone']) ?>">
                                <?= htmlspecialchars($contact['phone']) ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Email:</div>
                        <div class="col-md-8">
                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                <?= htmlspecialchars($contact['email']) ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Added on:</div>
                        <div class="col-md-8">
                            <?= htmlspecialchars(date('F j, Y', strtotime($contact['created_at']))) ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-danger delete-contact" data-id="<?= $contact['id'] ?>">
                        Delete Contact
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete contact
    $('.delete-contact').click(function() {
        if (confirm('Are you sure you want to delete this contact?')) {
            var contactId = $(this).data('id');
            
            $.ajax({
                url: '<?= url('contacts.delete', ['id' => 'PLACEHOLDER_ID']) ?>'.replace('PLACEHOLDER_ID', contactId),
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = '/contacts';
                    } else {
                        alert('Failed to delete contact.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>