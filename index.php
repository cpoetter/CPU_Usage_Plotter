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

            .c3-legend-item, .c3-axis-x-label, .c3-axis-y-label {
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
                padding-top: 15px;
                -webkit-box-shadow: 0px 2px 5px 2px rgba(0, 0, 0, 0.35);
                -moz-box-shadow: 0px 2px 5px 2px rgba(0, 0, 0, 0.35);
                box-shadow: 4px 2px 5px 2px rgba(0, 0, 0, 0.35);
                color: white;
            }
            #main {
                width: 200%;
                position: absolute;
                left: 0px;
                padding: 0px;
                margin: 0px;
                margin-top: 40px;
            }

            #main_cpu, #main_gpu {
                display: inline-block;
                width: 49.9%;
                padding: 0px;
                margin: 0px;
                vertical-align: top;
            }
        </style>

        <script type="text/javascript">

            var chart = '';
            var chart_gpu = '';
            
            function on_page_load() {
                            
                chart = c3.generate({
                data: {
                    columns: [
                            ['data1', 0]
                        ],
                        type: 'bar',
                        names: {
                            data1: 'Server ID'
                        },
                    },
                    axis: {
                        y: {
                            label: {
                                text: 'Utilization',
                                position: 'outer-middle'
                            },
                            max: 100,
                            min: 0,
                            padding: {top:0, bottom:0}
                        }
                    }
                });
                
                
                chart_gpu = c3.generate({
                bindto: '#chart_gpu',
                data: {
                    columns: [
                            ['data1', 0]
                        ],
                        type: 'bar',
                        names: {
                            data1: 'Server ID'
                        },
                    },
                    color: {
                        pattern: ['#b42020']
                    },
                    axis: {
                        y: {
                            label: {
                                text: 'Utilization',
                                position: 'outer-middle'
                            },
                            max: 100,
                            min: 0,
                            padding: {top:0, bottom:0}
                        }
                    }
                });
                
                get_data();
                get_gpu();
                window.setInterval(function(){
                    get_data();
                    get_gpu();
                }, 15000);
            }
            
            var process_names = '';
            var user_names = '';
            
            function print_legend(item, index) {
                document.getElementById('legend').innerHTML = document.getElementById('legend').innerHTML + "<div style='padding: 0px; display: inline-block;'><div style='border-bottom: 1px solid gray;'><font color='#2077B4'><b>" + index + "</b></font></div><div style='padding: 15px;'>" + item+ "<br>" + user_names[index] + "<br>" + process_names[index] + "</div></div>";
            }
            
            function get_data() {
                $.ajax({
                       url: "get_data.php",
                       type: "POST",
                       dataType: 'json',
                       success: function (response) {

                        document.getElementById("chart_waiting").style.display = "none";
                        document.getElementById('legend').innerHTML = "";
                        document.getElementById('legend_left').innerHTML = "<div style='border-bottom: 1px solid gray; border-right: 1px solid gray;'>&nbsp;</div><div style='padding: 15px; border-right: 1px solid gray;'><font color='#2077B4'><b>Server</b></font><br><font color='#2077B4'><b>User</b></font><br><font color='#2077B4'><b>Process</b></font></div>";
                           
                        var server_names = JSON.parse(response.names);
                        user_names = JSON.parse(response.user);   
                        process_names = JSON.parse(response.process);   
                        server_names.forEach(print_legend);
                        
                        chart.load({
                            columns: [
                                ['data1'].concat(JSON.parse(response.data))
                            ]
                        });
                           
                           
                       },
                       error: function (xhr, ajaxOptions, thrownError) {
                       document.getElementById('chart').innerHTML = "An Error occured.";
                       }
                       });
            }

            
            function print_legend_gpu(item, index) {
                document.getElementById('legend_gpu').innerHTML = document.getElementById('legend_gpu').innerHTML + "<div style='padding: 0px; display: inline-block;'><div style='border-bottom: 1px solid gray;'><font color='#b42020'><b>" + index + "</b></font></div><div style='padding: 15px;'>" + item+ "<br></div></div>";
            }
            
             function get_gpu() {
                $.ajax({
                       url: "get_gpu.php",
                       type: "POST",
                       dataType: 'json',
                       success: function (response) {

                        document.getElementById("chart_waiting_gpu").style.display = "none";
                        document.getElementById('legend_gpu').innerHTML = "";
                        document.getElementById('legend_left_gpu').innerHTML = "<div style='border-bottom: 1px solid gray; border-right: 1px solid gray;'>&nbsp;</div><div style='padding: 15px; border-right: 1px solid gray;'><font color='#b42020'><b>Server</b></font></div>";
                           
                        var server_names = JSON.parse(response.names);
                        server_names.forEach(print_legend_gpu);
                        

                        chart_gpu.load({
                            columns: [
                                ['data1'].concat(JSON.parse(response.data))
                            ]
                        });
                           
                       },
                       error: function (xhr, ajaxOptions, thrownError) {
                       document.getElementById('chart_gpu').innerHTML = "An Error occured.";
                       }
                       });
            }


            function scroll_gpu() {
                $('html, body').animate({ scrollLeft:  $(main_gpu).offset().left}, 'fast');
            }
            function scroll_cpu() {
                $('html, body').animate({ scrollLeft:  $(main_cpu).offset().left}, 'fast');
            }
        </script>
    </head>
    <body onload="on_page_load();">
        <div id='header'><a href='javascript: scroll_cpu();'><b>CPU</b></a> &nbsp; &nbsp; &nbsp; <a href='javascript: scroll_gpu();'><b>GPU</b></a></div>
        <div id='footer'>
            <small>This website updates its content every 15 sec</small>
        </div>
        <div id='main'>
            <div id='main_cpu'>
                <h2 style='text-align:center;'>Server CPU Utilization</h2>
                <div style='position: relative; line-height: 2; margin:auto; width:70%; text-align: center;'>
                    <div id='chart_waiting' style='width: 100%; position: absolute; top:0; left: 0;'><br><br><i>Retrieving Data ...<br></i><img src='loading.gif' width='60px'></div>
                    <div style='position: absolute; top:0; left: 0;' id="chart"></div>
                </div>
                <div style='line-height: 2; margin:auto; text-align: center;' id="legend_main">
                    <div style='padding: 0px; display: inline-block;' id='legend_left'></div><div  style='padding: 0px; display: inline-block;' id="legend"></div>
                </div>
            </div><div id='main_gpu'>
                <h2 style='text-align:center;'>Server GPU Memory Utilization</h2>
                <div style='position: relative; line-height: 2; margin:auto; width:70%; text-align: center;'>
                    <div id='chart_waiting_gpu' style='width: 100%; position: absolute; top:0; left: 0;'><br><br><i>Retrieving Data ...<br></i><img src='loading.gif' width='60px'></div>
                    <div style='position: absolute; top:0; left: 0;' id="chart_gpu"></div>
                </div>
                <div style='line-height: 2; margin:auto; text-align: center;' id="legend_main_gpu">
                    <div style='padding: 0px; display: inline-block;' id='legend_left_gpu'></div><div  style='padding: 0px; display: inline-block;' id="legend_gpu"></div>
                </div>
            </div>
        </div>
    </body>
</html>
