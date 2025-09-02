<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';

$contact_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message_content = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    $errors = [];

    if (empty($name)) {
        $errors['name'] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (empty($subject)) {
        $errors['subject'] = 'Subject is required.';
    }
    if (empty($message_content)) {
        $errors['message'] = 'Message content is required.';
    }

    if (empty($errors)) {
        // In a real application, you would send this email.
        // For now, we'll just simulate success.
        // Example: mail('admin@cinemaxpro.com', $subject, $message_content, "From: $email");
        $contact_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <strong class="font-bold">Success!</strong>
                                <span class="block sm:inline">Your message has been sent successfully. We will get back to you shortly.</span>
                            </div>';
    } else {
        $contact_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <strong class="font-bold">Error!</strong>
                                <span class="block sm:inline">Please correct the following errors: ' . implode(' ', $errors) . '</span>
                            </div>';
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Contact Us
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                We'd love to hear from you! Send us a message.
            </p>
        </div>

        <?php echo $contact_message; ?>

        <form class="mt-8 space-y-6" action="contact.php" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="name" class="sr-only">Your Name</label>
                    <input id="name" name="name" type="text" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['name']) ? 'border-red-500' : ''; ?>"
                           placeholder="Your Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    <?php if (isset($errors['name'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['email']) ? 'border-red-500' : ''; ?>"
                           placeholder="Email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="subject" class="sr-only">Subject</label>
                    <input id="subject" name="subject" type="text" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['subject']) ? 'border-red-500' : ''; ?>"
                           placeholder="Subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                    <?php if (isset($errors['subject'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['subject']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="message" class="sr-only">Your Message</label>
                    <textarea id="message" name="message" rows="4" required
                              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['message']) ? 'border-red-500' : ''; ?>"
                              placeholder="Your Message"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['message']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
