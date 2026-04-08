<style>
    .welcome-header {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
        letter-spacing: -1px;
        margin-bottom: 2rem;
    }
    .info-card {
        background: white;
        border-radius: var(--border-radius-md);
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
        transition: transform 0.3s;
        border: 1px solid rgba(0,0,0,0.05);
        height: 100%;
        display: flex;
        align-items: center;
        animation: slideUp 0.6s ease-out forwards;
        opacity: 0;
    }
    .info-card:hover {
        transform: translateY(-5px);
    }
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1.5rem;
        flex-shrink: 0;
    }
    .card-label {
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .card-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2d3436;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }
    .delay-5 { animation-delay: 0.5s; }
    .delay-6 { animation-delay: 0.6s; }
    .delay-7 { animation-delay: 0.7s; }
    .delay-8 { animation-delay: 0.8s; }
</style>

<h1 class="welcome-header text-center pt-4">Welcome, <?php echo $_settings->userdata('firstname') ?>!</h1>
<div class="container-fluid pt-4">
    <div class="row g-4">
        <!-- Active Room -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-1">
                <div class="icon-box bg-light text-warning">
                    <i class="fas fa-bed"></i>
                </div>
                <div>
                    <div class="card-label">Active Rooms</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `room_list` where delete_flag = 0 and status = 1 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
        <!-- Inactive Room -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-2">
                <div class="icon-box bg-light text-secondary">
                    <i class="fas fa-bed"></i>
                </div>
                <div>
                    <div class="card-label">Inactive Rooms</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `room_list` where delete_flag = 0 and status = 0 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
        <!-- Pending Reservation -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-3">
                <div class="icon-box bg-light text-orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="card-label">Pending Res.</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `reservation_list` where `status` = 0 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
        <!-- Confirmed Reservation -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-4">
                <div class="icon-box bg-light text-primary">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="card-label">Confirmed Res.</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `reservation_list` where `status` = 1 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
        <!-- Cancelled Reservation -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-5">
                <div class="icon-box bg-light text-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <div class="card-label">Cancelled Res.</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `reservation_list` where `status` = 2 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
        <!-- Active Activities -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-6">
                <div class="icon-box bg-light text-success">
                    <i class="fas fa-swimmer"></i>
                </div>
                <div>
                    <div class="card-label">Active Activities</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `activity_list` where delete_flag = 0 and status=1 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
        <!-- Inactive Activities -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-7">
                <div class="icon-box bg-light text-danger">
                    <i class="fas fa-swimmer"></i>
                </div>
                <div>
                    <div class="card-label">Inactive Act.</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `activity_list` where delete_flag = 0 and status=0 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
        <!-- Unread Inquiries -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="info-card delay-8">
                <div class="icon-box bg-light text-info">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div>
                    <div class="card-label">New Inquiries</div>
                    <div class="card-value"><?php echo $conn->query("SELECT id FROM `message_list` where status=0 ")->num_rows; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
