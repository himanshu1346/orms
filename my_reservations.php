<div class="content py-5">
    <div class="card card-outline card-primary rounded-0 shadow">
        <div class="card-header">
            <h4 class="card-title"><b>My Reservation History</b></h4>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <table class="table table-hover table-striped table-bordered" id="reservation-list">
                    <colgroup>
                        <col width="5%">
                        <col width="15%">
                        <col width="20%">
                        <col width="20%">
                        <col width="15%">
                        <col width="15%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Room</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $client_id = $_settings->userdata('id');
                        $qry = $conn->query("SELECT r.*, rm.name as room_name from `reservation_list` r inner join `room_list` rm on r.room_id = rm.id where r.client_id = '{$client_id}' order by unix_timestamp(r.date_created) desc ");
                        while($row = $qry->fetch_assoc()):
                        ?>
                            <tr>
                                <td class="text-center"><?= $i++ ?></td>
                                <td><?= $row['code'] ?></td>
                                <td><?= $row['room_name'] ?></td>
                                <td>
                                    <small><span class="text-muted">In:</span> <?= date("M d, Y", strtotime($row['check_in'])) ?></small><br>
                                    <small><span class="text-muted">Out:</span> <?= date("M d, Y", strtotime($row['check_out'])) ?></small>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        switch ($row['status']){
                                            case 0:
                                                echo '<span class="badge badge-secondary px-3 rounded-pill">Pending</span>';
                                                break;
                                            case 1:
                                                echo '<span class="badge badge-primary px-3 rounded-pill">Confirmed</span>';
                                                break;
                                            case 2:
                                                echo '<span class="badge badge-danger px-3 rounded-pill">Cancelled</span>';
                                                break;
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-flat btn-default btn-sm view_data" data-id="<?= $row['id'] ?>"><i class="fa fa-eye"></i> View</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('.view_data').click(function(){
            uni_modal("Reservation Details", "admin/reservations/view_details.php?id="+$(this).attr('data-id'), "mid-large")
        })
        $('#reservation-list').dataTable({
            columnDefs: [
                { orderable: false, targets: [5] }
            ],
        });
    })
</script>
