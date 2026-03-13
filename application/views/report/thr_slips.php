<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="rownumbers: true">
    <thead>
        <tr>
            <th data-options="field:'departement_name',width:200,halign:'center'">Departement</th>
            <th data-options="field:'departement_sub_name',width:200,halign:'center'">Departement Sub</th>
            <th data-options="field:'employee',width:100,halign:'center'">Employee</th>
            <th data-options="field:'print',width:100,halign:'center',formatter:FormatterFile"> Print</th>
            <th data-options="field:'mail',width:100,halign:'center',formatter:FormatterMail"> Email</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 235px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" panelHeight="auto" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement</span>
                <input style="width:60%;" id="filter_departement" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 49%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement Sub</span>
                <input style="width:60%;" id="filter_departement_sub" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Employee</span>
                <input style="width:60%;" id="filter_employee" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Group</span>
                <input style="width:60%;" id="filter_group" name="filter_group" class="easyui-combobox">
            </div>
        </div>
    </fieldset>

    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excelReport()"><i class="fa fa-file"></i> Export Report</a>
    <iframe id="printout_recap" src="" style="width: 100%; height:500px; border: 0;" hidden></iframe>
</div>

<div id="dlg_generate" class="easyui-dialog" title="Generating Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the sending mail process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">

    </div>
</div>

<div id="dlg_print" class="easyui-window" title="Print Preview" data-options="closed: true,minimizable:false,collapsible:false,maximizable:false,modal:true,footer:'#footer'" style="width: 1000px; height: 500px; top: 20px;">
    <iframe id="printout" src="" style="width: 100%; height:410px; border: 0;"></iframe>
    <div id="footer" style="padding:5px; text-align:right;">
        <a class="easyui-linkbutton c6" onclick="pdf_detail()" style="width:120px">Print</a>
    </div>
</div>

<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout_recap").get(0).contentWindow.print();
    }

    function pdf_detail() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        if (filter_year == "" || filter_division == "") {
            toastr.warning("Please Choose Period and Division");
        } else {
            var url = "?filter_division=" + filter_division +
                "&filter_departement=" + filter_departement +
                "&filter_departement_sub=" + filter_departement_sub +
                '&filter_year=' + filter_year +
                '&filter_group=' + filter_group +
                '&filter_employee=' + filter_employee;

            $('#dg').datagrid({
                url: '<?= base_url('report/thr_slips/datatables') ?>' + url
            });

            $("#printout_recap").attr('src', '<?= base_url('report/thr_slips/print_recap') ?>' + url);
        }
    }

    function pdf_view(filter_departement, filter_departement_sub) {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        if (filter_year == "" || filter_division == "") {
            toastr.warning("Please Choose Period and Division");
        } else {
            $("#dlg_print").window('open');
            var url = "?filter_division=" + filter_division +
                "&filter_departement=" + filter_departement +
                "&filter_departement_sub=" + filter_departement_sub +
                '&filter_year=' + filter_year +
                '&filter_employee=' + filter_employee +
                '&filter_group=' + filter_group;

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('report/thr_slips/print') ?>' + url);
        }
    }

    function excel() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        if (filter_year == "" || filter_division == "") {
            toastr.warning("Please Choose Period and Division");
        } else {
            var url = "?filter_division=" + filter_division +
                "&filter_departement=" + filter_departement +
                "&filter_departement_sub=" + filter_departement_sub +
                '&filter_year=' + filter_year +
                '&filter_group=' + filter_group +
                '&filter_employee=' + filter_employee;

            window.location.assign('<?= base_url('report/thr_slips/print/excel') ?>' + url);
        }
    }

    function slipMail(filter_departement, filter_departement_sub) {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        if (filter_year == "" || filter_division == "") {
            toastr.warning("Please Choose Period and Division");
        } else {
            var url = "filter_division=" + filter_division +
                "&filter_departement=" + filter_departement +
                "&filter_departement_sub=" + filter_departement_sub +
                '&filter_year=' + filter_year +
                '&filter_group=' + filter_group +
                '&filter_employee=' + filter_employee;

            Swal.fire({
                title: 'Please Wait for Sending Mail',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.get("<?= base_url('report/thr_slips/getData') ?>", url,
                function(data, textStatus, jqXHR) {
                    $('#dlg_generate').dialog('open');
                    Swal.close();

                    if (data.length == 0) {
                        Swal.fire(
                            'Not Found',
                            'Employee Not Found in Payroll',
                            'error'
                        );
                    } else {
                        requestSendMail(data.length, data);
                    }

                    function requestSendMail(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.ajax({
                                type: "POST",
                                async: true,
                                url: "<?= base_url('report/thr_slips/sendMail?filter_year=') ?>" + filter_year,
                                data: {
                                    "data": json[number - 1]
                                },
                                cache: false,
                                dataType: "json",
                                success: function(result) {
                                    if (result.theme == "success") {
                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                        setTimeout(function() {
                                            requestSendMail(total, json, number + 1, value);
                                        }, 50000);
                                    } else {
                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                        setTimeout(function() {
                                            requestSendMail(total, json, number + 1, value);
                                        }, 50000);
                                    }
                                    $("#p_remarks").append(title + "<br>");
                                }
                            });
                        } else {
                            $('#dlg_generate').dialog('close');
                            Swal.fire(
                                'Completed',
                                'Send Mail Salary Slip is Completed',
                                'success'
                            );
                        }
                    }
                },
                "json"
            );
        }
    }

    function excelReport() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        if (filter_year == "") { toastr.warning("Please Choose Period"); }
        else {
            var url = "?filter_division=" + filter_division +
                "&filter_departement=" + filter_departement +
                "&filter_departement_sub=" + filter_departement_sub +
                '&filter_year=' + filter_year +
                '&filter_group=' + filter_group +
                '&filter_employee=' + filter_employee;

            window.location.assign('<?= base_url('report/thr_slips/printReport/excel') ?>' + url);
        }
    }

    $(function() {
        //Filter Cutoff
        $('#filter_year').combobox({
            url: '<?php echo base_url('payroll/thr/readYears'); ?>',
            valueField: 'year',
            textField: 'year',
            prompt: 'Choose Period',
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

        $("#filter_group").combobox({
            url: '<?= base_url('admin/privilege_groups/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    });

    function FormatterFile(value, row) {
        // if(row.approved_to != ""){
        //     return "<b style='color:red;'>NOT APPROVED</b>";
        // }else{
        var linkPrint = "pdf_view('" + row.departement_id + "','" + row.departement_sub_id + "','" + row.group_id + "')";
        return '<a href="#" onclick="' + linkPrint + '" class="btn btn-primary btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-eye"></i> View</a>';
        //}
    };

    function FormatterMail(value, row) {
        // if(row.approved_to != ""){
        //     return "<b style='color:red;'>NOT APPROVED</b>";
        // }else{
        var linkPrint = "slipMail('" + row.departement_id + "','" + row.departement_sub_id + "')";
        return '<a href="#" onclick="' + linkPrint + '" class="btn btn-success btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-envelope"></i> Send</a>';
        //}
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