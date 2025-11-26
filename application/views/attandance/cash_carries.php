<!-- TABLE DATAGRID -->

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">

    <thead frozen="true">

        <tr>

            <th field="ck" checkbox="true"></th>

            <th data-options="field:'employee_number',width:120,halign:'center'">Employee ID</th>

            <th data-options="field:'employee_name',width:200,halign:'center'">Employee Name</th>

        </tr>

    </thead>

    <thead>

        <tr>

            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">Type</th>

            <th colspan="8" data-options="field:'',width:80,halign:'center'">Request</th>

            <th colspan="3" data-options="field:'',width:80,halign:'center'">Attandance</th>

            <th rowspan="2" data-options="field:'meal',width:80,halign:'center',align:'center',styler:cellStyler, formatter:cellFormatter">Meal</th>

            <th rowspan="2" data-options="field:'amount',width:80,halign:'center',align:'right', formatter:numberformat">Plan<br>Amount</th>

            <th rowspan="2" data-options="field:'amount_actual',width:80,halign:'center',align:'right', formatter:numberformat">Actual<br>Amount</th>

            <th rowspan="2" data-options="field:'plan',width:80,halign:'center',align:'right'">Plan<br>Output</th>

            <th rowspan="2" data-options="field:'actual',width:80,halign:'center',align:'right'">Actual<br>Output</th>

            <th rowspan="2" data-options="field:'remarks',width:400,halign:'center'">Remarks</th>

            <th rowspan="2" data-options="field:'attachment',width:100,align:'center', formatter:fileFormatter">Attachment</th>

            <th rowspan="2" data-options="field:'attachment_idm',width:100,align:'center', formatter:fileFormatter2">Attachment<br>IDM</th>

            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approval</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>

        </tr>

        <tr>

            <th data-options="field:'trans_date',width:100,halign:'center'">Request Date</th>

            <th data-options="field:'request_code',width:150,halign:'center'">Request No</th>

            <th data-options="field:'idm_no',width:200,halign:'center'">IDM No</th>

            <th data-options="field:'fullname',width:200,halign:'center'">Request Name</th>

            <th data-options="field:'start',width:80,align:'center'">Start</th>

            <th data-options="field:'end',width:80,align:'center'">End</th>

            <th data-options="field:'break',width:80,align:'center'">Break<br>(Minute)</th>

            <th data-options="field:'duration_hour',width:80,align:'center'">Duration<br>(Hour)</th>

            <th data-options="field:'time_in',width:80,align:'center'">Time In</th>

            <th data-options="field:'time_out',width:80,align:'center'">Time Out</th>

            <th data-options="field:'duration_att',width:80,align:'center'">Duration</th>

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

            <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">

                <legend><b>Form Filter Data</b></legend>

                <div style="width: 50%; float: left;">

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Request Date</span>

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

                        <span style="width:30%; display:inline-block;">Request Code</span>

                        <input style="width:60%;" id="filter_request_code" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">IDM No</span>

                        <input style="width:60%;" id="filter_idm" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Approval</span>

                        <select style="width:60%;" id="filter_approval" class="easyui-combobox" data-options="panelHeight:'auto'">

                            <option value="">Choose All</option>

                            <option value="0">Approved</option>

                            <option value="1">Checked</option>

                        </select>

                    </div>

                </div>

            </fieldset>

        </div>

    </div>

    <?= $button ?>

</div>



<div id="toolbar2">

    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" id="addrow" onclick="append()"><i class="fa fa-plus"></i> Add</a>

</div>



<!-- DIALOG SAVE -->

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 500px; padding:10px; top: 20px;">

    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>

        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

            <legend><b>Form Data</b></legend>

            <div style="width: 50%; float: left;">

                <div class="fitem">

                    <span style="width:30%; display:inline-block;">Request Date</span>

                    <input style="width:60%;" name="trans_date" id="trans_date" required class="easyui-datebox" data-options="formatter:myformatter,parser:myparser,editable:false" value="<?= date("Y-m-d") ?>">

                </div>

                <div class="fitem" id="u_division">

                    <span style="width:30%; display:inline-block;">Division</span>

                    <input style="width:60%;" id="division_id" class="easyui-combobox">

                </div>

                <div class="fitem" id="u_departement">

                    <span style="width:30%; display:inline-block;">Departement</span>

                    <input style="width:60%;" id="departement_id" class="easyui-combobox">

                </div>

                <div class="fitem">

                    <span style="width:30%; display:inline-block;">Request Code</span>

                    <input style="width:60%;" name="request_code" readonly data-options="prompt:'Automatic'" id="request_code" class="easyui-textbox" required>

                </div>

                <div class="fitem">

                    <span style="width:30%; display:inline-block;">Request Name</span>

                    <input style="width:60%;" name="request_name" readonly data-options="prompt:'Automatic'" id="request_name" class="easyui-textbox" required>

                </div>

            </div>

            <div style="width: 50%; float: left;">

                <div class="fitem">

                    <span style="width:30%; display:inline-block;">IDM No</span>

                    <input style="width:60%;" name="idm_no" id="idm_no" class="easyui-textbox" required>

                </div>

                <div class="fitem" id="u_idm">

                    <span style="width:30%; display:inline-block;">Attachment IDM</span>

                    <input style="width:60%;" name="attachment_idm" id="attachment_idm" accept=".jpg, .png, .jpeg, .pdf" class="easyui-filebox" data-options="prompt: 'Max 2 Mb'">

                </div>

                <div class="fitem" id="u_attachment">

                    <span style="width:30%; display:inline-block;">Attachment</span>

                    <input style="width:60%;" name="attachment" id="attachment" accept=".jpg, .png, .jpeg, .pdf" class="easyui-filebox" data-options="prompt: 'Max 2 Mb'">

                </div>

                <div class="fitem">

                    <span style="width:30%; display:inline-block;">Type</span>

                    <select style="width:60%;" id="type" panelHeight="auto" data-options="prompt:'Choose Type Cash Carry'" required name="type" class="easyui-combobox">

                        <option value="REGULAR">REGULAR</option>

                        <option value="CORRECTION">CORRECTION</option>

                    </select>

                </div>

            </div>

        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Employee Lists" toolbar="#toolbar2"></table>

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

            <div class="fitem">

                <span style="width:30%; display:inline-block;">IDM No</span>

                <input style="width:60%;" name="idm_no" id="idm_no" class="easyui-textbox" required>

            </div>

            <div class="fitem">

                <span style="width:30%; display:inline-block;">Attachment IDM</span>

                <input style="width:60%;" name="attachment_idm" id="attachment_idm" accept=".jpg, .png, .jpeg, .pdf" class="easyui-filebox" data-options="prompt: 'Max 2 Mb'">

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

        $('#dg2').datagrid('loadData', []);

        url_save = '<?= base_url('attandance/cash_carries/create') ?>';

        $('#trans_date').datebox('setValue', '<?= date("Y-m-d") ?>');

        $('#request_name').textbox('setValue', '<?= $this->session->name ?>');

        $("#u_division").show();

        $("#u_departement").show();

        $("#u_idm").show();

        $("#u_attachment").show();

        $("#addrow").show();

    }



    //EDIT DATA

    function update() {

        var row = $('#dg').datagrid('getSelected');

        if (row) {

            $('#dlg_insert').dialog('open');

            $('#frm_insert').form('load', row);

            url_save = '<?= base_url('attandance/cash_carries/update') ?>';

            $("#u_division").hide();

            $("#u_departement").hide();

            $("#u_idm").hide();

            $("#u_attachment").hide();

            $("#addrow").hide();



            var lastIndex;

            $('#dg2').datagrid({

                singleSelect: true,

                url: '<?= base_url('attandance/cash_carries/datatable_updates?request_code=') ?>' + window.btoa(row.request_code),

                columns: [

                    [{

                        field: 'employee_name',

                        width: 200,

                        readonly: true,

                        halign: 'center',

                        title: "Employee Name"

                    }, {

                        field: 'employee_id',

                        hidden: true,

                        width: 100,

                        halign: 'center',

                        title: "ID",

                    }, {

                        field: 'start',

                        width: 100,

                        halign: 'center',

                        title: "Start",

                        editor: {

                            type: 'textbox',

                            options: {

                                required: true

                            }

                        }

                    }, {

                        field: 'end',

                        width: 100,

                        halign: 'center',

                        title: "End",

                        editor: {

                            type: 'textbox',

                            options: {

                                required: true

                            }

                        }

                    }, {

                        field: 'break',

                        width: 80,

                        halign: 'center',

                        title: "Break<br>(Minutes)",

                        editor: {

                            type: 'numberbox',

                            options: {

                                required: true

                            }

                        }

                    }, {

                        field: 'meal',

                        width: 80,

                        align: 'center',

                        title: "Meal",

                        editor: {

                            type: 'checkbox',

                            options: {

                                on: '1',

                                off: '0'

                            }

                        }

                    }, {

                        field: 'plan',

                        width: 80,

                        halign: 'center',

                        title: "Plan<br>Output",

                        editor: {

                            type: 'numberbox',

                            options: {

                                required: true,

                            }

                        }

                    }, {

                        field: 'actual',

                        width: 80,

                        halign: 'center',

                        title: "Actual<br>Output",

                        editor: {

                            type: 'numberbox',

                            options: {

                                required: true,

                            }

                        }

                    }, {

                        field: 'remarks',

                        width: 250,

                        halign: 'center',

                        title: "Remarks",

                        editor: {

                            type: 'textbox'

                        }

                    }, {

                        field: 'action',

                        title: 'Action',

                        width: 120,

                        align: 'center',

                        formatter: function(value, row, index) {

                            if (row.editing) {

                                var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';

                                var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';

                                return s + c;

                            } else {

                                var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';

                                var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';

                                return e + d;

                            }

                        }

                    }]

                ],

                onEndEdit: function(index, row) {

                    var ed = $(this).datagrid('getEditor', {

                        index: index,

                        field: 'employee_id'

                    });

                },

                onBeforeEdit: function(index, row) {

                    row.editing = true;

                    $(this).datagrid('refreshRow', index);

                },

                onAfterEdit: function(index, row) {

                    row.editing = false;

                    $(this).datagrid('refreshRow', index);

                },

                onCancelEdit: function(index, row) {

                    row.editing = false;

                    $(this).datagrid('refreshRow', index);

                },

            });

        } else {

            toastr.warning("Please select one of the data in the table first!", "Information");

        }

    }



    function addTable(departement_id) {

        var lastIndex;

        var dg = $('#dg2').datagrid({

            singleSelect: true,

            columns: [

                [{

                    field: 'employee_name',

                    width: 250,

                    halign: 'center',

                    title: "Employee Name",

                    editor: {

                        type: 'combogrid',

                        options: {

                            url: '<?= base_url('attandance/cash_carries/readEmployees?departement_id=') ?>' + departement_id,

                            required: true,

                            panelWidth: 350,

                            idField: 'name',

                            textField: 'name',

                            mode: 'remote',

                            fitColumns: true,

                            prompt: 'Choose Employee',

                            columns: [

                                [{

                                    field: 'number',

                                    title: 'Employee ID',

                                    width: 100

                                }, {

                                    field: 'name',

                                    title: 'Employee Name',

                                    width: 200

                                }]

                            ],

                            onSelect: function(value, rows) {

                                var dg = $('#dg2');

                                var row = dg.datagrid('getSelected');

                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {

                                    index: rowIndex,

                                    field: 'employee_id'

                                });

                                $(ed.target).textbox('setValue', rows.id);

                            }

                        }

                    }

                }, {

                    field: 'employee_id',

                    hidden: true,

                    width: 100,

                    halign: 'center',

                    title: "ID",

                    editor: {

                        type: 'textbox'

                    }

                }, {

                    field: 'start',

                    width: 100,

                    halign: 'center',

                    title: "Start",

                    editor: {

                        type: 'textbox',

                        options: {

                            required: true

                        }

                    }

                }, {

                    field: 'end',

                    width: 100,

                    halign: 'center',

                    title: "End",

                    editor: {

                        type: 'textbox',

                        options: {

                            required: true

                        }

                    }

                }, {

                    field: 'break',

                    width: 80,

                    halign: 'center',

                    title: "Break<br>(Minutes)",

                    editor: {

                        type: 'numberbox',

                        options: {

                            required: true

                        }

                    }

                }, {

                    field: 'meal',

                    width: 80,

                    align: 'center',

                    title: "Meal",

                    editor: {

                        type: 'checkbox',

                        options: {

                            on: '1',

                            off: '0'

                        }

                    }

                }, {

                    field: 'plan',

                    width: 80,

                    halign: 'center',

                    title: "Plan<br>Output",

                    editor: {

                        type: 'numberbox',

                        options: {

                            required: true,

                        }

                    }

                }, {

                    field: 'actual',

                    width: 80,

                    halign: 'center',

                    title: "Actual<br>Output",

                    editor: {

                        type: 'numberbox',

                        options: {

                            required: true,

                        }

                    }

                }, {

                    field: 'remarks',

                    width: 200,

                    halign: 'center',

                    title: "Remarks",

                    editor: {

                        type: 'textbox'

                    }

                }, {

                    field: 'action',

                    title: 'Action',

                    width: 120,

                    align: 'center',

                    formatter: function(value, row, index) {

                        if (row.editing) {

                            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';

                            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';

                            return s + c;

                        } else {

                            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';

                            var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';

                            return e + d;

                        }

                    }

                }]

            ],

            onEndEdit: function(index, row) {

                var ed = $(this).datagrid('getEditor', {

                    index: index,

                    field: 'employee_id'

                });

            },

            onBeforeEdit: function(index, row) {

                row.editing = true;

                $(this).datagrid('refreshRow', index);

            },

            onAfterEdit: function(index, row) {

                row.editing = false;

                $(this).datagrid('refreshRow', index);

            },

            onCancelEdit: function(index, row) {

                row.editing = false;

                $(this).datagrid('refreshRow', index);

            },

        });

    }



    var editIndex = undefined;



    function endEditing() {

        if (editIndex == undefined) {

            return true

        }

        if ($('#dg2').datagrid('validateRow', editIndex)) {

            $('#dg2').datagrid('endEdit', editIndex);

            editIndex = undefined;

            return true;

        } else {

            return false;

        }

    }



    function append() {

        var departement_id = $("#departement_id").combobox('getValue');

        if (departement_id != "") {

            if (endEditing()) {

                $('#dg2').datagrid('appendRow', {

                    start: '00:00',

                    end: '00:00',

                    plan: '0',

                    break: '0',

                });

                editIndex = $('#dg2').datagrid('getRows').length - 1;

                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

            }

        } else {

            toastr.error("Please Choose Departement first");

        }

    }



    function getRowIndex(target) {

        var tr = $(target).closest('tr.datagrid-row');

        return parseInt(tr.attr('datagrid-row-index'));

    }



    function editrow(target) {

        $('#dg2').datagrid('beginEdit', getRowIndex(target));

    }



    function deleterow(target) {

        $.messager.confirm('Confirm', 'Are you sure?', function(r) {

            if (r) {

                $('#dg2').datagrid('deleteRow', getRowIndex(target));

            }

        });

    }



    function saverow(target) {

        $('#dg2').datagrid('endEdit', getRowIndex(target));

    }



    function cancelrow(target) {

        $('#dg2').datagrid('cancelEdit', getRowIndex(target));

    }



    //EDIT DATA

    // function update() {

    //     var row = $('#dg').datagrid('getSelected');

    //     if (row) {

    //         $('#dlg_update').dialog('open');

    //         $('#frm_update').form('load', row);

    //         url_update = '<?= base_url('attandance/cash_carries/update') ?>?id=' + window.btoa(row.id);

    //     } else {

    //         toastr.warning("Please select one of the data in the table first!", "Information");

    //     }

    // }



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

                            url: '<?= base_url('attandance/cash_carries/delete') ?>',

                            data: {

                                id: row.id,

                                attachment: row.attachment,

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

        window.location.assign('<?= base_url('template/tmp_cash_carries.xls') ?>');

    }



    function filter() {

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_division = $("#filter_division").combobox('getValue');

        var filter_departement = $("#filter_departement").combobox('getValue');

        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');

        var filter_employee = $("#filter_employee").combobox('getValue');

        var filter_request_code = window.btoa($("#filter_request_code").combobox('getValue'));

        var filter_idm = window.btoa($("#filter_idm").combobox('getValue'));

        var filter_approval = $("#filter_approval").combobox('getValue');



        var url = "?filter_from=" + filter_from +

            "&filter_to=" + filter_to +

            "&filter_division=" + filter_division +

            "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub +

            "&filter_employee=" + filter_employee +

            "&filter_request_code=" + filter_request_code +

            "&filter_idm=" + filter_idm +

            "&filter_approval=" + filter_approval;



        $('#dg').datagrid({

            url: '<?= base_url('attandance/cash_carries/datatables') ?>' + url

        });



        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

        $("#printout").attr('src', '<?= base_url('attandance/cash_carries/print') ?>' + url);

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

        var filter_request_code = window.btoa($("#filter_request_code").combobox('getValue'));

        var filter_idm = window.btoa($("#filter_idm").combobox('getValue'));

        var filter_approval = $("#filter_approval").combobox('getValue');



        var url = "?filter_from=" + filter_from +

            "&filter_to=" + filter_to +

            "&filter_division=" + filter_division +

            "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub +

            "&filter_employee=" + filter_employee +

            "&filter_request_code=" + filter_request_code +

            "&filter_idm=" + filter_idm +

            "&filter_approval=" + filter_approval;



        window.location.assign('<?= base_url('attandance/cash_carries/print/excel') ?>' + url);

    }

    //RELOAD

    function reload() {

        window.location.reload();

    }



    $(function() {

        $('#dg').datagrid({

            url: '<?= base_url('attandance/cash_carries/datatables') ?>',

            pagination: true,

            rownumbers: true,

            fit: true,

            pageList: [20, 50, 100, 500, 1000],

            pageSize: 20,

        });



        //SAVE DATA

        $('#dlg_insert').dialog({

            buttons: [{

                text: 'Save',

                iconCls: 'icon-ok',

                handler: function() {

                    var trans_date = $("#trans_date").datebox('getValue');

                    var request_code = $("#request_code").textbox('getValue');

                    var request_name = $("#request_name").textbox('getValue');

                    var idm_no = $("#idm_no").textbox('getValue');

                    var type = $("#type").combobox('getValue');

                    var file_attachment = "";

                    var file_attachment_idm = "";



                    var rows = $('#dg2').datagrid('getRows');

                    var totalrows = rows.length;

                    endEditing();



                    if (idm_no != "" && request_code != "" && totalrows > 0) {

                        Swal.fire({

                            title: 'Please Wait for Save Data',

                            showConfirmButton: false,

                            allowOutsideClick: false,

                            allowEscapeKey: false,

                            didOpen: () => {

                                Swal.showLoading();

                            },

                        });



                        $('#frm_insert').form('submit', {

                            url: '<?= base_url('attandance/cash_carries/uploadFile') ?>',

                            method: 'POST',

                            onSubmit: function() {

                                return $(this).form('validate');

                            },

                            success: function(data) {

                                var data = eval('(' + data + ')');

                                var file_attachment = data.attachment;

                                var file_attachment_idm = data.attachment_idm;



                                for (let i = 0; i < totalrows; i++) {

                                    if (rows[i].employee_id) {

                                        $.ajax({

                                            type: "post",

                                            url: url_save,

                                            data: {

                                                trans_date: trans_date,

                                                request_code: request_code,

                                                request_name: request_name,

                                                idm_no: idm_no,

                                                type: type,

                                                attachment: file_attachment,

                                                attachment_idm: file_attachment_idm,

                                                employee_id: rows[i].employee_id,

                                                start: rows[i].start,

                                                end: rows[i].end,

                                                meal: rows[i].meal,

                                                plan: rows[i].plan,

                                                actual: rows[i].actual,

                                                break: rows[i].break,

                                                remarks: rows[i].remarks

                                            },

                                            dataType: "json",

                                            success: function(result) {

                                                if (totalrows == (i + 1)) {

                                                    Swal.close();

                                                    Swal.fire({

                                                        title: "Save Cash Carries Success",

                                                        icon: "success",

                                                        confirmButtonText: 'Ok',

                                                        allowOutsideClick: false,

                                                    }).then((result) => {

                                                        if (result.isConfirmed) {

                                                            location.reload();

                                                        }

                                                    });

                                                }

                                            }

                                        });

                                    }

                                }

                            }

                        });



                        // if ($("#attachment").filebox('getValue') == "") {

                        //     var file_attachment = "";

                        // } else {

                        //     var attachment = $("#attachment").filebox('files')[0];

                        //     var explode = attachment['name'].split('.');

                        //     var file_attachment = request_code + "." + explode[1];

                        // }



                        // if ($("#attachment_idm").filebox('getValue') == "") {

                        //     var file_attachment_idm = "";

                        // } else {

                        //     var attachment_idm = $("#attachment_idm").filebox('files')[0];

                        //     var explode_idm = attachment_idm['name'].split('.');

                        //     var file_attachment_idm = idm_no + "." + explode_idm[1];

                        // }



                        $('#dlg_insert').dialog('close');

                    } else {

                        toastr.info("Please complete your cash carry data");

                    }

                }

            }]

        });



        //UPLOAD DATA

        $('#dlg_upload').dialog({

            buttons: [{

                text: 'List Failed',

                handler: function() {

                    window.open('<?= base_url('attandance/cash_carries/uploadDownloadFailed') ?>', '_blank');

                }

            }, {

                text: 'Upload',

                iconCls: 'icon-ok',

                handler: function() {

                    $('#frm_upload').form('submit', {

                        url: '<?= base_url('attandance/cash_carries/upload') ?>',

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

                                url: "<?= base_url('attandance/cash_carries/uploadclearFailed') ?>"

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

                                        url: "<?= base_url('attandance/cash_carries/uploadCreate') ?>",

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

                                                    url: "<?= base_url('attandance/cash_carries/uploadcreateFailed') ?>",

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

                                    url: '<?= base_url('attandance/cash_carries/readEmployees?departement_sub_id=') ?>' + departement_sub.id,

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

            url: '<?= base_url('attandance/cash_carries/readEmployees') ?>',

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



        $('#filter_request_code').combobox({

            url: '<?= base_url('attandance/cash_carries/readRequestCode') ?>',

            valueField: 'request_code',

            textField: 'request_code',

            prompt: "Choose All",

            icons: [{

                iconCls: 'icon-clear',

                handler: function(e) {

                    $(e.data.target).combobox('clear').combobox('textbox').focus();

                }

            }]

        });



        $('#filter_idm').combobox({

            url: '<?= base_url('attandance/cash_carries/readIdm') ?>',

            valueField: 'idm_no',

            textField: 'idm_no',

            prompt: "Choose All",

            icons: [{

                iconCls: 'icon-clear',

                handler: function(e) {

                    $(e.data.target).combobox('clear').combobox('textbox').focus();

                }

            }]

        });



        $('#division_id').combobox({

            url: '<?php echo base_url('employee/divisions/reads'); ?>',

            valueField: 'id',

            textField: 'name',

            prompt: 'Choose Division',

            onSelect: function(div) {

                $('#departement_id').combobox({

                    url: '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + div.id,

                    valueField: 'id',

                    textField: 'name',

                    prompt: 'Choose Departement',

                    onSelect: function(dept) {

                        $.ajax({

                            url: '<?= base_url('attandance/cash_carries/requestCode') ?>',

                            type: 'post',

                            data: 'departement_id=' + dept.id,

                            success: function(requestCode) {

                                addTable(dept.id);

                                $("#request_code").textbox('setValue', requestCode);

                            }

                        });

                    }

                });

            }

        });

    });



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



    function numberformat(value, row) {

        const formatter = new Intl.NumberFormat('id-ID', {

            style: 'currency',

            currency: 'IDR',

            minimumFractionDigits: 0

        });



        return "<b>" + formatter.format(value) + "</b>";

    }



    //CELLSTYLE STATUS

    function cellStyler(value, row, index) {

        if (value == 0) {

            return 'background: #FF5F5F; color:white;';

        } else {

            return 'background: #53D636; color:white;';

        }

    }



    //FORMATTER STATUS

    function cellFormatter(value) {

        if (value == 0) {

            return 'NO';

        } else {

            return 'YES';

        }

    };



    function fileFormatter(value) {

        if (value == "" || value == null) {

            return '-';

        } else {

            return '<a href="<?= base_url('assets/image/cash_carry/') ?>' + value + '" target="_blank" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;"><i class="fa fa-eye"></i> View File</a>';

        }

    };



    function fileFormatter2(value) {

        if (value == "" || value == null) {

            return '-';

        } else {

            return '<a href="<?= base_url('assets/image/cash_carry/') ?>' + value + '" target="_blank" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;"><i class="fa fa-eye"></i> View File</a>';

        }

    };

</script>