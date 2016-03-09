# CPU_Usage_Plotter
This is a little PhP script that I wrote for our Institute. It plots the CPU Utilization and GPU Memory Usage for all our Server in a nice Bar Diagram. Changes are detected every 15 seconds and thanks to the nice C3 Library very fluent.

![alt tag](https://raw.githubusercontent.com/cpoetter/CPU_Usage_Plotter/master/Screenshot.png)

Dependencies are:
- NetSSH2
- jQuery
- Ajax
- C3
- D3

All our Server are run Ubuntu, I think it should also work for Macintosh Server. To employ it for Windows Server you would probably need to change the commands which read our the CPU and GPU usage. The plotting itself should be fine.
