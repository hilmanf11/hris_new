<!-- TABLE DATAGRID -->

<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">

    <thead>

        <tr>

            <th rowspan="2" field="ck" checkbox="true"></th>

            <th rowspan="2" data-options="field:'table_name',width:200,halign:'center'">Module</th>

            <th rowspan="2" data-options="field:'division_name',width:150,halign:'center'">Division</th>

            <th rowspan="2" data-options="field:'departement_name',width:150,halign:'center'">Departement</th>

            <th rowspan="2" data-options="field:'departement_sub_name',width:150,halign:'center'">Departement Sub</th>

            <th rowspan="2" data-options="field:'user_approval_name_1',width:200,halign:'center'">Approval 1</th>

            <th rowspan="2" data-options="field:'user_approval_name_2',width:200,halign:'center'">Approval 2</th>

            <th rowspan="2" data-options="field:'user_approval_name_3',width:200,halign:'center'">Approval 3</th>

            <th rowspan="2" data-options="field:'user_approval_name_4',width:200,halign:'center'">Approval 4</th>

            <th rowspan="2" data-options="field:'user_approval_name_5',width:200,halign:'center'">Approval 5</th>

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

<div id="toolbar" style="height: 35px;">

    <?= $button ?>

</div>



<!-- DIALOG SAVE AND UPDATE -->

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true" style="width: 400px; padding:10px; top: 20px;">

    <form id="frm_insert" method="post" novalidate>

        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

            <legend><b>Form Data</b></legend>

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

                <span style="width:35%; display:inline-block;">Module</span>

                <select style="width:60%;" name="table_name" id="table_name" required="true" data-options="prompt:'Select Module'" class="easyui-combobox">

                    <option value="users">Users</option>

                    <option value="employees">Employees</option>

                    <option value="warning_letters">Warning Letters</option>

                    <option value="resignations">Resignations</option>

                    <option value="agreements">Agreements</option>

                    <option value="mutations">Mutations</option>

                    <!-- <option value="employee_requests">Employee Requests</option> -->

                    <!-- <option value="overtimes">Overtimes</option> -->

                    <option value="cash_carries">Cash Carries</option>

                    <option value="cash_carries_harian_lepas">Cash Carries Harian Lepas</option>

                    <option value="payroll_harian_lepas">Payroll Harian Lepas</option>

                    <option value="permits">Permits</option>

                    <option value="change_days">Change Days</option>

                    <option value="setup_salaries">Setup Salary</option>

                    <option value="payrolls">Payrolls</option>

                    <option value="lnd_request_training_approvals_history">Request Training</option>

                    <option value="request">Request</option>
                    
                    <option value="setup_allowances">Setup Allowances</option>
                </select>

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Approval 1</span>

                <input style="width:60%;" name="user_approval_1" id="user_approval_1" required="" class="easyui-combobox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Approval 2</span>

                <input style="width:60%;" name="user_approval_2" id="user_approval_2" class="easyui-combobox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Approval 3</span>

                <input style="width:60%;" name="user_approval_3" id="user_approval_3" class="easyui-combobox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Approval 4</span>

                <input style="width:60%;" name="user_approval_4" id="user_approval_4" class="easyui-combobox">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Approval 5</span>

                <input style="width:60%;" name="user_approval_5" id="user_approval_5" class="easyui-combobox">

            </div>

        </fieldset>

    </form>

</div>



<!-- PDF -->

<iframe id="printout" src="<?= base_url('admin/approvals/print') ?>" style="width: 100%;" hidden></iframe>



<script>

    //ADD DATA

    function add() {

        $('#dlg_insert').dialog('open');

        url_save = '<?= base_url('admin/approvals/create') ?>';

        $('#frm_insert').form('clear');

    }



    //EDIT DATA

    function update() {

        var row = $('#dg').datagrid('getSelected');

        if (row) {

            $('#dlg_insert').dialog('open');

            $('#frm_insert').form('load', row);

            url_save = '<?= base_url('admin/approvals/update') ?>?id=' + btoa(row.id);

        } else {

            toastr.info("Please select one of the data in the table first");

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

                            url: '<?= base_url('admin/approvals/delete') ?>',

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

            toastr.info("Please select one of the data in the table first");

        }

    }



    //PRINT PDF

    function pdf() {

        $("#printout").get(0).contentWindow.print();

    }

    //EXPORT EXCEL

    function excel() {

        window.location.assign('<?= base_url('admin/approvals/print/excel') ?>');

    }

    //RELOAD

    function reload() {

        window.location.reload();

    }



    $(function() {

        //SETTING DATAGRID EASYUI

        $('#dg').datagrid({

            url: '<?= base_url('admin/approvals/datatables') ?>',

            pagination: true,

            clientPaging: false,

            remoteFilter: true,

            rownumbers: true,

            fit: true,

            pageList: [20, 50, 100, 500, 1000],

            pageSize: 20,

        }).datagrid('enableFilter');



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



                            //$('#dlg_insert').dialog('close');

                            $('#dg').datagrid('reload');

                        }

                    });

                }

            }]

        });



        //DATA USERS

        $('#user_approval_1').combogrid({

            url: '<?= base_url('admin/setting_users/getusers') ?>',

            panelWidth: 420,

            idField: 'username',

            textField: 'name',

            mode: 'remote',

            fitColumns: true,

            prompt: "Select User",

            columns: [

                [{

                    field: 'username',

                    title: 'Username',

                    width: 150

                }, {

                    field: 'name',

                    title: 'Fullname',

                    width: 250

                }, ]

            ]

        });



        //DATA USERS

        $('#user_approval_2').combogrid({

            url: '<?= base_url('admin/setting_users/getusers') ?>',

            panelWidth: 420,

            idField: 'username',

            textField: 'name',

            mode: 'remote',

            fitColumns: true,

            prompt: "Select User",

            columns: [

                [{

                    field: 'username',

                    title: 'Username',

                    width: 150

                }, {

                    field: 'name',

                    title: 'Fullname',

                    width: 250

                }, ]

            ]

        });



        //DATA USERS

        $('#user_approval_3').combogrid({

            url: '<?= base_url('admin/setting_users/getusers') ?>',

            panelWidth: 420,

            idField: 'username',

            textField: 'name',

            mode: 'remote',

            fitColumns: true,

            prompt: "Select User",

            columns: [

                [{

                    field: 'username',

                    title: 'Username',

                    width: 150

                }, {

                    field: 'name',

                    title: 'Fullname',

                    width: 250

                }, ]

            ]

        });



        //DATA USERS

        $('#user_approval_4').combogrid({

            url: '<?= base_url('admin/setting_users/getusers') ?>',

            panelWidth: 420,

            idField: 'username',

            textField: 'name',

            mode: 'remote',

            fitColumns: true,

            prompt: "Select User",

            columns: [

                [{

                    field: 'username',

                    title: 'Username',

                    width: 150

                }, {

                    field: 'name',

                    title: 'Fullname',

                    width: 250

                }, ]

            ]

        });



        //DATA USERS

        $('#user_approval_4').combogrid({

            url: '<?= base_url('admin/setting_users/getusers') ?>',

            panelWidth: 420,

            idField: 'username',

            textField: 'name',

            mode: 'remote',

            fitColumns: true,

            prompt: "Select User",

            columns: [

                [{

                    field: 'username',

                    title: 'Username',

                    width: 150

                }, {

                    field: 'name',

                    title: 'Fullname',

                    width: 250

                }, ]

            ]

        });



        //DATA USERS

        $('#user_approval_5').combogrid({

            url: '<?= base_url('admin/setting_users/getusers') ?>',

            panelWidth: 420,

            idField: 'username',

            textField: 'name',

            mode: 'remote',

            fitColumns: true,

            prompt: "Select User",

            columns: [

                [{

                    field: 'username',

                    title: 'Username',

                    width: 150

                }, {

                    field: 'name',

                    title: 'Fullname',

                    width: 250

                }, ]

            ]

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
                            prompt: 'Choose Departement Sub'
                        });
                    }
                });
            }
        });
    });

</script>