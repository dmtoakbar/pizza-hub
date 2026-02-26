<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';

include('codeLogic/notification/send.php');

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');
?>

<div class="content-wrapper">

  <!-- ================= SEND NOTIFICATION MODAL ================= -->
  <div class="modal fade" id="AddNotificationModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Send Notification</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST">

          <div class="modal-body">

            <div class="form-group">
              <label>Title</label>
              <input type="text" name="title" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Message</label>
              <textarea name="message" class="form-control" required></textarea>
            </div>

            <div class="form-group">
              <label>Type</label>
              <select name="type" class="form-control" required>
                <option value="general">General</option>
                <option value="promotion">Promotion</option>
                <option value="order">Order</option>
                <option value="system">System</option>
                <option value="custom">Custom</option>
              </select>
            </div>

            <div class="form-group">
              <label>Send To</label>
              <select name="user_id" class="form-control">
                <option value="">Broadcast (All Users)</option>
                <?php
                $users = mysqli_query($conn, "SELECT id, name FROM users");
                while ($user = mysqli_fetch_assoc($users)):
                ?>
                  <option value="<?= $user['id'] ?>">
                    <?= htmlspecialchars($user['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Reference ID (Optional)</label>
              <input type="text" name="reference_id" class="form-control">
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="sendNotification" class="btn btn-primary">
              Send
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- ================= HEADER ================= -->
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Notifications</h1>
    </div>
  </div>

  <!-- ================= TABLE ================= -->
  <div class="container">

  <?php include('./message/message.php'); ?>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">All Notifications</h3>
        <button class="btn btn-primary btn-sm float-right"
          data-toggle="modal"
          data-target="#AddNotificationModal">
          Send Notification
        </button>
      </div>


      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Sr</th>
              <th>Title</th>
              <th>Message</th>
              <th>Type</th>
              <th>User</th>
              <th>Date</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $query = "SELECT * FROM notifications ORDER BY created_at DESC";
            $run = mysqli_query($conn, $query);
            $n = 0;

            while ($row = mysqli_fetch_assoc($run)):
              $n++;
            ?>
              <tr>
                <td><?= $n ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['message']) ?></td>
                <td><?= $row['type'] ?></td>
                <td><?= $row['user_id'] ?: 'Broadcast' ?></td>
                <td><?= $row['created_at'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>

        </table>
      </div>
    </div>
  </div>

</div>

<?php include('include/script.php'); ?>

<?php include('include/footer.php'); ?>