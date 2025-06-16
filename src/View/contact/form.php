<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <a href="<?= url('contacts.index') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?= htmlspecialchars($formTitle ?? 'Добавить новый контакт') ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="<?= $formMethod ?? 'post' ?>" action="<?= url('contacts.update') ?>" enctype="multipart/form-data">
                        <?= \App\Middleware\CsrfMiddleware::getTokenField() ?>
                        <?= $methodField ?? '' ?>

                        <div class="mb-3">
                            <label for="first_name" class="form-label">Имя</label>
                            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                   id="first_name" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['first_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Фамилия</label>
                            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                   id="last_name" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['last_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                   id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['phone']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['email']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <?php if (!empty($_POST['image_path'])): ?>
                                <div class="mb-2">Текущее изображение:</div>
                                <img src="/<?= htmlspecialchars($_POST['image_path']) ?>" alt="Текущее фото" width="100" class="img-thumbnail mb-2">
                            <?php endif; ?>

                            <label for="image" class="form-label">Фото (необязательно)</label>
                            <input type="file" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
                                   id="image" name="image" accept="image/jpeg, image/png">
                            <div class="form-text">JPEG или PNG, макс 5МБ<?= !empty($_POST['image_path']) ? '. Оставьте пустым, чтобы сохранить текущее изображение.' : '' ?></div>
                            <?php if (isset($errors['image'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['image']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <?= isset($_POST['id']) ? 'Сохранить изменения' : 'Добавить контакт' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>