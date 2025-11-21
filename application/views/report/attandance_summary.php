<div class="easyui-accordion" style="width:100%;">

    <div title="Click this to hide the filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">

        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">

            <legend><b>Form Filter Data</b></legend>

            <div style="width: 50%; float:left;">

                <div class="fitem">

                    <span style="width:35%; display:inline-block;">Trans Date</span>

                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To

                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">

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

                    <span style="width:35%; display:inline-block;">Group</span>

                    <input style="width:60%;" id="filter_group" class="easyui-combobox">

                </div>

                <div class="fitem">

                    <span style="width:35%; display:inline-block;">Employee</span>

                    <input style="width:60%;" id="filter_employee" class="easyui-combogrid">

                </div>

            </div>

        </fieldset>

        <?= $button ?>

    </div>

</div>



<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">

    <iframe id="printout" src="" style="width: 100%; height:95%; border: 0;"></iframe>

</div>

<script>

    function reload() {

        window.location.reload();

    }



    function pdf() {

        $("#printout").get(0).contentWindow.print();

    }



    function filter() {

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_division = $("#filter_division").combobox('getValue');

        var filter_departement = $("#filter_departement").combobox('getValue');

        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');

        var filter_group = $("#filter_group").combobox('getValue');

        var filter_employee = $("#filter_employee").combogrid('getValue');



        var url = "?filter_division=" + filter_division +

            "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub +

            "&filter_group=" + filter_group +

            '&filter_from=' + filter_from +

            '&filter_to=' + filter_to +

            '&filter_employee=' + filter_employee;



        var date1 = new Date(filter_from);

        var date2 = new Date(filter_to);



        // // Do the math.

        var Difference_In_Time = date2.getTime() - date1.getTime();

        var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);



        if (Difference_In_Days <= 240) {

            if ("<?= $user->access ?>" == "0") {

                // if ((filter_division != "" && filter_departement != "") || filter_employee != "") {

                    $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

                    $("#printout").attr('src', '<?= base_url('report/attandance_summary/print') ?>' + url);

                // } else {

                //     toastr.info("Please Select Division, Departement or Employee");

                // }

            } else {

                // if ((filter_division != "" && filter_departement != "") || filter_employee != "") {

                    $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

                    $("#printout").attr('src', '<?= base_url('report/attandance_summary/print') ?>' + url);

                // } else {

                //     toastr.info("Please Select Division, Departement or Employee");

                // }

            }

        } else {

            if (filter_employee != "") {

                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

                $("#printout").attr('src', '<?= base_url('report/attandance_summary/print') ?>' + url);

            } else {

                toastr.info("Please Select Employee");

            }

        }

    }



    function excel() {

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_division = $("#filter_division").combobox('getValue');

        var filter_departement = $("#filter_departement").combobox('getValue');

        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');

        var filter_group = $("#filter_group").combobox('getValue');

        var filter_employee = $("#filter_employee").combogrid('getValue');



        var url = "?filter_division=" + filter_division +

            "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub +

            "&filter_group=" + filter_group +

            '&filter_from=' + filter_from +

            '&filter_to=' + filter_to +

            '&filter_employee=' + filter_employee;



        var date1 = new Date(filter_from);

        var date2 = new Date(filter_to);



        // // Do the math.

        var Difference_In_Time = date2.getTime() - date1.getTime();

        var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);



        //if (Difference_In_Days <= 240) {

            //if ((filter_division != "" && filter_departement != "") || filter_employee != "") {

                window.location.assign('<?= base_url('report/attandance_summary/print/excel') ?>' + url);

            // } else {

            //     toastr.info("Please Select Division, Departement or Employee");

            // }

        //} else {

            //toastr.error("Max Duration Period Date is 31 Days");

        //}

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

                                $('#filter_group').combobox({

                                    url: '<?php echo base_url('report/attandance_summary/readGroupFilters'); ?>?division_id=' + division.id + '&departement_id=' + departement.id + '&departement_sub_id=' + departement_sub.id,

                                    valueField: 'id',

                                    textField: 'name',

                                    prompt: 'Choose All',

                                    icons: [{

                                        iconCls: 'icon-clear',

                                        handler: function(e) {

                                            $(e.data.target).combobox('clear').combobox('textbox').focus();

                                        }

                                    }],

                                    onSelect: function(group) {

                                        $('#filter_employee').combogrid({

                                            url: '<?= base_url('employee/employees/reads?group_id=') ?>' + group.id,

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



        $('#filter_group').combobox({

            url: '<?php echo base_url('report/attandance_summary/readGroupFilters'); ?>',

            valueField: 'id',

            textField: 'name',

            prompt: 'Choose All',

            icons: [{

                iconCls: 'icon-clear',

                handler: function(e) {

                    $(e.data.target).combobox('clear').combobox('textbox').focus();

                }

            }],

            onSelect: function(group) {

                $('#filter_employee').combogrid({

                    url: '<?= base_url('employee/employees/reads?group_id=') ?>' + group.id,

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

</script>