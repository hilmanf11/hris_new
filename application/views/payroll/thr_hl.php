<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th data-options="field:'employee_number',width:150,halign:'center'">Employee ID</th>
            <th data-options="field:'employee_name',width:250,halign:'center'">Employee Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'departement_name',width:200,halign:'center'">Departement</th>
            <th data-options="field:'departement_sub_name',width:200,halign:'center'">Departement Sub</th>
            <th data-options="field:'date_sign',width:100,halign:'center'">Join Date</th>
            <th data-options="field:'cutoff',width:100,halign:'center'">Cutoff</th>
            <th data-options="field:'type',width:150,halign:'center',align:'center'">THR Type</th>
            <th data-options="field:'services',width:150,halign:'center'">Fit for Service</th>
            <th data-options="field:'position_name',width:100,halign:'center'">Position</th>
            <th data-options="field:'marital',width:100,halign:'center'">Marital</th>
            <th data-options="field:'tax_id',width:100,halign:'center'">Tax Id</th>
            <th data-options="field:'salary',width:100,halign:'center',align:'right', formatter:numberformat">Salary</th>
            <th data-options="field:'thr',width:100,halign:'center',align:'right', formatter:numberformat">THR</th>
            <th data-options="field:'income',width:100,halign:'center',align:'right', formatter:numberformat">Payroll</th>
            <th data-options="field:'income_thr',width:100,halign:'center',align:'right', formatter:numberformat">Total Income</th>
            <th data-options="field:'pph',width:100,halign:'center',align:'right', formatter:numberformat">TER</th>
            <th data-options="field:'total_thr',width:100,halign:'center',align:'right', formatter:numberformat">Total THR</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 99%; border:2px solid #d0d0d0; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width: 32%; float: left;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Period</span>
                        <input style="width:60%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" panelHeight="auto" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Cutoff</span>
                        <input style="width:60%;" name="filter_cutoff" id="filter_cutoff" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable: false">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Fit for Service</span>
                        <input style="width:60%;" id="filter_service" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="formula()"><i class="fa fa-percent"></i> Formula</a>
                    </div>
                </div>
                <div style="width: 33%; float: left;">
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
                </div>
                <div style="width: 33%; float: left;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Employee</span>
                        <input style="width:60%;" id="filter_employee" class="easyui-combogrid">
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="btnAdditional()"><i class="fa fa-plus"></i> Additional THR</a>
</div>

<div id="dlg_generate" class="easyui-dialog" title="Generating Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the generate process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">
        <p>History Generate</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<div id="dlg_additional" class="easyui-dialog" style="width: 1200px; height: 550px;">
    <div id="toolbar_additional">
        <table>
            <tr>
                <td style="width: 77px;">THR Type</td>
                <td><input class="easyui-textbox" data-options="readonly: true, value: 'ADDITIONAL'"></td>
            </tr>
            <tr>
                <td>Cutoff THR</td>
                <td><input class="easyui-datebox" id="cutoff_thr"></td>
            </tr>
            <tr><td colspan="2">
                <div class="row">
                    <div class="col-auto">
                        <span style="width: 200px;">Employee</span>
                    </div>
                    <div class="col-auto">
                        <input id="employee_thr" class="easyui-combogrid">
                    </div>
                    <div class="col-auto">
                        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="btnAddCal()"><i class="fa fa-plus"></i> Add & Calculate THR</a>
                    </div>
                    <div class="col-auto">
                        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="btnDelete()"><i class="fa fa-minus"></i> Delete</a>
                    </div>
                </div>
            </td></tr>
        </table>
    </div>
    <table id="dg_additional" class="easyui-datagrid" style="width: 100%; height: 100%;"></table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('payroll/thr_hl/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function formula(){
        Swal.fire({
            width: 600,
            html: `<div style="text-align:left;"><center><b style="font-size:16px !important;">Ketentuan THR</b><hr></center>
                <b>Employee Type Contract & Permanent</b>
                <ul>
                    <li>Masa kerja diatas 1 tahun, Full 1 kali gaji THR (Salary + Allowence (FIX))</li>
                    <li>Masa kerja 1 - 11 bulan, Proporsional THR (Salary + Allowence (FIX))</li>
                    <li>Masa kerja belum 1 bulan, tidak dapat THR</li>
                </ul>
                <b>Employee Type Magang</b>
                <ul>
                    <li>Masa kerja diatas 1 tahun full 1 kali gaji (Source -> THR Fee)</li>
                    <li>Masa kerja 1 - 11 bulan, Proporsional THR (Source -> THR Fee)</li>
                    <li>Masa kerja belum 1 bulan, tidak dapat THR</li>
                </ul>
                <b>Employee Type PKL</b>
                <ul>
                    <li>Masa kerja diatas 4-6 bulan Rp. 100.000</li>
                    <li>Masa kerja 3 bulan Rp. 50.000</li>
                    <li>Masa kerja dibawah 1-2 bulan Rp. 30.000</li>
                </ul>`,
            showClass: {
                popup: `animate__animated animate__fadeInUp animate__faster`
            },
            hideClass: {
                popup: `animate__animated animate__fadeOutDown animate__faster`
            }
        });
    }

    function add() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_service = $("#filter_service").combobox('getValue');

        if (filter_year == "") {
            toastr.warning("Please Enter Period", "Filter Date");
        } else {
            $.ajax({
                url: "<?= base_url('payroll/thr_hl/read') ?>",
                type: 'get',
                data: 'filter_year=' + filter_year,
                success: function(message) {
                    var message_read = eval('(' + message + ')');

                    if (message_read['status'] != "1") {
                        if (message_read['generate'] == "EXIST") {
                            $.messager.confirm('Warning', 'Are you sure you want to repeat generate data?', function(r) {
                                if (r) {
                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('payroll/thr_hl/delete') ?>',
                                        data: {
                                            filter_year: filter_year,
                                            filter_division: filter_division,
                                            filter_departement: filter_departement,
                                            filter_departement_sub: filter_departement_sub,
                                            filter_employee: filter_employee
                                        },
                                        success: function(deleted) {
                                            Swal.fire({
                                                title: 'Please Wait for Generating Data',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            var url = "filter_division=" + filter_division +
                                                "&filter_departement=" + filter_departement +
                                                "&filter_departement_sub=" + filter_departement_sub +
                                                '&filter_year=' + filter_year +
                                                '&filter_cutoff=' + btoa(filter_cutoff) +
                                                '&filter_employee=' + filter_employee;

                                            $.ajax({
                                                url: "<?= base_url('payroll/thr_hl/generatePayroll') ?>",
                                                type: 'get',
                                                data: url,
                                                success: function(result) {
                                                    $('#dlg_generate').dialog('open');
                                                    Swal.close();
                                                    var json = eval('(' + result + ')');
                                                    requestData(json.total, json);

                                                    function requestData(total, json, number = 1, value = 0) {
                                                        if (value < 100) {
                                                            value = Math.floor((number / total) * 100);
                                                            $('#p_upload').progressbar('setValue', value);
                                                            $('#p_start').html(number);
                                                            $('#p_finish').html(total);

                                                            $.ajax({
                                                                url: "<?= base_url('payroll/thr_hl/create') ?>?" + url,
                                                                type: 'post',
                                                                data: json['rows'][number - 1],
                                                                success: function(note) {
                                                                    var result = eval('(' + note + ')');
                                                                    if (result.theme == "success") {
                                                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                                    } else {
                                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                                    }
                                                                    $("#p_remarks").append(title + "<br>");
                                                                    requestData(total, json, number + 1, value);

                                                                    if (number == total) {
                                                                        $('#dlg_generate').dialog('close');
                                                                        Swal.fire(
                                                                            'Generate Completed',
                                                                            'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',
                                                                            'success'
                                                                        );
                                                                    }
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
                                                                            requestData(total, json, number, value);
                                                                        }, 5000);
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }
                                                }
                                            });
                                        },
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Please Wait for Generating Data',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            var url = "filter_division=" + filter_division +
                                "&filter_departement=" + filter_departement +
                                "&filter_departement_sub=" + filter_departement_sub +
                                '&filter_year=' + filter_year +
                                '&filter_cutoff=' + btoa(filter_cutoff) +
                                '&filter_employee=' + filter_employee;
                            $.ajax({
                                url: "<?= base_url('payroll/thr_hl/generatePayroll') ?>",
                                type: 'get',
                                data: url,
                                success: function(result) {
                                    $('#dlg_generate').dialog('open');
                                    Swal.close();
                                    var json = eval('(' + result + ')');
                                    requestData(json.total, json);

                                    function requestData(total, json, number = 1, value = 0) {
                                        if (value < 100) {
                                            value = Math.floor((number / total) * 100);
                                            $('#p_upload').progressbar('setValue', value);
                                            $('#p_start').html(number);
                                            $('#p_finish').html(total);

                                            $.ajax({
                                                url: "<?= base_url('payroll/thr_hl/create') ?>?" + url,
                                                type: 'post',
                                                data: json['rows'][number - 1],
                                                success: function(note) {
                                                    var result = eval('(' + note + ')');
                                                    if (result.theme == "success") {
                                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                    } else {
                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                    }
                                                    $("#p_remarks").append(title + "<br>");
                                                    requestData(total, json, number + 1, value);

                                                    if (number == total) {
                                                        $('#dlg_generate').dialog('close');
                                                        Swal.fire(
                                                            'Generate Completed',
                                                            'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',
                                                            'success'
                                                        );
                                                    }
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
                                                            requestData(total, json, number, value);
                                                        }, 5000);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    } else {
                        Swal.fire(
                            'Cannot Generate!',
                            'the payroll data that you entered has been approved, please contact the relevant admin if you want to repeat generate payroll again',
                            'warning'
                        );
                    }
                }
            });
        }
    }

    function filter() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_service = $("#filter_service").combobox('getValue');

        var url = "?filter_year=" + filter_year +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_service=" + filter_service;

        if (filter_year == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {

            $('#dg').datagrid({
                url: '<?= base_url('payroll/thr_hl/datatables') ?>' + url
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('payroll/thr_hl/print') ?>' + url);
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid("getSelections");
        if (rows.length == 0) { toastr.warning("Please select data!", "Warning"); }
        else {
            var ids = [];

            rows.forEach(function (value, key) {
                ids.push(btoa(value.id));
            });

            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    $.ajax({
                        method: 'post',
                        url: '<?= base_url('payroll/thr_hl/delete') ?>',
                        data: {id: ids},
                        success: function(deleted) { $('#dg').datagrid("reload"); }
                    });
                }
            });
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_service = $("#filter_service").combobox('getValue');

        var url = "?filter_year=" + filter_year +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_service=" + filter_service;


        if (filter_year == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {
            window.location.assign('<?= base_url('payroll/thr_hl/print/excel') ?>' + url);
        }
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    var url_emp_additional = "<?= base_url('payroll/thr_hl/readEmployee') ?>";
    $(function() {
        $("#add").html("Generate THR");
        $('#dg').datagrid({
            url: '<?= base_url('payroll/thr_hl/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //Filter Cutoff
        $('#filter_year').combobox({
            url: '<?php echo base_url('payroll/thr_hl/readYears'); ?>',
            valueField: 'year',
            textField: 'year',
            prompt: 'Choose Period',
        });

        //Filter Cutoff
        $('#filter_service').combobox({
            textField: 't',
            valueField: 'v',
            panelHeight: 'auto',
            prompt: 'Choose All',
            data: [{v: '0', t: ' < 1 Year'}, {v: '1', t: ' >= 1 Year'}],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
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

        $("#cutoff_thr").datebox({
            editable: false,
            parser: myparser,
            formatter: myformatter,
            onSelect: function (date) {
                $("#employee_thr").combogrid("grid")
                    .datagrid("reload", {cutoff: btoa(myformatter(date))});
            }
        });
        $("#employee_thr").combogrid({
            url: url_emp_additional,
            idField: "number",
            textField: "name",
            editable: false,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear'); }
            }],
            panelWidth: 530,
            panelHeight: 427,
            pagination: true,
            rownumbers: true,
            columns: [[
                {field: 'number', title: 'Employee ID', width: '130', halign: 'center'},
                {field: 'name', title: 'Employee Name', width: '350', halign: 'center'}
            ]]
        }).combogrid("grid").datagrid("enableFilter");

        $("#dg_additional").datagrid({
            toolbar: "#toolbar_additional",
            checkOnSelect: true,
            selectOnCheck: true,
            rownumbers: true,
            fit: true,
            frozenColumns: [[
                {field: "ck", checkbox: true},
                {field: "employee_number", title: "Employee ID", halign: "center"},
                {field: "employee_name", title: "Employee Name", halign: "center"}
            ]],
            columns: [[
                {field: 'departement_name', title: "Departement", halign: 'center'},
                {field: 'departement_sub_name', title: "Departement Sub", halign: 'center'},
                {field: 'source_name', title: "Source", halign: 'center'},
                {field: 'date_sign', title: "Join Date", halign: 'center'},
                {field: 'cutoff', title: "Cutoff", halign: 'center'},
                {field: 'type', title: "THR Type", halign: 'center', align: 'center'},
                {field: 'services', title: "Fit for Service", halign: 'center'},
                {field: 'position_name', title: "Position", halign: 'center'},
                {field: 'marital', title: "Marital", halign: 'center'},
                {field: 'tax_id', title: "Tax Id", halign: 'center'},
                {field: 'salary', title: "Salary", halign: 'center', align: 'right', formatter: numberformat},
                {field: 'thr', title: "THR", halign: 'center', align: 'right', formatter: numberformat},
                {field: 'income', title: "Payroll", halign: 'center', align: 'right', formatter: numberformat},
                {field: 'income_thr', title: "Total Income", halign: 'center', align: 'right', formatter: numberformat},
                {field: 'pph', title: "TER", halign: 'center', align: 'right', formatter: numberformat},
                {field: 'total_thr', title: "Total THR", halign: 'center', align: 'right', formatter: numberformat}
            ]]
        });
        $("#dlg_additional").dialog({
            title: 'Additional THR',
            closed: true,
            modal: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $("#dg_additional").datagrid("loading");
                    $.ajax({
                        url: "<?= base_url('payroll/thr_hl/additionalSave') ?>",
                        data: {
                            periode: btoa($("#filter_year").combobox("getValue")),
                            data: btoa(JSON.stringify($("#dg_additional").datagrid("getData").rows))
                        },
                        async: false,
                        method: 'POST',
                        dataType: 'json',
                        success: function (result) {
                            $("#dg_additional").datagrid("loaded");
                            if (result.stat != '') { toastr.error("Failed save Additional!", "ERROR"); }
                            else { $("#dlg_additional").dialog("close"); filter(); }
                        }
                    });
                }
            }]
        }).dialog("close");
    });

    function btnAdditional() {
        var date = new Date(), now = myformatter(date);

        $("#cutoff_thr").datebox("setValue", now);
        $("#employee_thr").combogrid("clear").combogrid("grid")
            .datagrid("reload", {cutoff: btoa(now)});

        $("#dg_additional").datagrid("loadData", []);
        $("#dlg_additional").dialog("open");
    }

    function btnAddCal() {
        var cutoff = $("#cutoff_thr").datebox("getValue");
        var mp = $("#employee_thr").combogrid("grid").datagrid("getSelected");

        if (cutoff == '' || mp == '') { toastr.warning("Please fill in all fields!", "Warning"); }
        else if ($("#dg_additional").datagrid("getRows").some(obj => obj.employee_number == mp))
            { toastr.warning("Employees available!", "Warning"); }
        else {
            $("#dg_additional").datagrid("loading");
            $.ajax({
                url: "<?= base_url('payroll/thr_hl/additionalCalculation') ?>",
                data: {cutoff: btoa(cutoff), mp: btoa(mp.number)},
                async: false,
                method: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result == null) { toastr.error("No Data!", "Failed"); }
                    else {
                        $("#dg_additional").datagrid('appendRow', result);
                        $("#employee_thr").combogrid("clear");
                    }
                    $("#dg_additional").datagrid("loaded");

                    ["employee_number","employee_name","departement_name","departement_sub_name","source_name","date_sign","cutoff","type","services","position_name","marital","tax_id","salary","thr","income","income_thr","pph","total_thr"].forEach(function (value, key) { $("#dg_additional").datagrid("autoSizeColumn", value); });
                }
            });
        }
    }

    function btnDelete() {
        var dg = $("#dg_additional"), selections = dg.datagrid("getSelections");
        if (dg.length == 0) { toastr.warning("Please select one of the data in the table first!", "Information"); }
        else {
            for (var i = selections.length - 1; i >= 0; i--) {
                var index = dg.datagrid('getRowIndex', selections[i]);
                dg.datagrid('deleteRow', index);
            }
            dg.datagrid('acceptChanges');
        }
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

    function StylerAllowence(value, row, index) {
        return 'background: #C6FFB5;';
    }

    function StylerDeduction(value, row, index) {
        return 'background: #FFB5B5;';
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID');

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>