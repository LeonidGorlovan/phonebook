<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="alert alert-danger">
        <h3>Security Error</h3>
        <p>CSRF token validation failed. This could happen for the following reasons:</p>
        <ul>
            <li>You submitted an expired form</li>
            <li>You navigated using browser back button and resubmitted a form</li>
            <li>Someone might be trying to trick you into submitting a form</li>
        </ul>
        <p>Please <a href="javascript:history.back()">go back</a> and try again, or <a href="<?= url('home') ?>">return to the homepage</a>.</p>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>