<table id="dg" class="easyui-datagrid" style="width:100%;">
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <div style="width: 100%; padding: 10px;">
        <!-- <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Competence ID</span>
                <input style="width:60%;" id="competence_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset> -->
        <?= $button ?>
    </div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Index</span>
                <input style="width:60%;" name="index" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Competence Name</span>
                <input style="width:60%;" name="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remark" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Type</span>
                <select style="width:60%;" name="file_type" id="file_type" panelHeight="auto" class="easyui-combobox">
                    <option value="excel">Excel</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
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

<!-- PDF -->
<iframe id="printout" src="<?= base_url('lnd/competence/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    // Initialize competence grid on page load
    window.onload = initCompetenceGrid;

    // Grid initialization
    function initCompetenceGrid() {
        $('#competence_id').combogrid({
            url: '<?php echo base_url('lnd/competence/list'); ?>',
            panelWidth: 420,
            idField: 'competenceId',
            textField: 'competenceId', 
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Competence",
            icons: [{
                iconCls: 'icon-clear',
                handler: clearCompetenceGrid
            }],
            columns: [[
                {field: 'competenceId', title: 'Competence ID'},
                {field: 'name', title: 'Competence Name', width: 250}
            ]]
        });
    }

    // Clear grid handler
    function clearCompetenceGrid(e) {
        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
    }

    // Dialog handlers
    function add() {
        openDialog('add');
    }

    function update() {
        const row = $('#dg').datagrid('getSelected');
        if (row) {
            openDialog('edit', row);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function openDialog(mode, data = null) {
        $('#dlg_insert').dialog('open');
        url_save = mode === 'add' 
            ? '<?= base_url('lnd/competence/create_data') ?>'
            : '<?= base_url('lnd/competence/update_data/') ?>' + data.id;
        method = mode === 'add' ? 'POST' : 'PUT';
        $('#frm_insert').form('clear');
        if(data) {
            $('#frm_insert').form('load', data);
        }
    }

    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // Print functions
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('lnd/competence/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    // Delete handler
    function deleted() {
        const rows = $('#dg').datagrid('getSelections');
        if (!rows.length) {
            toastr.warning("Please select one of the data in the table first!", "Information");
            return;
        }

        $.messager.confirm('Warning', 'Are you sure you want to delete this data?', (confirmed) => {
            if (!confirmed) return;
            
            rows.forEach(row => {
                deleteRecord(row.id);
            });
        });
    }

    async function deleteRecord(id) {
        try {
            const response = await fetch('<?= base_url('lnd/competence/delete_data/') ?>'+id, {
                method: 'DELETE'
            });
            const data = await response.json();
            
            if (data.code === 200) {
                $('#dg').datagrid('reload');
                toastr.success(data.message, 'Success');
            } else {
                toastr.error("Something Wrong", 'Error');
            }
        } catch(error) {
            console.error('Error:', error);
            toastr.error("Something Wrong", 'Error');
        }
    }

    // Filter handler
    function filter() {
        const competence_id = $("#competence_id").combogrid('getValue');
        const params = "?competenceId=" + competence_id;

        $('#dg').datagrid({
            url: '<?= base_url('lnd/competence/datatables') ?>' + params
        });

        updatePrintout(params);
    }

    function updatePrintout(params) {
        $("#printout").contents().find('html')
            .html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('lnd/competence/print') ?>' + params);
    }

    // Server communication
    async function sendDataToServer(requestData) {
        try {
            const formData = new URLSearchParams(requestData).toString();
            const response = await fetch(url_save, {
                method: method,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            });
            const data = await response.json();

            if(data.code >= 200 && data.code <= 300) {
                toastr.success(data.message, 'Success');
                $('#dg').datagrid('reload');
                $('#dlg_insert').dialog('close');
            } 
            if(data.code == 400) {
                toastr.error(data.message, 'Something Error');
                $('#dg').datagrid('reload');
                // $('#dlg_insert').dialog('close');
            }
        } catch(error) {
            toastr.error('Something Error', 'Error');
            console.error('Error:', error);
        }
    }

    // Initialize components
    $(function() {
        initDataGrid();
        initDialogs();
    });

    function initDataGrid() {
        $('#dg').datagrid({
            url: '<?= base_url('lnd/competence/datatables') ?>',
            columns: [[
                {field: 'ck', rowspan:'2', checkbox: true},
                {field: 'competenceId', rowspan:'2', width:150, title:'Competence ID', halign: 'center'},
                {field: 'index', rowspan:'2', width:80, title:'Index', halign: 'center'},
                {field: 'name', rowspan:'2', width:200, title:'Competence Name', halign: 'center'},
                {field: 'remark', rowspan:'2', width:100, title:'Remarks', halign: 'center'},
                {field: '', colspan:2, title:'Created', width:80, align: 'center'},
                {field: '', colspan:2, title:'Updated', width:80, align: 'center'},
            ],[
                {field: 'createdBy', title:'By', width:100, align: 'center'},
                {field: 'createdTime', title:'Date', width:150, align: 'center'},
                {field: 'updatedBy', title:'By', width:100, align: 'center'},
                {field: 'updatedTime', title:'Date', width:150, align: 'center'},
            ]],
            toolbar: '#toolbar',
            pagination: true,
            rownumbers: true,
            remoteFilter: true,
            fit: true,
            singleSelect: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
    }

    function initDialogs() {
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: handleSave
            }]
        });

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: () => {
                    window.open('<?= base_url('lnd/competence/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: handleUpload
            }]
        });
    }

    function handleSave() {
        if($('#frm_insert').form('validate')) {
            const formData = $('#frm_insert').serialize();
            sendDataToServer(formData);
        }
    }

    function handleUpload() {
        $('#frm_upload').form('submit', {
            url: '<?= base_url('lnd/competence/generatedata') ?>',
            onSubmit: validateUpload,
            success: handleUploadSuccess
        });
    }

    function validateUpload() {
        if (!$(this).form('validate')) {
            return false;
        }
        
        $.messager.progress({
            title: 'Please Wait',
            msg: 'Importing Excel to Database'
        });
        return true;
    }

    function handleUploadSuccess(result) {
        $.messager.progress('close');
        clearFailedUploads();

        const json = eval('(' + result + ')');
        processUploadData(json.total, json);
    }

    function clearFailedUploads() {
        $.ajax({
            url: "<?= base_url('lnd/competence/uploadclearFailed') ?>"
        });
    }

    function processUploadData(total, json, number = 1, value = 0, success = 1, failed = 1) {
        if (value >= 100) return;

        value = Math.floor((number / total) * 100);
        updateProgressBar(value, number, total);

        $.ajax({
            type: "POST",
            async: true,
            url: "<?= base_url('lnd/competence/upload') ?>",
            data: {"data": json[number - 1]},
            cache: false,
            dataType: "json",
            success: (result) => handleUploadResult(result, total, json, number, value, success, failed),
            fail: (jqXHR, textStatus) => handleUploadError(total, json, number, value, success, failed)
        });
    }

    function updateProgressBar(value, current, total) {
        $('#p_upload').progressbar('setValue', value);
        $('#p_start').html(current);
        $('#p_finish').html(total);
    }

    function handleUploadResult(result, total, json, number, value, success, failed) {   
        if (result.theme == "success") {
            $('#p_success').html(success);
            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
            processUploadData(total, json, number + 1, value, success + 1, failed);
        } else {
            $('#p_failed').html(failed);
            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
            logFailedUpload(json[number - 1], result.message);
            processUploadData(total, json, number + 1, value, success, failed + 1);
        }
        $("#p_remarks").append(title + "<br>");
    }

    function logFailedUpload(data, message) {
        $.ajax({
            type: "POST",
            async: true,
            url: "<?= base_url('lnd/competence/uploadFailed') ?>",
            data: {data, message},
            cache: false
        });
    }

    function handleUploadError(total, json, number, value, success, failed) {
        Swal.fire({
            title: 'Connection Time Out, Check Your Connection',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        setTimeout(() => {
            processUploadData(total, json, number, value, success, failed);
        }, 5000);
    }
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_competence_new.xls') ?>');
    }
</script>