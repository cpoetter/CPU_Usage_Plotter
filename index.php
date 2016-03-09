<html>
    <head>
        <title>Server Utilization</title>

        <!-- Load c3.css -->
        <link href="c3-0.4.10/c3.css" rel="stylesheet" type="text/css">
    
        <!-- Load d3.js and c3.js -->
        <script src="c3-0.4.10/d3/d3.min.js" charset="utf-8"></script>
        <script src="c3-0.4.10/c3.min.js"></script>

         <script src="https://code.jquery.com/jquery-1.9.1.js"></script>


        <style type="text/css">
            body {
                color: #222222;
                font-family: Verdana, Geneva, sans-serif;
                padding: 0px;
                margin: 0px;
                background-color: #eeeeee;
                overflow-x: hidden;
            }

            .c3-legend-item, .c3-axis-x-label, .c3-axis-y-label, .c3-axis-x {
                font-size:15;
            }
            
            a:hover, a:visited, a:link, a:active {
                text-decoration: none;
                color: #FFFFFF;
            }
            
            #footer {
                width: 100%;
                height: 25px;
                padding-top: 7px;
                position: fixed;
                bottom: 0px;
                margin:0;
                left:0;
                right:0;
                color: gray;
                z-index:300;
                text-align:center;
                background-color: #eeeeee;
                margin-bottom: 0px;
                padding-bottom: 0px;
            }
            
            #header {
                position: fixed;
                margin: auto;
                text-align: center;
                width: 100%;
                left: 0px;
                padding-bottom: 5px;
                height: 30px;
                background-color: #840000;
                z-index: 100;
                padding-top: 7px;
                -webkit-box-shadow: 0px 2px 5px 2px rgba(0, 0, 0, 0.35);
                -moz-box-shadow: 0px 2px 5px 2px rgba(0, 0, 0, 0.35);
                box-shadow: 4px 2px 5px 2px rgba(0, 0, 0, 0.35);
                color: white;
            }
            #main {
                width: 100%;
                position: absolute;
                left: 0px;
                padding: 0px;
                margin: 0px;
                margin-top: 60px;
            }
        </style>

        <script type="text/javascript">

            var chart = '';
            var server_names = ["<<AddressServer1>>", "<<AddressServer2>>", "<<AddressServer3>>", "<<AddressServer4>>"];
            var cpu = new Array(server_names.length);
            var gpu = new Array(server_names.length);
            var gpu_memory = new Array(server_names.length);
            var user = new Array(server_names.length);
            var process = new Array(server_names.length);
            var memory = new Array(server_names.length);
            
            function on_page_load() {
                            
                chart = c3.generate({
                data: {
                    x : 'x',
                    columns: [
                            ['x'].concat(server_names),
                            ['data1', 0],
                            ['data2', 0],
                            ['data3', 0],
                            ['data4', 0]
                        ],
                        type: 'bar',
                        names: {
                            data1: 'CPU',
                            data2: 'Memory',
                            data3: 'GPU',
                            data4: 'GPU Memory'
                        },
                    },
                    axis: {
                        x: {
                            label: {
                                text: '',
                                position: 'outer-right'
                            },
                            type: 'category',
                        },
                        y: {
                            label: {
                                text: 'Utilization in %',
                                position: 'outer-middle'
                            },
                            min: 0,
                            padding: {top:0, bottom:0}
                        }
                    }
                });
                
                for(var i = 0; i < server_names.length; i++) {
                    get_data(i, server_names[i]);
                }
                
                window.setInterval(function() {
                    for(var i = 0; i < server_names.length; i++) {
                        get_data(i, server_names[i]);
                    }
                    redraw();
                }, 15000);
                }
            
            
            var process_names = '';
            var user_names = '';
            
            function print_legend(item, index) {
                document.getElementById('legend').innerHTML = document.getElementById('legend').innerHTML + "<div style='padding: 0px; display: inline-block;'><div style='border-bottom: 1px solid gray;'><font color='#2077B4'><b>" + index + "</b></font></div><div style='padding: 15px;'>" + item+ "<br>" + user_names[index] + "<br>" + process_names[index] + "</div></div>";
            }
            
            function get_data(index, server) {                
                $.ajax({
                    url: "get_utilization.php",
                    type: "POST",
                    data: ({server: server}),
                    dataType: 'json',
                    success: function (response) {
                        cpu[index] = response.cpu;
                        gpu[index] = response.gpu;
                        gpu_memory[index] = response.gpu_memory;
                        memory[index] = response.memory;
                        user[index] = response.user;
                        process[index] = response.process;
                    },
                   error: function (xhr, ajaxOptions, thrownError) {
                       if(cpu[index] == null) {
                           cpu[index] = 0;
                       }
                       if(gpu[index] == null) {
                           gpu[index] = 0;
                       }
                       if(memory[index] == null) {
                           memory[index] = 0;
                       }
                       if(gpu_memory[index] == null) {
                           gpu_memory[index] = 0;
                       }
                       if(user[index] == null) {
                           user[index] = 'Error';
                       }
                       if(process[index] == null) {
                           process[index] = 'Error';
                       }
                   }
                });
            }

            function redraw() {
                document.getElementById("chart_waiting").style.display = "none";
                document.getElementById('legend').innerHTML = "";
                document.getElementById('legend_left').innerHTML = "<div style='border-bottom: 1px solid gray; border-right: 1px solid gray;'>&nbsp;</div><div style='padding: 10px;  padding-left: 15px; padding-right:15px; border-right: 1px solid gray;'><font color='#2077B4'><b>User</b></font><br><font color='#2077B4'><b>Process</b></font></div>";                
                
                for(var i = 0; i < cpu.length; i++) {
                    document.getElementById('legend').innerHTML = document.getElementById('legend').innerHTML + "<div style='padding: 0px; display: inline-block;'><div style='border-bottom: 1px solid gray;'><font color='#2077B4'><b>" + server_names[i] + "</b></font></div><div style='padding: 10px; padding-left: 15px; padding-right:15px;'>" + user[i] + "<br>" + process[i] + "</div></div>";
                }
                
                chart.load({
                    columns: [
                        ['data1'].concat(cpu),
                        ['data2'].concat(memory),
                        ['data3'].concat(gpu),
                        ['data4'].concat(gpu_memory)
                    ]
                }); 
                
            }
            
            function scroll_cpu() {
                $('html, body').animate({ scrollLeft:  $(main_cpu).offset().left}, 'fast');
            }
        </script>
    </head>
    <body onload="on_page_load();">
        <div id='header'>
            <h2 style='padding: 0px; margin:0px;'>Server Utilization</h2>
        </div>
        <div id='footer'>
            <small>This website updates its content every 15 sec</small>
            <div style='margin-right: 4px; position: absolute; bottom: 10px; right: 10px;'><small>Version 0.2</small></div>
        </div>
        <div id='main'>
            <div style='padding-bottom: 20px; position: relative; line-height: 2; margin:auto; width:85%; text-align: center;'>
                <div id='chart_waiting' style='width: 100%; position: absolute; top:0; left: 0;'><br><br><i>Retrieving Data ...<br></i><img src='loading.gif' width='60px'></div>
                <div style='position: absolute; top:0; left: 0;' id="chart"></div>
            </div>
            <div style='line-height: 2; margin:auto; text-align: center;' id="legend_main">
                <div style='padding: 0px; display: inline-block;' id='legend_left'></div><div  style='padding: 0px; display: inline-block;' id="legend"></div>
            </div>
        </div>
    </body>
</html>
