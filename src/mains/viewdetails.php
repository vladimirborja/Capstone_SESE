<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lost & Found | Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body { background:#f5f9ff; font-family:Arial,sans-serif; }
    .topbar { background:#1877f2; height:56px; display:flex; align-items:center; }
    .container { max-width:1000px; margin:30px auto; padding:20px; }
    .pet-details { display:flex; gap:30px; }
    .pet-image { width:260px; height:260px; border:2px solid #ccc; border-radius:10px; }
    .tag.lost { background:red; color:white; padding:4px 10px; border-radius:4px; }
    .tag.found { background:orange; color:white; padding:4px 10px; border-radius:4px; }
    .contact-btn { background:red; color:white; border:none; padding:10px 18px; border-radius:6px; }
    .post-btn { display:block; margin:30px auto; background:#ffc107; border:none; padding:12px 24px; border-radius:6px; font-weight:bold; }
  </style>
</head>

<body>

<!-- HEADER -->
<?php include 'header.php'; ?>

<div class="container">

  <div class="pet-details">
    <!-- Image -->
    <div class="pet-image"></div>

    <!-- Info -->
    <div class="pet-info">
      <p><strong>Pet Name:</strong> <span id="name"></span></p>
      <p><strong>Tag:</strong> <span id="tag"></span></p>
      <p><strong>Gender:</strong> <span id="gender"></span></p>
      <p><strong>Pet Type:</strong> <span id="type"></span></p>
      <p><strong>Date last seen:</strong> <span id="date"></span></p>
      <p><strong>Location last seen:</strong> <span id="location"></span></p>
      <p><strong>Description:</strong> <span id="description"></span></p>

    </div>
  </div>

  <!-- CTA -->
  <h2 class="text-center text-primary mt-5">Found or Lost any Pet?</h2>

  <div class="d-flex gap-3 mt-3">
    <div class="p-3 text-white rounded bg-primary">
      If your pet went missing around the community, report it to alert others nearby.
    </div>
    <div class="p-3 text-white rounded bg-info">
      Found a lost pet? Submit a found pet report to help reunite them.
    </div>
  </div>

  <button class="post-btn" onclick="window.location.href='postapet.php'">
    Post a Pet
  </button>

</div>

<script>
const pets = JSON.parse(localStorage.getItem("pets")) || [];
const petId = Number(localStorage.getItem("selectedPet"));

const pet = pets.find(p => p.id === petId);

if (pet) {
  document.getElementById("name").textContent = pet.name;
  document.getElementById("tag").innerHTML =
    `<span class="tag ${pet.tag.toLowerCase()}">${pet.tag}</span>`;
  document.getElementById("gender").textContent = pet.gender;
  document.getElementById("type").textContent = pet.type;
  document.getElementById("date").textContent = pet.date;
  document.getElementById("location").textContent = pet.location;
  document.getElementById("description").textContent = pet.description;
}
</script>

</body>
</html>
