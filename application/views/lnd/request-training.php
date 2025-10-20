<table id="dg" class="easyui-datagrid" style="width:100%;">
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width:50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Suggest Training Date</span>
                        <input style="width:60%;" id="suggestTrainingDateFilter" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Training Activities</span>
                        <input style="width:60%;" id="trainingMaterialFilter" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Trainee</span>
                        <input style="width:60%;" id="traineeFilter" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width:50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Reasons</span>
                        <!-- <input style="width:60%;"  class="easyui-combobox"> -->
                        <select style="width:60%;" id="reasonsFilter" value="" name="reasons" class="easyui-combobox" panelHeight="auto">
                            <option value="" selected></option>    
                            <option value="Promotion">Promotion</option>
                            <option value="Mutation">Mutation</option>
                            <option value="New Product">New Product</option>
                            <option value="New Technology">New Technology</option>
                            <option value="New System">New System</option>
                            <option value="Skill Upgrades">Skill Upgrades</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement</span>
                        <input style="width:60%;" id="departementFilter" class="easyui-combogrid">
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <div style="width:50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Induction</span>
                    <input style="width:60%;" id="inductionForm" name ="induction" class="easyui-textbox" >
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Training Activities</span>
                    <input style="width:60%;" id="trainingActivitiesForm" name="trainingActivities" class="easyui-textbox" required="">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Suggest Date Training</span>
                    <input style="width:60%;" id="suggestDateTrainingForm" name="suggestDateTraining" class="easyui-datebox" required="" data-options="formatter:myformatter,parser:myparser, editable: false" >
                </div>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Reasons</span>
					<select style="width:60%;" name="reasons" required="" class="easyui-combobox" panelHeight="auto">
						<option value="Promotion">Promotion</option>
						<option value="Mutation">Mutation</option>
						<option value="New Product">New Product</option>
						<option value="New Technology">New Technology</option>
						<option value="New System">New System</option>
						<option value="Skill Upgrades">Skill Upgrades</option>
					</select>
				</div>
            </div>
            <div style="width:50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trainer</span>
                    <select style="width:60%;" name="trainer" id="trainer" required="" class="easyui-combobox" panelHeight="auto">
                        <option value="Internal">Internal</option>
                        <option value="External">External</option>
                    </select>  
                </div>
				<div class="fitem" id="trainerNameContainer">
					<!-- <span style="width:35%; display:inline-block;">Trainer Name</span>
					<input style="width:60%;" id="trainerNameText" name="trainer_name_internal" class="easyui-textbox" required=""> -->
				</div>
                <div class="fitem" id="trainerNameExternalContainer">
					<!-- <span style="width:35%; display:inline-block;">Trainer Name</span>
					<input style="width:60%;" id="trainerNameFilter" name="trainer_name_external" class="easyui-combogrid" required=""> -->
				</div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trainer Fees</span>
                    <input style="width:60%;" id="trainerFeesForm" name="trainerFees" class="easyui-textbox">
                </div>
				<div class="fitem" style="display: flex">
					<span style="width:35%;">Attachment</span>
                    <div style="width:60%;">
                        <input style="width:100%;" name="attachment" id="attachment" class="easyui-filebox"  data-options="
                            accept: 'image/jpeg,image/png,image/gif,application/pdf',">
						<span style="color:red;">Only PDF and image files are allowed for data attachment upload.</span>
						<span style="color:red;">Max 2 MB</span>
                    </div>
				</div>
            </div>
        </fieldset>
            <div class="easyui-tabs" style="width:100%; float: left;">
                <!-- Trainee Information -->
                <div title="Trainee">
                    <div id="toolbarTrainee">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="addTrainee()"><i class="fa fa-plus"></i> Add</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeTrainee()"><i class="fa fa-times"></i> Remove</a>
                    </div>

                    <table id="dgTrainee" class="easyui-datagrid" style="width:100%;" toolbar="#toolbarTrainee"></table>
                </div>
            </div>
    </form>
</div>

<!-- DIALOG UPLOAD -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<div id="dlg_attachment" class="easyui-dialog" title="Attachment" data-options="closed: true,modal:true" style="width: 1500px; height: 700px; padding:10px; top: 20px;">
    <div id="attachmentContainer">
        
    </div>
</div>

<div id="dlg_approval_history" class="easyui-dialog" title="History Approval" data-options="closed: true,modal:true" style="width: 500px; height: 500px; padding:10px; top: 20px;">
    <table id="dgApprovalHistory" class="easyui-datagrid" style="width:100%;"></table>
</div>

<!-- PDF -->
<iframe id="printout" style="width: 100%;" hidden></iframe>

<script>
    window.onload = function() {
        
    };

    var isUpdate;

    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('lnd/request_training/create_data') ?>';
        method = 'POST';
        $('#frm_insert').form('clear');
        tableTrainee();
        isUpdate = false;
    }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            tableTrainee(row.id);
            url_save = '<?= base_url('lnd/request_training/update_data/') ?>' + row.id;
            method = 'POST';
            isUpdate = true;
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        fetch('<?= base_url('lnd/request_training/delete_data/') ?>'+row.id, {
                            method: 'DELETE', // Metode DELETE
                        })
                        .then(response => response.json()) // Konversi response ke JSON
                        .then(data => {
                            if (data.code === 200) {
                                $('#dg').datagrid('reload');
                                toastr.success(data.message, 'Success');
                            } else {
                                toastr.success("Something Wrong", 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Terjadi kesalahan:', error);
                            toastr.success("Something Wrong", 'error');
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    // function sendDataToServer(requestData) {
    //     // Buat body dengan format x-www-form-urlencoded (query string)
    //     const formData = new URLSearchParams(requestData).toString();
    //     fetch(url_save, {
    //         method: method, // Metode POST
    //         headers: {
    //             'Content-Type': 'application/x-www-form-urlencoded' // Header penting
	// 			multi
    //         },
    //         body: formData // Data body
    //     })
    //     .then(response => {
    //         return response.json()}) // Ubah response ke JSON
    //     .then(data => {
    //         if(data.code >= 200 && data.code <= 300) {
    //             toastr.success(data.message, 'Success');
    //             $('#dg').datagrid('reload');
    //             $('#dlg_insert').dialog('close');
    //
    //         }
    //     })
    //     .catch(error => {
    //         toastr.error('Something Error', 'Error');
    //         console.error('Terjadi kesalahan:', error);
    //     });
    // }

	function sendDataToServer(requestData, isFormData) {
		const options = {
			method: method // e.g. 'POST'
		};

		if (isFormData) {
			options.body = requestData;
			// Do NOT set Content-Type — browser sets it automatically for FormData
		} else {
			const formEncoded = new URLSearchParams(requestData).toString();
			options.headers = {
				'Content-Type': 'application/x-www-form-urlencoded'
			};
			options.body = formEncoded;
		}

		fetch(url_save, options)
			.then(response => response.json())
			.then(data => {
				if (data.code >= 200 && data.code <= 300) {
                    let id = data.data.id;
                    insertTraine(data, id);
				} else {
					toastr.error(data.message || 'Server Error', 'Error');
				}
			})
			.catch(error => {
				toastr.error('Something went wrong', 'Error');
				console.error('Request failed:', error);
			});
	}

    function insertTraine(data, id) {

        var rowForm = $('#dgTrainee').datagrid('getRows');
        var totalForm = rowForm.length;
        const updateId = url_save.split('/').pop();
        var completedRequests = 0;

        for (let i = 0; i < totalForm; i++) {
            if (rowForm[i].fullName) {
				console.log(rowForm);
                $.ajax({
                    type: "POST", 
                    url: '<?= base_url('lnd/request_training/create_data_trainee') ?>',
                    data: {
                        id: rowForm[i].id,
                        fullName: rowForm[i].fullName,
                        trainingRequestId: isUpdate ? updateId: id,
                        national_id: rowForm[i].national_id,
                        position: rowForm[i].position,
                        departement: rowForm[i].departement,
                        departement_subs: rowForm[i].departement_subs,
                        date_sign: rowForm[i].date_sign
                    },
                    dataType: "json",
                    success: function(res) {
                        console.log(res, ' 392');
                        completedRequests++;
                        if (completedRequests === totalForm) {
                            toastr.success(data.message, 'Success');
                            $('#dg').datagrid('reload');
                            $('#dlg_insert').dialog('close');
                        }
                    }
                });
            }
        }

    }

    function updateTraine(id) {
        var rowForm = $('#dgTrainee').datagrid('getRows');
        var totalForm = rowForm.length;
        var completedRequests = 0;

        for (let i = 0; i < totalForm; i++) {
            if (rowForm[i].fullName) {
                $.ajax({
                    type: "POST",
                    url: '<?= base_url('lnd/request_training/create_data_trainee') ?>',
                    data: {
                        id: rowForm[i].id,
                        fullName: rowForm[i].fullName,
                        national_id: rowForm[i].national_id,
                        position: rowForm[i].position,
                        departement: rowForm[i].departement,
                        departement_subs: rowForm[i].departement_subs,
                        date_sign: rowForm[i].date_sign
                    },
                    dataType: "json",
                    success: function(res) {
                        console.log(res, ' update trainee');
                        completedRequests++;
                        if (completedRequests === totalForm) {
                            toastr.success('Data berhasil diupdate', 'Success');
                            $('#dg').datagrid('reload');
                            $('#dlg_insert').dialog('close');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Gagal mengupdate data', 'Error');
                        console.error('Request failed:', error);
                    }
                });
            }
        }
    }


    function showHistoryApproval(id) {
        
        $("#dgApprovalHistory").datagrid({
            url: '<?= base_url('lnd/request_training/history_approval') ?>?trainingRequestId=' + id,
            width: 500,
            height: 400,
            pagination: false,
            rownumbers: true,
            fit: true,
            nowrap: false,
            singleSelect: true,
            loadMsg: 'Loading...',
            columns: [[
                {field: 'approver_name', title: 'Historical', width: 250, halign: 'center'},
                {field: 'approved_date', title: 'Date Approved', width: 150, halign: 'center'}
            ]],
            onLoadSuccess: function(data) {
                if (data.total === 0) {
                    $(this).datagrid('appendRow', {
                        requestTrainingId: 'No data available',
                        induction: '-'
                    });
                }
            }
        });
        $('#dlg_approval_history').dialog('open');
        
    }
    
	$(function() {
        var id = '123'
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('lnd/request_training/datatables') ?>',
            view: detailview,
            width:500,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;"><table class="ddv"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('lnd/request_training/detailTrainee') ?>?trainingRequestId=' + row.id,
                    fitColumns: false,
                    nowrap: false,
                    singleSelect: true,
                    rownumbers: true,
                    loadMsg: '',
                    height: 'auto',
                    columns: [[
                        {field: 'fullName', title: 'Full Name', width: 150, halign: 'center'},
                        {field: 'national_id', title: 'Employee ID', width: 150, halign: 'center'},
                        {field: 'position', title: 'Position', width: 150, halign: 'left'},
                        {field: 'departement', title: 'Departement', width: 150, align: 'center'},
                        {field: 'departement_subs', title: 'Sub Departement', width: 150, align: 'center'},
                        {field: 'join_date', title: 'Join Date', width: 250, align: 'center'},
                    ]],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            },
            columns: [[
                {field: 'ck', rowspan:'2', checkbox: true},
                {field: 'requestTrainingId', rowspan:'2', width:150, title:'Request Training ID', halign: 'center'},
                {field: 'induction', rowspan:'2', width:280, title:'Induction', halign: 'center'},
                {field: 'trainingActivities', rowspan:'2', width:180, title:'Training Activities', halign: 'center'},
                {field: 'suggestDate', rowspan:'2', width:150, title:'Suggest Date Training', halign: 'center'},
                {field: 'reasons', rowspan:'2', width:150, title:'Reasons', halign: 'center'},
				{field: 'trainer', rowspan:'2', width:100, title:'Trainer', halign: 'center'},
				{field: 'trainerName', rowspan:'2', width:100, title:'Trainer Name', halign: 'center'},
				{field: 'trainerFees', rowspan:'2', width:100, title:'TrainerFees', halign: 'center'},
				{field: 'attachment', rowspan:'2', width:100, title:'Attachment', halign: 'center',
					formatter: function (value, row, index) {
						return fileFormatter(value);
					}
				},
                {field: '', colspan:2, title:'Status', width:80, align: 'center'},
                {field: '', colspan:2, title:'Created', width:80, align: 'center'},
                {field: '', colspan:2, title:'Updated', width:80, align: 'center'},
                {field: '', colspan:2, title:'Approved', width:80, align: 'center'},
            ],[
                {field: 'statusApproved', title:'Approval', width:100, align: 'center',
                    formatter: function(value, row, index) {
						let approverName = row.approverName;
						let gender = row.gender;
						let inputterName = row.inputter;
                        if(row.approvedData === 'COMPLETED') {
                            return `<div style="color:black;padding:5px;"><b><a href="#" onClick="showHistoryApproval('${row.requestTrainingId}')">Completed<a/></b></div>`;
                        }
                        if(row.statusApproval == '-1') {
                            return '<div style="background-color:orange;color:white;padding:5px;">Remark: ' + row.approvedData + ' </div>';
                        } else {
							if (inputterName == null || inputterName === '' || approverName == null || approverName == '') {
								return '<div style="background-color:red;color:white;padding:5px;">Not Valid</div>';
							}
                            if(value === '-1') {
                                const label = 'Waiting Revision ' + (inputterName);
                                return '<div style="background-color:red;color:white;padding:5px;">' + label + '</div>';
                            } else {
                                const label = 'Waiting Approval ' + (approverName);
                                return '<div style="background-color:green;color:white;padding:5px;">' + label + '</div>';
                            }
                        }
                        return '-';
                    }
                },
                {field: 'statusTraining', title:'Training', width:150, align: 'center',
                    formatter: function(value, row, index) {
                        if(row.statusApproval === '-1') return 'Pending';
                        if(row.statusApproval === '0') return 'Open';
                        if(row.statusApproval === '1') return 'On Progress';
                        if(row.statusApproval === '2' || row.statusApproved === '4') return 'Complete';
                        return '-';
                    }
                },
                {field: 'createdBy', title:'By', width:100, align: 'center'},
                {field: 'createdTime', title:'Date', width:150, align: 'center'},
                {field: 'updatedBy', title:'By', width:100, align: 'center'},
                {field: 'updatedTime', title:'Date', width:150, align: 'center'},
                {field: 'approved_by', title:'By', width:100, align: 'center',
                    formatter: function(value, row, index) {
                        if(parseInt(row.statusApproved) > 1) return value;
                        return '-';
                    }
                },
                {field: 'approved_date', title:'Date', width:150, align: 'center',
                    formatter: function(value, row, index) {
                        if(parseInt(row.statusApproved) > 1) return value;
                        return '-';
                    }
                },
            ]],
            toolbar: '#toolbar',
            pagination: true,
            rownumbers: true,
            fit: true,
            nowrap: false,
            // remoteFilter: true,
            // sortName: 'index',
            // sortOrder: 'asc'
            singleSelect:true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    if ($('#frm_insert').form('validate')) {
						const formElement = document.getElementById('frm_insert');
						const formData = new FormData();

						// Append all regular inputs
						$('#frm_insert').serializeArray().forEach(({ name, value }) => {
							formData.append(name, value);
						});

						// Append files manually
						const fileInputs = formElement.querySelectorAll('input[type="file"]');
						for (const input of fileInputs) {
							if (input.files.length > 0) {
								// You can allow multiple files per field if needed
								for (let i = 0; i < input.files.length; i++) {
									formData.append(input.name, input.files[i]);
								}
							}
						}

                        var rowForm = $('#dgTrainee').datagrid('getRows');
                        

						// Call with isFormData = true
						sendDataToServer(formData, method === 'POST');

                        // Wait for sendDataToServer to finish before proceeding
                        // setTimeout(() => {
                        //     var rowForm = $('#dgTrainee').datagrid('getRows');
                        //     var totalForm = rowForm.length;

                        //     for (let i = 0; i < totalForm; i++) {
                        //         if (rowForm[i].fullName) {
                        //             $.ajax({
                        //                 type: "POST",
                        //                 url: '<?= base_url('lnd/request_training/create_data_trainee') ?>',
                        //                 data: {
                        //                     id: rowForm[i].id,
                        //                     fullName: rowForm[i].fullName,
                        //                     national_id: rowForm[i].national_id,
                        //                     position: rowForm[i].position,
                        //                     departement: rowForm[i].departement,
                        //                     departement_subs: rowForm[i].departement_subs,
                        //                     date_sign: rowForm[i].date_sign
                        //                 },
                        //                 dataType: "json",
                        //                 success: function(res) {
                        //                     console.log(res, ' 392');
                        //                     $('#dgTrainee').datagrid('reload');
                        //                 }
                        //             });
                        //         }
                        //     }
                        // }, 500); // Small delay to ensure sendDataToServer executes first
                    }
                }
            }]
        });

        $('#trainingMaterialFilter').combogrid({
            url: '<?= base_url('lnd/request_training/reads') ?>',
            panelWidth: 450,
            idField: 'trainingActivities',
            textField: 'trainingActivities',
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
                    field: 'trainingActivities',
                    title: 'Training Material',
                    width: 120
                }]
            ],
        });

        $('#traineeFilter').combogrid({
            url: '<?= base_url('lnd/request_training/readsTrainee') ?>',
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
					title: 'ID Trainee',
					width: 120
				}, {
                    field: 'name',
                    title: 'Trainee',
                    width: 120
                }]
            ],
        });

		$('#trainerNameFilter').combogrid({
			url: '<?= base_url('lnd/request_training/readsEmployeesStaffUp') ?>',
			panelWidth: 450,
			idField: 'id',
			textField: 'name',
			mode: 'remote',
			fitColumns: true,
			prompt: 'Choose Trainer',
			icons: [{
				iconCls: 'icon-clear',
				handler: function(e) {
					$(e.data.target).combogrid('clear').combogrid('textbox').focus();
				}
			}],
			columns: [
				[{
					field: 'number',
					title: 'ID Trainer',
					width: 120
				}, {
					field: 'name',
					title: 'Trainer',
					width: 120
				}]
			],
		});
        // $('#reasonsFilter').combogrid({
        //     url: '<?= base_url('lnd/request_training/reads') ?>',
        //     panelWidth: 450,
        //     idField: 'reasons',
        //     textField: 'reasons',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: 'Choose All',
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combogrid('clear').combogrid('textbox').focus();
        //         }
        //     }],
        //     columns: [
        //         [{
        //             field: 'reasons',
        //             title: 'Reasons',
        //             width: 120
        //         }]
        //     ],
        // });

        $('#departementFilter').combogrid({
            url: '<?= base_url('lnd/schedule_training/readsDepartements') ?>',
			panelWidth: 450,
			idField: 'id',
			textField: 'name',
			mode: 'remote',
			fitColumns: true,
            prompt: 'Select Departement',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
			columns: [
				[
					{
						field: 'division',
						title: 'Division Name',
						width: 120
					},
					{
						field: 'name',
						title: 'Department Name',
						width: 120
					}]
			],
        });
    });
    
    // var editingIndex = null; // Track which row is being edited

    $(document).ready(function() {
        $('#dgForm').datagrid({
            data: [], // Empty at first, can be filled from an API
            columns: [[
                { field: 'full_name', title: 'Full Name', width: 180, align: 'center', 
                    formatter: function(value, row, index) {
                        return value || '';
                    },
                    singleSelect: true,
                    editor: { 
                        type: 'combogrid', 
                        options: {
                            required: true,
                            panelWidth: 300,
                            url: '<?= base_url("lnd/request_training/getEmployees") ?>',
                            idField: 'id',
                            textField: 'name',
                            mode: 'remote',
                            columns: [[
                                { field: 'name', title: 'Name', width: 280 },
                            ]],
                            onSelect: function(index, row) {
                                let selectedRow = $('#dgForm').datagrid('getSelected'); // Get the selected row
                                if (selectedRow) {
                                    let rowIndex = $('#dgForm').datagrid('getRowIndex', selectedRow); // Find row index
                                    $('#dgForm').datagrid('updateRow', {
                                        index: rowIndex,
                                        row: {
                                            full_name: row.name,
                                            national_id: row.national_id,
                                            position_id: row.position,
                                            departement_id: row.departement,
                                            departement_sub_id: row.departement_subs,
                                            date_sign: row.date_sign
                                        }
                                    });
                                    $('#dgForm').datagrid('endEdit', rowIndex);
                                    $('#dgForm').datagrid('unselectRow', rowIndex); // Unselect previous row
                                    
                                    // Close the combogrid dropdown
                                    setTimeout(() => {
                                        let ed = $('#dgForm').datagrid('getEditor', { index: rowIndex, field: 'full_name' });
                                        if (ed) {
                                            let $input = $(ed.target);
                                            $input.combogrid('grid').datagrid('clearSelections'); // 🔥 Clear selection
                                            $input.combogrid('hidePanel'); // 🔥 Hide panel
                                            $input.combogrid('clear'); // 🔥 Reset selection
                                            $input.combogrid('textbox').blur(); // 🔥 Force blur
                                            $('.panel').hide(); // 🔥 Destroy ALL panels
                                            $('body').trigger('click'); // 🔥 Fake click to refresh UI
                                            $('.panel-body').hide();  // Hide all combogrid panels
                                        }
                                    }, 50);
                                }
                            }
                        }
                    } 
                },
                { field: 'national_id', title: 'Employee ID', width: 200, align: 'center'},
                { field: 'position_id', title: 'Position', width: 120, align: 'center' },
                { field: 'departement_id', title: 'Department', width: 150, align: 'center' },
                { field: 'departement_sub_id', title: 'Sub Department', width: 150, align: 'center' },
                { field: 'date_sign', title: 'Join Date', width: 150, align: 'center' }
            ]],
            onClickCell: function(index, row) {
                $('#dgForm').datagrid('beginEdit', index); // Start editing the selected row
                let ed = $('#dgForm').datagrid('getEditor', { index: index, field: 'full_name' });
                console.log(ed && ed.oldHtml == "");
                if (ed && ed.oldHtml == "") {
                    $(ed.target).combogrid('showPanel'); // Open the combogrid dropdown automatically
                }
            }
        });

        // Add row button
        $("#btnAdd").click(function() {
            $('#dgForm').datagrid('appendRow', {
                full_name: '',
                employeeId: '',
                position: '',
                department: '',
                sub_department: '',
                join_date: ''
            });
            var lastIndex = $('#dgForm').datagrid('getRows').length - 1;
            $('#dgForm').datagrid('beginEdit', lastIndex);
        });

        // Remove selected row button
        $("#btnRemove").click(function() {
            var row = $('#dgForm').datagrid('getSelected');
            if (row) {
                var index = $('#dgForm').datagrid('getRowIndex', row);
                $('#dgForm').datagrid('deleteRow', index);
            } else {
                alert("Please select a row to remove.");
            }
        });

        $("#trainer").combobox({
            onSelect:function(record){
                updateTrainerName(record.value);
            }
        })



        function updateTrainerName(type) {
            var containerTrainer = $('#trainerNameContainer');
            containerTrainer.empty();
            
            
            var template = "";
            if (type === 'Internal') {
                template = templateInternal();
            } else if (type === 'External') {
                template = templateExternal();
            }
            containerTrainer.append(template);
            $.parser.parse(`#trainerNameContainer`);
        }
    });

    // function download_excel() {
    //     window.location.assign('<?= base_url('template/tmp_training_activity3.xls') ?>');
    // }

    // //UPLOAD DATA
    // function upload() {
    //     $('#dlg_upload').dialog('open');
    // }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function templateExternal() {
        const html = `<span style="width:35%; display:inline-block;">Trainer Name</span>
					<input style="width:60%;" id="trainerNameText" name="trainer_name" class="easyui-textbox" required="">`;
        return html;
    } 

    function templateInternal() {
        const html = `<span style="width:35%; display:inline-block;">Trainer Name</span>
					<input style="width:60%;" id="trainerNameFilter" name="trainer_name" class="easyui-combogrid" data-options="url: '<?= base_url('lnd/request_training/readsEmployeesStaffUp') ?>',
                        panelWidth: 450,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: 'Choose Trainer',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                            }
                        }],
                        columns: [
                            [{
                                field: 'number',
                                title: 'ID Trainer',
                                width: 120
                            }, {
                                field: 'name',
                                title: 'Trainer',
                                width: 120
                            }]
                        ]" required="">`;
        return html;
    } 

    // PRINT PDF
	function pdf() {
		const iframe = document.getElementById("printout");
		const row = $('#dg').datagrid('getSelected');

		if(row) {
			// Set the onload BEFORE setting src to ensure it catches the event
			iframe.onload = function () {
				// Trigger print inside iframe when it's loaded
				this.contentWindow.print();
			};

			// Set the source with ID parameter
			iframe.src = '<?= base_url('lnd/request_training/print') ?>/' + row.id;
		} else {
			alert("Please select a row first.");
		}

	}

     //PRINT EXCEL
	function excel() {
		const row = $('#dg').datagrid('getSelected');

		if (row) {
			// Go to the export URL with the selected ID
			window.location.assign('<?= base_url('lnd/request_training/print/excel') ?>/' + row.id);
		} else {
			alert("Please select a row first.");
		}
	}

    //UPLOAD DATA
    // $('#dlg_upload').dialog({
    //     buttons: [{
    //         text: 'List Failed',
    //         handler: function() {
    //             window.open('<?= base_url('lnd/training_activity/uploadDownloadFailed') ?>', '_blank');
    //         }
    //     }, {
    //         text: 'Upload',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_upload').form('submit', {
    //                 url: '<?= base_url('lnd/training_activity/upload') ?>',
    //                 onSubmit: function() {
    //                     if ($(this).form('validate') == false) {
    //                         return $(this).form('validate');
    //                     } else {
    //                         $.messager.progress({
    //                             title: 'Please Wait',
    //                             msg: 'Importing Excel to Database'
    //                         });
    //                     }
    //                 },
    //                 success: function(result) {
    //                     $.messager.progress('close');

    //                     //Clear File
    //                     $.ajax({
    //                         url: "<?= base_url('lnd/training_activity/uploadclearFailed') ?>"
    //                     });

    //                     var json = eval('(' + result + ')');
    //                     requestData(json.total, json);

    //                     function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
    //                         if (value < 100) {
    //                             value = Math.floor((number / total) * 100);
    //                             $('#p_upload').progressbar('setValue', value);
    //                             $('#p_start').html(number);
    //                             $('#p_finish').html(total);

    //                             $.ajax({
    //                                 type: "POST",
    //                                 async: true,
    //                                 url: "<?= base_url('lnd/training_activity/uploadCreate') ?>",
    //                                 data: {
    //                                     "data": json[number - 1]
    //                                 },
    //                                 cache: false,
    //                                 dataType: "json",
    //                                 success: function(result) {
    //                                     console.log(result);
    //                                     if (result.theme == "success") {
    //                                         $('#p_success').html(success);
    //                                         var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
    //                                         requestData(total, json, number + 1, value, success + 1, failed + 0);
    //                                     } else {
    //                                         $('#p_failed').html(failed);
    //                                         var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

    //                                         //Json Failed
    //                                         $.ajax({
    //                                             type: "POST",
    //                                             async: true,
    //                                             url: "<?= base_url('lnd/training_activity/uploadcreateFailed') ?>",
    //                                             data: {
    //                                                 data: json[number - 1],
    //                                                 message: result.message
    //                                             },
    //                                             cache: false
    //                                         });

    //                                         requestData(total, json, number + 1, value, success + 0, failed + 1);
    //                                     }

    //                                     $("#p_remarks").append(title + "<br>");
    //                                 },
    //                                 fail: function(jqXHR, textStatus) {
    //                                     if (textStatus == "error") {
    //                                         Swal.fire({
    //                                             title: 'Connection Time Out, Check Your Connection',
    //                                             showConfirmButton: false,
    //                                             allowOutsideClick: false,
    //                                             allowEscapeKey: false,
    //                                             didOpen: () => {
    //                                                 Swal.showLoading();
    //                                             },
    //                                         });

    //                                         setTimeout(function() {
    //                                             requestData(total, json, number, value, success + 0, failed + 0);
    //                                         }, 5000);
    //                                     }
    //                                 }
    //                             });
    //                         }
    //                     }
    //                 }
    //             });
    //         }
    //     }]
    // });

    function filter() {
        var trainingMaterial = $("#trainingMaterialFilter").combogrid('getValue');
        var trainee = $("#traineeFilter").combogrid('getValue');
        var reasons = $("#reasonsFilter").combobox('getValue');
        var departement = $("#departementFilter").combogrid('getValue');
        var suggestTrainingDate = $("#suggestTrainingDateFilter").datebox('getValue');
        var params = "?trainingActivities=" + trainingMaterial + "&id=" + trainee + "&reasons=" + reasons + "&departement=" + departement + "&suggestDateTraining=" + btoa(suggestTrainingDate);

        $('#dg').datagrid({
            url: '<?= base_url('lnd/request_training/datatables') ?>' + params
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
    }

    function tableTrainee(number) {
        var lastIndex;
        var dgTrainee = $('#dgTrainee').datagrid({
            url: '<?= base_url('lnd/request_training/getTrainee/') ?>' + number,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Trainee ID",
                    hidden: true,
                    editor: {
                        type: 'textbox',
                    }
                },{
                    field: 'fullName',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Full Name",
                    formatter: function(value, row, index) {
                        return value || '';
                    },
                    singleSelect: true,
                    editor: { 
                        type: 'combogrid', 
                        options: {
                            required: true,
                            panelWidth: 300,
                            url: '<?= base_url("lnd/request_training/getEmployees") ?>',
                            idField: 'id',
                            textField: 'name',
                            mode: 'remote',
                            columns: [[
                                { field: 'name', title: 'Name', width: 280 },
                            ]],
                            onSelect: function(index, row) {
                                let selectedRow = $('#dgTrainee').datagrid('getSelected'); // Get the selected row
                                if (selectedRow) {
                                    let rowIndex = $('#dgTrainee').datagrid('getRowIndex', selectedRow); // Find row index
                                    $('#dgTrainee').datagrid('endEdit', rowIndex);
                                    $('#dgTrainee').datagrid('unselectRow', rowIndex); // Unselect previous row
                                    
                                    setTimeout(() => {
                                        $('#dgTrainee').datagrid('updateRow', {
                                            index: rowIndex,
                                            row: {
                                                fullName: row.name,
												national_id: row.employeeId,
                                                position: row.position,
                                                departement: row.departement,
                                                departement_subs: row.departement_subs,
                                                date_sign: row.date_sign
                                            }
                                        });
                                        
                                    }, 50);
                                }
                            }
                        }
                    }
                }, {
                    field: 'national_id',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Employee ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'position',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Position",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'departement',
                    width: 150,
                    halign: 'center',
                    title: "Departement",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'departement_subs',
                    width: 150,
                    halign: 'center',
                    title: "Sub Departement",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'date_sign',
                    width: 150,
                    halign: 'center',
                    title: "Join Date",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dgTrainee').datagrid('getEditors', rowIndex);
            },
            onClickCell: onClickCellTrainee,
        });
    }

    var editIndexTrainee = undefined;

    function endEditingTrainee() {
        if (editIndexTrainee == undefined) {
            return true
        }
        if ($('#dgTrainee').datagrid('validateRow', editIndexTrainee)) {
            $('#dgTrainee').datagrid('endEdit', editIndexTrainee);
            editIndexTrainee = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCellTrainee(index, field) {
        if (editIndexTrainee != index) {
            if (endEditingTrainee()) {
                $('#dgTrainee').datagrid('selectRow', index).datagrid('beginEdit', index);
                var ed = $('#dgTrainee').datagrid('getEditor', {
                    index: index,
                    field: field
                });
                if (ed) {
                    ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                }
                editIndexTrainee = index;
            } else {
                setTimeout(function() {
                    $('#dgTrainee').datagrid('selectRow', editIndexTrainee);
                }, 0);
            }
        }
    }

    function addTrainee() {
        if (endEditingTrainee()) {
            $('#dgTrainee').datagrid('appendRow', {
                qty: '0'
            });
            editIndexTrainee = $('#dgTrainee').datagrid('getRows').length - 1;
            $('#dgTrainee').datagrid('selectRow', editIndexTrainee).datagrid('beginEdit', editIndexTrainee);
        }
    }

    function removeTrainee(target) {
        var dg = $('#dgTrainee');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndexTrainee,
            field: 'id'
        });

        $.ajax({
            method: 'post',
            url: "<?= base_url('lnd/request_training/deleteTrainee') ?>",
            data: {
                id: row.id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //toastr.error(jqXHR.statusText);
            },
        });

        $('#dgTrainee').datagrid('cancelEdit', editIndexTrainee).datagrid('deleteRow', editIndexTrainee);
        editIndexTrainee = undefined;
    }

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

    function openAttachment(url){
        $('#dlg_attachment').dialog('open');
        $('#attachmentContainer').empty();
        $('#attachmentContainer').append(`
            <div class="attachment-preview">
                <iframe src="${url}" width="100%" height="600px" frameborder="0"></iframe>
                <div class="attachment-actions">
                    <a href="${url}" class="easyui-linkbutton" target="_blank">
                        <i class="fa fa-download"></i> Download File
                    </a>
                </div>
            </div>
        `);
    }

	function fileFormatter(value) {
        if (value == "" || value == null) {
            return '-';
		} else {
            const baseUrl = '<?= base_url('assets/document/request-training/') ?>';
			return `<a style="text-decoration: none;" onclick="openAttachment('${baseUrl}${value}')"><i class="fa fa-download"></i> Download</a>`;
		}
	};
</script>
