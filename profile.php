<?php
require_once __DIR__ . '/includes/init.php';
requireUserLogin(); // Ensure the user is logged in

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT user_id, email, first_name, last_name, role FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-extrabold text-gray-900">
                My Profile
            </h2>
        </div>

        <?php if ($user): ?>
            <div class="space-y-6">
                <section>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['first_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['last_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                        </div>
                    </div>
                </section>

                <!-- Add more profile sections here, e.g., booking history, account settings -->

            </div>
        <?php else: ?>
            <p class="text-center text-red-500">Could not load user profile information.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
