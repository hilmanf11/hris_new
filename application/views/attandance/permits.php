<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'trans_date',width:120,halign:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'id',width:120,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'request_name',width:200,halign:'center'">Request Name</th>
            <th rowspan="2" data-options="field:'division_name',width:200,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'departement_name',width:200,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'departement_sub_name',width:200,halign:'center'">Departement Sub</th>
            <th rowspan="2" data-options="field:'employee_number',width:120,halign:'center'">Employee ID</th>
            <th rowspan="2" data-options="field:'employee_name',width:200,halign:'center'">Employee Name</th>
            <th rowspan="2" data-options="field:'permit_type_name',width:100,halign:'center'">Permit Type</th>
            <th rowspan="2" data-options="field:'permit_date',width:100,halign:'center'">Permit Date</th>
            <th rowspan="2" data-options="field:'reason_name',width:200,halign:'center'">Reason</th>
            <th rowspan="2" data-options="field:'note',width:200,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center', formatter:FormatterFile">Attachment</th>
            <th colspan="3">Approval</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved',width:100,align:'center',styler:statusStyler, formatter:statusFormatter"> Status</th>
            <th data-options="field:'approved_by',width:120,align:'center'"> By</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 99%; border:2px solid #d0d0d0; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Period Date</span>
                        <input style="width:28%;" name="filter_from" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox"> To
                        <input style="width:28%;" name="filter_to" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Division</span>
                        <input style="width:60%;" id="filter_division" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Departement</span>
                        <input style="width:60%;" id="filter_departement" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Departement Sub</span>
                        <input style="width:60%;" id="filter_departement_sub" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Employee</span>
                        <input style="width:60%;" id="filter_employee" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Permit Type</span>
                        <input style="width:60%;" id="filter_permit_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Request By</span>
                        <input style="width:60%;" id="filter_request" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Approval</span>
                        <select style="width:60%;" id="filter_approval" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="0">Approve</option>
                            <option value="1">Checked</option>
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 450px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Employee</span>
                <input style="width:60%;" name="employee_id" id="employee_id" required="">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Permit Type</span>
                <input style="width:60%;" name="permit_type_id" id="permit_type_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Reason</span>
                <input style="width:60%;" name="reason_id" id="reason_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Permit Date</span>
                <input style="width:30%;" name="date_from" id="date_from" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" name="date_to" id="date_to" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem" hidden>
                <span style="width:30%; display:inline-block;">Duration</span>
                <input style="width:30%;" name="duration" id="duration" readonly required class="easyui-numberbox" data-options="buttonText:'Day', buttonAlign:'right'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Working Day</span>
                <input style="width:30%;" name="working_day" id="working_day" readonly required class="easyui-numberbox" data-options="buttonText:'Day', buttonAlign:'right'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Permit Available</span>
                <input style="width:30%;" name="leave" id="leave" readonly required class="easyui-numberbox" data-options="buttonText:'Day', buttonAlign:'right'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Note</span>
                <input style="width:60%; height:60px;" name="note" id="note" class="easyui-textbox" multiline="true">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Attachment</span>
                <input style="width:60%;" name="attachment" id="attachment" accept=".pdf, .jpg, .png" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
</div>

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

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('attandance/permits/create') ?>';
        $('#frm_insert').form('clear');
        $('#employee_id').combogrid('enable');
        $('#permit_type_id').combobox('enable');
        $('#reason_id').combobox('enable');
        $('#date_from').datebox('enable');
        $('#date_to').datebox('enable');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelections');
        if (row.length == 1) {
            var get = row[0];
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', get);
            $('#employee_id').combogrid('disable');
            $('#permit_type_id').combobox('disable');
            $('#reason_id').combobox('disable');
            $('#date_from').datebox('setValue', get.permit_date).datebox('disable');
            $('#date_to').datebox('setValue', get.permit_date).datebox('disable');
            selectWorking(get.employee_id);
            url_save = '<?= base_url('attandance/permits/update') ?>?id=' + btoa(get.id);
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('attandance/permits/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_permits.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combobox('getValue');
        var filter_permit_type = $("#filter_permit_type").combobox('getValue');
        var filter_request = $("#filter_request").combobox('getValue');
        var filter_approval = $("#filter_approval").combobox('getValue');
        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_permit_type=" + filter_permit_type +
            "&filter_request=" + filter_request +
            "&filter_approval=" + filter_approval;

        $('#dg').datagrid({
            url: '<?= base_url('attandance/permits/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('attandance/permits/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combobox('getValue');
        var filter_permit_type = $("#filter_permit_type").combobox('getValue');
        var filter_request = $("#filter_request").combobox('getValue');
        var filter_approval = $("#filter_approval").combobox('getValue');
        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_permit_type=" + filter_permit_type +
            "&filter_request=" + filter_request +
            "&filter_approval=" + filter_approval;

        window.location.assign('<?= base_url('attandance/permits/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        //UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('attandance/permits/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('attandance/permits/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');

                            //Clear File
                            $.ajax({
                                url: "<?= base_url('attandance/permits/uploadclearFailed') ?>"
                            });

                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('attandance/permits/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('attandance/permits/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        },
                                        fail: function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                Swal.fire({
                                                    title: 'Connection Time Out, Check Your Connection',
                                                    showConfirmButton: false,
                                                    allowOutsideClick: false,
                                                    allowEscapeKey: false,
                                                    didOpen: () => {
                                                        Swal.showLoading();
                                                    },
                                                });

                                                setTimeout(function() {
                                                    requestData(total, json, number, value, success + 0, failed + 0);
                                                }, 5000);
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        //Get Departement
        $('#filter_division').combobox({
            url: '<?php echo base_url('employee/divisions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(division) {
                $('#filter_departement').combobox({
                    url: '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + division.id,
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
                        $('#filter_departement_sub').combobox({
                            url: '<?php echo base_url('employee/departement_subs/reads'); ?>?division_id=' + division.id + '&departement_id=' + departement.id,
                            valueField: 'id',
                            textField: 'name',
                            prompt: 'Choose All',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                            onSelect: function(departement_sub) {
                                $('#filter_employee').combogrid({
                                    url: '<?= base_url('employee/employees/reads?departement_sub_id=') ?>' + departement_sub.id,
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
                            }
                        });
                    }
                });
            }
        });

        $('#filter_employee').combogrid({
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

        $('#filter_permit_type').combobox({
            url: '<?= base_url('attandance/permit_types/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#filter_request').combobox({
            url: '<?= base_url('attandance/permits/readRequest') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#employee_id').combogrid({
            url: '<?= base_url('employee/employees/reads') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Employee',
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
            onSelect: function(value, employee) {
                $("#permit_type_id").combobox('clear');
                $("#reason_id").combobox('clear');

                selectWorking(employee.id);
            }
        });

        $("#permit_type_id").combobox({
            url: '<?= base_url('attandance/permit_types/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Permit Type",
            onSelect: function(permit) {
                var employee_id = $('#employee_id').combogrid('getValue');

                // $.ajax({
                //     url: '<?= base_url('attandance/permits/readLeave') ?>',
                //     type: 'post',
                //     data: 'employee_id=' + employee_id + '&permit_type_id=' + permit.id,
                //     success: function(row) {
                //         var result = eval('(' + row + ')');
                //         if (result.total == 0) {
                //             $("#leave").numberbox('clear');
                //         } else {
                //             if (permit.cutoff == "YES") {
                //                 $("#duration").numberbox('setValue', 1);
                //                 $("#leave").numberbox('setValue', (result.total));
                //             } else {
                //                 $("#duration").numberbox('setValue', 0);
                //                 $("#leave").numberbox('setValue', (result.total));
                //             }
                //         }
                //     }
                // });

                $("#duration").numberbox('setValue', (permit.cutoff == "YES") ? 1 : 0);

                $("#reason_id").combobox({
                    url: '<?= base_url('attandance/reasons/reads') ?>?permit_type_id=' + permit.id,
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose Reason',
                });

                $("#date_from").datebox({
                    onChange: function(index) {
                        var date_to = $("#date_to").datebox('getValue');
                        var working_day = $("#working_day").numberbox('getValue');

                        if (index != '' && date_to != '') {
                            if (index > date_to) { toastr.warning("Please Select Permit Date From < Date To"); }
                            else {
                                $.ajax({
                                    url: '<?= base_url('attandance/permits/getDays') ?>',
                                    type: 'post',
                                    data: 'date_from=' + index + '&date_to=' + date_to + '&working_day=' + working_day + '&employee_id=' + employee_id + '&permit_type_id=' + permit.id,
                                    success: function(result) {
                                        var result = eval("("+result+")");
                                        var total = parseInt(result.total);

                                        if ((total - parseInt(result.days)) < 0) {
                                            toastr.error("the Leave is over", "Leave 0");
                                            $("#date_from").datebox('clear');
                                            $("#date_to").datebox('clear');
                                            $("#leave").numberbox('clear')
                                        } else { $("#leave").numberbox('setValue', total); }
                                    }
                                });
                            }
                        }
                    }
                });

                $("#date_to").datebox({
                    onChange: function(index) {
                        var date_from = $("#date_from").datebox('getValue');
                        var working_day = $("#working_day").numberbox('getValue');
                        if (date_from != '' && index != '') {
                            if (date_from > index) { toastr.warning("Please Select Permit Date From < Date To"); }
                            else {
                                $.ajax({
                                    url: '<?= base_url('attandance/permits/getDays') ?>',
                                    type: 'post',
                                    data: 'date_from=' + date_from + '&date_to=' + index + '&working_day=' + working_day + working_day + '&employee_id=' + employee_id + '&permit_type_id=' + permit.id,
                                    success: function(result) {
                                        var result = eval("("+result+")");
                                        var total = parseInt(result.total);

                                        if ((total - parseInt(result.days)) < 0) {
                                            toastr.error("the Leave is over", "Leave 0");
                                            $("#date_from").datebox('clear');
                                            $("#date_to").datebox('clear');
                                            $("#leave").numberbox('clear')
                                        } else { $("#leave").numberbox('setValue', total); }
                                    }
                                });
                            }
                        }
                    }
                });
            }
        });
    });

    function selectWorking(employee) {
        $.ajax({
            url: '<?= base_url('attandance/permits/readShifts') ?>',
            type: 'post',
            data: 'employee_id=' + employee,
            success: function(row) {
                var result = eval('(' + row + ')');
                if (row == "null") {
                    $("#working_day").numberbox('clear');
                } else {
                    $("#working_day").numberbox('setValue', result.days);
                }
            }
        });
    }

    //CELLSTYLE STATUS
    function statusStyler(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function statusFormatter(value, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'Approved';
        } else {
            return 'Checked';
        }
    };

    function FormatterFile(value) {
        if (value == "" || value == null) {
            return '-';
        } else {
            return '<a href="' + value + '" target="_blank" style="text-decoration: none;"><i class="fa fa-download"></i> Download</a>';
        }
    };

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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>