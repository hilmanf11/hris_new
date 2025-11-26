<!-- TABLE DATAGRID -->

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">

    <thead>

        <tr>

            <th rowspan="2" field="ck" checkbox="true"></th>

            <th rowspan="2" data-options="field:'employee_number',width:150,halign:'center'">Employee ID</th>

            <th rowspan="2" data-options="field:'employee_name',width:200,halign:'center'">Employee Name</th>

            <th rowspan="2" data-options="field:'division_name',width:150,halign:'center'">Division</th>

            <th rowspan="2" data-options="field:'departement_name',width:150,halign:'center'">Departement</th>

            <th rowspan="2" data-options="field:'departement_sub_name',width:150,halign:'center'">Departement Name</th>

            <th rowspan="2" data-options="field:'warning_letter',width:100,align:'center', styler:cellStyler, formatter:cellFormatter">Warning Letter</th>

            <th rowspan="2" data-options="field:'issue_date',width:120,align:'center'">Trans Date</th>

            <th rowspan="2" data-options="field:'issue_date',width:100,align:'center'">Issue Date</th>

            <th rowspan="2" data-options="field:'violation_name',width:150,halign:'center'">Violation</th>

            <th rowspan="2" data-options="field:'remarks',width:200,halign:'center'">Remarks</th>

            <th rowspan="2" data-options="field:'attachment',width:155,halign:'center',align:'center',formatter:formatBtnAttachment">Attachment</th>

            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approval</th>

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

                        <input style="width:60%;" id="filter_division" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Departement</span>

                        <input style="width:60%;" id="filter_departement" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Departement Sub</span>

                        <input style="width:60%;" id="filter_departement_sub" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;"></span>

                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>

                    </div>

                </div>

                <div style="width: 50%; float: left;">

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Employee</span>

                        <input style="width:60%;" id="filter_employee" class="easyui-combogrid">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Waning Letter</span>

                        <select style="width:60%;" id="filter_warning_letter" class="easyui-combobox" panelHeight="auto">

                            <option value="">Choose All</option>

                            <option value="1">1</option>

                            <option value="2">2</option>

                            <option value="3">3</option>

                            <option value="4">TERMINATION</option>

                        </select>

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Violation</span>

                        <input style="width:60%;" id="filter_violation" class="easyui-combobox">

                    </div>

                </div>

            </fieldset>

        </div>

    </div>

    <?= $button ?>

</div>



<!-- DIALOG SAVE AND UPDATE -->

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">

    <form id="frm_insert" method="post" novalidate>

        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

            <legend><b>Form Data</b></legend>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Trans Date</span>

                <input style="width:60%;" name="trans_date" id="trans_date" required="" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Employee Name</span>

                <input style="width:60%;" name="employee_id" id="employee_id" class="easyui-combogrid" required="">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Waning Letter</span>

                <input style="width:60%;" name="warning_letter" id="warning_letter" required="" panelHeight="auto" class="easyui-combobox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Issue Date</span>

                <input style="width:60%;" name="issue_date" id="issue_date" required="" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Violation</span>

                <input style="width:60%;" name="violation_id" id="violation_id" required="" class="easyui-combobox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Remarks</span>

                <input style="width:60%; height: 50px;" name="remarks" class="easyui-textbox" multiline="true">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Attachment</span>

                <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">

            </div>

        </fieldset>

    </form>

</div>



<!-- PDF -->

<iframe id="printout" src="<?= base_url('relation/warning_letters/print') ?>" style="width: 100%;" hidden></iframe>



<script>

    //ADD DATA

    function add() {

        $('#dlg_insert').dialog('open');

        url_save = '<?= base_url('relation/warning_letters/create') ?>';

        $('#frm_insert').form('clear');

        $('#trans_date').datebox('setValue', "<?= date("Y-m-d") ?>");

        $('#employee_id').combogrid('enable');

        $('#warning_letter').combobox('enable');

    }



    //EDIT DATA

    function update() {

        var row = $('#dg').datagrid('getSelected');

        if (row) {

            $('#dlg_insert').dialog('open');

            $('#frm_insert').form('load', row);

            $('#frm_insert').form('load', row);

            url_save = '<?= base_url('relation/warning_letters/update') ?>?id=' + btoa(row.id);



            $('#employee_id').combogrid('disable');

            $('#warning_letter').combobox('disable');

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

                            url: '<?= base_url('relation/warning_letters/delete') ?>',

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

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_division = $("#filter_division").combobox('getValue');

        var filter_departement = $("#filter_departement").combobox('getValue');

        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');

        var filter_employee = $("#filter_employee").combogrid('getValue');

        var filter_warning_letter = $("#filter_warning_letter").combobox('getValue');

        var filter_violation = $("#filter_violation").combobox('getValue');



        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_division=" + filter_division + "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub + "&filter_employee=" + filter_employee +

            "&filter_warning_letter=" + filter_warning_letter + "&filter_violation=" + filter_violation;



        $('#dg').datagrid({

            url: '<?= base_url('relation/warning_letters/datatables') ?>' + url

        });



        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

        $("#printout").attr('src', '<?= base_url('relation/warning_letters/print') ?>' + url);

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

        var filter_employee = $("#filter_employee").combogrid('getValue');

        var filter_warning_letter = $("#filter_warning_letter").combobox('getValue');

        var filter_violation = $("#filter_violation").combobox('getValue');



        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_division=" + filter_division + "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub + "&filter_employee=" + filter_employee +

            "&filter_warning_letter=" + filter_warning_letter + "&filter_violation=" + filter_violation;



        window.location.assign('<?= base_url('relation/warning_letters/print/excel') ?>' + url);

    }

    //RELOAD

    function reload() {

        window.location.reload();

    }



    $(function() {

        //SETTING DATAGRID EASYUI

        $('#dg').datagrid({

            url: '<?= base_url('relation/warning_letters/datatables') ?>',

            pagination: true,

            rownumbers: true,

            fit: true,

            pageList: [20, 50, 100, 500, 1000],

            pageSize: 20,

            rowStyler: function(index, row) {
                if (row.employee_status == 1) {
                    return 'background-color:#FFDCDC;';
                }

                if (row.is_six_months_or_more == 1) {
                    return 'background-color:#FFA500;';
                }

                return '';
            }

        });



        //SAVE DATA

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var msg = '';
                    var input = $('#attachment').next().find('input[type=file]')[0];

                    if (input.files && input.files.length > 0) {
                        var file = input.files[0];
                        if (!['pdf', 'xls', 'xlsx'].includes(file.name.split('.').pop().toLowerCase()))
                            { msg = 'No file types allowed! Only PDF, XLS, and XLSX.'; }
                        else if (file.size > (2 * 1024 * 1024))
                            { msg = 'The file size is too large! Maximum 2MB.'; }

                        if (msg == '') {
                            $.ajax({
                                url: '<?= base_url('relation/warning_letters/upload'); ?>',
                                data: new FormData($('#frm_insert')[0]),
                                type: 'POST',
                                dataType: 'json',
                                processData: false,
                                contentType: false,
                                beforeSend: function () { $.messager.progress({title:'Process Upload', msg:'Please wait...'}); },
                                success: function (result) { $.messager.progress('close'); saveInput(result); },
                                error: function() { $.messager.progress('close'); toastr.error('Failed upload!', 'Error'); }
                            });
                        } else { toastr.warning(msg, 'Warning'); }
                    } else { saveInput(); }
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

            onSelect: function(val, row) {

                $('#warning_letter').combobox({

                    url: '<?= base_url('relation/warning_letters/readWarningLetter/') ?>' + row.id,

                    prompt: 'Choose Warning Letter',

                    valueField: 'id',

                    textField: 'name',

                });

            }

        });



        $('#violation_id').combobox({

            url: '<?php echo base_url('relation/violations/reads'); ?>',

            valueField: 'id',

            textField: 'name',

            prompt: 'Choose Violation',

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

                        });

                    }

                });

            }

        });



        $('#filter_violation').combobox({

            url: '<?php echo base_url('relation/violations/reads'); ?>',

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

    });

    function saveInput(upload = '') {
        $('#frm_insert').form('submit', {
            url: url_save,
            onSubmit: function(param) {
                if (upload != '') { param['attachment'] = upload; }
                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');

                if (result.theme == "success") { toastr.success(result.message, result.title); }
                else { toastr.error(result.message, result.title); }

                $('#dlg_insert').dialog('close');
                $('#dg').datagrid('reload');
            }
        });
    }

    //CELLSTYLE STATUS

    function cellStyler(value, row, index) {

        if (value == "1" || value == "2" || value == "3") {

            return 'background: #53D636; color:white;';

        } else {

            return 'background: #FF5F5F; color:white;';

        }

    }



    //FORMATTER STATUS

    function cellFormatter(value) {

        if (value == "4") {

            return 'TERMINATION';

        } else {

            return value;

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


    //Formatter Attachment
    function formatBtnAttachment(value, row, index) { return (value == '' || value == null) ? '' : '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="btnAttachment('+"'"+window.btoa(value)+"'"+')"><i class="fa fa-download"></i> Download</a>'; }
    function btnAttachment(file) {
        var name = window.atob(file);
        var ext = '.'+name.split('.')[1];
        var date = new Date();
        var y = date.getFullYear();
        var m = (date.getMonth()+1).toString().padStart(2, '0');
        var d = (date.getDate()).toString().padStart(2, '0');
        var a = document.createElement('a');

        a.href = '<?= base_url($location) ?>'+name;
        a.download = 'warning_letter_'+y+m+d+ext;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
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

</script>