<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:120,halign:'center'">Employee ID</th>
            <th rowspan="2" data-options="field:'employee_name',width:200">Employee Name</th>
            <th rowspan="2" data-options="field:'division_name',width:200,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'departement_name',width:150,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'departement_sub_name',width:150,halign:'center'">Departement Sub</th>
            <th rowspan="2" data-options="field:'position_name',width:150,halign:'center'">Position</th>
            <th rowspan="2" data-options="field:'contract_name',width:150,halign:'center'">Employee Type</th>
            <th rowspan="2" data-options="field:'date_sign',width:100,halign:'center',formatter:FormatterDate">Join Date</th>
            <th rowspan="2" data-options="field:'date_expired',width:120,halign:'center',formatter:FormatterDate">Contract Expired</th>
            <th rowspan="2" data-options="field:'note',width:300,halign:'center'">Note</th>
            <th rowspan="2" data-options="field:'attachment',width:100,align:'center', formatter:fileFormatter">Attachment</th>
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
                        <span style="width:35%; display:inline-block;">Contract Expired</span>
                        <select style="width:60%;" id="filter_expired" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="0">Yesterday</option>
                            <option value="1">Today</option>
                            <option value="2">Tomorrow</option>
                            <option value="3">1 Weeks</option>
                            <option value="4">1 Months</option>
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
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" required="" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Employee ID</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="employee_name" id="employee_name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Date Sign Contract</span>
                <input style="width:60%;" name="date_sign" id="date_sign" required="" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Date Expired</span>
                <input style="width:60%;" name="date_expired" id="date_expired" required="" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Duration</span>
                <input style="width:60%;" name="duration" id="duration" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" name="division_id" id="division_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Department</span>
                <input style="width:60%;" name="departement_id" id="departement_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Department Sub</span>
                <input style="width:60%;" name="departement_sub_id" id="departement_sub_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Process</span>
                <input style="width:60%;" name="process" id="process" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Employee Type</span>
                <input style="width:60%;" name="contract_id" id="contract_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Position</span>
                <input style="width:60%;" name="position_id" id="position_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Group</span>
                <input style="width:60%;" name="group_id" id="group_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Attachment</span>
                <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Notes</span>
                <input style="width:60%; height: 80px;" name="note" id="note" multiline="true" class="easyui-textbox">
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
                <input name="file_upload" style="width: 60%;" required="" accept=".xlsx" id="file_excel" class="easyui-filebox">
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
<iframe id="printout" src="<?= base_url('employee/agreements/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        url_save = '<?= base_url('employee/agreements/create') ?>';
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
        $('#number').combogrid('readonly', false);
        $('#departement_id').combobox('loadData', []);
        $('#departement_sub_id').combobox('loadData', []);
        $('#date_sign').datebox('setValue', "<?= date("Y-m-d") ?>");
        $('#trans_date').datebox('setValue', "<?= date("Y-m-d") ?>");
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.status == 0) {
                if (row.status_check == "" || row.status_check == null) {
                    url_save = '<?= base_url('employee/agreements/update') ?>?id=' + btoa(row.id);
                    $('#dlg_insert').dialog('open');
                    $('#number').combogrid('readonly', true);
                    $('#frm_insert').form('load', row);
                    $('#duration').textbox('setValue', calculateDuration(row.date_sign, row.date_expired).text);
                } else {
                    toastr.error("Approval still Checked");
                }
            } else {
                toastr.error("You can't edit this data, klik add to change employee position, group or another!");
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
                        if (row.status != 0) {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('employee/agreements/delete') ?>',
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
                        } else {
                            toastr.error("You can't delete this data because the status is active!");
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    //FILTER DATA
    function filter() {
        var filter_divisions = $("#filter_divisions").combobox('getValue');
        var filter_departements = $("#filter_departements").combobox('getValue');
        var filter_departement_subs = $("#filter_departement_subs").combobox('getValue');
        var filter_employees = $("#filter_employees").combogrid('getValue');
        var filter_expired = $("#filter_expired").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_divisions=" + filter_divisions +
            "&filter_departements=" + filter_departements +
            "&filter_departement_subs=" + filter_departement_subs +
            "&filter_employees=" + filter_employees +
            "&filter_expired=" + filter_expired +
            "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('employee/agreements/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('employee/agreements/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_divisions = $("#filter_divisions").combobox('getValue');
        var filter_departements = $("#filter_departements").combobox('getValue');
        var filter_departement_subs = $("#filter_departement_subs").combobox('getValue');
        var filter_employees = $("#filter_employees").combogrid('getValue');
        var filter_expired = $("#filter_expired").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_divisions=" + filter_divisions +
            "&filter_departements=" + filter_departements +
            "&filter_departement_subs=" + filter_departement_subs +
            "&filter_employees=" + filter_employees +
            "&filter_expired=" + filter_expired +
            "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('employee/agreements/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    var dataUpload = [];
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('employee/agreements/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            rowStyler: function(index, row) {
                if (row.status == 1) {
                    return 'background-color:#FFDCDC;';
                }
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

        $("#dlg_upload").dialog({
            title: "Upload Data",
            closed: true,
            modal: true,
            buttons: [{
                text: "Upload",
                iconCls: "icon-ok",
                handler: function() {
                    $("#frm_upload").form("submit", {
                        url: "<?= base_url('employee/agreements/') ?>uploadTemplate",
                        onSubmit: function() {
                            if ($(this).form("validate") == false) {
                                return $(this).form("validate");
                            } else {
                                $.messager.progress({
                                    title: "Please Wait",
                                    msg: "Importing Excel to Database"
                                });
                            }
                        },
                        success: function(result) {
                            var json = JSON.parse(result);

                            $.messager.progress("close");
                            if (json.stat != "") { toastr.warning(json.stat, "Warning"); }
                            else {
                                dataUpload = json.data;
                                $("#p_remarks").html("");
                                $("#p_success").html("0");
                                $("#p_failed").html("0");
                                uploadProcess();
                            }
                        }
                    });
                }
            }]
        });

        //Get Division
        // $('#division_id').combogrid({
        //     url: '<?= base_url('employee/divisions/reads') ?>',
        //     panelWidth: 420,
        //     idField: 'id',
        //     textField: 'name',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Choose Division",
        //     columns: [
        //         [{
        //             field: 'number',
        //             title: 'Division No',
        //             width: 80
        //         }, {
        //             field: 'name',
        //             title: 'Division Name',
        //             width: 250
        //         }, ]
        //     ]
        // });

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
            url: '<?php echo base_url('employee/groups/readAll'); ?>',
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

        $('#number').combogrid({
            url: '<?= base_url('employee/agreements/readEmployees') ?>',
            panelWidth: 450,
            idField: 'number',
            textField: 'number',
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
            onSelect: function (index, row) { $('#employee_name').textbox('setValue', row.name); }
        });

        $('#trans_date').datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false
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
                    { toastr.warning('Please select date sign contract < date expired!', 'Warning'); }
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
                    { toastr.warning('Please select date sign contract < date expired!', 'Warning'); }
            }
        });

        $('#division_id').combobox({
            url: '<?= base_url('employee/agreements/readDivisions') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Division',
            onSelect: function (rec) {
                $('#departement_id').combobox('reload', '<?= base_url('employee/agreements/readDepartments') ?>?id='+window.btoa(rec.id));
            },
            onLoadSuccess: function (data) {
                var get = $(this).combobox('getValue');
                if (get != '') { if (!data.some(value => get == value.id)) { $(this).combobox('clear'); } }
            }
        });

        $('#departement_id').combobox({
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Department',
            onSelect: function (rec) {
                $('#departement_sub_id').combobox('reload', '<?= base_url('employee/agreements/readDepartmentSubs') ?>?id='+window.btoa(rec.id));
            },
            onLoadSuccess: function (data) {
                var get = $(this).combobox('getValue');
                if (get != '') { if (!data.some(value => get == value.id)) { $(this).combobox('clear'); } }
            }
        });

        $('#departement_sub_id').combobox({
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Department Sub',
            onLoadSuccess: function (data) {
                var get = $(this).combobox('getValue');
                if (get != '') { if (!data.some(value => get == value.id)) { $(this).combobox('clear'); } }
            }
        });
    });

    function upload() { $("#dlg_upload").dialog("open"); }

    function uploadProcess(index = 0) { getprocess("templateProcess", dataUpload[index]); }

    function download_excel() {
        $.messager.alert({title: "Please Wait", msg: "Downloading template..."});
        window.location.assign("<?= base_url('employee/agreements/') ?>template");
    }

    function getprocess(act, data) {
        $("#dg").datagrid("loading");
        $.ajax({
            url: "<?= base_url('employee/agreements/') ?>"+act,
            data: data,
            async: false,
            method: "POST",
            dataType: "json",
            success: function (result) {
                if (act == "templateProcess") {
                    var index = dataUpload.indexOf(data);
                    var number = index + 1;
                    var total = dataUpload.length;

                    $("#p_upload").progressbar("setValue", Math.floor((number / total) * 100));
                    $("#p_start").html(number);
                    $("#p_finish").html(total);
                    $("#p_remarks").append(
                        `<b style="color: `+((result.theme != "success") ? "red" : "green")+`;"> `+ result.title + `</b> | ` +
                        result.message + `<br>`
                    );
                    $("#p_success").html(parseInt($("#p_success").html()) + ((result.theme == "success") ? 1 : 0));
                    $("#p_failed").html(parseInt($("#p_failed").html()) + ((result.theme == "success") ? 0 : 1));

                    setTimeout(function () {
                        if (number != total) { getprocess("templateProcess", dataUpload[number]); }
                        else { $("#dg").datagrid("reload"); }
                    },1);
                }
                $("#dg").datagrid("loaded");
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

    function fileFormatter(value) {
        if (value == "" || value == null) {
            return '-';
        } else {
            return '<a href="' + value + '" style="text-decoration: none;"><i class="fa fa-download"></i> Download</a>';
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