<div class="easyui-accordion" style="width:100%;">
	<div title="Click this to hide the filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
		<form id="frm_search" method="post" enctype="multipart/form-data" novalidate>
			<!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
			<fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
				<legend><b>Form Filter Data</b></legend>
				<div style="width: 50%; float:left;">
					<div class="fitem">
						<span style="width:30%; display:inline-block;">Period Date</span>
						<input style="width:29%;" name="filter_from" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox"> To
                        <input style="width:29%;" name="filter_to" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
					</div>
					<div class="fitem">
						<span style="width:30%; display:inline-block;">Departement</span>
						<input style="width:60%;" name="filter_departement" id="filter_departement" class="easyui-combobox">
					</div>
					<div class="fitem">
						<span style="width:30%; display:inline-block;">Training Name</span>
						<input style="width:60%;" name="filter_training_name" id="filter_training_name" class="easyui-combobox">
					</div>
					<div class="fitem">
						<span style="width:30%; display:inline-block;"></span>
						<a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
					</div>
				</div>
				<div style="width: 49%; float:left;">
					<div class="fitem">
						<span style="width:30%; display:inline-block;">Trainer Name</span>
						<input style="width:60%;" name="filter_trainer_name" id="filter_trainer_name" class="easyui-combogrid">
					</div>
					<div class="fitem">
						<span style="width:30%; display:inline-block;">Trainee Name</span>
						<input style="width:60%;" name="filter_trainee_name" id="filter_trainee_name" class="easyui-combobox">
					</div>
				</div>
			</fieldset>
			<?= $button ?>
		</form>
	</div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">
	<iframe id="printout" src="" style="width: 100%; height:100vh; border:0;"></iframe>
</div>

<script>
	function reload() {
		window.location.reload();
	}

	function pdf() {
		$("#printout").get(0).contentWindow.print();
	}

	function filter() {
		var form = $("#frm_search").serialize()
		var url = form;

		$("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
		$("#printout").attr('src', '<?= base_url('lnd/Trainee_Training_History/print?') ?>' + url);
	}

	function excel() {
		var form = $("#frm_search").serialize()
		var url = form;

		window.location.assign('<?= base_url('lnd/Trainee_Training_History/print/excel?') ?>' + url);
	}

	$(function() {
		//Get Departement
		$('#filter_departement').combobox({
			url: '<?php echo base_url('employee/departements/reads'); ?>',
			valueField: 'id',
			textField: 'name',
			prompt: 'Choose All',
			icons: [{
				iconCls: 'icon-clear',
				handler: function(e) {
					$(e.data.target).combobox('clear').combobox('textbox').focus();
				}
			}],
			onSelect: function(departement) {
				console.log("#dept", departement);
				
				$('#filter_training_name').combogrid({
					url: '<?= base_url('lnd/Trainee_Training_History/readTrainingName?dept_name=') ?>'+encodeURIComponent(departement.name),
					panelWidth: 450,
					idField: 'id',
					textField: 'name',
					mode: 'remote',
					fitColumns: true,
					prompt: 'Choose All',
					icons: [{
						iconCls: 'icon-clear',
						handler: function(e) {
							$(e.data.target).combogrid('clear').combogrid('textbox').focus();
						}
					}],
					columns: [
						[{
							field: 'name',
							title: 'Training Name',
							width: 200
						}, {
							field: 'induction',
							title: 'Induction',
							width: 200
						}]
					],
				});
			}
		});

		$('#filter_trainer_name').combogrid({
			url: '<?= base_url('lnd/request_training/readsEmployeesStaffUp') ?>',
			panelWidth: 450,
			idField: 'name',
			textField: 'name',
			mode: 'remote',
			fitColumns: true,
			prompt: 'Choose All',
			icons: [{
				iconCls: 'icon-clear',
				handler: function(e) {
					$(e.data.target).combogrid('clear').combogrid('textbox').focus();
				}
			}],
			columns: [
				[{
					field: 'number',
					title: 'Employee ID',
					width: 120
				}, {
					field: 'name',
					title: 'Employee Name',
					width: 200
				}]
			],
		});

		$('#filter_trainee_name').combogrid({
			url: '<?= base_url('employee/employees/reads') ?>',
			panelWidth: 450,
			idField: 'id',
			textField: 'name',
			mode: 'remote',
			fitColumns: true,
			prompt: 'Choose All',
			icons: [{
				iconCls: 'icon-clear',
				handler: function(e) {
					$(e.data.target).combogrid('clear').combogrid('textbox').focus();
				}
			}],
			columns: [
				[{
					field: 'number',
					title: 'Employee ID',
					width: 120
				}, {
					field: 'name',
					title: 'Employee Name',
					width: 200
				}]
			],
		});

		$('#filter_training_name').combogrid({
			url: '<?= base_url('lnd/Trainee_Training_History/readTrainingName') ?>',
			panelWidth: 450,
			idField: 'id',
			textField: 'name',
			mode: 'remote',
			fitColumns: true,
			prompt: 'Choose All',
			icons: [{
				iconCls: 'icon-clear',
				handler: function(e) {
					$(e.data.target).combogrid('clear').combogrid('textbox').focus();
				}
			}],
			columns: [
				[{
					field: 'name',
					title: 'Training Name',
					width: 200
				}, {
					field: 'induction',
					title: 'Induction',
					width: 200
				}]
			],
		});
	});

	//Format Datepicker
	function myformatter(date) {
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
	}

	//Format Datepicker
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
