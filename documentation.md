# Traffic Log Analysis


## Requirements

- PHP 7.4+

## Installation
 no dependencies need to be installed.


## Guide
-  Run preprocess.php to generate firewall-logs-fixed.csv with  the fixed  "User Agent" column changed (include firewall-logs.csv to the project first)
-  Run analyze.php to generate reports of the analysis( You need to run preprocess.php first)
-  Open chart.html to view chart generated from request_timeseries.csv