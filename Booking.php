<?php
// config - database connection
$host = "db4free.net";
$user = "santoshkumar1234";
$pass = "santosh1234";
$dbname = "santoshkumar1234";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$timeSlotMap = [
    '6am-8am' => 'slot_6_8am',
    '8am-10am' => 'slot_8_10am',
    '10am-12pm' => 'slot_10_12pm',
    '12pm-2pm' => 'slot_12_2pm',
    '2pm-4pm' => 'slot_2_4pm',
    '4pm-6pm' => 'slot_4_6pm',
    '6pm-8pm' => 'slot_6_8pm',
    '8pm-10pm' => 'slot_8_10pm'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tool = $_POST['equipment'];
    $slot = $_POST['slot'];
    $column = $timeSlotMap[$slot];

    $check = $conn->prepare("SELECT * FROM equipment_bookings WHERE equipment_name = ?");
    $check->bind_param("s", $tool);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row[$column] === 'booked') {
            echo "already_booked";
        } else {
            $stmt = $conn->prepare("UPDATE equipment_bookings SET $column = 'booked' WHERE equipment_name = ?");
            $stmt->bind_param("s", $tool);
            $stmt->execute();
            echo "booked";
        }
    } else {
        $cols = "equipment_name, $column";
        $vals = "'$tool', 'booked'";
        foreach ($timeSlotMap as $key => $col) {
            if ($col !== $column) {
                $cols .= ", $col";
                $vals .= ", 'available'";
            }
        }
        $sql = "INSERT INTO equipment_bookings ($cols) VALUES ($vals)";
        $conn->query($sql);
        echo "booked";
    }
    exit();
}

$equipmentData = [];
$sql = "SELECT * FROM equipment_bookings";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $equipmentData[$row['equipment_name']] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Gym Equipment Booking</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: Arial, sans-serif;
      background-color: rgb(195, 195, 195);
      padding-top: 80px;
    }

    nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background-color: #333;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 999;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    nav h1 {
      font-size: 22px;
    }

    #searchInput {
      padding: 8px 12px;
      border-radius: 6px;
      border: none;
      font-size: 14px;
      width: 200px;
    }

    h1.main-title {
      text-align: center;
      margin: 20px 0;
      color: #333;
    }

    #equipmentContainer {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
    }

    .equipment {
  width: 250px;
  height: 200px;
  margin: 10px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 0 15px 5px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  background-color: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: start;
}

.outer-box {
  width: 300px;
  height: 100px;
  border: 2px solid #000;
  padding: 10px;
}

.image-box {
  width: 50%;
  height: 20%;
  overflow: hidden;
  border-radius: 10px;
}

.image-box img {
  width: 300px;
  height: 100%;
  object-fit: cover;
  display: block;
}

.outer-image-box {
  width: 100%;
  height: 150px;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  background-color: #f5f5f5;
}

.inner-image-box {
  width: 100%;
  height: 100%;
}

.inner-image-box img {
  width: 100%;
  height: 100%;
  object-fit: contain; /* Show the full image */
  background-color: white;
  border-radius: 6px;
  display: block;
}


    .equipment-content {
      
      text-align: center;
    }

    .equipment h3 {
      margin: 10px 0;
      font-size: 16px;
      color: #333;
    }

    .book-btn {
      padding: 6px 10px;
      background: linear-gradient(to right, #4f4f4f, #3e3e3e);
      
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }

    .book-btn:hover {
      background-color: rgba(41, 128, 185, 0.9);
    }

    #timeSlots {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background-color: #333;
      padding: 20px;
      display: none;
      z-index: 10;
    }

    #equipmentTitle {
      margin: 0 0 10px;
      font-size: 18px;
      color: #fff;
      text-align: center;
    }

    #slotsContainer {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }

    .slot {
      width: 100px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      font-size: 13px;
      cursor: pointer;
      color: white;
    }

    .available {
      background-color: rgb(78, 78, 78);
      text-align: center;
    }

    .booked {
      background-color: rgb(194, 87, 76); 
      pointer-events: none;
      text-align:center;
    }

    @media (max-width: 600px) {
      .equipment { width: 90%; height: auto; }
      .slot { width: 80px; height: 35px; font-size: 12px; }
      #searchInput { width: 120px; }
    }

    .logo {
      color: rgb(195, 195, 195);
      font-size: 24px;
      font-weight: bold;
    }
    .book-box {
  margin-top: 10px;
  text-align: center;
  width: 300px;
  height:50px;
  padding:15px;
  background: linear-gradient(to right, #444, #222);
  color: white;
  text-align: center;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.book-box:hover {
  background-color: #555;
}

  </style>
</head>
<body>

<nav>
  <div class="logo">Gym Booking</div>
  <input type="text" id="searchInput" placeholder="Search equipment...">
</nav>

<h1 class="main-title">Book Your Equipment</h1>
<div id="equipmentContainer"></div>

<div id="timeSlots">
  <h2 id="equipmentTitle"></h2>
  <div id="slotsContainer"></div>
</div>

<script>
const tools = [
  { name: 'Treadmill', img: 'data:image/jpeg;base64,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' },
  { name: 'Cycle', img: 'https://th.bing.com/th/id/OIP.OJmBbfWHJwe5HDyIHAUlQwHaHa?w=199&h=199&c=7&r=0&o=5&dpr=1.3&pid=1.7' },
  { name: 'Dumbbells', img: 'https://th.bing.com/th/id/OIP.d20PYGGFVFnnh0HdKG_M0QAAAA?rs=1&pid=ImgDetMain' },
  { name: 'Rowing Machine', img: 'https://fitnessfighters.co.uk/wp-content/uploads/2018/03/7141KnkoPnL._SL1500_.jpg' },
  { name: 'Elliptical', img: 'https://th.bing.com/th/id/OIP.YNvXcqT6KhZMXYrqGgBZcQHaHa?w=600&h=600&rs=1&pid=ImgDetMain' },
  { name: 'Stepper', img: 'https://th.bing.com/th/id/OIP.W7Qyiky3C8zQXwRvXyzL_gHaFi?w=2048&h=1530&rs=1&pid=ImgDetMain' },
  { name: 'Kettlebells', img: 'https://www.pngmart.com/files/7/Kettlebell-PNG-File-372x279.png' },
  { name: 'Punching Bag', img: 'https://th.bing.com/th/id/R.d6e77592959d5bdc485fe84ade98519d?rik=BTVooOq%2b6BkmFQ&riu=http%3a%2f%2fatlas-content-cdn.pixelsquid.com%2fstock-images%2fpunching-bag-8dEWav4-600.jpg&ehk=CCkDuKGvzhk6KJ5REeH0WK8bRpg5CpgiFSjoK242xhw%3d&risl=&pid=ImgRaw&r=0' },
  { name: 'Leg Press', img: 'https://nrgfitness.co.nz/wp-content/uploads/2016/12/45.png' },
  { name: 'Bench Press', img: 'https://graysfitness.com.au/wp-content/uploads/Life-Fitness-Flat-Bench-Press-4-600x440.jpg' },
  { name: 'Pull-up Bar', img: 'https://th.bing.com/th/id/OIP.LDITF9welqrsXsbHPEvkMQHaEK?rs=1&pid=ImgDetMain' },
  { name: 'Resistance Bands', img: 'https://www.shelf.guide/wp-content/uploads/2020/07/Resistance-Band-White-BG.jpg' },
  { name: 'Barbell', img: 'https://th.bing.com/th/id/OIP.d3YmGIWhXSg1ppdygAy64gHaD9?w=2048&h=1097&rs=1&pid=ImgDetMain' },
  { name: 'Cable Machine', img: 'https://th.bing.com/th/id/OIP.BenBkPJ-tUKU_dLhUDo7dQHaHa?w=480&h=480&rs=1&pid=ImgDetMain' }
];

const timeSlots = [
  '6am-8am','8am-10am','10am-12pm','12pm-2pm',
  '2pm-4pm','4pm-6pm','6pm-8pm','8pm-10pm'
];

const slotToColumn = {
  '6am-8am': 'slot_6_8am',
  '8am-10am': 'slot_8_10am',
  '10am-12pm': 'slot_10_12pm',
  '12pm-2pm': 'slot_12_2pm',
  '2pm-4pm': 'slot_2_4pm',
  '4pm-6pm': 'slot_4_6pm',
  '6pm-8pm': 'slot_6_8pm',
  '8pm-10pm': 'slot_8_10pm'
};

const bookedData = <?php echo json_encode($equipmentData); ?>;
const container = document.getElementById("equipmentContainer");

function renderTools(filter = "") {
  container.innerHTML = "";
  tools
    .filter(t => t.name.toLowerCase().includes(filter.toLowerCase()))
    .forEach(tool => {
      const div = document.createElement("div");
      div.className = "equipment";
      div.innerHTML = `
        <div class="outer-image-box">
          <div class="inner-image-box">
            <img src="${tool.img}" alt="${tool.name}">
          </div>
        </div>
        <div class="equipment-content">
          <h3>${tool.name}</h3>
          <div class="book-box" onclick="showTimeSlots('${tool.name}')">Click to Book</div>
        </div>
      `;
      container.appendChild(div);
    });
}

renderTools();

document.getElementById("searchInput").addEventListener("input", e => {
  renderTools(e.target.value);
});

function showTimeSlots(toolName) {
  document.getElementById('equipmentTitle').textContent = `Time Slots for ${toolName}`;
  const box = document.getElementById('timeSlots');
  const slotBox = document.getElementById('slotsContainer');
  box.style.display = 'block';
  slotBox.innerHTML = '';

  timeSlots.forEach(slot => {
    const div = document.createElement('div');
    div.classList.add('slot');
    const col = slotToColumn[slot];
    const isBooked = bookedData[toolName] && bookedData[toolName][col] === 'booked';
    div.classList.add(isBooked ? 'booked' : 'available');
    div.textContent = slot + (isBooked ? ' (Booked)' : ' (Available)');
    if (!isBooked) {
      div.onclick = () => bookSlot(toolName, slot, div);
    }
    slotBox.appendChild(div);
  });
}

function bookSlot(tool, slot, el) {
  fetch("", {
    method: "POST",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `equipment=${encodeURIComponent(tool)}&slot=${encodeURIComponent(slot)}`
  })
  .then(res => res.text())
  .then(data => {
    if (data === 'booked') {
      el.className = "slot booked";
      el.textContent = slot + ' (Booked)';
      el.onclick = null;
      alert(`Booked ${tool} at ${slot}`);
    } else {
      alert("Already booked!");
    }
  });
}
</script>
</body>
</html>
