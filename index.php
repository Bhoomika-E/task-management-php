<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// fetch user
$stmtU = $conn->prepare("SELECT id, username, level, xp FROM users WHERE id = ?");
$stmtU->bind_param("i", $user_id);
$stmtU->execute();
$user = $stmtU->get_result()->fetch_assoc();
$stmtU->close();

// fetch tasks
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC, due_time ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TaskHero Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <h1>TaskHero</h1>
  <div class="user-info">
    Welcome, <?php echo htmlspecialchars($user['username']); ?> | Level: <?php echo (int)$user['level']; ?> | XP: <?php echo (int)$user['xp']; ?> 
    <a href="logout.php" class="btn small">Logout</a>
  </div>
</header>

<main class="container">
  <section class="left">
    <div class="card">
      <h2>Add New Task</h2>
      <form method="POST" action="add_task.php">
        <input type="text" name="title" placeholder="Task title" required>
        <textarea name="description" placeholder="Task description"></textarea>
        <div class="row">
          <input type="date" name="due_date" required>
          <input type="time" name="due_time" required>
        </div>
        <button type="submit">Add Task</button>
      </form>
    </div>

    <div class="card">
      <h2>Your Tasks</h2>
      <?php while($task = $result->fetch_assoc()) { ?>
        <div class="task <?php echo ($task['status'] == 'completed') ? 'completed' : ''; ?>">
          <h3><?php echo htmlspecialchars($task['title']); ?></h3>
          <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
          <p>🗓️ <?php echo $task['due_date']; ?> ⏰ <?php echo $task['due_time']; ?></p>
          <p>Status: <strong><?php echo $task['status']; ?></strong></p>
          <?php if ($task['status'] != 'completed') { ?>
            <a href="complete_task.php?id=<?php echo $task['id']; ?>"><button>✅ Complete</button></a>
          <?php } ?>
          <a href="delete_task.php?id=<?php echo $task['id']; ?>"><button class="danger">🗑 Delete</button></a>
        </div>
      <?php } ?>
    </div>
  </section>

  <aside class="right">
    <div class="card avatar-card">
      <?php
      $maxAvatarFiles = 5;
      $avatarIndex = min(max(1, (int)$user['level']), $maxAvatarFiles);
      $imagesDir = 'images';
      $avatarFile = $imagesDir . '/avatar' . $avatarIndex . '.png';
      if (!file_exists(__DIR__ . '/' . $avatarFile)) {
          $avatarFile = $imagesDir . '/avatar1.png';
      }
      $avatarWeb = $avatarFile . '?v=' . (@filemtime(__DIR__ . '/' . $avatarFile) ?: time());
      ?>
      <img src="<?php echo $avatarWeb; ?>" id="avatarImg" class="avatar">
      <h3>Level <?php echo (int)$user['level']; ?></h3>
      <p>XP: <?php echo (int)$user['xp']; ?></p>
    </div>

    <div class="card">
      <h3>Reminders</h3>
      <ul id="remList">
        <li>Loading...</li>
      </ul>
    </div>
  </aside>
</main>

<!-- Level Up Popup -->
<div id="levelUpPopup" class="popup">
  <div class="popup-box">
    <h2>🎉 Level Up!</h2>
    <p>You’ve reached a new level — your avatar evolved!</p>
    <button onclick="downloadAvatar()">⬇ Download Avatar</button>
    <button onclick="closePopup()">OK</button>
  </div>
</div>

<script>
function closePopup(){ document.getElementById('levelUpPopup').style.display='none'; }
<?php if (isset($_GET['levelup']) && $_GET['levelup']=='1') { ?>
  document.getElementById('levelUpPopup').style.display='flex';
<?php } ?>

function downloadAvatar() {
    const avatar = document.getElementById('avatarImg');
    const link = document.createElement('a');
    link.href = avatar.src;
    link.download = 'avatar_level_' + <?php echo (int)$user['level']; ?> + '.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Fetch reminders via AJAX
function loadReminders(){
  fetch('get_reminders.php').then(r=>r.json()).then(data=>{
    const ul = document.getElementById('remList');
    ul.innerHTML='';
    if (!data || data.length===0) { ul.innerHTML='<li>No pending tasks</li>'; return; }
    data.forEach(t=>{
      const li = document.createElement('li');
      li.textContent = t.title + ' — ' + t.due_date + ' ' + t.due_time;
      ul.appendChild(li);
    });
  }).catch(()=>{ document.getElementById('remList').innerHTML='<li>Error loading</li>'; });
}
loadReminders();
setInterval(loadReminders, 60000);
</script>


<!-- Anime Puzzle Modal (inserted by assistant) -->
<div id="animePuzzleModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;align-items:center;justify-content:center;">
  <div id="animePuzzleContent" style="background:#fff;padding:16px;border-radius:8px;max-width:90%;max-height:90%;overflow:auto;position:relative;">
    <button id="closePuzzleBtn" style="position:absolute;right:8px;top:8px;">✕</button>
    <h2 style="margin-top:0">Level Up! Solve the anime puzzle to claim your reward</h2>
    <div id="puzzleArea" style="display:flex;gap:12px;flex-direction:column;align-items:center;">
      <div id="puzzleCanvasWrap"></div>
      <div id="puzzleMsg" style="min-height:24px"></div>
    </div>
  </div>
</div>
<script src="puzzle.js"></script>

</body>
</html>
