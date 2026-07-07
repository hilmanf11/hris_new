<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th data-options="field:'employee_number',width:100,halign:'center'">Employee ID</th>
            <th data-options="field:'national_id',width:140,halign:'center'">NIK</th>
            <th data-options="field:'employee_name',width:250,halign:'center'">Employee Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'account_coa',width:100,halign:'center'">COA No</th>
            <th data-options="field:'division_name',width:200,halign:'center'">Division</th>
            <th data-options="field:'departement_name',width:200,halign:'center'">Departement</th>
            <th data-options="field:'departement_sub_name',width:200,halign:'center'">Departement Sub</th>
            <th data-options="field:'source_name',width:200,halign:'center'">Source</th>
            <th data-options="field:'date_sign',width:100,halign:'center'">Join Date</th>
            <th data-options="field:'services',width:150,halign:'center'">Fit of Services</th>
            <th data-options="field:'attandance',width:150,halign:'center'">Attandance</th>
            <th data-options="field:'allowence',width:80,align:'center', formatter:numberformat">Allowence</th>
            <th data-options="field:'correction_plus',width:100,align:'center', formatter:numberformat">Correction Plus</th>
            <th data-options="field:'correction_minus',width:120,align:'center', formatter:numberformat">Correction Minus</th>
            <th data-options="field:'total_income',width:100,align:'center', formatter:numberformat">Total Income</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 99%; border:2px solid #d0d0d0; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width: 50%; float:left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period Date</span>
                        <input style="width:28%;" name="filter_from" id="filter_from" class="easyui-combogrid"> To
                        <input style="width:28%;" name="filter_to" id="filter_to" data-options="prompt:'Date To'" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Source</span>
                        <input style="width:60%;" id="filter_source" name="filter_source" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width: 50%; float:left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Employee</span>
                        <input style="width:60%;" id="filter_employee" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Status</span>
                        <select style="width:60%;" id="filter_status" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="0">Active</option>
                            <option value="1">Resign</option>
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
</div>

<div id="dlg_generate" class="easyui-dialog" title="Generating Data" data-options="closed: true,modal:true,closable: true" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the generate process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">
        <p>History Generate</p>
        <ul id="remarks"></ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('payroll/payroll_pkl/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    var data_failed = [];
    function add() {
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');
        var filter_source = $("#filter_source").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date");
        } else {
            $.ajax({
                url: "<?= base_url('payroll/payroll_pkl/read') ?>",
                type: 'get',
                data: 'filter_from=' + filter_from,
                success: function(message) {
                    var message_read = eval('(' + message + ')');

                    if (message_read['status'] != "1") {
                        if (message_read['generate'] == "EXIST") {
                            $.messager.confirm('Warning', 'Are you sure you want to repeat generate data?', function(r) {
                                if (r) {
                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('payroll/payroll_pkl/delete') ?>',
                                        data: {
                                            filter_from: filter_from,
                                            filter_to: filter_to,
                                            filter_source: filter_source,
                                            filter_employee: filter_employee,
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

                                            var url = 'filter_from=' + filter_from +
                                                '&filter_to=' + filter_to +
                                                '&filter_source=' + filter_source +
                                                '&filter_employee=' + filter_employee;
                                            $.ajax({
                                                url: "<?= base_url('payroll/payroll_pkl/generatePayroll') ?>",
                                                type: 'get',
                                                data: url,
                                                success: function(result) {
                                                    data_failed = [];
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
                                                                url: "<?= base_url('payroll/payroll_pkl/create') ?>?" + url,
                                                                type: 'post',
                                                                data: json['rows'][number - 1],
                                                                success: function(note) {
                                                                    var result = eval('(' + note + ')');
                                                                    if (result.theme == "success") {
                                                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                                    } else {
                                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                                        data_failed.push(result.title + " | " + result.message);
                                                                    }
                                                                    $("#p_remarks").append(title + "<br>");
                                                                    requestData(total, json, number + 1, value);

                                                                    if (number == total) {
                                                                        var obj = {
                                                                            title: 'Generate Completed',
                                                                            text: 'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',
                                                                            icon: 'success'
                                                                        };
                                                                        if (data_failed.length != 0) {
                                                                            obj.reverseButtons = true;
                                                                            obj.showCancelButton = true;
                                                                            obj.confirmButtonText = "Download Failed";
                                                                            obj.cancelButtonText = "OK";
                                                                            obj.confirmButtonColor = "#d33";
                                                                            obj.cancelButtonColor = "#3085d6";
                                                                        }
                                                                        $('#dlg_generate').dialog('close');
                                                                        Swal.fire(obj).then((result) => {
                                                                            if (result.isConfirmed && data_failed.length != 0) { downloadFailed(); }
                                                                        });
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

                            var url = 'filter_from=' + filter_from +
                                '&filter_to=' + filter_to +
                                '&filter_source=' + filter_source +
                                '&filter_employee=' + filter_employee;
                            $.ajax({
                                url: "<?= base_url('payroll/payroll_pkl/generatePayroll') ?>",
                                type: 'get',
                                data: url,
                                success: function(result) {
                                    data_failed = [];
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
                                                url: "<?= base_url('payroll/payroll_pkl/create') ?>?" + url,
                                                type: 'post',
                                                data: json['rows'][number - 1],
                                                success: function(note) {
                                                    var result = eval('(' + note + ')');
                                                    if (result.theme == "success") {
                                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                    } else {
                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                        data_failed.push(result.title + " | " + result.message);
                                                    }
                                                    $("#p_remarks").append(title + "<br>");
                                                    requestData(total, json, number + 1, value);

                                                    if (number == total) {
                                                        var obj = {
                                                            title: 'Generate Completed',
                                                            text: 'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',
                                                            icon: 'success'
                                                        };
                                                        if (data_failed.length != 0) {
                                                            obj.reverseButtons = true;
                                                            obj.showCancelButton = true;
                                                            obj.confirmButtonText = "Download Failed";
                                                            obj.cancelButtonText = "OK";
                                                            obj.confirmButtonColor = "#d33";
                                                            obj.cancelButtonColor = "#3085d6";
                                                        }
                                                        $('#dlg_generate').dialog('close');
                                                        Swal.fire(obj).then((result) => {
                                                            if (result.isConfirmed && data_failed.length != 0) { downloadFailed(); }
                                                        });
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

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('payroll/payroll_pkl/delete_id') ?>',
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

    function filter() {
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');
        var filter_source = $("#filter_source").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_source=" + filter_source +
            "&filter_employee=" + filter_employee +
            "&filter_status=" + filter_status;

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {

            $('#dg').datagrid({
                url: '<?= base_url('payroll/payroll_pkl/datatables') ?>' + url
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('payroll/payroll_pkl/print') ?>' + url);
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');
        var filter_source = $("#filter_source").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_source=" + filter_source +
            "&filter_employee=" + filter_employee +
            "&filter_status=" + filter_status;

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {
            window.location.assign('<?= base_url('payroll/payroll_pkl/print/excel') ?>' + url);
        }
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html("Generate Payroll");
        $('#dg').datagrid({
            url: '<?= base_url('payroll/payroll_pkl/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //Filter Cutoff
        $('#filter_from').combogrid({
            url: '<?= base_url('payroll/cutoff/reads') ?>',
            panelWidth: 300,
            idField: 'start',
            textField: 'start',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Date From',
            columns: [
                [{
                    field: 'start',
                    title: 'Date From',
                    width: 120
                }, {
                    field: 'finish',
                    title: 'Date To',
                    width: 120
                }]
            ],
            onSelect: function(val, row) {
                $("#filter_to").textbox('setValue', row.finish);
            }
        });

        $('#filter_source').combobox({
            url: '<?= base_url('employee/sources/read') ?>/PKL',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(source) {
                $('#filter_employee').combogrid({
                    url: '<?= base_url('employee/employees/reads?source_id=') ?>' + source.id,
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

    function downloadFailed() {
        const blob = new Blob([data_failed.join('\r\n')], { type: 'text/plain;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        const now = new Date();
  
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0'); 
        const date = String(now.getDate()).padStart(2, '0');

        const hour = String(now.getHours()).padStart(2, '0');
        const minute = String(now.getMinutes()).padStart(2, '0');
        const second = String(now.getSeconds()).padStart(2, '0');

        link.href = url;
        link.download = "Generate Payroll PKL, failed generate ("+year+"-"+month+"-"+date+" "+hour+";"+minute+";"+second+").txt";
        link.click();
        URL.revokeObjectURL(url);
    }
</script>