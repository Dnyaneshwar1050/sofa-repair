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

// Parse service options
$serviceOptions = [];
try {
    $serviceOptions = json_decode($service->service_options ?? '[]', true) ?: [];
} catch (Exception $e) {}

// Fetch reviews for this service
$reviewStmt = $pdo->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.service_id = ? 
    ORDER BY r.created_at DESC
");
$reviewStmt->execute([$serviceId]);
$reviews = $reviewStmt->fetchAll();

// Calculate review statistics
$totalReviews = count($reviews);
$avgRating = 0;
$ratingBreakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
if ($totalReviews > 0) {
    $sumRating = 0;
    foreach ($reviews as $r) {
        $sumRating += $r->rating;
        $ratingBreakdown[$r->rating] = ($ratingBreakdown[$r->rating] ?? 0) + 1;
    }
    $avgRating = round($sumRating / $totalReviews, 1);
}

// Check if user has a completed booking (for "Verified Purchase")
$userHasBooking = false;
if (isLoggedIn()) {
    $bStmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND service_id = ? AND status = 'completed' LIMIT 1");
    $bStmt->execute([$_SESSION['user_id'], $serviceId]);
    $userHasBooking = (bool) $bStmt->fetch();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }
    $rating = (int) ($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');
    
    // For review, we need a booking_id. Use 0 or find one.
    $bookingId = 0;
    if (isLoggedIn()) {
        $bkStmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND service_id = ? LIMIT 1");
        $bkStmt->execute([$_SESSION['user_id'], $serviceId]);
        $bk = $bkStmt->fetch();
        if ($bk) $bookingId = $bk->id;
    }
    
    try {
        $insReview = $pdo->prepare("INSERT INTO reviews (user_id, service_id, booking_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $insReview->execute([$_SESSION['user_id'], $serviceId, $bookingId, $rating, $comment]);
        header("Location: /service-details.php?id=$serviceId#reviews");
        exit;
    } catch (Exception $e) {
        // silently fail
    }
}

// Handle booking request
$bookingSuccess = false;
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service'])) {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }

    $phone = $_POST['phone'] ?? '';
    $house_no = $_POST['house_no'] ?? '';
    $street = $_POST['street'] ?? '';
    $landmark = $_POST['landmark'] ?? '';
    $area = $_POST['area'] ?? '';
    $city = $_POST['city'] ?? '';
    $pincode = $_POST['pincode'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $selected_package = $_POST['selected_package'] ?? 'Basic';

    $fullAddress = trim("$house_no, $street, $landmark, $area, $city - $pincode");
    
    if (empty($house_no) || empty($area) || empty($city) || empty($pincode)) {
        $bookingError = "Please fill in all required address fields.";
    } else {
        $userId = $_SESSION['user_id'];
        $totalAmount = $service->base_price;
        
        // Find matching package price if any
        foreach ($serviceOptions as $opt) {
            if ($opt['name'] === $selected_package) {
                $totalAmount = $service->base_price + ($opt['price_diff'] ?? 0);
                break;
            }
        }
        
        try {
            $stmtBook = $pdo->prepare("INSERT INTO bookings (user_id, service_id, scheduled_date, address, total_amount, notes, status) VALUES (?, ?, NOW(), ?, ?, ?, 'pending')");
            if ($stmtBook->execute([$userId, $service->id, $fullAddress, $totalAmount, "Package: $selected_package. Budget: ₹$budget. Phone: $phone. $notes"])) {
                $bookingSuccess = true;
            } else {
                $bookingError = "Failed to create booking request.";
            }
        } catch (PDOException $e) {
            $bookingError = "Booking error: " . $e->getMessage();
        }
    }
}

// Get user phone if logged in
$userPhone = '';
if (isLoggedIn()) {
    $uStmt = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
    $uStmt->execute([$_SESSION['user_id']]);
    $uData = $uStmt->fetch();
    if ($uData) $userPhone = $uData->phone ?? '';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-6">

        <!-- Back to Category -->
        <a href="/services.php?category_id=<?= $service->category_id ?>" 
           class="inline-flex items-center text-orange-600 font-semibold text-sm mb-6 hover:text-orange-700 transition">
            <i class="fa-solid fa-chevron-left mr-2"></i> Back to <?= htmlspecialchars($service->category_name) ?>
        </a>

        <?php if ($bookingSuccess): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6 flex items-center justify-between shadow-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check text-green-500 mr-3 text-xl"></i>
                    <div>
                        <h4 class="font-bold">Booking Request Submitted!</h4>
                        <p class="text-sm">Our team will contact you shortly with a detailed quote.</p>
                    </div>
                </div>
                <a href="/my-bookings.php" class="text-green-800 font-semibold underline text-sm">View Bookings</a>
            </div>
        <?php endif; ?>

        <?php if ($bookingError): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6 flex items-center shadow-sm">
                <i class="fa-solid fa-circle-exclamation mr-3 text-xl"></i>
                <p><?= htmlspecialchars($bookingError) ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            <!-- Left Column (3/5) -->
            <div class="lg:col-span-3 space-y-8">
                
                <!-- Service Header -->
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($service->name) ?></h1>
                    <a href="/services.php?category_id=<?= $service->category_id ?>" 
                       class="text-orange-600 font-medium text-sm hover:underline"><?= htmlspecialchars($service->category_name) ?></a>
                    
                    <!-- Star Rating -->
                    <?php if ($totalReviews > 0): ?>
                        <div class="flex items-center gap-2 mt-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($avgRating)): ?>
                                    <i class="fa-solid fa-star text-yellow-400 text-sm"></i>
                                <?php elseif ($i - $avgRating < 1): ?>
                                    <i class="fa-solid fa-star-half-stroke text-yellow-400 text-sm"></i>
                                <?php else: ?>
                                    <i class="fa-regular fa-star text-gray-300 text-sm"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span class="text-gray-600 text-sm"><?= $avgRating ?> (<?= $totalReviews ?> reviews)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Service Image -->
                <div class="rounded-lg overflow-hidden">
                    <img src="/frontend/public/<?= htmlspecialchars($service->image) ?>"
                         alt="<?= htmlspecialchars($service->name) ?>" 
                         class="w-full h-80 object-cover"
                         onerror="this.onerror=null;this.src='/frontend/public/default-service.png'">
                </div>

                <!-- Description -->
                <p class="text-gray-600 leading-relaxed"><?= htmlspecialchars($service->description) ?></p>

                <!-- Divider -->
                <hr class="border-gray-200">

                <!-- Select a Package -->
                <?php if (!empty($serviceOptions)): ?>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Select a Package</h2>
                    <div class="space-y-4">
                        <?php foreach ($serviceOptions as $opt): ?>
                            <div class="border border-gray-200 rounded-lg p-5 flex justify-between items-start hover:border-orange-300 transition">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($opt['name']) ?></h3>
                                    <?php if (!empty($opt['description'])): ?>
                                        <ul class="mt-1 text-gray-600 text-sm list-disc pl-5">
                                            <li><?= htmlspecialchars($opt['description']) ?></li>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right shrink-0 ml-4">
                                    <?php if (($opt['price_diff'] ?? 0) > 0): ?>
                                        <span class="text-orange-600 font-bold">₹<?= number_format($service->base_price + $opt['price_diff'], 0) ?></span>
                                    <?php else: ?>
                                        <span class="text-orange-600 font-semibold text-sm">Contact for Pricing</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <hr class="border-gray-200">
                <?php endif; ?>

                <!-- Reviews & Ratings Section -->
                <div id="reviews">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Reviews &amp; Ratings</h2>
                        <?php if (isLoggedIn()): ?>
                            <button onclick="document.getElementById('review-modal').classList.remove('hidden')"
                                    class="bg-gray-900 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover:bg-gray-800 transition">
                                Write a Review
                            </button>
                        <?php else: ?>
                            <a href="/login.php" class="bg-gray-900 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover:bg-gray-800 transition">
                                Write a Review
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Rating Breakdown -->
                    <?php if ($totalReviews > 0): ?>
                    <div class="flex items-start gap-8 mb-8">
                        <!-- Overall Rating -->
                        <div class="text-center">
                            <div class="flex items-center gap-1 mb-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($avgRating)): ?>
                                        <i class="fa-solid fa-star text-yellow-400"></i>
                                    <?php elseif ($i - $avgRating < 1): ?>
                                        <i class="fa-solid fa-star-half-stroke text-yellow-400"></i>
                                    <?php else: ?>
                                        <i class="fa-regular fa-star text-gray-300"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="text-3xl font-bold text-gray-900"><?= $avgRating ?></span>
                            <p class="text-gray-500 text-sm"><?= $totalReviews ?> reviews</p>
                        </div>

                        <!-- Bar Breakdown -->
                        <div class="flex-1 space-y-1.5">
                            <?php for ($star = 5; $star >= 1; $star--): ?>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-4 text-right font-medium text-gray-700"><?= $star ?></span>
                                    <i class="fa-solid fa-star text-yellow-400 text-xs"></i>
                                    <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                        <?php $pct = $totalReviews > 0 ? ($ratingBreakdown[$star] / $totalReviews) * 100 : 0; ?>
                                        <div class="bg-yellow-400 h-full rounded-full" style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="w-8 text-gray-500 text-xs"><?= $ratingBreakdown[$star] ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Reviews List -->
                    <?php if ($totalReviews > 0): ?>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-800">Reviews (<?= $totalReviews ?>)</h3>
                            <select class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm text-gray-600 focus:ring-2 focus:ring-orange-500 outline-none">
                                <option>Newest</option>
                                <option>Highest Rated</option>
                                <option>Lowest Rated</option>
                            </select>
                        </div>
                        <div class="space-y-0 divide-y divide-gray-100">
                            <?php foreach ($reviews as $review): ?>
                                <div class="py-5">
                                    <div class="flex items-start gap-3">
                                        <!-- Avatar -->
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                            <?= strtoupper(substr($review->user_name ?? 'A', 0, 1)) ?>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-semibold text-gray-900"><?= htmlspecialchars($review->user_name ?? 'Anonymous') ?></span>
                                                <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full">
                                                    <i class="fa-solid fa-circle-check text-green-500 text-[10px]"></i> Verified Purchase
                                                </span>
                                            </div>
                                            <!-- Stars and Date -->
                                            <div class="flex items-center gap-2 mt-1">
                                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                                    <i class="fa-solid fa-star text-<?= $s <= $review->rating ? 'yellow-400' : 'gray-300' ?> text-xs"></i>
                                                <?php endfor; ?>
                                                <span class="text-gray-400 text-xs"><?= date('F j, Y', strtotime($review->created_at)) ?></span>
                                            </div>
                                            <?php if (!empty($review->comment)): ?>
                                                <p class="text-gray-700 mt-2 leading-relaxed text-sm"><?= htmlspecialchars($review->comment) ?></p>
                                            <?php endif; ?>
                                            <!-- Helpful -->
                                            <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                                                <span>Was this helpful?</span>
                                                <button class="hover:text-gray-600 transition"><i class="fa-regular fa-thumbs-up mr-1"></i><?= $review->helpfulness_votes ?? 0 ?></button>
                                                <button class="hover:text-gray-600 transition"><i class="fa-regular fa-thumbs-down mr-1"></i>0</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 text-gray-400">
                            <i class="fa-regular fa-comments text-4xl mb-3 block"></i>
                            <p>No reviews yet. Be the first to review this service!</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Column - Booking Summary (2/5) -->
            <div class="lg:col-span-2">
                <div class="bg-white border-2 border-orange-400 rounded-xl p-6 sticky top-24 shadow-sm">
                    <h3 class="text-xl font-bold text-orange-600 mb-4">Booking Summary</h3>
                    
                    <p class="text-orange-600 font-semibold text-sm mb-1">Contact for Pricing</p>
                    
                    <?php if (!empty($serviceOptions)): ?>
                        <p class="font-semibold text-gray-800 mb-1">Package: <?= htmlspecialchars($serviceOptions[0]['name'] ?? 'Basic') ?></p>
                    <?php endif; ?>
                    
                    <p class="text-gray-600 text-sm mb-1">Get a custom quote for your specific needs</p>
                    <p class="text-orange-500 text-xs italic mb-5">Our team will provide you with a detailed quote after understanding your requirements.</p>

                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="service-details.php?id=<?= $service->id ?>" class="space-y-4">
                            <input type="hidden" name="book_service" value="1">
                            <input type="hidden" name="selected_package" value="<?= htmlspecialchars($serviceOptions[0]['name'] ?? 'Basic') ?>">
                            
                            <h4 class="font-bold text-gray-900 text-sm">Confirm Details</h4>
                            
                            <!-- Phone -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Mobile Number (Override if different)</label>
                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-orange-500">
                                    <span class="bg-gray-50 px-3 py-2.5 text-gray-500 text-sm border-r border-gray-300">+91</span>
                                    <input type="tel" name="phone" value="<?= htmlspecialchars($userPhone) ?>" 
                                           class="flex-1 px-3 py-2.5 text-sm outline-none" placeholder="Your phone number">
                                </div>
                                <p class="text-xs text-gray-400 mt-1">You can use a different phone number for this booking if needed.</p>
                            </div>

                            <!-- Service Address -->
                            <fieldset class="border border-gray-200 rounded-lg p-4 space-y-3">
                                <legend class="font-bold text-gray-900 text-sm px-2">Service Address</legend>
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="text" name="house_no" placeholder="House/Flat No. *" required
                                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                    <input type="text" name="street" placeholder="Street / Road"
                                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                </div>
                                <input type="text" name="landmark" placeholder="Landmark (Optional)"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                <input type="text" name="area" placeholder="Area / Locality *" required
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="text" name="city" placeholder="City *" required
                                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                    <input type="text" name="pincode" placeholder="Pincode *" required
                                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                </div>
                            </fieldset>

                            <!-- Budget -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Your Estimated Budget (₹)</label>
                                <input type="text" name="budget" placeholder="Min <?= number_format($service->base_price, 0) ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                            </div>

                            <!-- Notes -->
                            <div>
                                <textarea name="notes" rows="3" placeholder="Notes (e.g., preferred time, entry instructions)"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 outline-none resize-y"></textarea>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                    class="w-full bg-gray-900 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition shadow-md text-sm">
                                Request Quote & Schedule Pickup
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fa-regular fa-user-circle text-4xl text-gray-300 mb-4 block"></i>
                            <p class="text-gray-600 mb-4 text-sm">Please log in to book this service.</p>
                            <a href="/login.php"
                               class="inline-block bg-orange-600 text-white font-bold py-3 px-8 rounded-xl hover:bg-orange-700 transition shadow-md text-sm">
                                Login to Book
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Write Review Modal -->
<?php if (isLoggedIn()): ?>
<div id="review-modal" class="hidden fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center px-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Write a Review</h3>
            <button onclick="document.getElementById('review-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="service-details.php?id=<?= $service->id ?>#reviews">
            <input type="hidden" name="submit_review" value="1">
            
            <!-- Star Rating -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Rating</label>
                <div class="flex gap-2" id="star-selector">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" onclick="setRating(<?= $i ?>)" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition" data-star="<?= $i ?>">
                            <i class="fa-solid fa-star"></i>
                        </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="rating-input" value="5">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
                <textarea name="comment" rows="4" placeholder="Share your experience..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 outline-none"></textarea>
            </div>

            <button type="submit" class="w-full bg-orange-600 text-white py-2.5 rounded-lg font-bold hover:bg-orange-700 transition">
                Submit Review
            </button>
        </form>
    </div>
</div>

<script>
function setRating(val) {
    document.getElementById('rating-input').value = val;
    document.querySelectorAll('.star-btn').forEach((btn, idx) => {
        btn.querySelector('i').className = idx < val ? 'fa-solid fa-star text-yellow-400' : 'fa-solid fa-star text-gray-300';
    });
}
// Initialize with 5 stars selected
document.addEventListener('DOMContentLoaded', () => setRating(5));
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>