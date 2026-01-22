<?php
include('./authentication/authentication.php');
include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <!-- /.content-header -->
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-md-12">
          <?php
          include('./message/message.php');
          ?>
        </div>

        <div class="col-lg-12">

          <!-- Page Title -->
          <div class="mb-4">
            <h2 class="fw-bold">Dashboard Overview</h2>
            <p class="text-muted">Summary of platform activity</p>
          </div>

          <!-- Stats Cards -->
          <div class="row g-4 mb-5">

            <div class="col-md-2 col-sm-6">
              <div class="stat-card">
                <div class="stat-icon bg-primary">👤</div>
                <div>
                  <h6>Customers</h6>
                  <h4 id="customers">0</h4>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-sm-6">
              <div class="stat-card">
                <div class="stat-icon bg-success">📦</div>
                <div>
                  <h6>Products</h6>
                  <h4 id="products">0</h4>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-sm-6">
              <div class="stat-card">
                <div class="stat-icon bg-warning">👨‍💼</div>
                <div>
                  <h6>Staff</h6>
                  <h4 id="staff">0</h4>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="stat-card">
                <div class="stat-icon bg-danger">⚠️</div>
                <div>
                  <h6>Reports</h6>
                  <h4 id="reports">0</h4>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="stat-card">
                <div class="stat-icon bg-info">✉️</div>
                <div>
                  <h6>Contact Messages</h6>
                  <h4 id="contacts">0</h4>
                </div>
              </div>
            </div>

          </div>

          <!-- Charts -->
          <div class="row g-4">

            <div class="col-lg-4">
              <div class="chart-card">
                <h5>Order Status</h5>
                <canvas id="statusChart"></canvas>
              </div>
            </div>

            <div class="col-lg-8">
              <div class="chart-card">
                <h5>Weekly Sales</h5>
                <canvas id="weeklyChart"></canvas>
              </div>
            </div>

            <div class="col-lg-12">
              <div class="chart-card">
                <h5>Monthly Sales</h5>
                <canvas id="monthlyChart"></canvas>
              </div>
            </div>

          </div>

        </div>


        <div class="row">

          <!-- Monthly Sales -->
          <div class="col-lg-12">
            <div class="card shadow-sm mb-4">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                  <i class="fas fa-calendar-alt text-warning"></i>
                  Monthly Sales
                </h5>
                <span class="badge badge-info">All Time</span>
              </div>
              <div class="card-body">
                <canvas id="monthlyChart" height="120"></canvas>
              </div>
            </div>
          </div>

        </div>




        <!-- ./col -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>

</div>











<?php
include('include/script.php');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  fetch('codeLogic/dashboard/dashboard.php')
    .then(res => res.json())
    .then(data => {

      // Counters
      document.getElementById('customers').innerText = data.counts.customers;
      document.getElementById('products').innerText = data.counts.products;
      document.getElementById('staff').innerText = data.counts.staff;
      document.getElementById('reports').innerText = data.counts.reports;
      document.getElementById('contacts').innerText = data.counts.contacts;

      // Status Chart
      new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
          labels: data.status.map(s => s.status),
          datasets: [{
            data: data.status.map(s => s.total)
          }]
        }
      });

      // Weekly Sales
      new Chart(document.getElementById('weeklyChart'), {
        type: 'line',
        data: {
          labels: data.weekly.map(w => w.day),
          datasets: [{
            label: 'Sales',
            data: data.weekly.map(w => w.sales),
            tension: 0.4,
            fill: true
          }]
        }
      });

      // Monthly Sales
      new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
          labels: data.monthly.map(m => m.month),
          datasets: [{
            label: 'Monthly Sales',
            data: data.monthly.map(m => m.sales)
          }]
        }
      });
    });
</script>



<?php
include('include/footer.php');
?>