<body class="easyui-layout">



	<!-- Header -->

	<div data-options="region:'north', border:false" id="header" style="background-image: url(<?= base_url('assets/image/header/' . $profile->theme . '.png') ?>);">

		<div style="float: left;" class="animate__animated animate__bounce animate__slower">

			<img src="<?= base_url('assets/image/logo/' . $profile->theme . '.png') ?>" width="150">

		</div>



		<div class="logo-company animate__animated animate__bounceIn animate__slower">

			<img src="<?= $config->favicon ?>" width="50"><br>

		</div>

		<div class="name-company animate__animated animate__bounceIn animate__slower">

			<b style="font-size: 16px !important;"><?= $config->name ?></b><br>

			<div class="name-lisence">

				<b><?= $config->description ?></b><br>

			</div>

		</div>



		<div class="logo">

			<a onclick="approval()" href="#" title="Approval" class="notification approval">

				<i class="fa fa-check-square" style="font-size: 25px !important;"></i>

				<div id="approvalCount"></div>

			</a>

			<a onclick="notification()" href="#" title="Notification" class="notification">

				<i class="fa fa-bell" style="font-size: 25px !important;"></i>

				<div id="notificationCount"></div>

			</a>

			<a onclick="profile()" href="#" title="Profile" class="notification">

				<i class="fa fa-users" style="font-size: 25px !important;"></i>

			</a>

			<a href="#" onClick="logout()" title="Logout" class="notification">

				<i class="fa fa-share" style="font-size: 25px !important;"></i>

			</a>

		</div>

	</div>



	<!-- MAIN MENU -->

	<div data-options="region:'west',split:false, collapsed:true, hideCollapsedContent:false, title:'Main Menu'" style="width:250px; padding:10px;">

		<input id="searchMenu" placeholder="Search Menus" onchange="searchMenu(this.value)" title="Please Enter for Search Menu" style="width:100%; padding: 5px;">

		<br><br>

		<ul class="easyui-tree" id="menu" data-options="url:'<?= base_url('home/menus') ?>', method:'get',animate:true, lines:true"></ul>

	</div>



	<!-- USERS ONLINE -->

	<div data-options="region:'east',split:false, collapsed:true, hideCollapsedContent:false" title="Users Online" style="width:250px;">

		<div style="height: 75%; width: 100%; overflow: auto;">

			<table class="user-header" style="width: 100%;">

				<?php

				foreach ($users as $user) {

					if ($user->avatar == "") {

						$avatar = base_url('assets/image/users/default.png');

					} else {

						$avatar = $user->avatar;

					}



					$chats = $this->crud->reads("chats", [], ["from_users_id" => $user->id, "status" => 0]);

					if (count($chats) > 0) {

						$totalChats = '<div style="border-radius:50%;background:red; color:white; text-align:center;">' . count($chats) . '</div>';

					} else {

						$totalChats = '';

					}



					$startChats = "onclick='startChats(" . $user->id . ")'";

					echo '	<tr>

								<td style="padding:6px;" width="50">

									<div class="icon-container">

										<img src="' . $avatar . '" class="user-online" />

										<div class="status-circle"></div>

									</div>

								</td>

								<td>

									<a href="#" ' . $startChats . ' style="text-decoration:none;">

										<b style="font-size:12px; color:black;">' . $user->name . '</b><br>

										<small style="color:black;">' . $user->position . '</small>

									</a>

								</td>

								<td style="padding:6px; text-align:right;" width="30">

									' . $totalChats . '

								</td>

							</tr>';

				}

				?>

			</table>

		</div>

		<div style="height: 25%; width: 100%;">

			<center>

				<img src="<?= base_url('assets/image/helpdesk.png') ?>" width="240" />

				<a class="btn btn-lg btn-primary w-75" style="pointer-events: visible; opacity: 1;">SUPPORT SYSTEM</a>

			</center>

		</div>

	</div>



	<!-- FOOTER -->

	<div data-options="region:'south',border:false" style="overflow: hidden;" id="footer">

		Welcome in Application <?= $config->description ?> <b><?= $this->session->name ?></b> You are login in time <?= date("d F Y H:m:s"); ?>

		<span style="float: right;"> Copyright &copy; <?= $config->name ?> 2022 Version 1.0</span>

	</div>



	<!-- TABS AND MODULE -->

	<div data-options="region:'center'" style="width: 100%;">

		<div class="easyui-tabs" id="tabs" style="width:100%; height: 100%;">

			<div title="Dashboard">

				<iframe src="<?= base_url("dashboard/dashboard") ?>" scrolling="yes" id="page" style="border: 0; width: 100%; height: 99%; margin:0;"></iframe>

			</div>

		</div>

	</div>



	<!-- CHANGE PROFILE -->

	<div id="dlg_profile" class="easyui-dialog" title="Profile" data-options="closed: true" style="width: 500px; padding:10px; top: 20px;">

		<form id="frm_profile" method="post" enctype="multipart/form-data" novalidate>

			<fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

				<legend><b>Profile Configuration</b></legend>

				<center>

					<img id="imagePreview" src="<?= $profile_img ?>" width="150">

					<br><br>

				</center>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">Avatar</span>

					<input style="width:60%;" name="avatar" id="avatar" class="easyui-filebox">

				</div>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">Fullname</span>

					<input style="width:60%;" name="name" id="name" value="<?= $profile->name ?>" class="easyui-textbox">

				</div>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">Username</span>

					<input style="width:60%;" name="username" id="username" value="<?= $profile->username ?>" readonly class="easyui-textbox">

				</div>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">Email</span>

					<input style="width:60%;" name="email" id="email" value="<?= $profile->email ?>" class="easyui-textbox">

				</div>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">Phone</span>

					<input style="width:60%;" name="phone" id="phone" value="<?= $profile->phone ?>" class="easyui-textbox">

				</div>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">Position</span>

					<input style="width:60%;" name="position" id="position" value="<?= $profile->position ?>" class="easyui-textbox">

				</div>

			</fieldset>

			<fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

				<legend><b>Theme Application</b></legend>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">Theme App</span>

					<select style="width:60%;" name="theme" id="theme" class="easyui-combobox">

						<option value="default">Default</option>

						<option value="cupertino">Cupertino</option>

						<option value="black">Black</option>

						<option value="bootstrap">Bootstrap</option>

						<option value="gray">Gray</option>

						<option value="pepper-grinder">Pepper Grinder</option>

						<option value="material">Material</option>

						<option value="material-blue">Material Blue</option>

						<option value="material-teal">Material Teal</option>

						<option value="metro">Metro</option>

						<option value="metro-blue">Metro Blue</option>

						<option value="metro-gray">Metro Gray</option>

						<option value="metro-green">Metro Green</option>

						<option value="metro-orange">Metro Orange</option>

						<option value="metro-red">Metro Red</option>

						<option value="sunny">Sunny</option>

					</select>

				</div>

			</fieldset>

			<fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

				<legend><b>Change Password</b></legend>

				<i style="color:red">If you don't want to change the password, don't fill in anything</i>

				<div class="fitem">

					<span style="width:35%; display:inline-block;">New Password</span>

					<input style="width:60%;" name="password" id="password" class="easyui-passwordbox">

				</div>

			</fieldset>

		</form>

	</div>



	<!-- APPROVAL -->

	<div id="dlg_approval" class="easyui-dialog" title="Approvals" data-options="closed: true" style="width: 500px; height: 400px; top: 20px;">

		<ul class="list-header" id="approvalList">



		</ul>

	</div>



	<!-- APPROVAL DETAIL -->

	<div id="toolbar_approval">

		<a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="approveall()"><i class="fa fa-check"></i> Approve ALL</a>

		<a href="javascript:void(0)" id="disapproveall" class="easyui-linkbutton" data-options="plain:true" onclick="disapproveall()"><i class="fa fa-times"></i> Disapprove ALL</a>

		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>

	</div>



	<div id="dlg_approval_detail" class="easyui-window" title="Approval Confirmation" data-options="closed: true,minimizable:false,collapsible:false" style="width: 1000px; height: 400px; top: 60px;">

		<div hidden>

			<input class="easyui-textbox" id="table_name" />

			<input class="easyui-textbox" id="approved_to" />

			<input class="easyui-textbox" id="approved_by" />

		</div>

		<table id="dg_approval" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar_approval" data-options="fitColumns: true, rownumbers: true"></table>

	</div>



	<!-- NOTIFICATIONS -->

	<div id="dlg_notif" class="easyui-dialog" title="Notifications" data-options="closed: true" style="width: 400px; height: 400px; top: 20px;">

		<ul class="list-header" id="notificationList">



		</ul>

	</div>

	<div id="dlg_agreement" class="easyui-dialog" title="Notification Data" data-options="closed: true" style="width: 1000px; height: 520px;">
		<div id="toolbar_agreement">
			<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="agreementExcel()"><i class="fa fa-file"></i> Export Excel</a>
			<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="agreementReload()"><i class="fa fa-rotate-right"></i> Reload</a>
		</div>
		<table id="dg_agreement" class="easyui-datagrid"></table>
	</div>

	<div id="dlg_form_agreement" class="easyui-dialog" data-options="{title: 'Add New', closed: true, modal: true}" style="width: 800px; height: 700px; top: 50px;">
		<table style="width: 100%; min-height: 101%;">
			<tr>
				<td style="width: 50%; vertical-align: top; height: 429px;"><fieldset style="height: 100%;">
					<legend>Personal Data</legend>
					<table style="width: 100%;">
						<tr>
							<td>Trans Date</td>
							<td><input id="trans_date" class="easyui-datebox" required></td>
						</tr>
						<tr>
							<td>Employee ID</td>
							<td><input id="emp_id" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>Name</td>
							<td><input id="emp_name" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>National ID</td>
							<td><input id="national_id" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>Place of Birth</td>
							<td><input id="place_birth" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>Birth Date</td>
							<td><input id="birthday" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>Gender</td>
							<td><input id="gender" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>Address</td>
							<td><input id="address" class="easyui-textbox" data-options="{multiline: true}" required style="height: 140px;"></td>
						</tr>
					</table>
				</fieldset></td>
				<td style="width: 50%; vertical-align: top; height: 429px;"><fieldset style="height: 100%;">
					<legend>Agreement</legend>
					<table style="width: 100%;">
						<tr>
							<td>Date Sign</td>
							<td><input id="date_sign" class="easyui-datebox" required></td>
						</tr>
						<tr>
							<td>Date Expired</td>
							<td><input id="date_expired" class="easyui-datebox" required></td>
						</tr>
						<tr>
							<td>Duration</td>
							<td><input id="duration" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>Division</td>
							<td><input id="division" class="easyui-combobox" required></td>
						</tr>
						<tr>
							<td>Department</td>
							<td><input id="dept" class="easyui-combobox" required></td>
						</tr>
						<tr>
							<td>Department Sub</td>
							<td><input id="dept_sub" class="easyui-combobox" required></td>
						</tr>
						<tr>
							<td>Process</td>
							<td><input id="process_agreement" class="easyui-textbox" required></td>
						</tr>
						<tr>
							<td>Employee Type</td>
							<td><input id="emp_type" class="easyui-combobox" required></td>
						</tr>
						<tr>
							<td>Position</td>
							<td><input id="position_agreement" class="easyui-combobox" required></td>
						</tr>
						<tr>
							<td>Group</td>
							<td><input id="group" class="easyui-combobox" required></td>
						</tr>
						<tr>
							<td>Attachment</td>
							<td><form id="formAttachment" enctype="multipart/form-data"><input id="attachment" name="attachment" class="easyui-filebox"></form></td>
						</tr>
						<tr>
							<td>Notes</td>
							<td><input id="notes" class="easyui-textbox"></td>
						</tr>
					</table>
				</fieldset></td>
			</tr>
			<tr>
				<td style="width: 50%; vertical-align: top;"><fieldset class="field_setup_agreement">
					<legend>Setup Allowances</legend>
					<table id="tb_allowance" style="width: 100%;"></table><br>
					<a href="javascript:;" id="btnAddAllowance" class="easyui-linkbutton" data-options="plain:true" onclick="btnAddAllowance()"><i class="fa fa-plus"></i> <span id="add">Add Allowance</span></a>
				</fieldset></td>
				<td style="width: 50%; vertical-align: top;"><fieldset class="field_setup_agreement">
					<legend>Setup Salary</legend>
					<table style="width: 100%;">
						<tr>
							<td>Component Salary</td>
							<td><input id="component_salary" class="easyui-combobox"></td>
						</tr>
						<tr>
							<td>Amount</td>
							<td><input id="amount" class="easyui-numberbox" data-options="{precision: 0}"></td>
						</tr>
						<tr>
							<td>BPJS</td>
							<td><input id="bpjs" class="easyui-numberbox" data-options="{precision: 0}"></td>
						</tr>
					</table>
				</fieldset></td>
			</tr>
		</table>
	</div>

	<!-- CHATS -->

	<div id="dlg_chats" class="easyui-dialog" title="Chats" data-options="closed: true, cls:'c2',border:'thin'" style="width: 400px; height: 600px; top: 60px;">

		<div style="width: 100%;">

			<div style="position: absolute; top: 35px; margin:5px; width: 100%;">

				<table style="width: 100%;">

					<tr>

						<td style="padding:5px;" width="50">

							<div class="icon-container">

								<img src="<?= base_url('assets/image/users/default.png') ?>" class="user-online" width="100" />

								<div class="status-circle"></div>

							</div>

						</td>

						<td>

							<a href="#" style="text-decoration:none;">

								<b style="font-size:12px; color:black;" id="chatName">Name</b><br>

								<small style="color:black;" id="chatPosition">Position</small>

							</a>

						</td>

					</tr>

				</table>

				<hr>

			</div>

			<div style="margin-top: 60px; height: 450px; overflow: auto;" id="messageChats">



			</div>

			<div style="position: absolute; bottom: 0px; margin:10px; width: 93%;">

				<input class="form-control w-100" id="to_users_id" hidden />

				<input class="form-control w-100" id="inputChats" autocomplete="false" autofocus placeholder="Type Message..." />

			</div>

		</div>

	</div>

	<iframe id="printcontract" src="" style="width: 100%;" hidden></iframe>
</body>
<script>
	function searchMenu(e) { menu(e); };

	$(function() {
		window.addEventListener('load', function(event) { detectInternet(); });
		window.addEventListener('online', function(event) { detectInternet(); });
		window.addEventListener('offline', function(event) { detectInternet(); });

		function detectInternet() {

			if (navigator.onLine) {

				Swal.close();

			} else {

				Swal.fire({

					title: 'Connection Time Out, Check Your Connection',

					showConfirmButton: false,

					allowOutsideClick: false,

					allowEscapeKey: false,

					didOpen: () => {

						Swal.showLoading();

					},

				});

			}

		}



		$("#theme").combobox('setValue', '<?= $profile->theme ?>');

		$('#dlg_profile').dialog({

			buttons: [{

				text: 'Save Changes',

				iconCls: 'icon-ok',

				handler: function() {

					$('#frm_profile').form('submit', {

						url: '<?= base_url('home/updateProfile') ?>',

						onSubmit: function() {

							return $(this).form('validate');

						},

						success: function(result) {

							var result = eval('(' + result + ')');



							Swal.fire({

								title: result.message,

								icon: result.theme,

								confirmButtonText: 'Ok',

								allowOutsideClick: false,

							}).then((result) => {

								if (result.isConfirmed) {

									window.location.reload();

								}

							});



							$('#dlg_profile').dialog('close');

						}

					});

				}

			}]

		});



		$('#avatar').filebox({

			onChange: function(value) {

				var id = $(this).filebox('options').fileboxId;

				var files = $('#' + id)[0].files;

				if (files.length) {

					var url = window.URL.createObjectURL(files[0]);

					$('#imagePreview').attr('src', url);

				}

			}

		});


		$('#dg_agreement').datagrid({
			url: '<?= base_url('home/datatablesagreement') ?>',
            pagination: true,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            toolbar: '#toolbar_agreement',
            columns: [[
            	{field: 'emp', title: 'Employee ID', width: '150', halign: 'center'},
            	{field: 'name', title: 'Employee Name', width: '300', halign: 'center'},
            	{field: 'division', title: 'Division', width: '100', halign: 'center'},
            	{field: 'dept', title: 'Departement', width: '150', halign: 'center'},
            	{field: 'dept_sub', title: 'Departement Sub', width: '150', halign: 'center'},
            	{field: 'position', title: 'Position', width: '100', halign: 'center'},
            	{field: 'type', title: 'Employee Type', width: '100', halign: 'center'},
            	{field: 'date_join', title: 'Join Date', width: '100', halign: 'center', align: 'center'},
            	{field: 'expired', title: 'Contract Expired', width: '120', halign: 'center', align: 'center'},
            	{field: 'note', title: 'Note', width: '300', halign: 'center'},
            	{field: 'act', title: 'Action', width: '100', halign: 'center', align: 'center', formatter: function (value, row, index) { return '<a href="javascript:;" class="easyui-linkbutton" onclick="viewHistory('
            		+"'"+window.btoa(JSON.stringify(row))+"'"+')"><i class="fa fa-file-pen"></i></a>'; }}
            ]],
            onLoadSuccess: function () { $('.easyui-linkbutton').linkbutton(); }
		}).datagrid('enableFilter');

		menu();

		$('#emp_id').textbox('readonly', true);
		$('#emp_name').textbox('readonly', true);
		$('#national_id').textbox('readonly', true);
		$('#place_birth').textbox('readonly', true);
		$('#birthday').textbox('readonly', true);
		$('#gender').textbox('readonly', true);
		$('#address').textbox('readonly', true);
		$('#duration').textbox('readonly', true);
		$('#amount').numberbox({precision: 0});
	});

	var agreement_setup_salary_req = true;
	var agreement_select_sub_dept = [];
	var agreement_setup_access = true;
	var agreement_select_salary = [];
	var agreement_select_dept = [];
	var agreement_allowance = [];
	var agreement_agreement = [];
	var agreement_templates = [];
	var agreement_employee = [];
	var agreement_salary = [];
	var agreement_setup = [];
	var template_contract = '';
	function formAgreement() {
		$.ajax({
			url: '<?= base_url('home/formAgreement'); ?>',
			async: false,
			method: 'POST',
			dataType: 'json',
			success: function (result) {
				var now = new Date();

				agreement_setup_access = result.access.allowance && result.access.salary;
				agreement_select_sub_dept = result.departement_sub;
				agreement_select_dept = result.departement;
				agreement_allowance = result.allowance;
				agreement_agreement = result.agreement;
				agreement_templates = result.templates;
				agreement_employee = result.employee;
				agreement_salary = result.salary;
				agreement_setup = result.setup;

				$('#trans_date').datebox({
					formatter: myformatter,
					parser: myparser,
					editable: false,
					value: now.getFullYear()+'-'+
					String(now.getMonth() + 1).padStart(2, '0')+'-'+
					String(now.getDate()).padStart(2, '0')
				});

				$('#date_sign').datebox({
					formatter: myformatter,
					parser: myparser,
					editable: false,
					onSelect: function (date) {
						var format = date.getFullYear()+'-'+(date.getMonth() + 1)+'-'+date.getDate();
						$('#duration').textbox(
							'setValue',
							calculateDuration(format, $('#date_expired').datebox('getValue')).text
						);

						if (format >= $('#date_expired').datebox('getValue'))
							{ toastr.warning('Please select date sign < date expired!', 'Warning'); }
					}
				});

				$('#date_expired').datebox({
					formatter: myformatter,
					parser: myparser,
					editable: false,
					onSelect: function (date) {
						var format = date.getFullYear()+'-'+(date.getMonth() + 1)+'-'+date.getDate();
						$('#duration').textbox(
							'setValue',
							calculateDuration($('#date_sign').datebox('getValue'), format).text
						);

						if ($('#date_sign').datebox('getValue') >= format)
							{ toastr.warning('Please select date sign < date expired!', 'Warning'); }
					}
				});

				$('#division').combobox({
					data: result.division,
					textField: 'name',
					valueField: 'id',
					editable: false,
					onSelect: function (rec) {
						var data = agreement_select_dept.filter(obj => obj.division == rec.id);
						$('#dept')
							.combobox('clear')
							.combobox('enable')
							.combobox('loadData', data);
						if (data.length == 1) { $('#dept').combobox('setValue', data[0].id).combobox('disable'); }
					}
				});

				$('#dept').combobox({
					textField: 'name',
					valueField: 'id',
					editable: false,
					onSelect: function (rec) {
						var data = agreement_select_sub_dept.filter(obj => obj.dept == rec.id);
						$('#dept_sub')
							.combobox('clear')
							.combobox('enable')
							.combobox('loadData', data);
						if (data.length == 1) { $('#dept_sub').combobox('setValue', data[0].id).combobox('disable'); }
					}
				}).combobox('clear').combobox('disable');

				$('#dept_sub').combobox({
					textField: 'name',
					valueField: 'id',
					editable: false
				}).combobox('clear').combobox('disable');

				$('#emp_type').combobox({
					data: result.employee_type,
					textField: 'name',
					valueField: 'id',
					editable: false,
					onSelect: function (rec) {
            			var all_tr = $('#tb_allowance tr');
						agreement_setup_salary_req = false;

						if (window.btoa(rec.id) != 'MjAyMjExMTkwMDAwMDE=') { $('.field_setup_agreement').hide(); }
						else {
							var stat_access = (agreement_setup_access) ? 'enable' : 'disable';

							if (agreement_setup_access) { agreement_setup_salary_req = true; }

							$('.field_setup_agreement').show();
							$('#btnAddAllowance').linkbutton(stat_access);
							$('#component_salary').combobox(stat_access);
							$('#amount').numberbox(stat_access);
							$('#bpjs').numberbox(stat_access);
						}

	            		for (var i = 0; i < all_tr.length; i++) {
	            			var row = all_tr[i];
	            			var allow_combo = $($(row).find('.easyui-combobox'));
	            			var allow_number = $($(row).find('.easyui-numberbox'));

            				if (allow_combo.combobox('getValue') == '' ||
            					allow_number.numberbox('getValue') == '')
            					{ $($(row).find('.easyui-linkbutton')).click(); }
            				else {
        						allow_combo.combobox((agreement_setup_salary_req) ? 'enable' : 'disable')
        							.combobox('textbox').validatebox({required: agreement_setup_salary_req});
        						allow_number.numberbox((agreement_setup_salary_req) ? 'enable' : 'disable')
        							.numberbox('textbox').validatebox({required: agreement_setup_salary_req});
            					allow_combo.combobox('validate');
								allow_number.numberbox('validate');
            				}
	            		}

						$('#amount').numberbox('textbox').validatebox({required: agreement_setup_salary_req});
						$('#bpjs').numberbox('textbox').validatebox({required: agreement_setup_salary_req});
						$('#amount').numberbox('validate');
						$('#bpjs').numberbox('validate');

						template_contract = getTemplateAgreement($('#position_agreement').combobox('getValue'), rec.id);
					}
				});

				$('#position_agreement').combobox({
					data: result.position,
					textField: 'name',
					valueField: 'id',
					editable: false,
					onSelect: function (rec) { template_contract = getTemplateAgreement(rec.id, $('#emp_type').combobox('getValue')); }
				});

				$('#group').combobox({
					data: result.group,
					textField: 'name',
					valueField: 'id',
					editable: false
				});

				$('#component_salary').combobox({
				    data: result.component,
					textField: 'name',
					valueField: 'id',
					editable: false,
					onSelect: function (rec) { selectSalary(rec.id); }
				});

				agreementReload();
				$('#dlg_notif').dialog('close');
				$('#dlg_agreement').dialog('open');
			}
		});
	}

	var is_contract = false;
	function getTemplateAgreement(position, contract) {
		var get_template = agreement_templates.find(value => value.position == position && value.contracts.includes(contract));
		is_contract = get_template != undefined;

		$('#btnSaveAgreement .l-btn-text').html('Save'+((is_contract) ? ' and Print Contract' : ''));
		return (is_contract) ? get_template.template : '';
	}

	var agreement_id = '';
	function viewHistory(row) {
		var data = JSON.parse(window.atob(row));
		var emp = agreement_employee.find(obj => obj.number == data.emp);
		var setups = agreement_setup.filter(obj => obj.employee == data.emp);
		var agreements = agreement_agreement.find(obj => obj.employee == data.emp);
		template_contract = getTemplateAgreement(agreements.position, agreements.type);
		agreement_id = data.id;

		$('#emp_id').textbox('setValue', emp.number);
		$('#emp_name').textbox('setValue', emp.name);
		$('#national_id').textbox('setValue', emp.national);
		$('#place_birth').textbox('setValue', emp.place);
		$('#birthday').textbox('setValue', emp.date);
		$('#gender').textbox('setValue', emp.gender);
		$('#address').textbox('setValue', emp.address);
		$('#component_salary').combobox('clear');
		$('#amount').numberbox('clear');
		$('#bpjs').numberbox('clear');

		$('#date_sign').datebox('setValue', agreements.sign);
		$('#date_expired').datebox('setValue', agreements.expired);
		$('#duration').textbox('setValue', calculateDuration(agreements.sign, agreements.expired).text);
		$('#division').combobox('setValue', agreements.division);
		$('#dept').combobox('setValue', agreements.dept);
		$('#dept_sub').combobox('setValue', agreements.sub_dept);
		$('#process_agreement').textbox('setValue', agreements.process);
		$('#emp_type').combobox('setValue', agreements.type);
		$('#position_agreement').combobox('setValue', agreements.position);
		$('#group').combobox('setValue', agreements.group_id);
		$('#notes').textbox('setValue', agreements.note);

		$('#tb_allowance').html('');
		btnAddAllowance(setups.length);
		var all_tr = $('#tb_allowance tr');
		for (var i = 0; i < all_tr.length; i++) {
			var setup = setups[i];
			var elem = $(all_tr[i]);
			var elem_allow = elem.find('.easyui-combobox');
			var elem_amount = elem.find('.easyui-numberbox');
			var get = agreement_allowance.find(obj => obj.id == setup.id);

			$(elem_allow).combobox('setValue', setup.id);
			if (!agreement_setup_salary_req) {
				$(elem_allow).combobox('disable');
				$(elem.find('.easyui-linkbutton')).linkbutton('disable');
			}

			$(elem_amount).numberbox('setValue', setup.amount).numberbox('disable');
			if (get.amount != undefined && agreement_setup_salary_req)
				{ if (get.amount == '0') { $(elem_amount).numberbox('enable'); } }
		}

		agreement_select_salary = agreement_salary.find(obj => obj.employee == data.emp);
		if (agreement_select_salary != undefined) {
			selectSalary(agreement_select_salary.component);
			$('#component_salary').combobox('setValue', agreement_select_salary.component);
		}

		$('#dlg_form_agreement').dialog({
			buttons:[{
				text: 'Save'+((is_contract) ? ' and Print Contract' : ''),
				id: 'btnSaveAgreement',
				iconCls: 'icon-ok',
				handler: function () {
					var trans_date = $('#trans_date').datebox('getValue');
					var emp_id = $('#emp_id').textbox('getValue');
					var emp_name = $('#emp_name').textbox('getValue');
					var national_id = $('#national_id').textbox('getValue');
					var place_birth = $('#place_birth').textbox('getValue');
					var birthday = $('#birthday').textbox('getValue');
					var gender = $('#gender').textbox('getValue');
					var address = $('#address').textbox('getValue');
					var date_sign = $('#date_sign').datebox('getValue');
					var date_expired = $('#date_expired').datebox('getValue');
					var duration = $('#duration').textbox('getValue');
					var division = $('#division').combobox('getValue');
					var dept = $('#dept').combobox('getValue');
					var dept_sub = $('#dept_sub').combobox('getValue');
					var process_agreement = $('#process_agreement').textbox('getValue');
					var emp_type = $('#emp_type').combobox('getValue');
					var position_agreement = $('#position_agreement').combobox('getValue');
					var group = $('#group').combobox('getValue');
					var component = $('#component_salary').combobox('getValue');
					var amount = $('#amount').numberbox('getValue');
					var bpjs = $('#bpjs').numberbox('getValue');
					var arr = [trans_date, emp_id, emp_name, national_id, place_birth, birthday, gender, address, date_sign, date_expired, duration, division, dept, dept_sub, process_agreement, emp_type, position_agreement, group];
					var allowances = $('#tb_allowance tr');
					var data_allowances = [];

		            if (agreement_setup_salary_req) { arr.push(amount); arr.push(bpjs); }

					for (var i = 0; i < allowances.length; i++) {
						var allow = $(allowances[i]).find('.easyui-combobox').combobox('getValue');
						var amount = parseInt($(allowances[i]).find('.easyui-numberbox').numberbox('getValue'));

	            		arr.push(allow);
		            	arr.push((amount == 0) ? NaN : amount);

		            	if (allow != '' && amount != NaN) { data_allowances.push({allow: allow, amount: amount}); }
		            }

					if (arr.includes('')) { toastr.warning('Please fill in the required input form!', 'Warning'); }
					else {
						var send = {
							agreement_id: window.btoa(agreement_id),
							trans_date: window.btoa(trans_date),
							emp_id: window.btoa(emp_id),
							date_sign: window.btoa(date_sign),
							date_expired: window.btoa(date_expired),
							duration: window.btoa(duration),
							division: window.btoa(division),
							dept: window.btoa(dept),
							dept_sub: window.btoa(dept_sub),
							process_agreement: window.btoa(process_agreement),
							emp_type: window.btoa(emp_type),
							position_agreement: window.btoa(position_agreement),
							group: window.btoa(group),
							notes: window.btoa($('#notes').textbox('getValue'))
						};

						if (agreement_setup_salary_req) {
							send.allowances = window.btoa(JSON.stringify(data_allowances));
							send.component = window.btoa(component);
							send.amount = window.btoa(amount);
							send.bpjs = window.btoa(bpjs);
						}

						if ($('#attachment').filebox('getValue') != '') {
							var file = $('#attachment').next().find('input[type=file]')[0].files[0];
							var ext = file.name.split('.').pop().toLowerCase();
						    var extensions = ['pdf', 'xls', 'xlsx'];
						    var maxSize = 2 * 1024 * 1024; // 2MB

						    if (!extensions.includes(ext))
						    	{ toastr.warning('No file types allowed! Only PDF, XLS, and XLSX.', 'Warning'); }
						    else if (file.size > maxSize)
						    	{ toastr.warning('The file size is too large! Maximum 2MB.', 'Warning'); }
						    else {
								$.ajax({
									url: '<?= base_url('home/saveAgreement'); ?>/attachment',
									data: new FormData($('#formAttachment')[0]),
									type: 'POST',
									dataType: 'json',
									processData: false,
									contentType: false,
									beforeSend: function () { $.messager.progress({title:'Process Upload', msg:'Please wait...'}); },
									success: function (result) {
										$.messager.progress('close');
										if (result.stat != 'ok') { toastr.warning('Failed upload!', 'Warning'); }
										else {
											send.attachment = window.btoa(result.attachment);
											validateFormContract(send);
										}
									},
									error: function() { $.messager.progress('close'); toastr.error('Failed upload!', 'Error'); }
								});
							}
						} else { validateFormContract(send); }
					}
				}
			}]
		}).dialog('open');
	}

	function validateFormContract(data) {
		if (is_contract && template_contract == '') { setTimeout(function () { validateContract(data); }, 0); }
		else { setTimeout(function () { saveAgreement(data); }, 0); }
	}

	function validateContract(data) {
		$.messager.confirm(
			'Warning',
			'The contract template is not registered yet. Do you want to continue saving?',
			function(r) { if (r) { saveAgreement(data); } }
		);
	}

	function saveAgreement(data) {
		$.ajax({
			url: '<?= base_url('home/saveAgreement'); ?>',
			data: data,
			async: false,
			method: 'POST',
			dataType: 'json',
			beforeSend: function () { $.messager.progress({title:'Process Save', msg:'Please wait...'}); },
			success: function (result) {
				$.messager.progress('close');
				if (result.stat != 'ok') { toastr.warning('Failed save!', 'Warning'); }
				else {
					$('#dlg_form_agreement').dialog('close');
					$('#dlg_agreement').dialog('close');
					toastr.success('Success save');
					formAgreement();
					if (is_contract && template_contract != '') { $("#printcontract").prop('src',
						'<?= base_url('excel/print_contract/index') ?>?id='+result.id+'&template='+window.btoa(result.loc+template_contract)
					); }
				}
			},
			error: function() {
				$.messager.progress('close');
				toastr.error('Failed save!', 'Error');
			}
		});
	}

	function calculateDuration(sign, expired) {
		var text = '';
		var start = new Date(sign);
		var end = new Date(expired);
		var time = parseInt((end - start) / (1000 * 60 * 60 * 24));

		if (sign < expired) {
			var year = end.getFullYear() - start.getFullYear();
			var month = end.getMonth() - start.getMonth();
			var day = end.getDate() - start.getDate();

			if (day < 0) {
				month--;
				day += new Date(end.getFullYear(), end.getMonth(), 0).getDate();
			}

			if (month < 0) { year--; month += 12; }

			text = year+' year '+month+' month '+day+' day';
		}

		return {text: text, time: time};
	}

	function selectSalary(component) {
		$('#amount').numberbox('clear');
		$('#bpjs').numberbox('clear');
		if (agreement_select_salary != undefined) {
			if (agreement_select_salary.component == component) {
				$('#amount').numberbox('setValue', agreement_select_salary.amount);
				$('#bpjs').numberbox('setValue', agreement_select_salary.bpjs);
			}
		}
	}

	function agreementReload() { $('#dg_agreement').datagrid('loadData', []).datagrid('reload'); }

	function agreementExcel() { window.location.assign('<?= base_url('home/agreementExcel') ?>'); }

	function btnAddAllowance(count = 1) {
		for (var i = 0; i < count; i++) {
			var row = $('<tr>');
			var input1 = $('<input class="easyui-combobox allows" style="width: 190px;" required>');
			var input2 = $('<input class="easyui-numberbox" style="width: 100px;" required>');
			var btnDelete = $('<a href="javascript:void(0)" class="easyui-linkbutton"></a>').linkbutton({plain: true, iconCls: 'icon-clear'});
			if (agreement_setup_salary_req) { btnDelete.on("click", function () { row.remove(); }); }

			row.append($('<td>').append(input1));
			row.append($('<td>').append(input2));
			row.append($('<td>').append(btnDelete));

			$('#tb_allowance').append(row);

			input1.combobox({
	            valueField: 'id',
	            textField: 'name',
				prompt: 'Select Allowance',
	            data: agreement_allowance,
	            onChange: function (newValue, oldValue) {
	            	if (newValue != '') {
		            	var msg = '';
	            		var all_tr = $('#tb_allowance tr');
	            		var this_tr = $(this).closest('tr');
	            		var this_allow = $(this_tr.find('.easyui-combobox'));
		            	var this_amount = $(this_tr.find('.easyui-numberbox'));

	            		for (var i = 0; i < all_tr.length; i++) {
	            			if (this_tr.index() != i && msg == '') {
	            				var elem = $($(all_tr[i]).find('.easyui-combobox')).combobox('getValue');

	            				if (elem == newValue) { msg = 'Allowance has been selected'; }
	            			}
	            		}

		            	if (msg == '') {
		            		var amount = parseFloat(agreement_allowance.find(obj => obj.id == newValue).amount);

		            		this_amount.numberbox('setValue', amount);
		            		if (amount == 0) { this_amount.numberbox('enable'); }
		            		else { this_amount.numberbox('disable'); }
		            	} else {
		            		toastr.info(msg, 'Information');
		            		this_allow.combobox('setValue', oldValue);
		            	}
	            	}
	            }
			});
			input2.numberbox({prompt: 'Amount', precision: 0});
		}
	}

	function menu(searchName = ""){

		$('#menu').tree({

			url: '<?= base_url('home/menus?name=') ?>' + searchName,

			method: 'get',

			animate: 'true',

			lines: 'true',

			loadFilter: function(rows) {

				return convert(rows);

			},

			onSelect: function(node) {

				var node = $(this).tree('getSelected');

				if (node) {

					var nama = node.text;

					var link = node.link;

					var id = node.id;

					if (node.attributes) {

						link += "," + node.attributes.p1 + "," + node.attributes.p2;

						nama += "," + node.attributes.p1 + "," + node.attributes.p2;

						id += "," + node.attributes.p1 + "," + node.attributes.p2;

					}

					if ($('#tabs').tabs('exists', nama)) {

						$('#tabs').tabs('select', nama);

					} else {

						if (link == null || link == "") {

							return false;

						} else {

							var content = '<iframe src="' + link + '/index/' + window.btoa(id) + '" scrolling="yes" id="page" style="border: 0; width: 100%; height: 99%; margin:0;"></iframe>';

							//$('#form').panel('setTitle', nama);

							$('#tabs').tabs('add', {

								title: nama,

								content: content,

								closable: true,

								iconCls: node.iconCls

							});

						}

					}

				}

			}

		});

	}



	function convert(rows) {

		function exists(rows, parentId) {

			for (var i = 0; i < rows.length; i++) {

				if (rows[i].id == parentId) return true;

			}

			return false;

		}



		var nodes = [];

		// get the top level nodes

		for (var i = 0; i < rows.length; i++) {

			var row = rows[i];

			if (!exists(rows, row.menus_id)) {

				nodes.push({

					id: row.id,

					text: row.name,

					link: row.link,

					state: row.state,

					iconCls: row.icon

				});

			}

		}



		var toDo = [];

		for (var i = 0; i < nodes.length; i++) {

			toDo.push(nodes[i]);

		}

		while (toDo.length) {

			var node = toDo.shift(); // the parent node

			// get the children nodes

			for (var i = 0; i < rows.length; i++) {

				var row = rows[i];

				if (row.state != "closed") {

					if (row.icon != "") {

						var iconCls = row.icon;

					} else {

						var iconCls = "icon-file";

					}

				} else {

					var iconCls = "";

				}

				if (row.menus_id == node.id) {

					var child = {

						id: row.id,

						text: row.name,

						link: row.link,

						state: row.state,

						iconCls: iconCls

					};

					if (node.children) {

						node.children.push(child);

					} else {

						node.children = [child];

					}

					toDo.push(child);

				}

			}

		}

		return nodes;

	}



	function startChats(user_id) {

		$("#to_users_id").val(user_id);

		$("#dlg_chats").dialog('open');

		var h = $("#messageChats").get(0).scrollHeight;

		$("#messageChats").animate({

			scrollTop: h

		});

		$('#inputChats').focus();



		$.messager.progress({

			title: 'Please waiting',

			msg: 'Open Chat...'

		});



		$.ajax({

			type: "post",

			url: "<?= base_url('admin/users/readId') ?>",

			data: "id=" + user_id,

			dataType: "json",

			success: function(user) {

				$("#chatName").html(user.name);

				$("#chatPosition").html(user.position);

			}

		});



		$.ajax({

			type: "post",

			url: "<?= base_url('home/chats') ?>",

			data: "to_users_id=" + user_id,

			dataType: "html",

			success: function(chats) {

				$("#messageChats").html(chats);

				$.messager.progress('close');

			}

		});

	}



	$('#inputChats').keypress(function(e) {

		if (e.which == 13) {

			var inputChats = $(this).val();

			var to_users_id = $("#to_users_id").val();



			if (inputChats == "") {

				toastr.error("Message empty");

			} else {

				$.ajax({

					type: "post",

					url: "<?= base_url('home/createChats') ?>",

					data: "to_users_id=" + to_users_id + "&messages=" + inputChats,

					dataType: "json",

					success: function(response) {

						toastr.success("Message has been sent");

						startChats(to_users_id);

						$("#inputChats").val('');

						$("#inputChats").focus();

					}

				});

			}

		}

	});



	function profile() {

		$('#dlg_profile').dialog('open');

	}



	function approval() {

		$('#dlg_approval').dialog('open');

	}



	approvalList();

	approvalCount();

	resignations();

	setInterval(approvalList, 10000);

	setInterval(approvalCount, 10000);



	function approvalList() {

		$.ajax({

			type: "post",

			url: "<?= base_url('approvals/approvalList') ?>",

			dataType: "html",

			success: function(response) {

				$('#approvalList').html(response);

			}

		});

	}



	function approvalCount() {

		$.ajax({

			type: "post",

			url: "<?= base_url('approvals/approvalCount') ?>",

			dataType: "html",

			success: function(response) {

				$('#approvalCount').html(response);

			}

		});

	}



	function approvalDetail(table = "", approved_to = "", approved_by = "") {

		if (table == "" || approved_to == "") {

			toastr.error("Notification Cannot get Data", "Error");

		} else {

			$('#dlg_approval_detail').window('open');

			$("#table_name").textbox('setValue', table);

			$("#approved_to").textbox('setValue', approved_to);

			$("#approved_by").textbox('setValue', approved_by);

			$("#approveall").linkbutton('enable');

			$("#disapproveall").linkbutton('enable');



			if (table == "users") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalUsers/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'number',

							width: 120,

							halign: 'center',

							title: "User ID",

						}, {

							field: 'name',

							width: 150,

							halign: 'center',

							title: "Name"

						}, {

							field: 'description',

							width: 150,

							halign: 'center',

							title: "Description"

						}, {

							field: 'username',

							width: 100,

							halign: 'center',

							title: "Username",

						}, {

							field: 'email',

							width: 120,

							halign: 'center',

							title: "Email",

						}, {

							field: 'phone',

							width: 100,

							halign: 'center',

							title: "Phone",

						}, {

							field: 'position',

							width: 100,

							halign: 'center',

							title: "Position",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'created_date',

							width: 100,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								if (val != "-") {

									var approve = "approve('" + row.id + "','" + table + "')";

									var disapprove = "disapprove('" + row.id + "','" + table + "')";

									var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

									var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

									return a + " " + b;

								}

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "agreements") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalAgreements/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 150,

							halign: 'center',

							title: "Employee Name"

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division"

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'position_name',

							width: 100,

							halign: 'center',

							title: "Position",

						}, {

							field: 'date_sign',

							width: 100,

							halign: 'center',

							title: "Join Date",

						}, {

							field: 'date_expired',

							width: 100,

							halign: 'center',

							title: "Contract Expired",

						}, {

							field: 'note',

							width: 150,

							halign: 'center',

							title: "Note",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'created_date',

							width: 100,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								if (val != "-") {

									var approve = "approve('" + row.id + "','" + table + "')";

									var disapprove = "disapprove('" + row.id + "','" + table + "')";

									var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

									var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

									return a + " " + b;

								}

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "mutations") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalMutations/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 150,

							halign: 'center',

							title: "Employee Name"

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division"

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'trans_date',

							width: 100,

							halign: 'center',

							title: "Trans Date",

						}, {

							field: 'type',

							width: 100,

							halign: 'center',

							title: "Type",

						}, {

							field: 'description',

							width: 150,

							halign: 'center',

							title: "Note",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'created_date',

							width: 100,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								if (val != "-") {

									var approve = "approve('" + row.id + "','" + table + "')";

									var disapprove = "disapprove('" + row.id + "','" + table + "')";

									var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

									var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

									return a + " " + b;

								}

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "permits") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalPermits/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'permit_type_name',

							width: 100,

							halign: 'center',

							title: "Permit Type",

						}, {

							field: 'permit_date',

							width: 100,

							align: 'center',

							title: "Permit Date",

						}, {

							field: 'reason_name',

							width: 100,

							halign: 'center',

							title: "Reason",

						}, {

							field: 'note',

							width: 200,

							halign: 'center',

							title: "Note",

						}, {

							field: 'attachment',

							width: 100,

							align: 'center',

							title: "Attachment",

							formatter: FormatterFile

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "overtimes") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalOvertimes/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'trans_date',

							width: 100,

							halign: 'center',

							title: "Trans Date",

						}, {

							field: 'start',

							width: 80,

							align: 'center',

							title: "Start",

						}, {

							field: 'end',

							width: 80,

							halign: 'center',

							title: "End",

						}, {

							field: 'type',

							width: 100,

							halign: 'center',

							title: "Type",

						}, {

							field: 'duration_hour',

							width: 80,

							halign: 'center',

							title: "Duration",

						}, {

							field: 'duration_convert',

							width: 100,

							halign: 'center',

							title: "Convert",

						}, {

							field: 'amount',

							width: 100,

							halign: 'center',

							title: "Amount",

						}, {

							field: 'remarks',

							width: 200,

							halign: 'center',

							title: "Note",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created By",

						}, {

							field: 'created_date',

							width: 200,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "cash_carries") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalCashCarries/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'trans_date',

							width: 100,

							halign: 'center',

							title: "Trans Date",

						}, {

							field: 'start',

							width: 80,

							align: 'center',

							title: "Start",

						}, {

							field: 'end',

							width: 80,

							halign: 'center',

							title: "End",

						}, {

							field: 'duration_hour',

							width: 80,

							halign: 'center',

							title: "Duration",

						}, {

							field: 'amount',

							width: 80,

							halign: 'center',

							align: 'right',

							title: "Amount",

						}, {

							field: 'plan',

							width: 100,

							halign: 'center',

							title: "Plan Output",

						}, {

							field: 'actual',

							width: 100,

							halign: 'center',

							title: "Actual Output",

						}, {

							field: 'type',

							width: 100,

							halign: 'center',

							title: "Type",

						}, {

							field: 'remarks',

							width: 200,

							halign: 'center',

							title: "Note",

						}, {

							field: 'attachment_idm',

							width: 80,

							halign: 'center',

							title: "IDM",

							formatter:fileFormatter2

						}, {

							field: 'remarks',

							width: 200,

							halign: 'center',

							title: "Note",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "change_days") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalChangeDays/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'start',

							width: 100,

							align: 'center',

							title: "Date From",

						}, {

							field: 'end',

							width: 100,

							halign: 'center',

							title: "Date To",

						}, {

							field: 'remarks',

							width: 200,

							halign: 'center',

							title: "Note",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created By",

						}, {

							field: 'created_date',

							width: 200,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "payrolls") {

				$("#approveall").linkbutton('disable');

				$("#disapproveall").linkbutton('disable');



				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					showFooter: true,

					url: '<?= base_url('approvals/approvalPayrolls/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'group_name',

							width: 150,

							halign: 'center',

							title: "Description",

						}, {

							field: 'employee',

							width: 80,

							halign: 'center',

							align: 'right',

							title: "Employee",

							formatter: numberformat

						}, {

							field: 'amount',

							width: 100,

							halign: 'center',

							align: 'right',

							title: "Amount",

							formatter: numberformat

						}, {

							field: 'name',

							width: 100,

							halign: 'center',

							title: "Created By",

						}, {

							field: 'created_date',

							width: 100,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								if (val != "-") {

									var approve = "approvePayrolls('" + row.group_id + "','" + table + "','" + btoa(row.period_start) + "','" + btoa(row.period_end) + "')";

									var disapprove = "disapprovePayrolls('" + row.group_id + "','" + table + "','" + btoa(row.period_start) + "','" + btoa(row.period_end) + "')";

									var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

									var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

									return a + " " + b;

								}

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "payroll_harian_lepas") {
				$("#approveall").linkbutton('disable');
				$("#disapproveall").linkbutton('disable');

				$('#dg_approval').datagrid({
					singleSelect: true,
					rownumbers: true,
					showFooter: true,
					url: '<?= base_url('approvals/approvalPayrollHarianLepas/') ?>' + approved_to + "/" + approved_by,
					columns: [
						[{
							field: 'group_name',
							width: 150,
							halign: 'center',
							title: "Description",
						}, {
							field: 'employee',
							width: 80,
							halign: 'center',
							align: 'right',
							title: "Employee",
							formatter: numberformat
						}, {
							field: 'amount',
							width: 100,
							halign: 'center',
							align: 'right',
							title: "Amount",
							formatter: numberformat
						}, {
							field: 'name',
							width: 100,
							halign: 'center',
							title: "Created By",
						}, {
							field: 'created_date',
							width: 100,
							halign: 'center',
							title: "Created Date",
						}, {
							field: 'action',
							width: 80,
							align: 'center',
							title: "Action",
							formatter: function(val, row) {
								if (val != "-") {
									var approve = "approvePayrollHarianLepas('" + row.group_id + "','" + table + "','" + btoa(row.period_start) + "','" + btoa(row.period_end) + "')";
									var disapprove = "disapprovePayrollHarianLepas('" + row.group_id + "','" + table + "','" + btoa(row.period_start) + "','" + btoa(row.period_end) + "')";
									var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';
									var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';
									return a + " " + b;
								}
							}
						}]
					],
				}).datagrid('enableFilter');

			} else if (table == "setup_salaries") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalSetupSalary/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'salary_component_name',

							width: 150,

							halign: 'center',

							title: "Salary Component",

						}, {

							field: 'amount',

							width: 150,

							halign: 'center',

							title: "Amount",

						}, {

							field: 'bpjs',

							width: 100,

							halign: 'center',

							title: "BPJS",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created By",

						}, {

							field: 'created_date',

							width: 200,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'updated_by',

							width: 100,

							halign: 'center',

							title: "Updated By",

						}, {

							field: 'updated_date',

							width: 200,

							halign: 'center',

							title: "Updated Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "warning_letters") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalWarningLetters/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'warning_letter',

							width: 100,

							align: 'center',

							title: "Warning Letter",

						}, {

							field: 'trans_date',

							width: 100,

							halign: 'center',

							title: "Trans Date",

						}, {

							field: 'issue_date',

							width: 100,

							halign: 'center',

							title: "Issue Date",

						}, {

							field: 'violation_name',

							width: 150,

							halign: 'center',

							title: "Violation",

						}, {

							field: 'remarks',

							width: 150,

							halign: 'center',

							title: "Remarks",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created By",

						}, {

							field: 'created_date',

							width: 200,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "resignations") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalResignations/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'resign_type',

							width: 100,

							align: 'center',

							title: "Resign Type",

						}, {

							field: 'trans_date',

							width: 100,

							halign: 'center',

							title: "Trans Date",

						}, {

							field: 'resign_date',

							width: 100,

							halign: 'center',

							title: "Resign Date",

						}, {

							field: 'reason_name',

							width: 150,

							halign: 'center',

							title: "Reason",

						}, {

							field: 'remarks',

							width: 150,

							halign: 'center',

							title: "Remarks",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created By",

						}, {

							field: 'created_date',

							width: 200,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "employees"){

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalEmployees/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'group_name',

							width: 120,

							halign: 'center',

							title: "Group",

						}, {

							field: 'marital_name',

							width: 100,

							halign: 'center',

							title: "Marital",

						}, {

							field: 'date_sign',

							width: 100,

							halign: 'center',

							title: "Sign Date",

						}, {

							field: 'date_expired',

							width: 100,

							halign: 'center',

							title: "Expired Date",

						}, {

							field: 'national_id',

							width: 150,

							halign: 'center',

							title: "National ID",

						}, {

							field: 'jamsostek',

							width: 120,

							halign: 'center',

							title: "Jamsostek",

						}, {

							field: 'jkn',

							width: 120,

							halign: 'center',

							title: "JKN",

						}, {

							field: 'email',

							width: 150,

							halign: 'center',

							title: "Email",

						}, {

							field: 'bank_no',

							width: 100,

							halign: 'center',

							title: "Bank No",

						}, {

							field: 'bank_name',

							width: 150,

							halign: 'center',

							title: "Bank Name",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			} else if (table == "lnd_request_training_approvals_history"){
				$('#dg_approval').datagrid({
					singleSelect: true,
					rownumbers: true,
					url: '<?= base_url('approvals/approvalRequestTraining/') ?>' + approved_to + "/" + approved_by,
					columns: [
						[{
							field: 'requestTrainingId',
							width: 120,
							halign: 'center',
							title: "Request Training ID",
						}, {
							field: 'induction',
							width: 200,
							halign: 'center',
							title: "Induction",
						}, {
							field: 'trainingActivities',
							width: 150,
							halign: 'center',
							title: "Training Activities",
						}, {
							field: 'suggestDate',
							width: 150,
							halign: 'center',
							title: "Suggest Date Training",
						}, {
							field: 'reasons',
							width: 150,
							halign: 'center',
							title: "Reasons",
						}, {
							field: 'trainer',
							width: 120,
							halign: 'center',
							title: "Trainer",
						}, {
							field: 'trainerName',
							width: 100,
							halign: 'center',
							title: "Trainer Name",
						}, {
							field: 'action',
							width: 80,
							align: 'center',
							title: "Action",
							formatter: function(val, row) {
								var approve = "approveLnd('" + row.approvalHistoryId + "','" + table + "','" + row.createdBy + "')";
								var disapprove = "disapproveLnd('" + row.approvalHistoryId + "','" + table + "')";
								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';
								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';
								return a + " " + b;
							}
						}]
					],
				}).datagrid('enableFilter');

			} else if (table == "setup_allowances") {

				$('#dg_approval').datagrid({

					singleSelect: true,

					rownumbers: true,

					url: '<?= base_url('approvals/approvalSetupAllowances/') ?>' + approved_to + "/" + approved_by,

					columns: [

						[{

							field: 'employee_number',

							width: 120,

							halign: 'center',

							title: "Employee ID",

						}, {

							field: 'employee_name',

							width: 200,

							halign: 'center',

							title: "Employee Name",

						}, {

							field: 'division_name',

							width: 150,

							halign: 'center',

							title: "Division",

						}, {

							field: 'departement_name',

							width: 150,

							halign: 'center',

							title: "Departement",

						}, {

							field: 'departement_sub_name',

							width: 150,

							halign: 'center',

							title: "Departement Sub",

						}, {

							field: 'allowance',

							width: 150,

							halign: 'center',

							title: "Allowance",

						}, {

							field: 'amount',

							width: 150,

							halign: 'center',

							title: "Amount",

						}, {

							field: 'created_by',

							width: 100,

							halign: 'center',

							title: "Created By",

						}, {

							field: 'created_date',

							width: 200,

							halign: 'center',

							title: "Created Date",

						}, {

							field: 'updated_by',

							width: 100,

							halign: 'center',

							title: "Updated By",

						}, {

							field: 'updated_date',

							width: 200,

							halign: 'center',

							title: "Updated Date",

						}, {

							field: 'action',

							width: 80,

							align: 'center',

							title: "Action",

							formatter: function(val, row) {

								var approve = "approve('" + row.id + "','" + table + "')";

								var disapprove = "disapprove('" + row.id + "','" + table + "')";

								var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';

								var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';

								return a + " " + b;

							}

						}]

					],

				}).datagrid('enableFilter');

			}

		}

	}

	function approveLnd(id, tablename, createdBy) {
		$.messager.confirm('Warning', 'Are you sure you want to approve this data?', function(r) {
			if (r) {
				$.post('<?= base_url('approvals/approveLnd') ?>', {
					id: id,
					tablename: tablename,
					createdBy: createdBy
				}, function(result) {
					var result = eval('(' + result + ')');
					if (result.theme == "success") {
						toastr.success(result.message, result.title);
						$("#dg_approval").datagrid("reload");
					} else {
						toastr.error(result.message, result.title);
					}
				});
			}
		});
	}

	function disapproveLnd(id, tablename) {
		$.messager.prompt('Warning', 'Are you sure you want to disapprove this data? please fill remark', function(r) {
			if (r) {
				$.post('<?= base_url('approvals/disapproveLnd') ?>', {
					id: id,
					tablename: tablename,
					remark: r
				}, function(result) {
					var result = eval('(' + result + ')');

					if (result.theme == "success") {
						toastr.success(result.message, result.title);
						$("#dg_approval").datagrid("reload");
					} else {
						toastr.error(result.message, result.title);
					}
				});
			}
		});
	}


	function export_excel() {

		$('#dg_approval').datagrid('toExcel', 'approvals.xls');

	}



	function notification() {

		$('#dlg_notif').dialog('open');

	}



	notificationList();

	notificationCount();

	setInterval(notificationList, 20000);

	setInterval(notificationCount, 20000);



	function notificationList() {

		$.ajax({

			type: "post",

			url: "<?= base_url('home/notificationList') ?>",

			dataType: "html",

			success: function(response) {

				$('#notificationList').html(response);

			}

		});

	}



	function notificationCount() {

		$.ajax({

			type: "post",

			url: "<?= base_url('home/notificationCount') ?>",

			dataType: "html",

			success: function(response) {

				$('#notificationCount').html(response);

			}

		});

	}



	function approveall() {

		var table_name = $("#table_name").textbox('getValue');

		var approved_to = $("#approved_to").textbox('getValue');

		var approved_by = $("#approved_by").textbox('getValue');



		$.messager.confirm('Warning', 'Are you sure you want to approve all data?', function(r) {

			if (r) {

				$.post('<?= base_url('approvals/approveall') ?>', {

					table_name: table_name,

					approved_to: approved_to,

					approved_by: approved_by,

				}, function(result) {

					var result = eval('(' + result + ')');

					if (result.theme == "success") {

						toastr.success(result.message, result.title);

						$("#dg_approval").datagrid("reload");

					} else {

						toastr.error(result.message, result.title);

					}

				});

			}

		});

	}



	function disapproveall() {

		var table_name = $("#table_name").textbox('getValue');

		var approved_to = $("#approved_to").textbox('getValue');

		var approved_by = $("#approved_by").textbox('getValue');



		$.messager.confirm('Warning', 'Are you sure you want to disapprove all data?', function(r) {

			if (r) {

				Swal.fire({

					title: 'Please Wait for Process Disapprove',

					showConfirmButton: false,

					allowOutsideClick: false,

					allowEscapeKey: false,

					didOpen: () => {

						Swal.showLoading();

					},

				});



				$.post('<?= base_url('approvals/disapproveall') ?>', {

					table_name: table_name,

					approved_to: approved_to,

					approved_by: approved_by,

				}, function(result) {

					var result = eval('(' + result + ')');

					Swal.close();



					if (result.theme == "success") {

						toastr.success(result.message, result.title);

						$("#dg_approval").datagrid("reload");

					} else {

						toastr.error(result.message, result.title);

					}

				});

			}

		});

	}



	function approve(id, tablename) {

		$.messager.confirm('Warning', 'Are you sure you want to approve this data?', function(r) {

			if (r) {

				$.post('<?= base_url('approvals/approve') ?>', {

					id: id,

					tablename: tablename

				}, function(result) {

					var result = eval('(' + result + ')');

					if (result.theme == "success") {

						toastr.success(result.message, result.title);

						$("#dg_approval").datagrid("reload");

					} else {

						toastr.error(result.message, result.title);

					}

				});

			}

		});

	}



	function disapprove(id, tablename) {

		$.messager.confirm('Warning', 'Are you sure you want to disapprove this data?', function(r) {

			if (r) {

				$.post('<?= base_url('approvals/disapprove') ?>', {

					id: id,

					tablename: tablename

				}, function(result) {

					var result = eval('(' + result + ')');



					if (result.theme == "success") {

						toastr.success(result.message, result.title);

						$("#dg_approval").datagrid("reload");

					} else {

						toastr.error(result.message, result.title);

					}

				});

			}

		});

	}



	function approvePayrolls(id, tablename, period_start, period_end) {

		$.messager.confirm('Warning', 'Are you sure you want to approve this data?', function(r) {

			if (r) {

				Swal.fire({

					title: 'Please Wait to Approved Data',

					showConfirmButton: false,

					allowOutsideClick: false,

					allowEscapeKey: false,

					didOpen: () => {

						Swal.showLoading();

					},

				});



				$.post('<?= base_url('approvals/approvePayrolls') ?>', {

					id: id,

					tablename: tablename,

					period_start: period_start,

					period_end: period_end

				}, function(result) {

					var result = eval('(' + result + ')');

					if (result.theme == "success") {

						toastr.success(result.message, result.title);

						$("#dg_approval").datagrid("reload");

						Swal.close();

					} else {

						toastr.error(result.message, result.title);

						Swal.close();

					}

				});

			}

		});

	}



	function disapprovePayrolls(id, tablename, period_start, period_end) {

		$.messager.confirm('Warning', 'Are you sure you want to disapprove this data?', function(r) {

			if (r) {

				Swal.fire({

					title: 'Please Wait to Disapprove Data',

					showConfirmButton: false,

					allowOutsideClick: false,

					allowEscapeKey: false,

					didOpen: () => {

						Swal.showLoading();

					},

				});



				$.post('<?= base_url('approvals/disapprovePayrolls') ?>', {

					id: id,

					tablename: tablename,

					period_start: period_start,

					period_end: period_end

				}, function(result) {

					var result = eval('(' + result + ')');



					if (result.theme == "success") {

						toastr.success(result.message, result.title);

						$("#dg_approval").datagrid("reload");

						Swal.close();

					} else {

						toastr.error(result.message, result.title);

						Swal.close();

					}

				});

			}

		});

	}



	function approvePayrollHarianLepas(id, tablename, period_start, period_end) {
		$.messager.confirm('Warning', 'Are you sure you want to approve this data?', function(r) {
			if (r) {
				Swal.fire({
					title: 'Please Wait to Approved Data',
					showConfirmButton: false,
					allowOutsideClick: false,
					allowEscapeKey: false,
					didOpen: () => {
						Swal.showLoading();
					},
				});

				$.post('<?= base_url('approvals/approvePayrollHarianLepas') ?>', {
					id: id,
					tablename: tablename,
					period_start: period_start,
					period_end: period_end
				}, function(result) {
					var result = eval('(' + result + ')');
					if (result.theme == "success") {
						toastr.success(result.message, result.title);
						$("#dg_approval").datagrid("reload");
						Swal.close();
					} else {
						toastr.error(result.message, result.title);
						Swal.close();
					}
				});
			}
		});
	}



	function disapprovePayrollHarianLepas(id, tablename, period_start, period_end) {
		$.messager.confirm('Warning', 'Are you sure you want to disapprove this data?', function(r) {
			if (r) {
				Swal.fire({
					title: 'Please Wait to Disapprove Data',
					showConfirmButton: false,
					allowOutsideClick: false,
					allowEscapeKey: false,
					didOpen: () => {
						Swal.showLoading();
					},
				});

				$.post('<?= base_url('approvals/disapprovePayrollHarianLepas') ?>', {
					id: id,
					tablename: tablename,
					period_start: period_start,
					period_end: period_end
				}, function(result) {
					var result = eval('(' + result + ')');

					if (result.theme == "success") {
						toastr.success(result.message, result.title);
						$("#dg_approval").datagrid("reload");
						Swal.close();
					} else {
						toastr.error(result.message, result.title);
						Swal.close();
					}
				});
			}
		});
	}



	//Resign Employee

	function resignations() {

		$.ajax({

			type: "post",

			url: "<?= base_url('relation/resignations/readEmployeeResign') ?>",

			success: function(response) {

				//

			}

		});

	}



	function logout() {

		Swal.fire({

			title: 'Please Wait for Logout System',

			showConfirmButton: false,

			allowOutsideClick: false,

			allowEscapeKey: false,

			didOpen: () => {

				Swal.showLoading();

			},

		});



		processingLogout();



		function processingLogout() {

			$.ajax({

				type: "post",

				url: "<?= base_url('login/logout') ?>",

				dataType: "html",

				success: function(response) {

					if (response == 0) {

						setTimeout(function() {

							window.location.assign("<?= base_url('login') ?>");

						}, 10000);

					} else {

						processingLogout();

					}

				}

			});

		}

	}



	function numberformat(value, row) {

		var formatter = new Intl.NumberFormat('id-ID');



		return "<b>" + formatter.format(value) + "</b>";

	}



	function FormatterFile(value) {

		if (value == "" || value == null) {

			return '-';

		} else {

			return '<a href="' + value + '" target="_blank" style="text-decoration: none;"><i class="fa fa-eye"></i> View</a>';

		}

	};



	function fileFormatter2(value) {

        if (value == "" || value == null) {

            return '-';

        } else {

            return '<a href="<?= base_url('assets/image/cash_carry/') ?>' + value + '" target="_blank" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;"><i class="fa fa-eye"></i> View</a>';

        }

    };

    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }
</script>



</html>