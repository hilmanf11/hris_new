<body>
    <div class="row m-2">
        <div class="col-lg-4">
            <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:20%; display:inline-block;">Period Date</span>
                    <input style="width:28%;" name="filter_from" id="filter_from" class="easyui-combogrid"> To
                    <input style="width:28%;" name="filter_to" id="filter_to" data-options="prompt:'Date To'" readonly class="easyui-textbox">
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter</a>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-3" id="manpower">
                    <div class="alert-info" style="height: 60px;">
                        <div style="float: left; width: 50px;">
                            <i class="fa fa-users" style="font-size: 35px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Total MP</small>
                            <h1 style="margin: 0; font-size:20px !important;" class="count" id="total_mp">0</h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3" id="salary">
                    <div class="alert-success" style="height: 60px;">
                        <div style="float: left; width: 50px;">
                            <i class="fa fa-money" style="font-size: 35px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Total Net Income</small>
                            <h1 style="margin: 0; font-size:18px !important;" class="count" id="total_income">0</h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3" id="deduction">
                    <div class="alert-danger" style="height: 60px;">
                        <div style="float: left; width: 50px;">
                            <i class="fa fa-money" style="font-size: 35px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Total PKL</small>
                            <h1 style="margin: 0; font-size:18px !important;" class="count" id="total_pkl">0</h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3" id="ter">
                    <div class="alert-warning" style="height: 60px;">
                        <div style="float: left; width: 50px;">
                            <i class="fa fa-money" style="font-size: 35px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Total TER/PPH</small>
                            <h1 style="margin: 0; font-size:18px !important;" class="count" id="total_ter">0</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            Payroll By Departement
                        </div>
                        <div class="card-body">
                            <div id="chartDepartement"></div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="dg_departement" class="easyui-datagrid" title="Table Data" style="width:100%;"></table>
        </div>
        <div class="col-lg-3">
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            Payroll By Position
                        </div>
                        <div class="card-body">
                            <div id="chartPosition"></div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="dg_position" class="easyui-datagrid" title="Table Data" style="width:100%;"></table>
        </div>
        <div class="col-lg-3">
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            Payroll By Group
                        </div>
                        <div class="card-body">
                            <div id="chartGroup"></div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="dg_group" class="easyui-datagrid" title="Table Data" style="width:100%;"></table>
        </div>
        <div class="col-lg-3">
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            Payroll by PKL
                        </div>
                        <div class="card-body">
                            <div id="chartPkl"></div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="dg_pkl" class="easyui-datagrid" title="Table Data" style="width:100%;"></table>
        </div>
    </div>
</body>
<script>
    function filter(){
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url("dashboard/payrolls/myPayroll?filter_from=") ?>" + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            dataType: "json",
            success: function (pay) {
                $("#total_mp").html(pay.total_mp);
                $("#total_pkl").html(pay.total_pkl);
                $("#total_income").html(pay.total_income);
                $("#total_ter").html(pay.total_ter);

                $(".count").each(function () {
                    $(this).prop("Counter", 0).animate({
                        Counter: $(this).text(),
                    },{
                        duration: 2000,
                        // easing: "Swing",
                        step: function(now){
                            now = Number(Math.ceil(now)).toLocaleString('idn');
                            $(this).text(now);
                        }
                    });
                });
            }
        });

        $('#dg_departement').datagrid({
            url: '<?= base_url('dashboard/payrolls/tableDepartement?filter_from=') ?>' + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            showFooter: true,
            rownumbers: true,
            pagination: false,
            columns: [[
                {field: 'name', title: 'Name', width: 160, halign: 'center'},
                {field: 'total_mp', title: 'MP', width: 50, align: 'center'},
                {field: 'total', title: 'Total', width: 120, halign: 'center', align: 'right', formatter: numberformat},
            ]]
        });

        $('#dg_position').datagrid({
            url: '<?= base_url('dashboard/payrolls/tablePosition?filter_from=') ?>' + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            showFooter: true,
            rownumbers: true,
            pagination: false,
            columns: [[
                {field: 'name', title: 'Name', width: 160, halign: 'center'},
                {field: 'total_mp', title: 'MP', width: 50, align: 'center'},
                {field: 'total', title: 'Total', width: 120, halign: 'center', align: 'right', formatter: numberformat},
            ]]
        });

        $('#dg_group').datagrid({
            url: '<?= base_url('dashboard/payrolls/tableGroup?filter_from=') ?>' + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            showFooter: true,
            rownumbers: true,
            pagination: false,
            columns: [[
                {field: 'name', title: 'Name', width: 160, halign: 'center'},
                {field: 'total_mp', title: 'MP', width: 50, align: 'center'},
                {field: 'total', title: 'Total', width: 120, halign: 'center', align: 'right', formatter: numberformat},
            ]]
        });

        $('#dg_pkl').datagrid({
            url: '<?= base_url('dashboard/payrolls/tablePkl?filter_from=') ?>' + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            showFooter: true,
            rownumbers: true,
            pagination: false,
            columns: [[
                {field: 'name', title: 'Name', width: 180, halign: 'center'},
                {field: 'total_mp', title: 'MP', width: 50, align: 'center'},
                {field: 'total', title: 'Total', width: 100, halign: 'center', align: 'right', formatter: numberformat},
            ]]
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('dashboard/payrolls/chartDepartement?filter_from=') ?>" + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            data: "period_start=",
            dataType: "json",
            success: function (dept) {
                Highcharts.chart('chartDepartement', {
                    chart: {
                        type: 'bar',
                        height: 250
                    },
                    title: {
                        text: '',
                    },
                    xAxis: {
                        categories: dept.name,
                        title: {
                            text: null
                        },
                        gridLineWidth: 1,
                        lineWidth: 0
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Income',
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        },
                        gridLineWidth: 0
                    },
                    tooltip: {
                        valueSuffix: ' IDR'
                    },
                    series: [{
                        name: 'Net Income',
                        data: dept.income
                    }]
                });
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('dashboard/payrolls/chartPosition?filter_from=') ?>" + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            data: "period_start=",
            dataType: "json",
            success: function (position) {
                Highcharts.chart('chartPosition', {
                    chart: {
                        type: 'bar',
                        height: 250
                    },
                    title: {
                        text: '',
                    },
                    xAxis: {
                        categories: position.name,
                        title: {
                            text: null
                        },
                        gridLineWidth: 1,
                        lineWidth: 0
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Income',
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        },
                        gridLineWidth: 0
                    },
                    tooltip: {
                        valueSuffix: ' IDR'
                    },
                    series: [{
                        name: 'Net Income',
                        data: position.income
                    }]
                });
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('dashboard/payrolls/chartGroup?filter_from=') ?>" + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            data: "period_start=",
            dataType: "json",
            success: function (group) {
                Highcharts.chart('chartGroup', {
                    chart: {
                        type: 'bar',
                        height: 250
                    },
                    title: {
                        text: '',
                    },
                    xAxis: {
                        categories: group.name,
                        title: {
                            text: null
                        },
                        gridLineWidth: 1,
                        lineWidth: 0
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Income',
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        },
                        gridLineWidth: 0
                    },
                    tooltip: {
                        valueSuffix: ' IDR'
                    },
                    series: [{
                        name: 'Net Income',
                        data: group.income
                    }]
                });
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('dashboard/payrolls/chartPkl?filter_from=') ?>" + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            data: "period_start=",
            dataType: "json",
            success: function (pkl) {
                Highcharts.chart('chartPkl', {
                    chart: {
                        type: 'pie',
                        height: 250
                    },
                    title: {
                        text: ''
                    },
                    plotOptions: {
                        series: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: [{
                                enabled: true,
                                distance: 20
                            }, {
                                enabled: true,
                                distance: -40,
                                format: '{point.percentage:.1f}%',
                                style: {
                                    fontSize: '1.2em',
                                    textOutline: 'none',
                                    opacity: 0.7
                                },
                                filter: {
                                    operator: '>',
                                    property: 'percentage',
                                    value: 10
                                }
                            }]
                        }
                    },
                    series: [{
                        name: 'Net Income',
                        colorByPoint: true,
                        data: pkl
                    }]
                });
            }
        });
    }
    
    $(function() {
        // filter();

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
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>