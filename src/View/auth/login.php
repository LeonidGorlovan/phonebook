<?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['register_success'])): ?>
                            <div class="alert alert-success">
                                Registration successful! Please login.
                            </div>
                            <?php unset($_SESSION['register_success']); ?>
                        <?php endif; ?>

                        <?php if (isset($errors['auth'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($errors['auth']) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= url('auth.login.send') ?>">
                            <?= \App\Middleware\CsrfMiddleware::getTokenField() ?>

                            <div class="mb-3">
                                <label for="login" class="form-label">Login</label>
                                <input type="text" class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>"
                                       id="login" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                                <?php if (isset($errors['login'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['login']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                       id="password" name="password">
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">Login</button>
                                <a href="<?= url('auth.register') ?>" class="btn btn-link">Register</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>