<!DOCTYPE html>
<html>
<head>
    <title>Print Employee Detail</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; }
        #customers { border-collapse: collapse; width: 100%; font-size: 12px; }
        #customers td, #customers th { border: 1px solid #ddd; padding: 5px; }
        #customers tr:nth-child(even) { background-color: #f2f2f2; }
        #customers th { background: #f8f8f8; font-weight: bold; border-bottom: none; }
        .section-title { font-weight: bold; margin-top: 16px; font-size: 16px; border-bottom: 1px solid #bbb; padding-bottom: 4px; margin-bottom: 8px; text-align: center;}
        .detail-title { font-weight: bold; font-size: 15px; margin: 8px 0 4px 0; }
        fieldset { border:1px solid #d0d0d0; border-radius:4px; margin-bottom: 10px; }
        legend { font-weight: bold; }
        .profile-photo-box img { width: 180px; height: 180px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 10px; }
        .half { width: 49%; float: left; }
        .clearfix::after { content: ""; clear: both; display: table; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()">
<div style="width: 100%;" class="clearfix">
    <fieldset style="width:17%; float:left;">
        <legend>Foto Profile</legend>
        <div class="profile-photo-box">
            <?php if(isset($employee->image_profile) && $employee->image_profile): ?>
                <img src="<?= $employee->image_profile ?>" alt="Foto Profile" />
            <?php else: ?>
                <img src="<?= base_url('assets/image/users/default.png') ?>" alt="Foto Profile" />
            <?php endif; ?>
        </div>
    </fieldset>
    <fieldset style="width:75%; float:right;">
        <legend>Personal Information</legend>
        <h1 style="font-size: 32px; margin:0;"><?= strtoupper($employee->name ?? '') ?></h1>
        <span style="font-size: 20px;"><?= $employee->number ?? '' ?></span>
        <div style="width:100%; margin-top:10px;">
            <div style="width:49%; float:left;">
                <table id="customers">
                    <tr><td>Email</td><td><b><?= $employee->email ?? '' ?></b></td></tr>
                    <tr><td>Phone No</td><td><b><?= $employee->mobile_phone ?? '' ?></b></td></tr>
                    <tr><td>Division</td><td><b><?= $division->name ?? '' ?></b></td></tr>
                    <tr><td>Departement</td><td><b><?= $departement->name ?? '' ?></b></td></tr>
                    <tr><td>Departement Sub</td><td><b><?= $departement_sub->name ?? '' ?></b></td></tr>
                    <tr><td>Employee Type</td><td><b><?= $contract->name ?? '' ?></b></td></tr>
                    <tr><td>Position</td><td><b><?= $position->name ?? '' ?></b></td></tr>
                    <tr><td>Group</td><td><b><?= $group->name ?? '' ?></b></td></tr>
                    <tr><td>Source</td><td><b><?= $source->name ?? '' ?></b></td></tr>
                    <tr><td>Sign Date</td><td><b><?= isset($employee->date_sign) ? date('d F Y', strtotime($employee->date_sign)) : '' ?></b></td></tr>
                    <tr><td>Contract Expired</td><td><b><?= isset($employee->date_expired) && $employee->date_expired != '0000-00-00' ? date('d F Y', strtotime($employee->date_expired)) : '-' ?></b></td></tr>
                    <tr><td>Fit of Service</td><td><b><?= $service ?? '' ?></b></td></tr>
                    <tr><td>Address</td><td><b><?= $employee->address ?? '' ?></b></td></tr>
                    <tr><td>Place of Birth</td><td><b><?= $employee->place_birth ?? '' ?></b></td></tr>
                    <tr><td>Birthday</td><td><b><?= isset($employee->birthday) ? date('d F Y', strtotime($employee->birthday)) : '' ?></b></td></tr>
                </table>
            </div>
            <div style="width:49%; float:right;">
                <table id="customers">
                    <tr><td>Gender</td><td><b><?= $employee->gender ?? '' ?></b></td></tr>
                    <tr><td>Blood Type</td><td><b><?= $employee->blood ?? '' ?></b></td></tr>
                    <tr><td>Religion</td><td><b><?= $religion->name ?? '' ?></b></td></tr>
                    <tr><td>Marital Status</td><td><b><?= $marital->name ?? '' ?></b></td></tr>
                    <tr><td>National ID</td><td><b><?= $employee->national_id ?? '' ?></b></td></tr>
                    <tr><td>Tax No</td><td><b><?= $employee->tax_id ?? '' ?></b></td></tr>
                    <tr><td>BPJS TK</td><td><b><?= $employee->jamsostek ?? '' ?></b></td></tr>
                    <tr><td>BPJS TK Date</td><td><b><?= $employee->jamsostek_date ?? '' ?></b></td></tr>
                    <tr><td>JKN</td><td><b><?= $employee->jkn ?? '' ?></b></td></tr>
                    <tr><td>JKN Date</td><td><b><?= $employee->jkn_date ?? '' ?></b></td></tr>
                    <tr><td>Number of Family (JKN)</td><td><b><?= $employee->jkn_family ?? '' ?></b></td></tr>
                    <tr><td>Emergency No</td><td><b><?= $employee->emergency_no ?? '' ?></b></td></tr>
                    <tr><td>Driving Licence</td><td><b><?= $employee->driving_no ?? '' ?></b></td></tr>
                    <tr><td>Vehicle Registration (STNK)</td><td><b><?= $employee->stnk_no ?? '' ?></b></td></tr>
                    <tr><td>Google Maps</td><td><b><?= $employee->maps ?? '' ?></b></td></tr>
                </table>
            </div>
        </div>
    </fieldset>
</div>

<div class="detail-title"></div>
<fieldset style="border:1px solid #bbb; border-radius:6px; padding:10px; margin-bottom:16px;">
    <legend style="font-weight:bold; font-size:16px; padding:0 8px;">Detail Personal Information</legend>
    <div class="section-title">Family</div>
    <table id="customers">
        <tr>
            <th>Family Name</th>
            <th>Place Birth</th>
            <th>Birthday</th>
            <th>Relation</th>
            <th>Profession</th>
            <th>Contact</th>
        </tr>
        <?php if(isset($family) && is_array($family)): foreach($family as $f): ?>
        <tr>
            <td><?= $f->name ?></td>
            <td><?= $f->place ?></td>
            <td><?= $f->birthday ?></td>
            <td><?= $f->relation ?></td>
            <td><?= $f->profesion ?></td>
            <td><?= $f->contact ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <div class="section-title">Education</div>
    <table id="customers">
        <tr>
            <th>Education Level</th>
            <th>Degree</th>
            <th>School/University</th>
            <th>Start</th>
            <th>End</th>
            <th>QPA</th>
        </tr>
        <?php if(isset($education) && is_array($education)): foreach($education as $e): ?>
        <tr>
            <td><?= $e->level ?></td>
            <td><?= $e->degree ?></td>
            <td><?= $e->school ?></td>
            <td><?= $e->start ?></td>
            <td><?= $e->end ?></td>
            <td><?= $e->qpa ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <div class="section-title">Experience</div>
    <table id="customers">
        <tr>
            <th>Company Name</th>
            <th>Industries Type</th>
            <th>Start</th>
            <th>End</th>
            <th>Position</th>
            <th>Salary</th>
        </tr>
        <?php if(isset($experience) && is_array($experience)): foreach($experience as $ex): ?>
        <tr>
            <td><?= $ex->name ?></td>
            <td><?= $ex->type ?></td>
            <td><?= $ex->start ?></td>
            <td><?= $ex->end ?></td>
            <td><?= $ex->position ?></td>
            <td><?= $ex->salary ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <div class="section-title">Training</div>
    <table id="customers">
        <tr>
            <th>Training Name</th>
            <th>Description</th>
            <th>Date</th>
            <th>Profession</th>
            <th>Contact</th>
        </tr>
        <?php if(isset($training) && is_array($training)): foreach($training as $tr): ?>
        <tr>
            <td><?= $tr->name ?></td>
            <td><?= $tr->description ?></td>
            <td><?= $tr->start ?></td>
            <td><?= $tr->profesion ?></td>
            <td><?= $tr->contact ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <div class="section-title">Carrer</div>
    <table id="customers">
        <tr>
            <th>Carrer Name</th>
            <th>Description</th>
            <th>Date</th>
            <th>Profession</th>
            <th>Contact</th>
        </tr>
        <?php if(isset($career) && is_array($career)): foreach($career as $cr): ?>
        <tr>
            <td><?= $cr->name ?></td>
            <td><?= $cr->description ?></td>
            <td><?= $cr->start ?></td>
            <td><?= $cr->profesion ?></td>
            <td><?= $cr->contact ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <div class="section-title">Warning Letter</div>
    <table id="customers">
        <tr>
            <th>Warning Letter</th>
            <th>Trans Date</th>
            <th>Issue Date</th>
            <th>Violation</th>
            <th>Remarks</th>
        </tr>
        <?php if(isset($warning) && !empty($warning)): foreach($warning as $w): ?>
        <tr>
            <td><?= ($w->warning_letter == "4") ? "TERMINATION" : "SP " . $w->warning_letter ?></td>
            <td><?= isset($w->trans_date) ? date('d F Y', strtotime($w->trans_date)) : '' ?></td>
            <td><?= isset($w->issue_date) ? date('d F Y', strtotime($w->issue_date)) : '' ?></td>
            <td><?= $w->violation_name ?? '' ?></td>
            <td><?= $w->remarks ?? '' ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <div class="section-title">Mutations</div>
    <table id="customers">
        <tr>
            <th>Trans Date</th>
            <th>Division</th>
            <th>Departement</th>
            <th>Departement Sub</th>
            <th>Type</th>
            <th>Mutation Type</th>
            <th>Note</th>
        </tr>
        <?php if(isset($mutations) && is_array($mutations)): foreach($mutations as $m): ?>
        <tr>
            <td><?= isset($m['trans_date']) ? (isset($m['trans_date']) && $m['trans_date'] != '0000-00-00' ? date('d F Y', strtotime($m['trans_date'])) : '-') : (isset($m->trans_date) && $m->trans_date != '0000-00-00' ? date('d F Y', strtotime($m->trans_date)) : '-') ?></td>
            <td><?= isset($m['division_to']) ? $m['division_to'] : (isset($m->division_to) ? $m->division_to : '') ?></td>
            <td><?= isset($m['departement_to']) ? $m['departement_to'] : (isset($m->departement_to) ? $m->departement_to : '') ?></td>
            <td><?= isset($m['departement_sub_to']) ? $m['departement_sub_to'] : (isset($m->departement_sub_to) ? $m->departement_sub_to : '') ?></td>
            <td><?= isset($m['type']) ? $m['type'] : (isset($m->type) ? $m->type : '') ?></td>
            <td><?= isset($m['mutation_type']) ? $m['mutation_type'] : (isset($m->mutation_type) ? $m->mutation_type : '') ?></td>
            <td><?= isset($m['description']) ? $m['description'] : (isset($m->description) ? $m->description : '') ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <div class="section-title">Contracs</div>
    <table id="customers">
        <tr>
            <th>Position</th>
            <th>Employee Type</th>
            <th>Expired Date</th>
        </tr>
        <?php if (isset($contracts)) {
            foreach ($contracts as $key => $row) { ?>
                <tr>
                    <td><?= $row->position ?></td>
                    <td><?= $row->contract ?></td>
                    <td align="center"><?= $row->date ?></td>
                </tr>
            <?php }
        } ?>
    </table>
</fieldset>
</body>
</html> 