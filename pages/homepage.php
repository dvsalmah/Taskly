<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homepage</title>
  <link rel="stylesheet" href="../css/homepage.css">
  <link rel="stylesheet" href="../css/main.css">
</head>
<body>

<div class="layout">
  <div class="main-wrap">
  <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/navbar.php'; ?>
  <main class="main">

    <section class="content">
      <h1>Welcome back, Sundar 👋</h1>

      <div class="grid">

        <div class="card">
          <h3>To-Do (20 June - Today)</h3>
          <div class="task">
            <h4>Attend Nischal’s Birthday Party</h4>
            <p>Buy gifts on the way and pick up cake from the bakery. (6 PM | Fresh Elements)</p>
            <small>Priority: Moderate | Status: Not Started | Created: 20/06/2023</small>
          </div>
          <div class="task">
            <h4>Landing Page Design for TravelDays</h4>
            <p>Get the work done by EOD and discuss with client before leaving. (4 PM | Meeting Room)</p>
            <small>Priority: Moderate | Status: In Progress | Created: 20/06/2023</small>
          </div>
          <div class="task">
            <h4>Presentation on Final Product</h4>
            <p>Make sure everything is functioning and all necessities are properly met. Prepare the team and get the documents ready…</p>
            <small>Priority: Moderate | Status: In Progress | Created: 19/06/2023</small>
          </div>
        </div>

        <div class="card">
          <h3>Task Status</h3>
          <div class="status">
            <div class="circle green">84%<br>Completed</div>
            <div class="circle blue">46%<br>In Progress</div>
            <div class="circle red">13%<br>Not Started</div>
          </div>
        </div>

        <div class="card full">
          <h3>Completed Task</h3>
          <div class="completed-grid">
            <div class="completed-task">
              <h4>Walk the dog</h4>
              <p>Take the dog to the park and bring treats as well.</p>
              <small>Status: Completed | 2 days ago</small>
            </div>
            <div class="completed-task">
              <h4>Conduct meeting</h4>
              <p>Meet with the client and finalize requirements.</p>
              <small>Status: Completed | 2 days ago</small>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>

</div>

</body>
</html>