<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'employee_number',width:120,halign:'center'">Employee ID</th>
            <th rowspan="2" data-options="field:'employee_name',width:200">Employee Name</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center',formatter:FormatterDate">Trans Date</th>
            <th rowspan="2" data-options="field:'division_name',width:200,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'departement_name',width:150,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'departement_sub_name',width:150,halign:'center'">Departement Sub</th>
            <th rowspan="2" data-options="field:'position',width:150,halign:'center'">Position</th>
            <th rowspan="2" data-options="field:'mutation_type',width:150,halign:'center'">Mutation Type</th>
            <th rowspan="2" data-options="field:'description',width:300,halign:'center'">Note</th>
            <th rowspan="2" data-options="field:'approved',width:100,align:'center',styler:statusStyler, formatter:statusFormatter">Approval</th>
            <th rowspan="2" data-options="field:'approved_by',width:120,align:'center'">Approval By</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
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
            <fieldset style="width: 100%; border:2px solid #d0d0d0; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Trans Date</span>
                        <input style="width:30%;" name="filter_from" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                        <input style="width:30%;" name="filter_to" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Division</span>
                        <input style="width:60%;" id="filter_divisions" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement</span>
                        <input style="width:60%;" id="filter_departements" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement Sub</span>
                        <input style="width:60%;" id="filter_departement_subs" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Employee Name</span>
                        <input style="width:60%;" id="filter_employees" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Approval</span>
                        <select style="width:60%;" id="filter_approval" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="0">Approve</option>
                            <option value="1">Checked</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Status</span>
                        <select style="width:60%;" id="filter_status" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="0">Active</option>
                            <option value="1">Not Active</option>
                        </select>
                    </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Mutation From</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Employee</span>
                <input style="width:60%;" name="employee_id" id="employee_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" id="division_old" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement</span>
                <input style="width:60%;" id="departement_old" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement Sub</span>
                <input style="width:60%;" id="departement_sub_old" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Position</span>
                <input style="width:60%;" name="position_old" id="position_old" readonly class="easyui-textbox">
            </div>
            <!-- Hidden inputs untuk menyimpan Position baru -->
            <input type="hidden" name="position_new" id="position_new">
            <!-- Hidden inputs untuk menyimpan ID lama -->
            <input type="hidden" name="division_old_id" id="division_old_id">
            <input type="hidden" name="departement_old_id" id="departement_old_id">
            <input type="hidden" name="departement_sub_old_id" id="departement_sub_old_id">
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Mutation To</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type</span>
                <select style="width:60%;" name="type" id="type" required="" class="easyui-combobox" panelHeight="auto">
                    <option value="TEMPORARY">TEMPORARY</option>
                    <option value="PERMANENT">PERMANENT</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" name="division_id" id="division_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement</span>
                <input style="width:60%;" name="departement_id" id="departement_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement Sub</span>
                <input style="width:60%;" name="departement_sub_id" id="departement_sub_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Mutation Type</span>
                <input style="width:60%;" name="mutation_type" id="mutation_type" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Position</span>
                <input style="width:60%;" name="position" id="position" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Note</span>
                <input style="width:60%; height: 80px;" name="description" multiline="true" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
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
<iframe id="printout" src="<?= base_url('employee/mutations/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('employee/mutations/create') ?>';
        $('#frm_insert').form('clear');
        $('#trans_date').datebox('setValue', "<?= date("Y-m-d") ?>");
        
        // Kosongkan hidden inputs
        $("#division_old_id").val('');
        $("#departement_old_id").val('');
        $("#departement_sub_old_id").val('');
        $('#position').combobox('clear').combobox('disable');
        $('#mutation_type').combobox('clear').combobox('disable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.status != 0) {
                if (row.status_check == "" || row.status_check == null) {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);
                    
                    // Set hidden inputs dengan ID lama dari data yang dipilih
                    if (row.division_old_id) {
                        $("#division_old_id").val(row.division_old_id);
                    }
                    if (row.departement_old_id) {
                        $("#departement_old_id").val(row.departement_old_id);
                    }
                    if (row.departement_sub_old_id) {
                        $("#departement_sub_old_id").val(row.departement_sub_old_id);
                    }
                    
                    // Set field lama untuk ditampilkan
                    if (row.division_old_id) {
                        // Ambil nama division lama
                        $.ajax({
                            url: '<?= base_url('employee/divisions/reads') ?>',
                            data: {id: row.division_old_id},
                            success: function(data) {
                                var divisions = JSON.parse(data);
                                if (divisions.length > 0) {
                                    $("#division_old").textbox('setValue', divisions[0].name);
                                }
                            }
                        });
                    }
                    if (row.departement_old_id) {
                        // Ambil nama departement lama
                        $.ajax({
                            url: '<?= base_url('employee/departements/reads') ?>',
                            data: {id: row.departement_old_id},
                            success: function(data) {
                                var departements = JSON.parse(data);
                                if (departements.length > 0) {
                                    $("#departement_old").textbox('setValue', departements[0].name);
                                }
                            }
                        });
                    }
                    if (row.departement_sub_old_id) {
                        // Ambil nama departement sub lama
                        $.ajax({
                            url: '<?= base_url('employee/departement_subs/reads') ?>',
                            data: {id: row.departement_sub_old_id},
                            success: function(data) {
                                var departement_subs = JSON.parse(data);
                                if (departement_subs.length > 0) {
                                    $("#departement_sub_old").textbox('setValue', departement_subs[0].name);
                                }
                            }
                        });
                    }
                    
                    url_save = '<?= base_url('employee/mutations/update') ?>?id=' + btoa(row.id);
                } else {
                    toastr.error("Approval still Checked");
                }
            } else {
                toastr.error("You can't edit this data because the status active!");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
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
                            url: '<?= base_url('employee/mutations/delete') ?>',
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

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_divisions = $("#filter_divisions").combobox('getValue');
        var filter_departements = $("#filter_departements").combobox('getValue');
        var filter_departement_subs = $("#filter_departement_subs").combobox('getValue');
        var filter_employees = $("#filter_employees").combogrid('getValue');
        var filter_approval = $("#filter_approval").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_divisions=" + filter_divisions +
            "&filter_departements=" + filter_departements +
            "&filter_departement_subs=" + filter_departement_subs +
            "&filter_employees=" + filter_employees +
            "&filter_approval=" + filter_approval +
            "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('employee/mutations/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('employee/mutations/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_divisions = $("#filter_divisions").combobox('getValue');
        var filter_departements = $("#filter_departements").combobox('getValue');
        var filter_departement_subs = $("#filter_departement_subs").combobox('getValue');
        var filter_employees = $("#filter_employees").combogrid('getValue');
        var filter_approval = $("#filter_approval").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_divisions=" + filter_divisions +
            "&filter_departements=" + filter_departements +
            "&filter_departement_subs=" + filter_departement_subs +
            "&filter_employees=" + filter_employees +
            "&filter_approval=" + filter_approval +
            "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('employee/mutations/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_mutations.xls') ?>');
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('employee/mutations/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            rowStyler: function(index, row) {
                if (row.status == 1) {
                    return 'background-color:#FFDCDC;';
                }
            },
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="History Mutation"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('employee/mutations/history?employee_id=') ?>' + row.employee_id,
                    singleSelect: true,
                    rownumbers: true,
                    columns: [[
                        {field: 'employee_number', title: 'Employee ID', width: 120},
                        {field: 'employee_name', title: 'Employee Name', width: 150},
                        {field: 'trans_date', title: 'Trans Date', width: 150},
                        {field: 'division_name', title: 'Division', width: 150},
                        {field: 'departement_name', title: 'Departement', width: 150},
                        {field: 'departement_sub_name', title: 'Departement Sub', width: 150},
                        {field: 'old_position', title: 'Position', width: 150},
                        {field: 'mutation_type', title: 'Mutation Type', width: 120},
                        {field: 'description', title: 'Note', width: 200}
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
            }
        });

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
                    window.open('<?= base_url('employee/mutations/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('employee/mutations/upload') ?>',
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
                                url: "<?= base_url('employee/mutations/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('employee/mutations/uploadCreate') ?>",
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
                                                    url: "<?= base_url('employee/mutations/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        //Get Division
        $('#division_id').combobox({
            url: '<?php echo base_url('employee/divisions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Division',
            onSelect: function(division) {
                $('#departement_id').combobox({
                    url: '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + division.id,
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose Departement',
                    onSelect: function(departement) {
                        $('#departement_sub_id').combobox({
                            url: '<?php echo base_url('employee/departement_subs/reads'); ?>?division_id=' + division.id + '&departement_id=' + departement.id,
                            valueField: 'id',
                            textField: 'name',
                            prompt: 'Choose Departement Sub',
                        });
                    }
                });
            }
        });

        //Get Division
        $('#filter_divisions').combobox({
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
                $('#filter_departements').combobox({
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
                        $('#filter_departement_subs').combobox({
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
                        });
                    }
                });
            }
        });

        $('#filter_employees').combogrid({
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
                    width: 150
                }, {
                    field: 'name',
                    title: 'Employee Name',
                    width: 200
                }]
            ],
        });

        $('#contract_id').combobox({
            url: '<?php echo base_url('employee/contracts/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Employee Type',
        });

        $('#group_id').combobox({
            url: '<?php echo base_url('employee/groups/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Group'
        });

        $('#position_id').combogrid({
            url: '<?= base_url('employee/positions/reads') ?>',
            panelWidth: 300,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Position',
            columns: [
                [{
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'level',
                    title: 'Level',
                    width: 80
                }]
            ]
        });

        var data_position = [];
        $.ajax({
            url: '<?= base_url('employee/mutations/readPositions'); ?>',
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                data_position = result;
                $('#position').combobox({
                    data: result,
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose Position',
                    onSelect: function (rec) { $('#position_new').val(rec.name); },
                    onLoadSuccess: function (data) {
                        if (!data.some(value => value.id == $(this).combobox('getValue')))
                            { $(this).combobox('clear'); }
                    }
                }).combobox('disable');
            }
        });

        $('#employee_id').combogrid({
            url: '<?= base_url('employee/employees/readFulls') ?>',
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
            onSelect: function(val, row) {
                var ol_position = data_position.find(value => value.id == row.position_id).name;

                $("#position_old").textbox('setValue', ol_position);
                $('#mutation_type').combobox('clear').combobox('enable');
                $("#division_old").textbox('setValue', row.division_name);
                $("#departement_old").textbox('setValue', row.departement_name);
                $("#departement_sub_old").textbox('setValue', row.departement_sub_name);
                $("#position").combobox('loadData', data_position).combobox('setValue', row.position_id);

                // Set hidden inputs dengan position baru
                $('#position_new').val(ol_position);
                // Set hidden inputs dengan ID lama
                $("#division_old_id").val(row.division_old_id);
                $("#departement_old_id").val(row.departement_old_id);
                $("#departement_sub_old_id").val(row.departement_sub_old_id);
            }
        });

        $('#mutation_type').combobox({
            editable: false,
            textField: 'name',
            valueField: 'name',
            prompt: 'Choose Mutation Type',
            data: [{name: 'MUTATION'}, {name: 'ROTATION'}, {name: 'PROMOTION'}, {name: 'DEMOTION'}],
            panelHeight: 'auto',
            onSelect: function (rec) {
                var position = $('#position').combobox('getValue');

                $("#position").combobox('readonly', !['PROMOTION', 'DEMOTION'].includes(rec.name)).combobox('enable');
                if (position != '') {
                    var level_mp = parseFloat(data_position.find(value => value.id == position).level);
                    var data = [];

                    data_position.forEach(function (value, key) {
                        if ((rec.name == 'PROMOTION') ? parseFloat(value.level) <= level_mp : parseFloat(value.level) >= level_mp)
                            { data.push(value); }
                    });
                    $("#position").combobox('loadData', data)
                }
            }
        })
    });

    function FormatterDate(val) {
        if (val == "0000-00-00") {
            return '-';
        } else {
            return val;
        }
    };

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