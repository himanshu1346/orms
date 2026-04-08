<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">List of Reservations</h3>
	</div>
	<div class="card-body">
		<div class="container-fluid">
            <fieldset class="border-bottom mb-3 pb-3">
                <legend class="text-muted small">Filters</legend>
                <form action="" id="filter-frm">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="status" class="control-label small text-muted">Status</label>
                            <select name="status" id="status" class="form-control form-control-sm rounded-0">
                                <option value="all" <?= !isset($_GET['status']) || $_GET['status'] == 'all' ? 'selected' : '' ?>>All</option>
                                <option value="0" <?= isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : '' ?>>Pending</option>
                                <option value="1" <?= isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="2" <?= isset($_GET['status']) && $_GET['status'] == '2' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="control-label small text-muted">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control form-control-sm rounded-0" value="<?= isset($_GET['date_from']) ? $_GET['date_from'] : '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="control-label small text-muted">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control form-control-sm rounded-0" value="<?= isset($_GET['date_to']) ? $_GET['date_to'] : '' ?>">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-flat btn-sm"><i class="fa fa-filter"></i> Filter</button>
                            <a href="./?page=reservations" class="btn btn-default btn-flat btn-sm"><i class="fa fa-sync-alt"></i> Reset</a>
                        </div>
                    </div>
                </form>
            </fieldset>
			<table class="table table-bordered table-hover table-striped">
				<colgroup>
					<col width="5%">
					<col width="15%">
					<col width="15%">
					<col width="20%">
					<col width="10%">
					<col width="10%">
					<col width="15%">
					<col width="10%">
				</colgroup>
				<thead>
					<tr>
						<th>#</th>
						<th>Reservation Code</th>
						<th>Room Name</th>
						<th>Reservor</th>
						<th>Check In</th>
						<th>Check Out</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
            <?php 
                $where = "";
                if(isset($_GET['status']) && $_GET['status'] != 'all'){
                    $where .= " and r.status = '{$_GET['status']}' ";
                }
                if(isset($_GET['date_from']) && !empty($_GET['date_from'])){
                    $where .= " and date(r.check_in) >= '{$_GET['date_from']}' ";
                }
                if(isset($_GET['date_to']) && !empty($_GET['date_to'])){
                    $where .= " and date(r.check_in) <= '{$_GET['date_to']}' ";
                }
                $qry = $conn->query("SELECT r.*, rm.name as room_name from `reservation_list` r inner join `room_list` rm on r.room_id = rm.id where 1=1 {$where} order by r.`status` asc, unix_timestamp(r.`date_created`) desc ");
                $i = 1;
                while($row = $qry->fetch_assoc()):
            ?>
						<tr>
							<td class="text-center"><?= $i++ ?></td>
							<td><?php echo ($row['code']) ?></td>
							<td class=""><?php echo ($row['room_name']) ?></td>
							<td class=""><p class="truncate-1"><?php echo ucwords($row['fullname']) ?></p></td>
							<td class=""><?php echo date("Y-m-d",strtotime($row['check_in'])) ?></td>
							<td class=""><?php echo date("Y-m-d",strtotime($row['check_out'])) ?></td>
							<td class="text-center">
								<?php 
									switch ($row['status']){
										case 0:
											echo '<span class="rounded-pill badge badge-secondary px-3">Pending</span>';
											break;
										case 1:
											echo '<span class="rounded-pill badge badge-primary px-3">Confirmed</span>';
											break;
										case 2:
											echo '<span class="rounded-pill badge badge-danger px-3">Cancelled</span>';
											break;
									}
								?>
							</td>
							<td align="center">
								 <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
				                  		Action
				                    <span class="sr-only">Toggle Dropdown</span>
				                  </button>
				                  <div class="dropdown-menu" role="menu">
				                    <a class="dropdown-item view_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-window-restore text-gray"></span> View</a>
									<div class="dropdown-divider"></div>
				                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
				                  </div>
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
	$(document).ready(function(){
        $('#filter-frm').submit(function(e){
            e.preventDefault()
            location.href = './?page=reservations&'+$(this).serialize()
        })
		$('.view_data').click(function(){
			uni_modal("Resevation Details","reservations/view_details.php?id="+$(this).attr('data-id'),"mid-large")
		})
		$('.delete_data').click(function(){
			_conf("Are you sure to delete this Reservation permanently?","delete_reservation",[$(this).attr('data-id')])
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('.table').dataTable({
            columnDefs: [
                { orderable: false, targets: [7] }
            ],
        });
	})
	function delete_reservation($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_reservation",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occured.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.reload();
				}else{
					alert_toast("An error occured.",'error');
					end_loader();
				}
			}
		})
	}
</script>