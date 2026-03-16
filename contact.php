<?php
require_once __DIR__ . '/includes/db.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Name, email, and message are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
        if ($stmt->execute([$name, $email, $subject, $message])) {
            $success = true;
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">

        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Contact Us</h1>
            <p class="text-lg text-gray-600">Have a question or need a quote? We're here to help.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="md:flex">
                <!-- Contact Info Side -->
                <div class="bg-gray-900 text-white p-8 md:w-1/3 flex flex-col justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-6 text-orange-500">Get in Touch</h3>

                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <i class="fa-solid fa-location-dot mt-1 text-orange-500 text-xl"></i>
                                <div>
                                    <h4 class="font-semibold mb-1">Our Location</h4>
                                    <p class="text-gray-400 text-sm">Paradise Heights, Back Gate Swami Narayan Temple,
                                        Narhe, Pune 411041</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <i class="fa-solid fa-phone mt-1 text-orange-500 text-xl"></i>
                                <div>
                                    <h4 class="font-semibold mb-1">Phone Number</h4>
                                    <a href="tel:+919689861811"
                                        class="text-gray-400 text-sm hover:text-white transition-colors">+919689861811</a>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <i class="fa-solid fa-envelope mt-1 text-orange-500 text-xl"></i>
                                <div>
                                    <h4 class="font-semibold mb-1">Email Address</h4>
                                    <a href="mailto:info@silvafurniture.com"
                                        class="text-gray-400 text-sm hover:text-white transition-colors break-all">info@silvafurniture.com</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form Side -->
                <div class="p-8 md:w-2/3">
                    <h3 class="text-2xl font-bold mb-6 text-gray-900">Send us a Message</h3>

                    <?php if ($success): ?>
                        <div
                            class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6 flex items-center shadow-sm">
                            <i class="fa-solid fa-circle-check mr-2 text-xl block"></i>
                            <p>Thank you! Your message has been sent successfully. We will get back to you soon.</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div
                            class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6 flex items-center shadow-sm">
                            <i class="fa-solid fa-circle-exclamation mr-2 text-xl block"></i>
                            <p>
                                <?= htmlspecialchars($error) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
                                <input type="text" name="name" required
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Email *</label>
                                <input type="email" name="email" required
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" name="subject"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Your Message *</label>
                            <textarea name="message" required rows="5"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none"></textarea>
                        </div>

                        <button type="submit"
                            class="bg-orange-600 text-white font-bold py-3 px-8 rounded-xl hover:bg-orange-700 transition-colors shadow-md flex items-center gap-2">
                            Send Message <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Map embedded -->
        <div class="mt-12 rounded-2xl overflow-hidden shadow-sm border border-gray-100 h-96">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15139.000305417072!2d73.81844075!3d18.4497063!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc295d97f26dce3%3A0xe1db0bc706f9c97b!2sKhushi%20Home%20Sofa%20Repairing!5e0!3m2!1sen!2sin!4v1709400000000!5m2!1sen!2sin"
                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>