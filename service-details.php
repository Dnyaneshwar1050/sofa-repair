<?php
require_once __DIR__ . '/includes/db.php';

$serviceId = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (!$serviceId) {
    header("Location: /services.php");
    exit;
}

$stmt = $pdo->prepare("SELECT s.*, c.name as category_name FROM services s LEFT JOIN categories c ON s.category_id = c.id WHERE s.id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: /services.php");
    exit;
}

// Handle Direct Booking Request (simplified for PHP)
$bookingSuccess = false;
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service'])) {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }

    $scheduledDate = $_POST['scheduled_date'] ?? '';
    $address = $_POST['address'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (empty($scheduledDate) || empty($address)) {
        $bookingError = "Date and address are required.";
    } else {
        $userId = $_SESSION['user_id'];
        $stmtBook = $pdo->prepare("INSERT INTO bookings (user_id, service_id, scheduled_date, address, total_amount, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        if ($stmtBook->execute([$userId, $service->id, $scheduledDate, $address, $service->base_price, $notes])) {
            $bookingSuccess = true;
        } else {
            $bookingError = "Failed to create booking.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-6xl mx-auto px-4">

        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6 flex items-center space-x-2">
            <a href="/" class="hover:text-orange-600">Home</a>
            <span>/</span>
            <a href="/services.php?category_id=<?= $service->category_id ?>" class="hover:text-orange-600">
                <?= htmlspecialchars($service->category_name) ?>
            </a>
            <span>/</span>
            <span class="text-gray-900 font-medium">
                <?= htmlspecialchars($service->name) ?>
            </span>
        </nav>

        <?php if ($bookingSuccess): ?>
            <div
                class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6 flex items-center justify-between shadow-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check text-green-500 mr-2 text-xl"></i>
                    <div>
                        <h4 class="font-bold">Booking Successful!</h4>
                        <p class="text-sm">We will contact you shortly to confirm.</p>
                    </div>
                </div>
                <a href="/my-bookings.php" class="text-green-800 font-semibold underline">View Bookings</a>
            </div>
        <?php endif; ?>

        <?php if ($bookingError): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6 flex items-center shadow-sm">
                <i class="fa-solid fa-circle-exclamation mr-2 text-xl"></i>
                <p>
                    <?= htmlspecialchars($bookingError) ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Service Details (Left 2/3) -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <img src="/frontend/public/<?= htmlspecialchars($service->image) ?>"
                        alt="<?= htmlspecialchars($service->name) ?>" class="w-full h-80 object-cover"
                        onerror="this.src='/frontend/public/default-service.png'">
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h1 class="text-3xl font-black text-gray-900 mb-2">
                                    <?= htmlspecialchars($service->name) ?>
                                </h1>
                                <span
                                    class="bg-orange-100 text-orange-800 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide">
                                    <?= htmlspecialchars($service->category_name) ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-gray-500 text-sm font-medium mb-1">Starting Price</p>
                                <p class="text-3xl font-bold text-orange-600">₹
                                    <?= number_format($service->base_price, 2) ?>
                                </p>
                            </div>
                        </div>

                        <div class="prose max-w-none text-gray-700">
                            <h3 class="text-xl font-bold mb-4">Service Description</h3>
                            <p class="whitespace-pre-line leading-relaxed">
                                <?= htmlspecialchars($service->description) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form (Right 1/3) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <i class="fa-regular fa-calendar-check text-orange-600"></i> Book Service
                    </h3>

                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="service-details.php?id=<?= $service->id ?>" class="space-y-4">
                            <input type="hidden" name="book_service" value="1">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Date & Time</label>
                                <input type="datetime-local" name="scheduled_date" required
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Service Address</label>
                                <textarea name="address" required rows="3" placeholder="Enter your full address"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes
                                    (Optional)</label>
                                <textarea name="notes" rows="2" placeholder="Any specific requirements?"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full bg-orange-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-orange-700 transition-all shadow-md active:scale-95 flex items-center justify-center gap-2 mt-4">
                                Confirm Booking <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fa-regular fa-user-circle text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600 mb-6">Please log in to book this service.</p>
                            <a href="/login.php"
                                class="inline-block bg-blue-600 text-white font-bold py-3 px-8 rounded-xl hover:bg-blue-700 transition-colors shadow-md">
                                Login to Book
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>