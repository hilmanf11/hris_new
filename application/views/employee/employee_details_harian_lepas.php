<style>

    .button-gray {

        display: inline-block;

        padding: 5px;

        cursor: pointer;

        text-align: center;

        text-decoration: none;

        outline: none;

        color: #fff;

        background-color: #C2C2C2;

        border: none;

        border-radius: 5px;

    }



    .button-gray:hover {

        background-color: #B9B9B9;

    }



    body {

        font-family: Arial, Helvetica, sans-serif;

    }



    #customers {

        border-collapse: collapse;

        width: 100%;

        font-size: 12px;

    }



    #customers td,

    #customers th {

        border: 1px solid #ddd;

        padding: 5px;

    }



    #customers tr:nth-child(even) {

        background-color: #f2f2f2;

    }



    #customers tr:hover {

        background-color: #ddd;

    }



    #customers th {

        padding-top: 2px;

        padding-bottom: 2px;

        text-align: left;

        color: black;

    }

</style>



<?php

if ($employee->status == 0) {

    $status = '<span class="button-green" style="width: 100%; font-size: 20px !important;">ACTIVE</span>';

} else {

    $status = '<span class="button-red" style="width: 100%; font-size: 20px !important;">NOT ACTIVE</span>';

}



if (!empty($resignation->employee_id)) {

    if ($resignation->status_resign == "ON PROCEDURE") {

        $status_resign = '<span class="button-green" style="width: 100%; font-size: 20px !important;">' . $resignation->status_resign . '</span>';

    } else {

        $status_resign = '<span class="button-red" style="width: 100%; font-size: 20px !important;">' . $resignation->status_resign . '</span>';

    }

} else {

    $status_resign = '<span class="button-gray" style="width: 100%; font-size: 20px !important;">NO DATA RESIGN</span>';

}



if ($employee->image_profile == "" || $employee->image_profile == null) {

    $image = "<img src='" . base_url('assets/image/users/default.png') . "' style='width: 100%;'>";

} else {

    $image = "<img src='" . $employee->image_profile . "' style='width: 100%;'>";

}

?>



<div class="easyui-panel" title="Employee Detail" style="width:99.5%;padding:10px;background:#fafafa;" data-options="collapsible:true">

    <div style="width: 100%; float: left;">

        <fieldset style="width:23%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

            <legend><b>Foto Profile</b></legend>

            <?= $image ?>

            <?= $status ?>

            <br>

            <?= $status_resign ?>

            <br>

            <a class="button-red" href="<?= base_url('employee/employee_harian_lepas/index/' . $id_menu) ?>" style="width: 100%; font-size: 20px !important;">BACK</a>

        </fieldset>

        <fieldset style="width:75%; padding:11px; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">

            <legend><b>Personal Information</b></legend>

            <div style="width: 100%;">

                <h1 style="font-size: 50px !important; margin:0; padding:0;"><?= $employee->name ?></h1>

                <span style="width:60%; font-size: 30px !important;"><?= $employee->number ?></span>

                <br><br>



                <div style="width: 50%; float: left; padding:10px;">

                    <table id="customers">

                        <tr>

                            <td>Email</td>

                            <td><b><?= $employee->email ?></b></td>

                        </tr>

                        <tr>

                            <td>Phone No</td>

                            <td><b><?= $employee->mobile_phone ?></b></td>

                        </tr>

                        <tr>

                            <td>Division</td>

                            <td><b><?= $division->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Departement</td>

                            <td><b><?= $departement->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Departement Sub</td>

                            <td><b><?= $departement_sub->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Employee Type</td>

                            <td><b><?= @$contract->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Position</td>

                            <td><b><?= @$position->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Group</td>

                            <td><b><?= @$group->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Source</td>

                            <td><b><?= @$source->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Sign Date</td>

                            <td><b><?= date("d F Y", strtotime($employee->date_sign)) ?></b></td>

                        </tr>

                        <tr>

                            <td>Contract Expired</td>

                            <td><b><?= $employee->date_expired ? date("d F Y", strtotime($employee->date_expired)) : '-' ?></b></td>

                        </tr>

                        <tr>

                            <td>Fit of Service</td>

                            <td><b><?= @$service ?></b></td>

                        </tr>

                        <tr>

                            <td>Address</td>

                            <td><b><?= $employee->address ?></b></td>

                        </tr>

                        <tr>

                            <td>Place Of Birth</td>

                            <td><b><?= $employee->place_birth ?></b></td>

                        </tr>

                        <tr>

                            <td>Birthday</td>

                            <td><b><?= date("d F Y", strtotime($employee->birthday)) ?></b></td>

                        </tr>

                    </table>

                </div>

                <div style="width: 50%; float: left; padding: 10px;">

                    <table id="customers">

                        <tr>

                            <td width="100">Gender</td>

                            <td width="200"><b><?= $employee->gender ?></b></td>

                        </tr>

                        <tr>

                            <td>Blood Type</td>

                            <td><b><?= $employee->blood ?></b></td>

                        </tr>

                        <tr>

                            <td>Religion</td>

                            <td><b><?= @$religion->name ?></b></td>

                        </tr>

                        <tr>

                            <td>Marital Status</td>

                            <td><b><?= @$marital->name ?></b></td>

                        </tr>

                        <tr>

                            <td>National ID</td>

                            <td><b><?= $employee->national_id ?></b></td>

                        </tr>

                        <tr>

                            <td>Tax No</td>

                            <td><b><?= $employee->tax_id ?></b></td>

                        </tr>

                        <tr>

                            <td>BPJS TK</td>

                            <td><b><?= $employee->jamsostek ?></b></td>

                        </tr>

                        <tr>

                            <td>BPJS TK Date</td>

                            <td><b><?= $employee->jamsostek_date ?></b></td>

                        </tr>

                        <tr>

                            <td>JKN</td>

                            <td><b><?= $employee->jkn ?></b></td>

                        </tr>

                        <tr>

                            <td>JKN Date</td>

                            <td><b><?= $employee->jkn_date ?></b></td>

                        </tr>

                        <tr>

                            <td>Number of Family (JKN)</td>

                            <td><b><?= $employee->jkn_family ?></b></td>

                        </tr>

                        <!-- <tr>

                            <td>Emergency No</td>

                            <td><b><?= $employee->emergency_no ?></b></td>

                        </tr>

                        <tr>

                            <td>Driving Licence</td>

                            <td><b><?= $employee->driving_no ?></b></td>

                        </tr>

                        <tr>

                            <td>Vehicle Registration (STNK)</td>

                            <td><b><?= $employee->stnk_no ?></b></td>

                        </tr>

                        <tr>

                            <td>Google Maps</td>

                            <td><b><?= $employee->maps ?></b></td>

                        </tr> -->

                    </table>

                </div>

            </div>

        </fieldset>

        <div class="easyui-tabs" style="width:100%; float: left;">

            <div title="Family">

                <table id="dgFamily" class="easyui-datagrid" style="width:100%;">

                    <thead>

                        <tr>

                            <th data-options="field:'name',width:150,halign:'center'">Family Name</th>

                            <th data-options="field:'place',width:100,halign:'center'">Place Birth</th>

                            <th data-options="field:'birthday',width:100,halign:'center'">Birthday</th>

                            <th data-options="field:'relation',width:100,halign:'center'">Relation</th>

                            <th data-options="field:'profesion',width:200,halign:'center'">Profession</th>

                            <th data-options="field:'contact',width:100,halign:'center'">Contact</th>

                        </tr>

                    </thead>

                </table>

            </div>



            <!-- Education Information -->

            <div title="Education">

                <table id="dgEducation" class="easyui-datagrid" style="width:100%;">

                    <thead>

                        <tr>

                            <th data-options="field:'level',width:150,halign:'center'">Education Level</th>

                            <th data-options="field:'degree',width:100,halign:'center'">Degree</th>

                            <th data-options="field:'school',width:250,halign:'center'">School/University</th>

                            <th data-options="field:'start',width:100,halign:'center'">Start</th>

                            <th data-options="field:'end',width:100,halign:'center'">End</th>

                            <th data-options="field:'qpa',width:100,halign:'center'">QPA</th>

                        </tr>

                    </thead>

                </table>

            </div>



            <!-- Working Experience -->

            <div title="Experience">

                <table id="dgExperience" class="easyui-datagrid" style="width:100%;">

                    <thead>

                        <tr>

                            <th data-options="field:'name',width:250,halign:'center'">Company Name</th>

                            <th data-options="field:'type',width:150,halign:'center'">Industries Type</th>

                            <th data-options="field:'start',width:100,halign:'center'">Start</th>

                            <th data-options="field:'end',width:100,halign:'center'">End</th>

                            <th data-options="field:'position',width:200,halign:'center'">Position</th>

                            <th data-options="field:'salary',width:100,halign:'center'">Salary</th>

                        </tr>

                    </thead>

                </table>

            </div>



            <!-- Training Experience -->

            <div title="Training">

                <table id="dgTraining" class="easyui-datagrid" style="width:100%;">

                    <thead>

                        <tr>

                            <th data-options="field:'name',width:200,halign:'center'">Training Name</th>

                            <th data-options="field:'description',width:300,halign:'center'">Description</th>

                            <th data-options="field:'start',width:100,halign:'center'">Date</th>

                            <th data-options="field:'profesion',width:150,halign:'center'">Profession</th>

                            <th data-options="field:'contact',width:100,halign:'center'">Contact</th>

                        </tr>

                    </thead>

                </table>

            </div>



            <!-- Carrer Information -->

            <div title="Carrer">

                <table id="dgCarrer" class="easyui-datagrid" style="width:100%;">

                    <thead>

                        <tr>

                            <th data-options="field:'name',width:200,halign:'center'">Carrer Name</th>

                            <th data-options="field:'description',width:300,halign:'center'">Description</th>

                            <th data-options="field:'start',width:100,halign:'center'">Date</th>

                            <th data-options="field:'profesion',width:150,halign:'center'">Profession</th>

                            <th data-options="field:'contact',width:100,halign:'center'">Contact</th>

                        </tr>

                    </thead>

                </table>

            </div>

            <!-- Contract Information -->

            <div title="Contract">

                <table id="dgContract" class="easyui-datagrid" style="width:100%;"></table>

            </div>

        </div>

    </div>

</div>



<script>

    $(function() {

        $('#dgFamily').datagrid({

            url: '<?= base_url('employee/employee_harian_lepas/readFamilys/' . $employee->number) ?>'

        });



        $('#dgEducation').datagrid({

            url: '<?= base_url('employee/employee_harian_lepas/readEducation/' . $employee->number) ?>'

        });



        $('#dgExperience').datagrid({

            url: '<?= base_url('employee/employee_harian_lepas/readExperience/' . $employee->number) ?>'

        });



        $('#dgTraining').datagrid({

            url: '<?= base_url('employee/employee_harian_lepas/readTraining/' . $employee->number) ?>'

        });



        $('#dgCarrer').datagrid({

            url: '<?= base_url('employee/employee_harian_lepas/readCarrer/' . $employee->number) ?>'

        });

        $('#dgContract').datagrid({
            url:'<?= base_url('employee/employee_harian_lepas/readContract/' . $employee->number) ?>',
            columns: [[
                {field: 'position', title: 'Position', width: 200, halign: 'center'},
                {field: 'contract', title: 'Employee Type', width: 200, halign: 'center'},
                {field: 'date', title: 'Expired Date', width: 100, halign: 'center', align: 'center'},
                {field: 'attachment', title: 'Attachment', width: 100, halign: 'center', align: 'center', formatter: function (value, row, index) { return (value == '') ? '' : '<a href="javascript:;" class="easyui-linkbutton" onclick="download_attachment('+"'"+window.btoa(value)+"'"+')"><i class="fa fa-download"></i> Download</a>'; }}
            ]],
            onLoadSuccess: function () { $('.easyui-linkbutton').linkbutton(); }
        });
    });

    function download_attachment(attachment) {
        var name = window.atob(attachment);
        var ext = '.'+name.split('.')[1];
        var date = new Date();
        var y = date.getFullYear();
        var m = (date.getMonth()+1).toString().padStart(2, '0');
        var d = (date.getDate()).toString().padStart(2, '0');
        var a = document.createElement('a');

        a.href = '<?= base_url($loc) ?>'+name;
        a.download = 'attachment_'+y+m+d+ext;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

</script>