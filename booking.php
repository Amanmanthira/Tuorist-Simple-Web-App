<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch guides
$guides = $conn->query("SELECT id, name, language, description FROM guides");
$guideData = [];
while ($g = $guides->fetch_assoc()) {
    $guideData[] = $g;
}

// Fetch hotels
$hotels = $conn->query("SELECT id, name, province, location, stars, price_min, price_max, image, description FROM hotels");
$hotelData = [];
while ($h = $hotels->fetch_assoc()) {
    $hotelData[] = $h;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Book a Tour</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f4f7fa;
      margin: 0;
      padding: 2rem;
    }
    h2 {
      text-align: center;
      color: #004d99;
      margin-bottom: 2rem;
    }
    .booking-form {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 2rem 2.5rem;
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      font-weight: 600;
      margin-top: 1.5rem;
      color: #333;
    }
    input, select {
      width: 100%;
      padding: 0.6rem;
      margin-top: 0.4rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }
    button, .open-modal-btn {
      background: #0066cc;
      color: #fff;
      padding: 0.8rem 1.2rem;
      font-size: 1rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      margin-top: 1rem;
      font-weight: 600;
      transition: background-color 0.25s ease;
    }
    button:hover, .open-modal-btn:hover {
      background: #004999;
    }
    .logout-link {
      text-align: center;
      margin-top: 2rem;
    }

    /* Modern Card Styles */
    .card {
      background: linear-gradient(145deg, #ffffff, #e6f0ff);
      border-radius: 16px;
      box-shadow:
        6px 6px 12px #becfea,
        -6px -6px 12px #ffffff;
      padding: 1.5rem;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.9rem;
    }
    .card:hover {
      transform: translateY(-8px);
      box-shadow:
        8px 8px 16px #a6b5d8,
        -8px -8px 16px #ffffff;
    }
    .card img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 8px 15px rgba(0, 102, 204, 0.3);
      transition: transform 0.3s ease;
    }
    .card:hover img {
      transform: scale(1.05);
    }
    .card h4 {
      font-weight: 700;
      font-size: 1.25rem;
      color: #003366;
      margin: 0;
    }
    .card p {
      font-size: 0.95rem;
      color: #4a4a4a;
      margin: 0;
      line-height: 1.3;
    }
    .card small {
      font-weight: 600;
      color: #0077cc;
      margin-left: 6px;
    }
    .select-btn {
      background: #0077cc;
      color: #fff;
      padding: 0.55rem 1.4rem;
      border-radius: 28px;
      font-weight: 600;
      font-size: 1rem;
      box-shadow: 0 4px 12px rgb(0 119 204 / 0.45);
      user-select: none;
      border: none;
      outline: none;
      transition: background-color 0.25s ease, box-shadow 0.25s ease;
      margin-top: auto;
      align-self: center;
    }
    .select-btn:hover {
      background-color: #005fa3;
      box-shadow: 0 6px 18px rgb(0 95 163 / 0.6);
    }

    /* Summary preview tweaks */
    .summary {
      background: #cce4ff;
      border-radius: 12px;
      padding: 1rem 1.3rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15);
      margin-top: 1rem;
    }
    .summary img {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      box-shadow: 0 4px 12px rgba(0, 102, 204, 0.35);
      object-fit: cover;
    }
    .summary h4 {
      font-size: 1.15rem;
      font-weight: 700;
      color: #004080;
      margin: 0;
    }
    .summary p {
      margin: 4px 0 0;
      font-size: 0.95rem;
      color: #0059b3;
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
      overflow-y: auto;
      padding: 2rem 1rem;
    }
    .modal-content {
      background: #fff;
      padding: 2rem;
      border-radius: 14px;
      max-width: 90%;
      width: 900px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      border-bottom: 1px solid #ddd;
      padding-bottom: 0.5rem;
    }
    .modal-header h3 {
      margin: 0;
      font-size: 1.3rem;
      color: #004d99;
    }
    .close-btn {
      font-size: 1.8rem;
      color: #666;
      cursor: pointer;
      font-weight: 600;
      transition: color 0.2s ease;
    }
    .close-btn:hover {
      color: #004999;
    }
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px,1fr));
      gap: 1.5rem;
    }

    @media (max-width: 600px) {
      .card-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

<h2>Book Your Guide & Hotel</h2>
<div class="booking-form">
  <form action="submit_booking.php" method="POST">

    <!-- Guide -->
    <label>Choose a Guide:</label>
    <div id="guide-summary-container"></div>
    <button type="button" class="open-modal-btn" onclick="openModal('guide')">Select Guide</button>
    <input type="hidden" name="guide_id" id="guide_id" required>

    <!-- Hotel -->
    <label>Choose a Hotel:</label>
    <div id="hotel-summary-container"></div>
    <button type="button" class="open-modal-btn" onclick="openModal('hotel')">Select Hotel</button>
    <input type="hidden" name="hotel_id" id="hotel_id" required>

    <!-- Rest of the form -->
    <label for="room_type">Room Type:</label>
    <select name="room_type" required>
      <option value="normal">Normal Room (with all meals)</option>
      <option value="luxury">Luxury Room (with all meals)</option>
    </select>

    <label for="tour_type">Tour Type:</label>
    <select name="tour_type" required>
      <option value="walking">Walking Tour</option>
      <option value="museum">Museum Visit</option>
      <option value="cultural">Cultural Experience</option>
    </select>

    <label for="people_count">How Many People:</label>
    <input type="number" name="people_count" min="1" required />

    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" required />

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" required />

    <button type="submit">Confirm Booking</button>
  </form>
</div>

<p class="logout-link"><a href="my_bookings.php">View Your Bookings</a></p>

<!-- Guide Modal -->
<div id="modal-guide" class="modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-guide-title">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modal-guide-title">Select a Guide</h3>
      <span class="close-btn" onclick="closeModal('guide')" aria-label="Close">&times;</span>
    </div>
    <div class="card-grid" id="guide-cards-container">
      <?php foreach ($guideData as $guide): ?>
        <div class="card" tabindex="0" role="button" onclick="selectGuide(<?= $guide['id'] ?>)" onkeypress="if(event.key==='Enter'){selectGuide(<?= $guide['id'] ?>)}" aria-label="Select guide <?= htmlspecialchars($guide['name']) ?>">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($guide['name']) ?>&background=0077cc&color=fff&size=128" alt="Guide <?= htmlspecialchars($guide['name']) ?>" />
          <h4><?= htmlspecialchars($guide['name']) ?></h4>
          <p><strong>Languages:</strong> <?= htmlspecialchars($guide['language']) ?></p>
          <p><?= htmlspecialchars($guide['description']) ?></p>
          <button type="button" class="select-btn" onclick="event.stopPropagation(); selectGuide(<?= $guide['id'] ?>)">Choose</button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Hotel Modal -->
<div id="modal-hotel" class="modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-hotel-title">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modal-hotel-title">Select a Hotel</h3>
      <span class="close-btn" onclick="closeModal('hotel')" aria-label="Close">&times;</span>
    </div>
    <div class="card-grid" id="hotel-cards-container">
      <?php foreach ($hotelData as $hotel): ?>
        <div class="card" tabindex="0" role="button" onclick="selectHotel(<?= $hotel['id'] ?>)" onkeypress="if(event.key==='Enter'){selectHotel(<?= $hotel['id'] ?>)}" aria-label="Select hotel <?= htmlspecialchars($hotel['name']) ?>">
          <img src="<?= htmlspecialchars($hotel['image'] ?: 'https://via.placeholder.com/128') ?>" alt="Hotel <?= htmlspecialchars($hotel['name']) ?>" />
          <h4><?= htmlspecialchars($hotel['name']) ?></h4>
          <p><strong>Location:</strong> <?= htmlspecialchars($hotel['location']) ?>, <?= htmlspecialchars($hotel['province']) ?></p>
          <p><strong>Stars:</strong> <?= intval($hotel['stars']) ?> ⭐</p>
          <p><strong>Price:</strong> <?= number_format($hotel['price_min']) ?>LKR - <?= number_format($hotel['price_max']) ?>LKR</p>
          <p><?= htmlspecialchars($hotel['description']) ?></p>
          <button type="button" class="select-btn" onclick="event.stopPropagation(); selectHotel(<?= $hotel['id'] ?>)">Choose</button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
  function openModal(type) {
    document.getElementById('modal-' + type).style.display = 'flex';
    document.getElementById('modal-' + type).focus();
  }

  function closeModal(type) {
    document.getElementById('modal-' + type).style.display = 'none';
  }

  // Guide data from PHP - minimal info for summary lookup
  const guides = <?= json_encode($guideData) ?>;
  const hotels = <?= json_encode($hotelData) ?>;

  function selectGuide(id) {
    const guide = guides.find(g => g.id == id);
    if (!guide) return;

    // Set hidden input
    document.getElementById('guide_id').value = guide.id;

    // Show summary
    const container = document.getElementById('guide-summary-container');
    container.innerHTML = `
      <div class="summary" role="region" aria-live="polite" aria-label="Selected guide">
        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(guide.name)}&background=0077cc&color=fff&size=128" alt="Guide ${guide.name}" />
        <div>
          <h4>${guide.name}</h4>
          <p><strong>Languages:</strong> ${guide.language}</p>
          <p>${guide.description}</p>
        </div>
      </div>
    `;

    closeModal('guide');
  }

  function selectHotel(id) {
    const hotel = hotels.find(h => h.id == id);
    if (!hotel) return;

    document.getElementById('hotel_id').value = hotel.id;

    const container = document.getElementById('hotel-summary-container');
    container.innerHTML = `
      <div class="summary" role="region" aria-live="polite" aria-label="Selected hotel">
        <img src="${hotel.image ? hotel.image : 'https://via.placeholder.com/128'}" alt="Hotel ${hotel.name}" />
        <div>
          <h4>${hotel.name}</h4>
          <p><strong>Location:</strong> ${hotel.location}, ${hotel.province}</p>
          <p><strong>Stars:</strong> ${hotel.stars} ⭐</p>
          <p><strong>Price:</strong> ₹${hotel.price_min.toLocaleString()} - ₹${hotel.price_max.toLocaleString()}</p>
          <p>${hotel.description}</p>
        </div>
      </div>
    `;

    closeModal('hotel');
  }

  // Close modal on clicking outside content
  window.onclick = function(event) {
    ['guide','hotel'].forEach(type => {
      const modal = document.getElementById('modal-' + type);
      if (event.target === modal) {
        closeModal(type);
      }
    });
  };

  // Accessibility: close modal on Escape key
  window.addEventListener('keydown', function(event) {
    if(event.key === "Escape") {
      ['guide','hotel'].forEach(type => closeModal(type));
    }
  });
</script>

</body>
</html>
