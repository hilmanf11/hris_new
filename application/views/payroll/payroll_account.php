<table id="dg" class="easyui-datagrid" style="width:100%;"></table>

<div id="toolbar"><?= $button ?></div>

<div id="dlg_form" class="easyui-dialog" style="width: 400px;">
    <fieldset style="width:98%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
        <legend><b>Form Data</b></legend>
        <table style="width: 100%;">
            <tr>
                <td style="width: 35%;">Bank</td>
                <td><input id="bank" class="easyui-combobox" required></td>
            </tr>
            <tr>
                <td>Account No</td>
                <td><input id="account" class="easyui-textbox" required></td>
            </tr>
        </table>
    </fieldset>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    var field_id = '';
    $(function() {
        $('#dg').datagrid({
            url: "<?= base_url('payroll/payroll_account/datatables') ?>",
            toolbar: '#toolbar',
            remoteFilter: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'ck', checkbox: true, rowspan: 2},
                {field: 'bank', title: 'Bank Name', width: '10%', halign: 'center', rowspan: 2},
                {field: 'account', title: 'Account No', width: '10%', halign: 'center', rowspan: 2},
                {title: 'Created', halign: 'center', colspan: 2},
                {title: 'Updated', halign: 'center', colspan: 2},
            ],[
                {field: 'c_by', title: 'By', width: '10%', halign: 'center', align: 'center'},
                {field: 'c_date', title: 'Date', width: '10%', halign: 'center', align: 'center'},
                {field: 'up_by', title: 'By', width: '10%', halign: 'center', align: 'center'},
                {field: 'up_date', title: 'Date', width: '10%', halign: 'center', align: 'center'},
            ]],
            onLoadSuccess: function (data) {
                $("#printout").attr('src', "<?= base_url('payroll/payroll_account/datatables/print') ?>");
            }
        }).datagrid('enableFilter');

        $('#dlg_form').dialog({
            modal: true,
            closed: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var bank = $('#bank').combobox('getValue');
                    var account = $('#account').textbox('getValue');

                    if ([bank, account].includes('')) { toastr.warning("Please fill in all fields!", "Information"); }
                    else {
                        var data = {
                            bank: btoa(bank),
                            account: btoa(account)
                        };
                        if (field_id != '') { data.id = field_id; }
                        getprocess((field_id == '') ? 'create' : 'update', data);
                    }
                }
            }]
        });

        $('#bank').combobox({
            data: <?= json_encode($banks) ?>,
            valueField: 'id',
            textField: 'name'
        });
    });

    function add() {
        field_id = '';
        $('#bank').combobox('clear');
        $('#account').textbox('clear');
        $('#dlg_form').dialog('setTitle', 'Add New').dialog('open');
    }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            field_id = btoa(row.id);
            $('#bank').combobox('setValue', row.bank_id);
            $('#account').textbox('setValue', row.account);
            $('#dlg_form').dialog('setTitle', 'Update Data').dialog('open');
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    $('#dg').datagrid('loading');
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('payroll/payroll_account/delete') ?>',
                            data: {id: btoa(row.id)},
                            success: function(result) { var result = eval('(' + result + ')'); },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                // $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) { $('#dg').datagrid('reload').datagrid('loaded'); }
                        });
                    }
                }
            });
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() { window.location.assign('<?= base_url('payroll/payroll_account/excel') ?>'); }

    function reload() { window.location.reload(); }

    function getprocess(act, data) {
        $('#dg').datagrid('loading');
        $.ajax({
            url: "<?= base_url('payroll/payroll_account/'); ?>"+act,
            data: data,
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                if (['create', 'update'].includes(act)) {
                    if (result.stat != '') { toastr.error(result.stat, 'Information'); }
                    else {
                        toastr.success('Success save data');
                        $('#dlg_form').dialog('close');
                        $('#dg').datagrid('reload');
                    }
                }
                $('#dg').datagrid('loaded');
            }
        });
    }
</script>