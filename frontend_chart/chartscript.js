window.onload = function() {
        fetch('../request_timeseries.csv')
        .then(response => {
            if (!response.ok) {
                throw new Error('CSV file not found (Run analyze.php first)');
            }
            return response.text();
        })
        .then(data => {
            var rows = data.split('\n').slice(1);
            var x = [];
            var y = [];
            rows.forEach(row => {
                var columns = row.split(',');
                x.push(columns[0]);
                y.push(columns[1]);
            });

            var trace = {
                x: x,
                y: y,
                type: 'bar'
            };

            var layout = {
                title: 'Request Count Over Time',
                xaxis: {
                    title: 'Timestamp',
                    type: 'category',
                    showticklabels: false
                },
                yaxis: {
                    title: 'Request Count',
                    autorange: true
                },
                annotations: [{
                    xref: 'paper',
                    yref: 'paper',
                    x: 0.0,
                    y: 1.05,
                    xanchor: 'left',
                    yanchor: 'bottom',
                    text: 'Requests from 2023-11-21 14:46:40 to 2023-11-20 14:52:3 in 10 second intervals.',
                    showarrow: false
                }]
            };

            Plotly.newPlot('chart', [trace], layout);
        })
        .catch(error => {
            document.body.innerHTML = '<p>Unable to load data: ' + error.message + '</p>';
        });
};