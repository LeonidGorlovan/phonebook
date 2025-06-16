<?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Register</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($errors['general']) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= url('auth.register.send') ?>">
                            <?= \App\Middleware\CsrfMiddleware::getTokenField() ?>

                            <div class="mb-3">
                                <label for="login" class="form-label">Login</label>
                                <input type="text" class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>"
                                       id="login" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                                <div class="form-text">Latin letters and numbers, up to 16 characters</div>
                                <?php if (isset($errors['login'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['login']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                       id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                       id="password" name="password">
                                <div class="form-text">At least 6 characters, must include uppercase, lowercase letters and numbers</div>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                       id="confirm_password" name="confirm_password">
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['confirm_password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">Register</button>
                                <a href="<?= url('auth.login') ?>" class="btn btn-link">Back to Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>