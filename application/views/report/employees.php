<div class="easyui-accordion" style="width:100%;">

    <div title="Click this to hide the filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">

        <form id="frm_search" method="post" enctype="multipart/form-data" novalidate>

            <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

            <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">

                <legend><b>Form Filter Data</b></legend>

                <div style="width: 50%; float:left;">

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Division</span>

                        <input style="width:60%;" name="filter_division" id="filter_division" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Departement</span>

                        <input style="width:60%;" name="filter_departement" id="filter_departement" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Departement Sub</span>

                        <input style="width:60%;" name="filter_departement_sub" id="filter_departement_sub" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;"></span>

                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>

                    </div>

                </div>

                <div style="width: 49%; float:left;">

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Employee</span>

                        <input style="width:60%;" name="filter_employee" id="filter_employee" class="easyui-combogrid">

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Status</span>

                        <select style="width:60%;" name="filter_status" id="filter_status" class="easyui-combobox" panelHeight="auto">

                            <option value="">Choose All</option>

                            <option value="0">ACTIVE</option>

                            <option value="1">NOT ACTIVE</option>

                        </select>

                    </div>

                    <div class="fitem">

                        <span style="width:35%; display:inline-block;">Display Column</span>

                        <select style="width:60%;" name="filter_column[]" id="filter_column" class="easyui-combobox" data-options="prompt:'Choose Column'" multiple="true">

                            <option value="number">ID</option>

                            <option value="name">Name</option>

                            <option value="status">Status</option>

                            <option value="nickname">Nick Name</option>

                            <option value="division_name">Division</option>

                            <option value="departement_name">Departement</option>

                            <option value="departement_sub_name">Departement Sub</option>

                            <option value="contract_name">Type</option>

                            <option value="position_name">Position</option>

                            <option value="group_name">Group</option>

                            <option value="source_name">Source</option>

                            <option value="marital_name">Marital Status</option>

                            <option value="religion_name">Religion</option>

                            <option value="date_sign">Sign In</option>

                            <option value="sign_date">Sign Date</option>

                            <option value="date_expired">Sign Expired</option>

                            <option value="date_resign">Resign Date</option>

                            <option value="level_education">Level of Education</option>

                            <option value="degree">Degree</option>

                            <option value="address">Address</option>

                            <option value="place_birth">Place Birth</option>

                            <option value="birthday">Birthday</option>

                            <option value="age">Age</option>

                            <option value="gender">Gender</option>

                            <option value="blood">Blood</option>

                            <option value="national_id">National ID</option>

                            <option value="tax_id">Tax ID</option>

                            <option value="jamsostek">Jamsostek</option>

                            <option value="jamsostek_date">Jamsostek Date</option>

                            <option value="jkn">JKN</option>

                            <option value="jkn_date">JKN Date</option>

                            <option value="jkn_family">JKN Family</option>

                            <option value="telphone">Telphone</option>

                            <option value="mobile_phone">Mobile Phone</option>

                            <option value="emergency_no">Emergency No</option>

                            <option value="email">Email</option>

                            <option value="driving_no">Driving No</option>

                            <option value="driving_date">Driving Date</option>

                            <option value="stnk_no">STNK No</option>

                            <option value="stnk_date">STNK Date</option>

                            <option value="maps">Maps</option>

                            <option value="bank_no">Bank No</option>

                            <option value="bank_name">Bank Name</option>

                            <option value="bank_branch">Bank Branch</option>

                            <option value="status_date">Status Date</option>

                            <option value="job_type">Job Type</option>

                            <option value="shift_type">Shift Type</option>

                        </select>

                    </div>

                </div>

            </fieldset>

            <?= $button ?>

        </form>

    </div>

</div>



<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">

    <iframe id="printout" src="" style="width: 100%; height:95%; border:0;"></iframe>

</div>

<script>

    function reload() {

        window.location.reload();

    }



    function pdf() {

        $("#printout").get(0).contentWindow.print();

    }



    function filter() {

        var form = $("#frm_search").serialize()

        var url = form;



        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

        $("#printout").attr('src', '<?= base_url('report/employees/print?') ?>' + url);

    }



    function excel() {

        var form = $("#frm_search").serialize()

        var url = form;



        window.location.assign('<?= base_url('report/employees/print/excel?') ?>' + url);

    }



    $(function() {

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

</script>